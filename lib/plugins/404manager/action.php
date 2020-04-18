<?php

if (!defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
// Needed for the page lookup
require_once(DOKU_INC . 'inc/fulltext.php');
// Needed to get the redirection manager
require_once(DOKU_PLUGIN . 'action.php');

class action_plugin_404manager extends DokuWiki_Action_Plugin
{


    var $targetId = '';
    var $sourceId = '';

    // The redirect source
    const REDIRECT_TARGET_PAGE_FROM_DATASTORE = 'dataStore';
    const REDIRECT_EXTERNAL = 'External';
    const REDIRECT_SOURCE_START_PAGE = 'startPage';
    const REDIRECT_SOURCE_BEST_PAGE_NAME = 'bestPageName';
    const REDIRECT_SOURCE_BEST_NAMESPACE = 'bestNamespace';
    const REDIRECT_SEARCH_ENGINE = 'searchEngine';

    // The constant parameters
    const GO_TO_SEARCH_ENGINE = 'GoToSearchEngine';
    const GO_TO_BEST_NAMESPACE = 'GoToBestNamespace';
    const GO_TO_BEST_PAGE_NAME = 'GoToBestPageName';
    const GO_TO_NS_START_PAGE = 'GoToNsStartPage';
    const GO_TO_EDIT_MODE = 'GoToEditMode';
    const NOTHING = 'Nothing';

    /**
     * The object that holds all management function
     * @var admin_plugin_404manager
     */
    var $redirectManager;

    /*
     * The event scope is made object
     */
    var $event;

    // The action name is used as check / communication channel between function hooks.
    // It will comes in the global $ACT variable
    const ACTION_NAME = '404manager';

    // The name in the session variable
    const MANAGER404_MSG = '404manager_msg';

    // To identify the object
    private $objectId;

    // Query String variable name to send the redirection message
    const QUERY_STRING_ORIGIN_PAGE = '404id';
    const QUERY_STRING_REDIR_TYPE = '404type';

    // Message
    private $message;


    function __construct()
    {
        // enable direct access to language strings
        $this->setupLocale();
        require_once(dirname(__FILE__) . '/Message404.php');
        $this->message = new Message404();
    }


    function register(Doku_Event_Handler $controller)
    {

        $this->objectId = spl_object_hash($this);

        /* This will call the function _handle404 */
        $controller->register_hook('DOKUWIKI_STARTED',
            'AFTER',
            $this,
            '_handle404',
            array());

        /* This will call the function _displayRedirectMessage */
        $controller->register_hook(
            'TPL_ACT_RENDER',
            'BEFORE',
            $this,
            '_displayRedirectMessage',
            array()
        );

    }

    /**
     * Verify if there is a 404
     * Inspiration comes from <a href="https://github.com/splitbrain/dokuwiki-plugin-notfound/blob/master/action.php">Not Found Plugin</a>
     * @param $event Doku_Event
     * @param $param
     * @return bool not required
     * @throws Exception
     */
    function _handle404(&$event, $param)
    {

        global $ACT;
        if ($ACT != 'show') return false;

        global $INFO;
        if ($INFO['exists']) return false;

        // We instantiate the redirect manager because it's use overall
        // it holds the function and methods
        require_once(dirname(__FILE__) . '/admin.php');
        if ($this->redirectManager == null) {
            $this->redirectManager = admin_plugin_404manager::get();
        }
        // Event is also used in some sub-function, we make it them object scope
        $this->event = $event;


        // Global variable needed in the process
        global $ID;
        global $conf;
        $targetPage = $this->redirectManager->getRedirectionTarget($ID);

        // If this is an external redirect
        if ($this->redirectManager->isValidURL($targetPage) && $targetPage) {

            $this->redirectToExternalPage($targetPage);
            return true;

        }

        // Internal redirect

        // Their is one action for a writer:
        //   * edit mode direct
        // If the user is a writer (It have the right to edit).
        If ($this->userCanWrite() && $this->getConf(self::GO_TO_EDIT_MODE) == 1) {

            $this->gotToEditMode($event);
            // Stop here
            return true;

        }

        // This is a reader
        // Their are only three actions for a reader:
        //   * redirect to a page (show another page id)
        //   * go to the search page
        //   * do nothing

        // If the page exist
        if (page_exists($targetPage)) {

            $this->redirectToDokuwikiPage($targetPage, self::REDIRECT_TARGET_PAGE_FROM_DATASTORE);
            return true;

        }

        // We are still a reader, the redirection does not exist the user not allowed to edit the page (public of other)
        if ($this->getConf('ActionReaderFirst') == self::NOTHING) {
            return true;
        }

        // We are reader and their is no redirection set, we apply the algorithm
        $readerAlgorithms = array();
        $readerAlgorithms[0] = $this->getConf('ActionReaderFirst');
        $readerAlgorithms[1] = $this->getConf('ActionReaderSecond');
        $readerAlgorithms[2] = $this->getConf('ActionReaderThird');

        $i = 0;
        while (isset($readerAlgorithms[$i])) {

            switch ($readerAlgorithms[$i]) {

                case self::NOTHING:
                    return true;
                    break;

                case self::GO_TO_NS_START_PAGE:

                    // Start page with the conf['start'] parameter
                    $startPage = getNS($ID) . ':' . $conf['start'];
                    if (page_exists($startPage)) {
                        $this->redirectToDokuwikiPage($startPage, self::REDIRECT_SOURCE_START_PAGE);
                        return true;
                    }
                    // Start page with the same name than the namespace
                    $startPage = getNS($ID) . ':' . curNS($ID);
                    if (page_exists($startPage)) {
                        $this->redirectToDokuwikiPage($startPage, self::REDIRECT_SOURCE_START_PAGE);
                        return true;
                    }
                    break;

                case self::GO_TO_BEST_PAGE_NAME:

                    $bestPageId = null;


                    $bestPage = $this->getBestPage($ID);
                    $bestPageId = $bestPage['id'];
                    $scorePageName = $bestPage['score'];

                    // Get Score from a Namespace
                    $bestNamespace = $this->scoreBestNamespace($ID);
                    $bestNamespaceId = $bestNamespace['namespace'];
                    $namespaceScore = $bestNamespace['score'];

                    // Compare the two score
                    if ($scorePageName > 0 or $namespaceScore > 0) {
                        if ($scorePageName > $namespaceScore) {
                            $this->redirectToDokuwikiPage($bestPageId, self::REDIRECT_SOURCE_BEST_PAGE_NAME);
                        } else {
                            $this->redirectToDokuwikiPage($bestNamespaceId, self::REDIRECT_SOURCE_BEST_PAGE_NAME);
                        }
                        return true;
                    }
                    break;

                case self::GO_TO_BEST_NAMESPACE:

                    $scoreNamespace = $this->scoreBestNamespace($ID);
                    $bestNamespaceId = $scoreNamespace['namespace'];
                    $score = $scoreNamespace['score'];

                    if ($score > 0) {
                        $this->redirectToDokuwikiPage($bestNamespaceId, self::REDIRECT_SOURCE_BEST_NAMESPACE);
                        return true;
                    }
                    break;

                case self::GO_TO_SEARCH_ENGINE:

                    $this->redirectToSearchEngine();

                    return true;
                    break;

                // End Switch Action
            }

            $i++;
            // End While Action
        }
        // End if not connected

        return true;

    }


    /**
     * Main function; dispatches the visual comment actions
     * @param   $event Doku_Event
     */
    function _displayRedirectMessage(&$event, $param)
    {

        // After a redirect to another page via query string ?
        global $INPUT;
        // Comes from method redirectToDokuwikiPage
        $pageIdOrigin = $INPUT->str(self::QUERY_STRING_ORIGIN_PAGE);

        if ($pageIdOrigin) {

            $redirectSource = $INPUT->str(self::QUERY_STRING_REDIR_TYPE);

            switch ($redirectSource) {

                case self::REDIRECT_TARGET_PAGE_FROM_DATASTORE:
                    $this->message->addContent(sprintf($this->lang['message_redirected_by_redirect'], hsc($pageIdOrigin)));
                    $this->message->setType(Message404::TYPE_CLASSIC);
                    break;

                case self::REDIRECT_SOURCE_START_PAGE:
                    $this->message->addContent(sprintf($this->lang['message_redirected_to_startpage'], hsc($pageIdOrigin)));
                    $this->message->setType(Message404::TYPE_WARNING);
                    break;

                case  self::REDIRECT_SOURCE_BEST_PAGE_NAME:
                    $this->message->addContent(sprintf($this->lang['message_redirected_to_bestpagename'], hsc($pageIdOrigin)));
                    $this->message->setType(Message404::TYPE_WARNING);
                    break;

                case self::REDIRECT_SOURCE_BEST_NAMESPACE:
                    $this->message->addContent(sprintf($this->lang['message_redirected_to_bestnamespace'], hsc($pageIdOrigin)));
                    $this->message->setType(Message404::TYPE_WARNING);
                    break;

                case self::REDIRECT_SEARCH_ENGINE:
                    $this->message->addContent(sprintf($this->lang['message_redirected_to_searchengine'], hsc($pageIdOrigin)));
                    $this->message->setType(Message404::TYPE_WARNING);
                    break;

            }

            // Add a list of page with the same name to the message
            // if the redirections is not planned
            if ($redirectSource!=self::REDIRECT_TARGET_PAGE_FROM_DATASTORE) {
                $this->addToMessagePagesWithSameName($pageIdOrigin);
            }

        }

        if ($event->data == 'show' || $event->data == 'edit' || $event->data == 'search') {

            $this->printMessage($this->message);

        }
    }


    /**
     * getBestNamespace
     * Return a list with 'BestNamespaceId Score'
     * @param $id
     * @return array
     */
    private function scoreBestNamespace($id)
    {

        global $conf;

        // Parameters
        $pageNameSpace = getNS($id);

        // If the page has an existing namespace start page take it, other search other namespace
        $startPageNameSpace = $pageNameSpace . ":";
        $dateAt = '';
        // $startPageNameSpace will get a full path (ie with start or the namespace
        resolve_pageid($pageNameSpace, $startPageNameSpace, $exists, $dateAt, true);
        if (page_exists($startPageNameSpace)) {
            $nameSpaces = array($startPageNameSpace);
        } else {
            $nameSpaces = ft_pageLookup($conf['start']);
        }

        // Parameters and search the best namespace
        $pathNames = explode(':', $pageNameSpace);
        $bestNbWordFound = 0;
        $bestNamespaceId = '';
        foreach ($nameSpaces as $nameSpace) {

            $nbWordFound = 0;
            foreach ($pathNames as $pathName) {
                if (strlen($pathName) > 2) {
                    $nbWordFound = $nbWordFound + substr_count($nameSpace, $pathName);
                }
            }
            if ($nbWordFound > $bestNbWordFound) {
                // Take only the smallest namespace
                if (strlen($nameSpace) < strlen($bestNamespaceId) or $nbWordFound > $bestNbWordFound) {
                    $bestNbWordFound = $nbWordFound;
                    $bestNamespaceId = $nameSpace;
                }
            }
        }

        $startPageFactor = $this->getConf('WeightFactorForStartPage');
        $nameSpaceFactor = $this->getConf('WeightFactorForSameNamespace');
        if ($bestNbWordFound > 0) {
            $bestNamespaceScore = $bestNbWordFound * $nameSpaceFactor + $startPageFactor;
        } else {
            $bestNamespaceScore = 0;
        }


        return array(
            'namespace' => $bestNamespaceId,
            'score' => $bestNamespaceScore
        );

    }

    /**
     * @param $event
     */
    private function gotToEditMode(&$event)
    {
        global $ID;
        global $conf;


        global $ACT;
        $ACT = 'edit';

        // If this is a side bar no message.
        // There is always other page with the same name
        $pageName = noNS($ID);
        if ($pageName != $conf['sidebar']) {

            if ($this->getConf('ShowMessageClassic') == 1) {
                $this->message->addContent($this->lang['message_redirected_to_edit_mode']);
                $this->message->setType(Message404::TYPE_CLASSIC);
            }

            // If Param show page name unique and it's not a start page
            $this->addToMessagePagesWithSameName($ID);


        }


    }

    /**
     * Return if the user has the right/permission to create/write an article
     * @return bool
     */
    private function userCanWrite()
    {
        global $ID;

        if ($_SERVER['REMOTE_USER']) {
            $perm = auth_quickaclcheck($ID);
        } else {
            $perm = auth_aclcheck($ID, '', null);
        }

        if ($perm >= AUTH_EDIT) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Redirect to an internal page, no external resources
     * @param $targetPage the target page id or an URL
     * @param string|the $redirectSource the source of the redirect
     * @throws Exception
     */
    private function redirectToDokuwikiPage($targetPage, $redirectSource = 'Not Known')
    {

        global $ID;

        //If the user have right to see the target page
        if ($_SERVER['REMOTE_USER']) {
            $perm = auth_quickaclcheck($targetPage);
        } else {
            $perm = auth_aclcheck($targetPage, '', null);
        }
        if ($perm <= AUTH_NONE) {
            return;
        }

        // TODO: Create a cache table ? with the source, target and type of redirections ?
        // if (!$this->redirectManager->isRedirectionPresent($ID)) {
        //    $this->redirectManager->addRedirection($ID, $targetPage);
        //}

        // Redirection
        $this->redirectManager->logRedirection($ID, $targetPage, $redirectSource);

        // Explode the page ID and the anchor (#)
        $link = explode('#', $targetPage, 2);

        // Query String to pass the message
        $urlParams = array(
            self::QUERY_STRING_ORIGIN_PAGE => $ID,
            self::QUERY_STRING_REDIR_TYPE => $redirectSource
        );

        // TODO: Status code
        // header('HTTP/1.1 301 Moved Permanently') will cache it in the browser !!!

        $wl = wl($link[0], $urlParams, true, '&');
        if ($link[1]) {
            $wl .= '#' . rawurlencode($link[1]);
        }
        send_redirect($wl);

    }

    /**
     * Redirect to an internal page, no external resources
     * @param string $url target page id or an URL
     */
    private function redirectToExternalPage($url)
    {

        global $ID;

        // No message can be shown because this is an external URL

        // Update the redirections
        $this->redirectManager->logRedirection($ID, $url, self::REDIRECT_EXTERNAL);

        // TODO: Status code
        // header('HTTP/1.1 301 Moved Permanently');
        send_redirect($url);

        if (defined('DOKU_UNITTEST')) return; // no exits during unit tests
        exit();

    }

    /**
     * @param $id
     * @return array
     */
    private function getBestPage($id)
    {

        // The return parameters
        $bestPageId = null;
        $scorePageName = null;

        // Get Score from a page
        $pageName = noNS($id);
        $pagesWithSameName = ft_pageLookup($pageName);
        if (count($pagesWithSameName) > 0) {

            // Search same namespace in the page found than in the Id page asked.
            $bestNbWordFound = 0;


            $wordsInPageSourceId = explode(':', $id);
            foreach ($pagesWithSameName as $targetPageId => $title) {

                // Nb of word found in the target page id
                // that are in the source page id
                $nbWordFound = 0;
                foreach ($wordsInPageSourceId as $word) {
                    $nbWordFound = $nbWordFound + substr_count($targetPageId, $word);
                }

                if ($bestPageId == null) {

                    $bestNbWordFound = $nbWordFound;
                    $bestPageId = $targetPageId;

                } else {

                    if ($nbWordFound >= $bestNbWordFound && strlen($bestPageId) > strlen($targetPageId)) {

                        $bestNbWordFound = $nbWordFound;
                        $bestPageId = $targetPageId;

                    }

                }

            }
            $scorePageName = $this->getConf('WeightFactorForSamePageName') + ($bestNbWordFound - 1) * $this->getConf('WeightFactorForSameNamespace');
            return array(
                'id' => $bestPageId,
                'score' => $scorePageName);
        }
        return array(
            'id' => $bestPageId,
            'score' => $scorePageName
        );

    }

    /**
     * Add the page with the same page name but in an other location
     * @param $pageId
     */
    private function addToMessagePagesWithSameName($pageId)
    {

        global $conf;

        $pageName = noNS($pageId);
        if ($this->getConf('ShowPageNameIsNotUnique') == 1 && $pageName <> $conf['start']) {

            //Search same page name
            $pagesWithSameName = ft_pageLookup($pageName);

            if (count($pagesWithSameName) > 0) {

                $this->message->setType(Message404::TYPE_WARNING);

                // Assign the value to a variable to be able to use the construct .=
                if ($this->message->getContent() <> '') {
                    $this->message->addContent('<br/><br/>');
                }
                $this->message->addContent($this->lang['message_pagename_exist_one']);
                $this->message->addContent('<ul>');

                $i = 0;
                foreach ($pagesWithSameName as $PageId => $title) {
                    $i++;
                    if ($i > 10) {
                        $this->message->addContent('<li>' .
                            tpl_link(
                                "doku.php?id=" . $pageId . "&do=search&q=" . rawurldecode($pageName),
                                "More ...",
                                'class="" rel="nofollow" title="More..."',
                                $return = true
                            ) . '</li>');
                        break;
                    }
                    if ($title == null) {
                        $title = $PageId;
                    }
                    $this->message->addContent('<li>' .
                        tpl_link(
                            wl($PageId),
                            $title,
                            'class="" rel="nofollow" title="' . $title . '"',
                            $return = true
                        ) . '</li>');
                }
                $this->message->addContent('</ul>');
            }
        }
    }

    /**
     * @param $message
     */
    private function printMessage($message): void
    {
        if ($this->message->getContent() <> "") {
            $pluginInfo = $this->getInfo();
            // a class can not start with a number then 404manager is not a valid class name
            $redirectManagerClass = "redirect-manager";

            if ($this->message->getType() == Message404::TYPE_CLASSIC) {
                ptln('<div class="alert alert-success ' . $redirectManagerClass . '" role="alert">');
            } else {
                ptln('<div class="alert alert-warning ' . $redirectManagerClass . '" role="alert">');
            }

            print $this->message->getContent();


            print '<div class="managerreference">' . $this->lang['message_come_from'] . ' <a href="' . $pluginInfo['url'] . '" class="urlextern" title="' . $pluginInfo['desc'] . '"  rel="nofollow">' . $pluginInfo['name'] . '</a>.</div>';
            print('</div>');
        }
    }

    /**
     * Redirect to the search engine
     */
    private function redirectToSearchEngine()
    {

        global $ID;

        $replacementPart = array(':', '_', '-');
        $query = str_replace($replacementPart, ' ', $ID);

        $urlParams = array(
            "do" => "search",
            "q" => $query,
            self::QUERY_STRING_ORIGIN_PAGE => $ID,
            self::QUERY_STRING_REDIR_TYPE => self::REDIRECT_SEARCH_ENGINE
        );

        // TODO: Status code ?
        // header('HTTP/1.1 301 Moved Permanently') will cache it in the browser !!!

        $url = wl($ID, $urlParams, true, '&');

        send_redirect($url);

    }


}
