<?php
/**
 * DokuWiki Plugin sqlite (Helper Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Andreas Gohr <gohr@cosmocode.de>
 */

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

if(!defined('DOKU_EXT_SQLITE')) define('DOKU_EXT_SQLITE', 'sqlite');
if(!defined('DOKU_EXT_PDO')) define('DOKU_EXT_PDO', 'pdo');
if(!defined('DOKU_EXT_NULL')) define('DOKU_EXT_NULL', 'null');

require_once(DOKU_PLUGIN.'sqlite/classes/adapter.php');

/**
 * Class helper_plugin_sqlite
 */
class helper_plugin_sqlite extends DokuWiki_Plugin {
    /** @var helper_plugin_sqlite_adapter_pdosqlite|helper_plugin_sqlite_adapter|\helper_plugin_sqlite_adapter_sqlite2|null  */
    protected $adapter = null;

    /**
     * @return helper_plugin_sqlite_adapter_pdosqlite|helper_plugin_sqlite_adapter|\helper_plugin_sqlite_adapter_sqlite2|null
     */
    public function getAdapter() {
        return $this->adapter;
    }

    /**
     * Keep separate instances for every call to keep database connections
     */
    public function isSingleton() {
        return false;
    }

    /**
     * constructor
     */
    public function __construct() {

        if(!$this->adapter) {
            if($this->existsPDOSqlite() && empty($_ENV['SQLITE_SKIP_PDO'])) {
                require_once(DOKU_PLUGIN.'sqlite/classes/adapter_pdosqlite.php');
                $this->adapter = new helper_plugin_sqlite_adapter_pdosqlite();
            }
        }

        if(!$this->adapter) {
            if($this->existsSqlite2()) {
                require_once(DOKU_PLUGIN.'sqlite/classes/adapter_sqlite2.php');
                $this->adapter = new helper_plugin_sqlite_adapter_sqlite2();
            }
        }

        if(!$this->adapter) {
            msg('SQLite & PDO SQLite support missing in this PHP install - plugin will not work', -1);
        }
    }

    /**
     * check availabilty of PHPs sqlite extension (for sqlite2 support)
     */
    public function existsSqlite2() {
        if(!extension_loaded('sqlite')) {
            $prefix = (PHP_SHLIB_SUFFIX === 'dll') ? 'php_' : '';
            if(function_exists('dl')) @dl($prefix.'sqlite.'.PHP_SHLIB_SUFFIX);
        }

        return function_exists('sqlite_open');
    }

    /**
     * check availabilty of PHP PDO sqlite3
     */
    public function existsPDOSqlite() {
        if(!extension_loaded('pdo_sqlite')) {
            $prefix = (PHP_SHLIB_SUFFIX === 'dll') ? 'php_' : '';
            if(function_exists('dl')) @dl($prefix.'pdo_sqlite.'.PHP_SHLIB_SUFFIX);
        }

        if(class_exists('pdo')) {
            foreach(PDO::getAvailableDrivers() as $driver) {
                if($driver == 'sqlite') {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Initializes and opens the database
     *
     * Needs to be called right after loading this helper plugin
     *
     * @param string $dbname
     * @param string $updatedir - Database update infos
     * @return bool
     */
    public function init($dbname, $updatedir) {
        $init = null; // set by initdb()
        if( !$this->adapter or !$this->adapter->initdb($dbname, $init) ){
            require_once(DOKU_PLUGIN.'sqlite/classes/adapter_null.php');
            $this->adapter = new helper_plugin_sqlite_adapter_null();
            return false;
        }

        $this->create_function('GETACCESSLEVEL', array($this, '_getAccessLevel'), 1);
        $this->create_function('PAGEEXISTS', array($this, '_pageexists'), 1);
        $this->create_function('REGEXP', array($this, '_regexp'), 2);
        $this->create_function('CLEANID', 'cleanID', 1);
        $this->create_function('RESOLVEPAGE', array($this, '_resolvePage'), 1);

        return $this->_updatedb($init, $updatedir);
    }

    /**
     * Return the current Database Version
     */
    private function _currentDBversion() {
        $sql = "SELECT val FROM opts WHERE opt = 'dbversion';";
        $res = $this->query($sql);
        if(!$res) return false;
        $row = $this->res2row($res, 0);
        return (int) $row['val'];
    }

    /**
     * Update the database if needed
     *
     * @param bool   $init      - true if this is a new database to initialize
     * @param string $updatedir - Database update infos
     * @return bool
     */
    private function _updatedb($init, $updatedir) {
        if($init) {
            $current = 0;
        } else {
            $current = $this->_currentDBversion();
            if($current === false) {
                msg("SQLite: no DB version found. '".$this->adapter->getDbname()."' DB probably broken.", -1);
                return false;
            }
        }

        // in case of init, add versioning table
        if($init) {
            if(!$this->_runupdatefile(dirname(__FILE__).'/db.sql', 0)) {
                msg("SQLite: '".$this->adapter->getDbname()."' database upgrade failed for version ", -1);
                return false;
            }
        }

        $latest = (int) trim(io_readFile($updatedir.'/latest.version'));

        // all up to date?
        if($current >= $latest) return true;
        for($i = $current + 1; $i <= $latest; $i++) {
            $file = sprintf($updatedir.'/update%04d.sql', $i);
            if(file_exists($file)) {
                // prepare Event data
                $data = array(
                    'from' => $current,
                    'to' => $i,
                    'file' => &$file,
                    'sqlite' => $this
                );
                $event = new Doku_Event('PLUGIN_SQLITE_DATABASE_UPGRADE', $data);
                if($event->advise_before()) {
                    // execute the migration
                    if(!$this->_runupdatefile($file, $i)) {
                        msg("SQLite: '".$this->adapter->getDbname()."' database upgrade failed for version ".$i, -1);
                        return false;
                    }
                } else {
                    if($event->result) {
                        $this->query("INSERT OR REPLACE INTO opts (val,opt) VALUES (?,'dbversion')", $i);
                    } else {
                        return false;
                    }
                }
                $event->advise_after();

            } else {
                msg("SQLite: update file $file not found, skipped.", -1);
            }
        }
        return true;
    }

    /**
     * Updates the database structure using the given file to
     * the given version.
     */
    private function _runupdatefile($file, $version) {
        if(!file_exists($file)) {
            msg("SQLite: Failed to find DB update file $file");
            return false;
        }
        $sql = io_readFile($file, false);

        $sql = $this->SQLstring2array($sql);
        array_unshift($sql, 'BEGIN TRANSACTION');
        array_push($sql, "INSERT OR REPLACE INTO opts (val,opt) VALUES ($version,'dbversion')");
        array_push($sql, "COMMIT TRANSACTION");

        if(!$this->doTransaction($sql)) {
            return false;
        }
        return ($version == $this->_currentDBversion());
    }

    /**
     * Callback checks the permissions for the current user
     *
     * This function is registered as a SQL function named GETACCESSLEVEL
     *
     * @param  string $pageid page ID (needs to be resolved and cleaned)
     * @return int permission level
     */
    public function _getAccessLevel($pageid) {
        static $aclcache = array();

        if(isset($aclcache[$pageid])) {
            return $aclcache[$pageid];
        }

        if(isHiddenPage($pageid)) {
            $acl = AUTH_NONE;
        } else {
            $acl = auth_quickaclcheck($pageid);
        }
        $aclcache[$pageid] = $acl;
        return $acl;
    }

    /**
     * Wrapper around page_exists() with static caching
     *
     * This function is registered as a SQL function named PAGEEXISTS
     *
     * @param string $pageid
     * @return int 0|1
     */
    public function _pageexists($pageid) {
        static $cache = array();
        if(!isset($cache[$pageid])) {
            $cache[$pageid] = page_exists($pageid);

        }
        return (int) $cache[$pageid];
    }

    /**
     * Match a regular expression against a value
     *
     * This function is registered as a SQL function named REGEXP
     *
     * @param string $regexp
     * @param string $value
     * @return bool
     */
    public function _regexp($regexp, $value) {
        $regexp = addcslashes($regexp, '/');
        return (bool) preg_match('/'.$regexp.'/u', $value);
    }

    /**
     * Resolves a page ID (relative namespaces, plurals etc)
     *
     * This function is registered as a SQL function named RESOLVEPAGE
     *
     * @param string $page The page ID to resolve
     * @param string $context The page ID (not namespace!) to resolve the page with
     * @return null|string
     */
    public function _resolvePage($page, $context) {
        if(is_null($page)) return null;
        if(is_null($context)) return cleanID($page);

        $ns = getNS($context);
        resolve_pageid($ns, $page, $exists);
        return $page;
    }

    /**
     * Split sql queries on semicolons, unless when semicolons are quoted
     *
     * @param string $sql
     * @return array sql queries
     */
    public function SQLstring2array($sql) {
        $statements = array();
        $len = strlen($sql);

        // Simple state machine to "parse" sql into single statements
        $in_str = false;
        $in_com = false;
        $statement = '';
        for($i=0; $i<$len; $i++){
            $prev = $i ? $sql[$i-1] : "\n";
            $char = $sql[$i];
            $next = $sql[$i+1];

            // in comment? ignore everything until line end
            if($in_com){
                if($char == "\n"){
                    $in_com = false;
                }
                continue;
            }

            // handle strings
            if($in_str){
                if($char == "'"){
                    if($next == "'"){
                        // current char is an escape for the next
                        $statement .= $char . $next;
                        $i++;
                        continue;
                    }else{
                        // end of string
                        $statement .= $char;
                        $in_str = false;
                        continue;
                    }
                }
                // still in string
                $statement .= $char;
                continue;
            }

            // new comment?
            if($char == '-' && $next == '-' && $prev == "\n"){
                $in_com = true;
                continue;
            }

            // new string?
            if($char == "'"){
                $in_str = true;
                $statement .= $char;
                continue;
            }

            // the real delimiter
            if($char == ';'){
                $statements[] = trim($statement);
                $statement = '';
                continue;
            }

            // some standard query stuff
            $statement .= $char;
        }
        if($statement) $statements[] = trim($statement);

        return $statements;
    }

    /**
     * @param array $sql queries without terminating semicolon
     * @param bool  $sqlpreparing
     * @return bool
     */
    public function doTransaction($sql, $sqlpreparing = true) {
        foreach($sql as $s) {
            $s = preg_replace('!^\s*--.*$!m', '', $s);
            $s = trim($s);
            if(!$s) continue;

            if($sqlpreparing) {
                $res = $this->query("$s;");
            } else {
                $res = $this->adapter->executeQuery("$s;");
            }
            if($res === false) {
                //TODO check rollback for sqlite PDO
                if($this->adapter->getName() == DOKU_EXT_SQLITE) {
                    $this->query('ROLLBACK TRANSACTION');
                } else {
                    $err = $this->adapter->getDb()->errorInfo();
                    msg($err[0].' '.$err[1].' '.$err[2].':<br /><pre>'.hsc($s).'</pre>', -1);
                }
                return false;
            }
        }
        return true;
    }

    /**
     * Dump db into a file in meta directory
     *
     */
    public function dumpDatabase($dbname, $from = DOKU_EXT_SQLITE) {
        global $conf;
        $adapterDumpDb = null;
        //connect to desired database
        if($this->adapter->getName() == $from) {
            $adapterDumpDb =& $this->adapter;
        } else {
            if($from == DOKU_EXT_SQLITE) {
                //TODO test connecting to sqlite2 database
                if($this->existsSqlite2()) {
                    require_once(DOKU_PLUGIN.'sqlite/classes/adapter_sqlite2.php');
                    $adapterDumpDb = new helper_plugin_sqlite_adapter_sqlite2();
                } else {
                    msg('PHP Sqlite Extension(needed for sqlite2) not available, database "'.hsc($dbname).'" is not dumped to file.');
                    return false;
                }
            }
        }
        if($adapterDumpDb === null) {
            msg('No adapter loaded');
            return false;
        }
        $init = false;
        if(!$adapterDumpDb->initdb($dbname, $init)) {
            msg('Opening database fails.', -1);
            return false;
        }

        $res    = $adapterDumpDb->query(array("SELECT name,sql FROM sqlite_master WHERE type='table'"));
        $tables = $adapterDumpDb->res2arr($res);

        $filename = $conf['metadir'].'/dumpfile_'.$dbname.'.sql';
        if($fp = fopen($filename, 'w')) {

            fwrite($fp, 'BEGIN TRANSACTION;'."\n");

            foreach($tables as $table) {

                fwrite($fp, $table['sql'].";\n");

                $sql = "SELECT * FROM ".$table['name'];
                $res = $adapterDumpDb->query(array($sql));

                while($row = $adapterDumpDb->res_fetch_array($res)) {

                    $line = 'INSERT INTO '.$table['name'].' VALUES(';
                    foreach($row as $no_entry => $entry) {
                        if($no_entry !== 0) {
                            $line .= ',';
                        }

                        if(is_null($entry)) {
                            $line .= 'NULL';
                        } elseif(!is_numeric($entry)) {
                            $line .= $adapterDumpDb->quote_string($entry);
                        } else {
                            //TODO depending on locale extra leading zeros are truncated e.g 1.300 (thousand three hunderd)-> 1.3
                            $line .= $entry;
                        }
                    }
                    $line .= ');'."\n";

                    fwrite($fp, $line);
                }
            }

            $res     = $adapterDumpDb->query(array("SELECT name,sql FROM sqlite_master WHERE type='index'"));
            $indexes = $adapterDumpDb->res2arr($res);
            foreach($indexes as $index) {
                fwrite($fp, $index['sql'].";\n");
            }

            fwrite($fp, 'COMMIT;'."\n");

            fclose($fp);
            return $filename;
        } else {
            msg('Dumping "'.hsc($dbname).'" has failed. Could not open '.$filename);
            return false;
        }
    }

    /**
     * Read $dumpfile and try to add it to database.
     * A existing database is backuped first as e.g. dbname.copy2.sqlite3
     *
     * @param string $dbname
     * @param string $dumpfile
     * @return bool true on succes
     */
    public function fillDatabaseFromDump($dbname, $dumpfile) {
        global $conf;
        //backup existing stuff
        $dbf    = $conf['metadir'].'/'.$dbname;
        $dbext  = $this->adapter->getFileextension();
        $dbfile = $dbf.$dbext;
        if(@file_exists($dbfile)) {

            $i            = 0;
            $backupdbfile = $dbfile;
            do {
                $i++;
                $backupdbfile = $dbf.".copy$i".$dbext;
            } while(@file_exists($backupdbfile));

            io_rename($dbfile, $backupdbfile);
        }

        $init = false;
        if(!$this->adapter->initdb($dbname, $init, $sqliteupgrade = true)) {
            msg('Initialize db fails');
            return false;
        }

        $sql = io_readFile($dumpfile, false);
        $sql = $this->SQLstring2array($sql);

        //skip preparing, because it interprets question marks as placeholders.
        return $this->doTransaction($sql, $sqlpreparing = false);
    }

    /**
     * Registers a User Defined Function for use in SQL statements
     */
    public function create_function($function_name, $callback, $num_args) {
        $this->adapter->create_function($function_name, $callback, $num_args);
    }

    /**
     * Convenience function to run an INSERT OR REPLACE operation
     *
     * The function takes a key-value array with the column names in the key and the actual value in the value,
     * build the appropriate query and executes it.
     *
     * @param string $table the table the entry should be saved to (will not be escaped)
     * @param array $entry A simple key-value pair array (only values will be escaped)
     * @return bool|SQLiteResult
     */
    public function storeEntry($table, $entry) {
        $keys = join(',', array_keys($entry));
        $vals = join(',', array_fill(0,count($entry),'?'));

        $sql = "INSERT INTO $table ($keys) VALUES ($vals)";
        return $this->query($sql, array_values($entry));
    }


    /**
     * Execute a query with the given parameters.
     *
     * Takes care of escaping
     *
     *
     * @param string ...$args - the arguments of query(), the first is the sql and others are values
     * @return bool|\SQLiteResult
     */
    public function query() {
        // get function arguments
        $args = func_get_args();

        return $this->adapter->query($args);
    }

    /**
     * Join the given values and quote them for SQL insertion
     */
    public function quote_and_join($vals, $sep = ',') {
        return $this->adapter->quote_and_join($vals, $sep);
    }

    /**
     * Run sqlite_escape_string() on the given string and surround it
     * with quotes
     */
    public function quote_string($string) {
        return $this->adapter->quote_string($string);
    }

    /**
     * Escape string for sql
     */
    public function escape_string($str) {
        return $this->adapter->escape_string($str);
    }

    /**
     * Closes the result set (and it's cursors)
     *
     * If you're doing SELECT queries inside a TRANSACTION, be sure to call this
     * function on all your results sets, before COMMITing the transaction.
     *
     * Also required when not all rows of a result are fetched
     *
     * @param $res
     * @return bool
     */
    public function res_close($res){
        return $this->adapter->res_close($res);
    }

    /**
     * Returns a complete result set as array
     */
    public function res2arr($res, $assoc = true) {
        return $this->adapter->res2arr($res, $assoc);
    }

    /**
     * Return the wanted row from a given result set as
     * associative array
     */
    public function res2row($res, $rownum = 0) {
        return $this->adapter->res2row($res, $rownum);
    }

    /**
     * Return the first value from the next row.
     */
    public function res2single($res) {
        return $this->adapter->res2single($res);
    }

    /**
     * fetch the next row as zero indexed array
     */
    public function res_fetch_array($res) {
        return $this->adapter->res_fetch_array($res);
    }

    /**
     * fetch the next row as assocative array
     */
    public function res_fetch_assoc($res) {
        return $this->adapter->res_fetch_assoc($res);
    }

    /**
     * Count the number of records in result
     *
     * This function is really inperformant in PDO and should be avoided!
     */
    public function res2count($res) {
        return $this->adapter->res2count($res);
    }

    /**
     * Count the number of records changed last time
     */
    public function countChanges($res) {
        return $this->adapter->countChanges($res);
    }

}
