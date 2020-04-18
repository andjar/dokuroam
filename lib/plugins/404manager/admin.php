<?php
// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');

// Surprisingly there is no constant for the info level
if (!defined('MANAGER404_MSG_ERROR')) define('MANAGER404_MSG_ERROR', -1);
if (!defined('MANAGER404_MSG_INFO')) define('MANAGER404_MSG_INFO', 0);
if (!defined('MANAGER404_MSG_SUCCESS')) define('MANAGER404_MSG_SUCCESS', 1);
if (!defined('MANAGER404_MSG_NOTIFY')) define('MANAGER404_MSG_NOTIFY', 2);

require_once(DOKU_PLUGIN . 'admin.php');
require_once(DOKU_INC . 'inc/parser/xhtml.php');

/**
 * All DokuWiki plugins to extend the admin function
 * need to inherit from this class
 *
 */
class admin_plugin_404manager extends DokuWiki_Admin_Plugin
{

    // A static function to hold the 404 manager
    private static $manager404 = null;

    // Data Store Type
    // The Data Store Type variable
    private $dataStoreType;

    // The Data Store Type possible value
    const DATA_STORE_TYPE_CONF_FILE = 'confFile';
    const DATA_STORE_TYPE_SQLITE = 'sqlite';


    // Variable var and not public/private because php4 can't handle this kind of variable

    // ###################################
    // Data Stored in a conf file
    // Deprecated
    // ###################################
    // The file path of the direct redirection (from an Page to a Page or URL)
    // No more used, replaced by a sqlite database
    const DATA_STORE_CONF_FILE_PATH = __DIR__ . "/404managerRedirect.conf";
    // The content of the conf file in memory
    var $pageRedirections = array();


    // Use to pass parameter between the handle and the html function to keep the form data
    var $redirectionSource = '';
    var $redirectionTarget = '';
    var $currentDate = '';
    // Deprecated
    private $redirectionType;
    // Deprecated
    var $isValidate = '';
    // Deprecated
    var $targetResourceType = 'Default';

    private $infoPlugin;

    /** @var helper_plugin_sqlite $sqlite */
    private $sqlite;

    // Name of the variable in the HTML form
    const FORM_NAME_SOURCE_PAGE = 'SourcePage';
    const FORM_NAME_TARGET_PAGE = 'TargetPage';


    /**
     * admin_plugin_404manager constructor.
     *
     * Use the get function instead
     */
    public function __construct()
    {

        // enable direct access to language strings
        // of use of $this->getLang
        $this->setupLocale();
        $this->currentDate = date("c");
        $this->infoPlugin = $this->getInfo();


    }

    /**
     * @return admin_plugin_404manager
     */
    public static function get()
    {
        if (self::$manager404 == null) {
            self::$manager404 = new admin_plugin_404manager();
        }
        return self::$manager404;
    }


    /**
     * Access for managers allowed
     */
    function forAdminOnly()
    {
        return false;
    }

    /**
     * return sort order for position in admin menu
     */
    function getMenuSort()
    {
        return 140;
    }

    /**
     * return prompt for admin menu
     * @param string $language
     * @return string
     */
    function getMenuText($language)
    {
        $menuText = $this->lang['AdminPageName'];
        if ($menuText == '') {
            $menuText = $this->infoPlugin['name'];
        }
        return $menuText;
    }

    /**
     * handle user request
     */
    function handle()
    {

        if ($_POST['Add']) {

            $this->redirectionSource = $_POST[self::FORM_NAME_SOURCE_PAGE];
            $this->redirectionTarget = $_POST[self::FORM_NAME_TARGET_PAGE];

            if ($this->redirectionSource == $this->redirectionTarget) {
                msg($this->lang['SameSourceAndTargetAndPage'] . ': ' . $this->redirectionSource . '', -1);
                return;
            }


            // This a direct redirection
            // If the source page exist, do nothing
            if (page_exists($this->redirectionSource)) {

                $title = false;
                global $conf;
                if ($conf['useheading']) {
                    $title = p_get_first_heading($this->redirectionSource);
                }
                if (!$title) $title = $this->redirectionSource;
                msg($this->lang['SourcePageExist'] . ' : <a href="' . wl($this->redirectionSource) . '">' . hsc($title) . '</a>', -1);
                return;

            } else {

                // Is this a direct redirection to a valid target page
                if (!page_exists($this->redirectionTarget)) {

                    if ($this->isValidURL($this->redirectionTarget)) {

                        $this->targetResourceType = 'Url';

                    } else {

                        msg($this->lang['NotInternalOrUrlPage'] . ': ' . $this->redirectionTarget . '', -1);
                        return;

                    }

                } else {

                    $this->targetResourceType = 'Internal Page';

                }
                $this->addRedirection($this->redirectionSource, $this->redirectionTarget);
                msg($this->lang['Saved'], 1);

            }


        }

        if ($_POST['Delete']) {

            $redirectionId = $_POST['SourcePage'];
            $this->deleteRedirection($redirectionId);
            msg($this->lang['Deleted'], 1);

        }
        if ($_POST['Validate']) {
            $redirectionId = $_POST['SourcePage'];
            $this->validateRedirection($redirectionId);
            msg($this->lang['Validated'], 1);
        }
    }

    /**
     * output appropriate html
     */
    function html()
    {

        global $conf;

        echo $this->locale_xhtml('intro');

        // Add a redirection
        ptln('<h2><a name="add_redirection" id="add_redirection">' . $this->lang['AddModifyRedirection'] . '</a></h2>');
        ptln('<div class="level2">');
        ptln('<form action="" method="post">');
        ptln('<table class="inline">');

        ptln('<thead>');
        ptln('		<tr><th>' . $this->lang['Field'] . '</th><th>' . $this->lang['Value'] . '</th> <th>' . $this->lang['Information'] . '</th></tr>');
        ptln('</thead>');

        ptln('<tbody>');
        ptln('		<tr><td><label for="add_sourcepage" >' . $this->lang['source_page'] . ': </label></td><td><input type="text" id="add_sourcepage" name="' . self::FORM_NAME_SOURCE_PAGE . '" value="' . $this->redirectionSource . '" class="edit" /></td><td>' . $this->lang['source_page_info'] . '</td></td></tr>');
        ptln('		<tr><td><label for="add_targetpage" >' . $this->lang['target_page'] . ': </label></td><td><input type="text" id="add_targetpage" name="' . self::FORM_NAME_TARGET_PAGE . '" value="' . $this->redirectionTarget . '" class="edit" /></td><td>' . $this->lang['target_page_info'] . '</td></tr>');
        ptln('		<tr>');
        ptln('			<td colspan="3">');
        ptln('				<input type="hidden" name="do"    value="admin" />');
        ptln('				<input type="hidden" name="page"  value="404manager" />');
        ptln('				<input type="submit" name="Add" class="button" value="' . $this->lang['btn_addmodify'] . '" />');
        ptln('			</td>');
        ptln('		</tr>');
        ptln('</tbody>');
        ptln('</table>');
        ptln('</form>');

        // Add the file add from the lang directory
        echo $this->locale_xhtml('add');
        ptln('</div>');


//      List of redirection
        ptln('<h2><a name="list_redirection" id="list_redirection">' . $this->lang['ListOfRedirection'] . '</a></h2>');
        ptln('<div class="level2">');

        ptln('<div class="table-responsive">');

        ptln('<table class="table table-hover">');
        ptln('	<thead>');
        ptln('		<tr>');
        ptln('			<th>&nbsp;</th>');
        ptln('			<th>' . $this->lang['SourcePage'] . '</th>');
        ptln('			<th>' . $this->lang['TargetPage'] . '</th>');
        ptln('			<th>' . $this->lang['CreationDate'] . '</th>');
        ptln('	    </tr>');
        ptln('	</thead>');

        ptln('	<tbody>');


        foreach ($this->getRedirections() as $key => $row) {

            if ($this->dataStoreType == self::DATA_STORE_TYPE_SQLITE) {
                $sourcePageId = $row['SOURCE'];
                $targetPageId = $row['TARGET'];
                $creationDate = $row['CREATION_TIMESTAMP'];
            } else {
                $sourcePageId = $key;
                $targetPageId = $row['TargetPage'];
                $creationDate = $row['CreationDate'];
            }
            $title = false;
            if ($conf['useheading']) {
                $title = p_get_first_heading($targetPageId);
            }
            if (!$title) $title = $targetPageId;


            ptln('	  <tr class="redirect_info">');
            ptln('		<td>');
            ptln('			<form action="" method="post">');
            ptln('				<input type="image" src="' . DOKU_BASE . 'lib/plugins/404manager/images/delete.jpg" name="Delete" title="Delete" alt="Delete" value="Submit" />');
            ptln('				<input type="hidden" name="Delete"  value="Yes" />');
            ptln('				<input type="hidden" name="SourcePage"  value="' . $sourcePageId . '" />');
            ptln('			</form>');

            ptln('		</td>');
            print('	<td>');
            tpl_link(wl($sourcePageId), $this->truncateString($sourcePageId, 30), 'title="' . $sourcePageId . '" class="wikilink2" rel="nofollow"');
            ptln('		</td>');
            print '		<td>';
            tpl_link(wl($targetPageId), $this->truncateString($targetPageId, 30), 'title="' . hsc($title) . ' (' . $targetPageId . ')"');
            ptln('		</td>');
            ptln('		<td>' . $creationDate . '</td>');
            ptln('    </tr>');
        }
        ptln('  </tbody>');
        ptln('</table>');
        ptln('</div>'); //End Table responsive
        ptln('</div>'); // End level 2


    }

    /**
     * Generate a text with a max length of $length
     * and add ... if above
     */
    function truncateString($myString, $length)
    {
        if (strlen($myString) > $length) {
            $myString = substr($myString, 0, $length) . ' ...';
        }
        return $myString;
    }

    /**
     * Delete Redirection
     * @param    string $sourcePageId
     */
    function deleteRedirection($sourcePageId)
    {

        if ($this->dataStoreType == null) {
            $this->initDataStore();
        }

        if ($this->dataStoreType == self::DATA_STORE_TYPE_CONF_FILE) {
            unset($this->pageRedirections[strtolower($sourcePageId)]);
            $this->savePageRedirections();
        } else {

            $res = $this->sqlite->query('delete from redirections where source = ?', $sourcePageId);
            if (!$res) {
                $this->throwRuntimeException("Something went wrong when deleting the redirections");
            }

        }

    }

    /**
     * Is Redirection of a page Id Present
     * @param  string $sourcePageId
     * @return int
     */
    function isRedirectionPresent($sourcePageId)
    {
        $sourcePageId = strtolower($sourcePageId);

        if ($this->dataStoreType == null) {
            $this->initDataStore();
        }

        if ($this->dataStoreType == self::DATA_STORE_TYPE_CONF_FILE) {

            if (isset($this->pageRedirections[$sourcePageId])) {
                return 1;
            } else {
                return 0;
            }

        } else {

            $res = $this->sqlite->query("SELECT * FROM redirections");
            $count = $this->sqlite->res2count($res);
            return $count;

        }

    }

    /**
     * @param $sourcePageId
     * @param $targetPageId
     */
    function addRedirection($sourcePageId, $targetPageId)
    {
        $this->addRedirectionWithDate($sourcePageId, $targetPageId, $this->currentDate);
    }

    /**
     * Add Redirection
     * This function was needed to migrate the date of the file conf store
     * You would use normally the function addRedirection
     * @param string $sourcePageId
     * @param string $targetPageId
     * @param $creationDate
     */
    function addRedirectionWithDate($sourcePageId, $targetPageId, $creationDate)
    {

        // Lower page name is the dokuwiki Id
        $sourcePageId = strtolower($sourcePageId);

        if ($this->dataStoreType == null) {
            $this->initDataStore();
        }

        if ($this->dataStoreType == self::DATA_STORE_TYPE_CONF_FILE) {

            if (isset($this->pageRedirections[$sourcePageId])) {
                $this->throwRuntimeException('Redirection for page (' . $sourcePageId . 'already exist');
            }

            $this->pageRedirections[$sourcePageId]['TargetPage'] = $targetPageId;
            $this->pageRedirections[$sourcePageId]['CreationDate'] = $creationDate;
            // If the call come from the admin page and not from the process function
            if (substr_count($_SERVER['HTTP_REFERER'], 'admin.php')) {

                $this->pageRedirections[$sourcePageId]['IsValidate'] = 'Y';
                $this->pageRedirections[$sourcePageId]['CountOfRedirection'] = 0;
                $this->pageRedirections[$sourcePageId]['LastRedirectionDate'] = $this->lang['Never'];
                $this->pageRedirections[$sourcePageId]['LastReferrer'] = 'Never';

            } else {

                $this->pageRedirections[$sourcePageId]['IsValidate'] = 'N';
                $this->pageRedirections[$sourcePageId]['CountOfRedirection'] = 1;
                $this->pageRedirections[$sourcePageId]['LastRedirectionDate'] = $creationDate;
                if ($_SERVER['HTTP_REFERER'] <> '') {
                    $this->pageRedirections[$sourcePageId]['LastReferrer'] = $_SERVER['HTTP_REFERER'];
                } else {
                    $this->pageRedirections[$sourcePageId]['LastReferrer'] = $this->lang['Direct Access'];
                }

            }

            if (!$this->isValidURL($targetPageId)) {
                $this->pageRedirections[$sourcePageId]['TargetPageType'] = 'Internal Page';
            } else {
                $this->pageRedirections[$sourcePageId]['TargetPageType'] = 'Url';
            }

            $this->savePageRedirections();

        } else {

            // Note the order is important
            // because it's used in the bin of the update statement
            $entry = array(
                'target' => $targetPageId,
                'creation_timestamp' => $creationDate,
                'source' => $sourcePageId
            );

            $statement = 'select * from redirections where source = ?';
            $res = $this->sqlite->query($statement, $sourcePageId);
            $count = $this->sqlite->res2count($res);
            if ($count <> 1) {
                $res = $this->sqlite->storeEntry('redirections', $entry);
                if (!$res) {
                    $this->throwRuntimeException("There was a problem during insertion");
                }
            } else {
                // Primary key constraint, the storeEntry function does not use an UPSERT
                $statement = 'update redirections set target = ?, creation_timestamp = ? where source = ?';
                $res = $this->sqlite->query($statement, $entry);
                if (!$res) {
                    $this->throwRuntimeException("There was a problem during the update");
                }
            }

        }
    }

    /**
     * Validate a Redirection
     * @param    string $sourcePageId
     */
    function validateRedirection($sourcePageId)
    {
        $sourcePageId = strtolower($sourcePageId);

        if ($this->dataStoreType == null) {
            $this->initDataStore();
        }

        if ($this->dataStoreType == self::DATA_STORE_TYPE_CONF_FILE) {

            $this->pageRedirections[$sourcePageId]['IsValidate'] = 'Y';
            $this->savePageRedirections();
        } else {

            $this->throwRuntimeException('Not implemented for a SQLite data store');

        }
    }

    /**
     * Get IsValidate Redirection
     * @param    string $sourcePageId
     * @return string
     */
    function getIsValidate($sourcePageId)
    {
        $sourcePageId = strtolower($sourcePageId);

        if ($this->dataStoreType == null) {
            $this->initDataStore();
        }

        if ($this->dataStoreType == self::DATA_STORE_TYPE_CONF_FILE) {

            if ($this->pageRedirections[$sourcePageId]['IsValidate'] == null) {
                return 'N';
            } else {
                return $this->pageRedirections[$sourcePageId]['IsValidate'];
            }
        } else {

            $this->throwRuntimeException("Not Yet implemented");

        }
    }

    /**
     * Get TargetPageType
     * @param    string $sourcePageId
     * @return
     * @throws Exception
     */
    function getTargetPageType($sourcePageId)
    {
        if ($this->dataStoreType == null) {
            $this->initDataStore();
        }

        if ($this->dataStoreType == self::DATA_STORE_TYPE_CONF_FILE) {

            $sourcePageId = strtolower($sourcePageId);
            return $this->pageRedirections[$sourcePageId]['TargetPageType'];

        } else {

            throw new Exception('Not Yet implemented');

        }

    }

    /**
     * Get TargetResource (It can be an external URL as an intern page id
     * @param    string $sourcePageId
     * @return
     * @throws Exception
     */
    function getRedirectionTarget($sourcePageId)
    {

        if ($this->dataStoreType == null) {
            $this->initDataStore();
        }

        if ($this->dataStoreType == self::DATA_STORE_TYPE_CONF_FILE) {

            $sourcePageId = strtolower($sourcePageId);
            return $this->pageRedirections[strtolower($sourcePageId)]['TargetPage'];

        } else {

            $res = $this->sqlite->query("select target from redirections where source = ?", $sourcePageId);
            if (!$res) {
                throw new RuntimeException("An exception has occurred with the query");
            }
            $value = $this->sqlite->res2single($res);
            return $value;

        }
    }

    /**
     *
     *   * For a conf file, it will update the Redirection Action Data as Referrer, Count Of Redirection, Redirection Date
     *   * For a SQlite database, it will add a row into the log
     *
     * @param string $sourcePageId
     * @param $targetPageId
     * @param $type
     */
    function logRedirection($sourcePageId, $targetPageId, $type)
    {
        if ($this->dataStoreType == null) {
            $this->initDataStore();
        }

        if ($this->dataStoreType == self::DATA_STORE_TYPE_CONF_FILE) {

            $sourcePageId = strtolower($sourcePageId);
            $this->pageRedirections[$sourcePageId]['LastRedirectionDate'] = $this->currentDate;
            $this->pageRedirections[$sourcePageId]['LastReferrer'] = $_SERVER['HTTP_REFERER'];
            // This cause to add one after the first insert but yeah, this is going to dye anyway
            $this->pageRedirections[$sourcePageId]['CountOfRedirection'] += 1;
            $this->savePageRedirections();

        } else {

            $row = array(
                "TIMESTAMP" => $this->currentDate,
                "SOURCE" => $sourcePageId,
                "TARGET" => $targetPageId,
                "REFERRER" => $_SERVER['HTTP_REFERER'],
                "TYPE" => $type
            );
            $res = $this->sqlite->storeEntry('redirections_log', $row);

            if (!$res) {
                throw new RuntimeException("An error occurred");
            }
        }
    }

    /**
     * Serialize and save the redirection data file
     *
     * ie Flush
     *
     */
    function savePageRedirections()
    {

        if ($this->dataStoreType == null) {
            $this->initDataStore();
        }

        if ($this->dataStoreType == self::DATA_STORE_TYPE_CONF_FILE) {

            io_saveFile(self::DATA_STORE_CONF_FILE_PATH, serialize($this->pageRedirections));

        } else {

            $this->throwRuntimeException('SavePageRedirections must no be called for a SQLite data store');

        }
    }

    /**
     * Validate URL
     * Allows for port, path and query string validations
     * @param    string $url string containing url user input
     * @return   boolean     Returns TRUE/FALSE
     */
    function isValidURL($url)
    {
        // of preg_match('/^https?:\/\//',$url) ? from redirect plugin
        return preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $url);
    }


    /**
     * @param $inputExpression
     * @return false|int 1|0
     * returns:
     *    - 1 if the input expression is a pattern,
     *    - 0 if not,
     *    - FALSE if an error occurred.
     */
    static function isRegularExpression($inputExpression)
    {

        $regularExpressionPattern = "/(\\/.*\\/[gmixXsuUAJ]?)/";
        return preg_match($regularExpressionPattern, $inputExpression);

    }

    /**
     *
     * Set the data store type. The value must be one of the constants
     *   * DATA_STORE_TYPE_CONF_FILE
     *   * DATA_STORE_TYPE_SQLITE
     *
     * @param $dataStoreType
     * @return $this
     *
     */
    public function setDataStoreType($dataStoreType)
    {
        $this->dataStoreType = $dataStoreType;
        $this->initDataStore();
        return $this;
    }

    /**
     * Init the data store
     */
    private function initDataStore()
    {

        if ($this->dataStoreType == null) {
            $this->sqlite = plugin_load('helper', 'sqlite');
            if (!$this->sqlite) {
                $this->dataStoreType = self::DATA_STORE_TYPE_CONF_FILE;
            } else {
                $this->dataStoreType = self::DATA_STORE_TYPE_SQLITE;
            }
        }

        if ($this->getDataStoreType() == self::DATA_STORE_TYPE_CONF_FILE) {

            msg($this->getLang('SqliteMandatory'), MANAGER404_MSG_INFO, $allow = MSG_MANAGERS_ONLY);

            //Set the redirection data
            if (@file_exists(self::DATA_STORE_CONF_FILE_PATH)) {
                $this->pageRedirections = unserialize(io_readFile(self::DATA_STORE_CONF_FILE_PATH, false));
            }

        } else {

            // initialize the database connection
            $pluginName = $this->infoPlugin['base'];
            if ($this->sqlite == null) {
                $this->sqlite = plugin_load('helper', 'sqlite');
                if (!$this->sqlite) {
                    $this->throwRuntimeException("Unable to load the sqlite plugin");
                }
            }
            $init = $this->sqlite->init($pluginName, DOKU_PLUGIN . $pluginName . '/db/');
            if (!$init) {
                msg($this->lang['SqliteUnableToInitialize'], MSG_MANAGERS_ONLY);
                return;
            }

            // Migration of the old store
            if (@file_exists(self::DATA_STORE_CONF_FILE_PATH)) {
                $this->dataStoreMigration();
            }


        }

    }

    /**
     * Delete all redirections
     * Use with caution
     */
    function deleteAllRedirections()
    {
        if ($this->dataStoreType == null) {
            $this->initDataStore();
        }

        if ($this->dataStoreType == self::DATA_STORE_TYPE_SQLITE) {

            $res = $this->sqlite->query("delete from redirections");
            if (!$res) {
                $this->throwRuntimeException('Errors during delete of all redirections');
            }

        } else {

            if (file_exists(self::DATA_STORE_CONF_FILE_PATH)) {
                $res = unlink(self::DATA_STORE_CONF_FILE_PATH);
                if (!$res) {
                    $this->throwRuntimeException('Unable to delete the file ' . self::DATA_STORE_TYPE_CONF_FILE);
                }
            }
            $this->pageRedirections = array();

        }
    }

    /**
     * Return the number of redirections
     * @return integer
     */
    function countRedirections()
    {
        if ($this->dataStoreType == null) {
            $this->initDataStore();
        }

        if ($this->dataStoreType == self::DATA_STORE_TYPE_SQLITE) {

            $res = $this->sqlite->query("select count(1) from redirections");
            if (!$res) {
                throw new RuntimeException('Errors during delete of all redirections');
            }
            $value = $this->sqlite->res2single($res);
            return $value;

        } else {

            return count($this->pageRedirections);

        }
    }

    public function getDataStoreType()
    {
        if ($this->dataStoreType == null) {
            $this->initDataStore();
        }
        return $this->dataStoreType;
    }

    /**
     * @return array
     */
    private function getRedirections()
    {
        if ($this->dataStoreType == null) {
            $this->initDataStore();
        }

        if ($this->dataStoreType == self::DATA_STORE_TYPE_SQLITE) {

            $res = $this->sqlite->query("select * from redirections");
            if (!$res) {
                throw new RuntimeException('Errors during select of all redirections');
            }
            $row = $this->sqlite->res2arr($res);
            return $row;

        } else {

            return $this->pageRedirections;

        }
    }

    /**
     * Dokuwiki will show a pink message when throwing an eexception
     * and it's difficult to see from where it comes
     *
     * This utility function will add the plugin name to it
     *
     * @param $message
     */
    private function throwRuntimeException($message): void
    {
        throw new RuntimeException($this->getPluginName() . ' - ' . $message);
    }

    /**
     * Migrate from a conf file to sqlite
     */
    function dataStoreMigration()
    {
        if (!file_exists(self::DATA_STORE_CONF_FILE_PATH)) {
            $this->throwRuntimeException("The file to migrate does not exist (" . self::DATA_STORE_CONF_FILE_PATH . ")");
        }
        // We cannot use the getRedirections method because this is a sqlite data store
        // it will return nothing
        $pageRedirections = unserialize(io_readFile(self::DATA_STORE_CONF_FILE_PATH, false));
        foreach ($pageRedirections as $key => $row) {


            $sourcePageId = $key;
            $targetPageId = $row['TargetPage'];
            $creationDate = $row['CreationDate'];
            $isValidate = $row['IsValidate'];

            if ($isValidate == 'Y') {
                $this->addRedirectionWithDate($sourcePageId, $targetPageId, $creationDate);
            }
        }

        rename(self::DATA_STORE_CONF_FILE_PATH, self::DATA_STORE_CONF_FILE_PATH . '.migrated');

    }


}
