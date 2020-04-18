<?php

/**
 * Every parameters and page description for each test case are here
 * TODO? May be move the test case path to their respective test cases ?
 *
 * @group plugin_404manager
 * @group plugins
 */
class constant_parameters
{

    static $MANAGER404_NAMESPACE;
    const PATH_SEPARATOR = ':';


    static $PAGE_EXIST_ID;
    static $PAGE_DOES_NOT_EXIST_ID;

    static $PAGE_DOES_NOT_EXIST_NO_REDIRECTION_ID;

    static $DIR_RESOURCES;

    static $INFO_PLUGIN;
    static $PLUGIN_BASE;
    static $PAGE_REDIRECTED_TO_EXTERNAL_WEBSITE;

    static $EXPLICIT_REDIRECT_PAGE_SOURCE;
    static $EXPLICIT_REDIRECT_PAGE_TARGET;

    static $REDIRECT_BEST_PAGE_NAME_SOURCE;
    static $REDIRECT_BEST_PAGE_NAME_TARGET_SAME_BRANCH;
    static $REDIRECT_BEST_PAGE_NAME_TARGET_OTHER_BRANCH;


    static $REDIRECT_TO_NAMESPACE_START_PAGE_SOURCE;
    static $REDIRECT_TO_NAMESPACE_START_PAGE_BAD_TARGET;
    static $REDIRECT_TO_NAMESPACE_START_PAGE_GOOD_TARGET;

    static $REDIRECT_TO_NAMESPACE_START_PAGE_PARENT_SOURCE;
    static $REDIRECT_TO_NAMESPACE_START_PAGE_PARENT_BAD_TARGET;
    static $REDIRECT_TO_NAMESPACE_START_PAGE_PARENT_GOOD_TARGET;

    // Page ID and namespace must be in lowercase
    const NS_PAGE = 'ns_attached_to_page';
    const NS_BRANCH_1 = 'ns_branch1';
    const NS_BRANCH_2 = 'ns_branch2';
    const NS_BRANCH_WITH_PARENT_NAME_START_PAGE = 'ns_branch3';


    static function init()
    {
        $pluginInfoFile = __DIR__ . '/../plugin.info.txt';
        self::$DIR_RESOURCES = __DIR__ . '/../_testResources';

        self::$INFO_PLUGIN = confToHash($pluginInfoFile);
        self::$PLUGIN_BASE = self::$INFO_PLUGIN['base'];

        self::$MANAGER404_NAMESPACE = self::$INFO_PLUGIN['base'];

        self::$PAGE_EXIST_ID = self::$MANAGER404_NAMESPACE . self::PATH_SEPARATOR . 'page_exist';
        self::$PAGE_DOES_NOT_EXIST_ID = self::$MANAGER404_NAMESPACE . self::PATH_SEPARATOR . 'page_does_not_exist';
        self::$PAGE_DOES_NOT_EXIST_NO_REDIRECTION_ID = self::$MANAGER404_NAMESPACE . self::PATH_SEPARATOR . 'page_does_not_exist_no_redirection';

        self::$PAGE_REDIRECTED_TO_EXTERNAL_WEBSITE = self::$MANAGER404_NAMESPACE . self::PATH_SEPARATOR . 'redirect_to_external_website';

        self::$EXPLICIT_REDIRECT_PAGE_SOURCE = self::$MANAGER404_NAMESPACE . self::PATH_SEPARATOR . 'explicit_redirect_to_internal_page_source';
        self::$EXPLICIT_REDIRECT_PAGE_TARGET = self::$MANAGER404_NAMESPACE . self::PATH_SEPARATOR . 'explicit_redirect_to_internal_page_target';

        self::$REDIRECT_BEST_PAGE_NAME_SOURCE = self::$MANAGER404_NAMESPACE . self::PATH_SEPARATOR . self::NS_BRANCH_1 . self::PATH_SEPARATOR . self::NS_PAGE . self::PATH_SEPARATOR .'redirect_best_page_name';
        // Without Level1
        self::$REDIRECT_BEST_PAGE_NAME_TARGET_SAME_BRANCH = self::$MANAGER404_NAMESPACE . self::PATH_SEPARATOR . self::NS_PAGE . self::PATH_SEPARATOR .'redirect_best_page_name';
        self::$REDIRECT_BEST_PAGE_NAME_TARGET_OTHER_BRANCH = self::$MANAGER404_NAMESPACE . self::PATH_SEPARATOR . self::NS_BRANCH_2 . self::PATH_SEPARATOR . self::NS_PAGE . self::PATH_SEPARATOR .'redirect_best_page_name';

        // Set of 3 pages, when a page has an homonym (same page name) but within another completly differents path (the name of the path have nothing in common)
        // the 404 manager must redirect to the start page of the namespace.
        self::$REDIRECT_TO_NAMESPACE_START_PAGE_SOURCE = self::$MANAGER404_NAMESPACE . self::PATH_SEPARATOR . self::NS_BRANCH_1 . self::PATH_SEPARATOR .'redirect_to_namespace_start_page';
        self::$REDIRECT_TO_NAMESPACE_START_PAGE_BAD_TARGET = self::$MANAGER404_NAMESPACE . self::PATH_SEPARATOR . self::NS_BRANCH_2 . self::PATH_SEPARATOR  .'redirect_to_namespace_start_page';
        self::$REDIRECT_TO_NAMESPACE_START_PAGE_GOOD_TARGET = self::$MANAGER404_NAMESPACE . self::PATH_SEPARATOR . self::NS_BRANCH_1 . self::PATH_SEPARATOR  .'start';


        // Set of 3 pages, when a page has an homonym (same page name) but within another completly differents path (the name of the path have nothing in common)
        // the 404 manager must redirect to the start page of the namespace.
        self::$REDIRECT_TO_NAMESPACE_START_PAGE_PARENT_SOURCE = self::$MANAGER404_NAMESPACE . self::PATH_SEPARATOR . self::NS_BRANCH_WITH_PARENT_NAME_START_PAGE . self::PATH_SEPARATOR .'redirect_to_namespace_start_page';
        self::$REDIRECT_TO_NAMESPACE_START_PAGE_PARENT_BAD_TARGET = self::$MANAGER404_NAMESPACE . self::PATH_SEPARATOR . self::NS_BRANCH_2 . self::PATH_SEPARATOR  .'redirect_to_namespace_start_page';
        self::$REDIRECT_TO_NAMESPACE_START_PAGE_PARENT_GOOD_TARGET = self::$MANAGER404_NAMESPACE . self::PATH_SEPARATOR . self::NS_BRANCH_WITH_PARENT_NAME_START_PAGE . self::PATH_SEPARATOR  . self::NS_BRANCH_WITH_PARENT_NAME_START_PAGE;



    }
}

constant_parameters::init();
