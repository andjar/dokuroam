<?php


/**
 * abstract for the adapter that gives access to different sqlite backends
 */
abstract class helper_plugin_sqlite_adapter {
    protected $dbname = '';
    protected $fileextension;
    protected $dbfile;
    protected $db = null;
    protected $data = array();
    protected $nativealter = false;

    /**
     * return name of adapter
     *
     * @return string backend name as defined in helper.php
     */
    public abstract function getName();

    /**
     * Should the nativ ALTER TABLE implementation be used instead of workaround?
     *
     * @param bool $set
     */
    public function setUseNativeAlter($set) {
        $this->nativealter = $set;
    }

    /**
     * The file extension used by the adapter
     *
     * @return string
     */
    public function getFileextension() {
        return $this->fileextension;
    }

    /**
     * @return string database name when set, otherwise an empty string
     */
    public function getDbname() {
        return $this->dbname;
    }

    /**
     * Gives direct access to the database
     *
     * This is only usefull for the PDO Adapter as this gives direct access to the PDO object
     * nontheless it should generally not be used
     *
     * @return null|PDO|resource
     */
    public function getDb() {
       return $this->db;
    }

    /**
     * Returns the path to the database file (if initialized)
     *
     * @return string
     */
    public function getDbFile() {
        return $this->dbfile;
    }

    /**
     * Registers a User Defined Function for use in SQL statements
     *
     * @param string   $function_name The name of the function used in SQL statements
     * @param callable $callback      Callback function to handle the defined SQL function
     * @param int      $num_args      Number of arguments accepted by callback function
     */
    public abstract function create_function($function_name, $callback, $num_args);

    /**
     * Initializes and opens the database
     * Needs to be called right after loading this helper plugin
     *
     * @param string $dbname    - name of database
     * @param bool   $init      - true if this is a new database to initialize
     * @param bool   $sqliteupgrade
     * @return bool
     */
    public function initdb($dbname, &$init, $sqliteupgrade = false) {
        global $conf;

        // check for already open DB
        if($this->db) {
            if($this->dbname == $dbname) {
                // db already open
                return true;
            }
            // close other db
            $this->closedb();

            $this->db     = null;
            $this->dbname = '';
        }

        $this->dbname = $dbname;
        $this->dbfile = $conf['metadir'].'/'.$dbname.$this->fileextension;

        $init = (!@file_exists($this->dbfile) || ((int) @filesize($this->dbfile)) < 3);
        return $this->opendb($init, $sqliteupgrade);
    }

    /**
     * Checks of given dbfile has Sqlite format 3
     *
     * first line tell the format of db file http://marc.info/?l=sqlite-users&m=109383875408202
     */
    public static function isSqlite3db($dbfile) {
        $firstline = @file_get_contents($dbfile, false, null, 0, 15);
        return $firstline == 'SQLite format 3';
    }

    /**
     * open db connection
     *
     * @param bool $init          true if this is a new database to initialize
     * @param bool $sqliteupgrade when connecting to a new database:
     *                              false stops connecting to an .sqlite3 db when an .sqlite2 db already exist and warns instead,
     *                              true let connecting so upgrading is possible
     * @return bool true if connecting to sqlite3 db succeed
     */
    protected abstract function opendb($init, $sqliteupgrade = false);

    /**
     * close current db connection
     */
    protected abstract function closedb();

    /**
     * Execute a query with the given parameters.
     *
     * Takes care of escaping
     *
     * @param array $args Array with sql string and parameters
     * @return bool|\PDOStatement|\SQLiteResult
     */
    public function query($args) {
        if(!$this->db) return false;

        //reset previous result
        $this->data = array();

        $sql = $this->prepareSql($args);
        if(!$sql) return false;

        // intercept ALTER TABLE statements
        if(!$this->nativealter) {
            $match = null;
            if(preg_match('/^ALTER\s+TABLE\s+([\w\.]+)\s+(.*)/i', $sql, $match)) {
                return $this->_altertable($match[1], $match[2]);
            }
        }

        // execute query
        return $this->executeQuery($sql);
    }

    /**
     * Execute a raw query
     *
     * @param $sql..
     */
    public abstract function executeQuery($sql);

    /**
     * Prepare a query with the given arguments.
     *
     * Takes care of escaping
     *
     * @param array $args
     *    array of arguments:
     *      - string $sql - the statement
     *      - arguments...
     * @return bool|string
     */
    public function prepareSql($args) {

        $sql = trim(array_shift($args));
        $sql = rtrim($sql, ';');

        if(!$sql) {
            if(!defined('SIMPLE_TEST')) msg('No SQL statement given', -1);
            return false;
        }

        $argc = count($args);
        if($argc > 0 && is_array($args[0])) {
            $args = $args[0];
            $argc = count($args);
        }

        // check number of arguments
        $qmc = substr_count($sql, '?');
        if($argc < $qmc) {
            if(!defined('SIMPLE_TEST')) msg(
                'Not enough arguments passed for statement. '.
                    'Expected '.$qmc.' got '.
                    $argc.' - '.hsc($sql), -1
            );
            return false;
        }elseif($argc > $qmc){
            if(!defined('SIMPLE_TEST')) msg(
                'Too much arguments passed for statement. '.
                    'Expected '.$qmc.' got '.
                    $argc.' - '.hsc($sql), -1
            );
            return false;
        }

        // explode at wildcard, then join again
        $parts = explode('?', $sql, $argc + 1);
        $args  = array_map(array($this, 'quote_string'), $args); // TODO
        $sql   = '';

        while(($part = array_shift($parts)) !== null) {
            $sql .= $part;
            $sql .= array_shift($args);
        }

        return $sql;
    }

    /**
     * Emulate ALTER TABLE
     *
     * The ALTER TABLE syntax is parsed and then emulated using a
     * temporary table
     *
     * @author <jon@jenseng.com>
     * @link   http://code.jenseng.com/db/
     * @author Andreas Gohr <gohr@cosmocode.de>
     */
    protected function _altertable($table, $alterdefs) {

        // load original table definition SQL
        $result = $this->query(
            array(
                 "SELECT sql,name,type
                                  FROM sqlite_master
                                 WHERE tbl_name = '$table'
                                   AND type = 'table'"
            )
        );

        if(($result === false) || ($this->getName() == DOKU_EXT_SQLITE && $this->res2count($result) <= 0)) {
            msg("ALTER TABLE failed, no such table '".hsc($table)."'", -1);
            return false;
        }

        $row = $this->res_fetch_assoc($result);

        if($row === false) {
            msg("ALTER TABLE failed, table '".hsc($table)."' had no master data", -1);
            return false;
        }

        // prepare temporary table SQL
        $tmpname            = 't'.time();
        $origsql            = trim(
            preg_replace(
                "/[\s]+/", " ",
                str_replace(
                    ",", ", ",
                    preg_replace(
                        '/\)$/', ' )',
                        preg_replace("/[\(]/", "( ", $row['sql'], 1)
                    )
                )
            )
        );
        $createtemptableSQL = 'CREATE TEMPORARY '.substr(trim(preg_replace("'".$table."'", $tmpname, $origsql, 1)), 6);

        // load indexes to reapply later
        $result = $this->query(
            array(
                 "SELECT sql,name,type
                                  FROM sqlite_master
                                 WHERE tbl_name = '$table'
                                   AND type = 'index'"
            )
        );
        if(!$result) {
            $indexes = array();
        } else {
            $indexes = $this->res2arr($result);
        }

        $defs     = preg_split("/[,]+/", $alterdefs, -1, PREG_SPLIT_NO_EMPTY);
        $prevword = $table;
        $oldcols  = preg_split("/[,]+/", substr(trim($createtemptableSQL), strpos(trim($createtemptableSQL), '(') + 1), -1, PREG_SPLIT_NO_EMPTY);
        $newcols  = array();

        for($i = 0; $i < count($oldcols); $i++) {
            $colparts              = preg_split("/[\s]+/", $oldcols[$i], -1, PREG_SPLIT_NO_EMPTY);
            $oldcols[$i]           = $colparts[0];
            $newcols[$colparts[0]] = $colparts[0];
        }
        $newcolumns = '';
        $oldcolumns = '';
        reset($newcols);
        while(list($key, $val) = each($newcols)) {
            $newcolumns .= ($newcolumns ? ', ' : '').$val;
            $oldcolumns .= ($oldcolumns ? ', ' : '').$key;
        }
        $copytotempsql      = 'INSERT INTO '.$tmpname.'('.$newcolumns.') SELECT '.$oldcolumns.' FROM '.$table;
        $dropoldsql         = 'DROP TABLE '.$table;
        $createtesttableSQL = $createtemptableSQL;

        foreach($defs as $def) {
            $defparts = preg_split("/[\s]+/", $def, -1, PREG_SPLIT_NO_EMPTY);
            $action   = strtolower($defparts[0]);
            switch($action) {
                case 'add':
                    if(count($defparts) < 2) {
                        msg('ALTER TABLE: not enough arguments for ADD statement', -1);
                        return false;
                    }
                    $createtesttableSQL = substr($createtesttableSQL, 0, strlen($createtesttableSQL) - 1).',';
                    for($i = 1; $i < count($defparts); $i++)
                        $createtesttableSQL .= ' '.$defparts[$i];
                    $createtesttableSQL .= ')';
                    break;

                case 'change':
                    if(count($defparts) <= 3) {
                        msg('ALTER TABLE: near "'.$defparts[0].($defparts[1] ? ' '.$defparts[1] : '').($defparts[2] ? ' '.$defparts[2] : '').'": syntax error', -1);
                        return false;
                    }

                    if($severpos = strpos($createtesttableSQL, ' '.$defparts[1].' ')) {
                        if($newcols[$defparts[1]] != $defparts[1]) {
                            msg('ALTER TABLE: unknown column "'.$defparts[1].'" in "'.$table.'"', -1);
                            return false;
                        }
                        $newcols[$defparts[1]] = $defparts[2];
                        $nextcommapos          = strpos($createtesttableSQL, ',', $severpos);
                        $insertval             = '';
                        for($i = 2; $i < count($defparts); $i++)
                            $insertval .= ' '.$defparts[$i];
                        if($nextcommapos)
                            $createtesttableSQL = substr($createtesttableSQL, 0, $severpos).$insertval.substr($createtesttableSQL, $nextcommapos);
                        else
                            $createtesttableSQL = substr($createtesttableSQL, 0, $severpos - (strpos($createtesttableSQL, ',') ? 0 : 1)).$insertval.')';
                    } else {
                        msg('ALTER TABLE: unknown column "'.$defparts[1].'" in "'.$table.'"', -1);
                        return false;
                    }
                    break;
                case 'drop':
                    if(count($defparts) < 2) {
                        msg('ALTER TABLE: near "'.$defparts[0].($defparts[1] ? ' '.$defparts[1] : '').'": syntax error', -1);
                        return false;
                    }
                    if($severpos = strpos($createtesttableSQL, ' '.$defparts[1].' ')) {
                        $nextcommapos = strpos($createtesttableSQL, ',', $severpos);
                        if($nextcommapos)
                            $createtesttableSQL = substr($createtesttableSQL, 0, $severpos).substr($createtesttableSQL, $nextcommapos + 1);
                        else
                            $createtesttableSQL = substr($createtesttableSQL, 0, $severpos - (strpos($createtesttableSQL, ',') ? 0 : 1) - 1).')';
                        unset($newcols[$defparts[1]]);
                    } else {
                        msg('ALTER TABLE: unknown column "'.$defparts[1].'" in "'.$table.'"', -1);
                        return false;
                    }
                    break;
                default:
                    msg('ALTER TABLE: near "'.$prevword.'": syntax error', -1);
                    return false;
            }
            $prevword = $defparts[count($defparts) - 1];
        }

        // this block of code generates a test table simply to verify that the
        // columns specifed are valid in an sql statement
        // this ensures that no reserved words are used as columns, for example
        $res = $this->query(array($createtesttableSQL));
        if($res === false) return false;

        $droptempsql = 'DROP TABLE '.$tmpname;
        $res         = $this->query(array($droptempsql));
        if($res === false) return false;

        $createnewtableSQL = 'CREATE '.substr(trim(preg_replace("'".$tmpname."'", $table, $createtesttableSQL, 1)), 17);
        $newcolumns        = '';
        $oldcolumns        = '';
        reset($newcols);
        while(list($key, $val) = each($newcols)) {
            $newcolumns .= ($newcolumns ? ', ' : '').$val;
            $oldcolumns .= ($oldcolumns ? ', ' : '').$key;
        }

        $copytonewsql = 'INSERT INTO '.$table.'('.$newcolumns.') SELECT '.$oldcolumns.' FROM '.$tmpname;

        $res = $this->query(array($createtemptableSQL)); //create temp table
        if($res === false) return false;
        $res = $this->query(array($copytotempsql)); //copy to table
        if($res === false) return false;
        $res = $this->query(array($dropoldsql)); //drop old table
        if($res === false) return false;

        $res = $this->query(array($createnewtableSQL)); //recreate original table
        if($res === false) return false;
        $res = $this->query(array($copytonewsql)); //copy back to original table
        if($res === false) return false;

        foreach($indexes as $index) { // readd indexes
            $res = $this->query(array($index['sql']));
            if($res === false) return false;
        }

        $res = $this->query(array($droptempsql)); //drop temp table
        if($res === false) return false;

        return $res; // return a valid resource
    }

    /**
     * Join the given values and quote them for SQL insertion
     */
    public function quote_and_join($vals, $sep = ',') {
        $vals = array_map(array($this, 'quote_string'), $vals);
        return join($sep, $vals);
    }

    /**
     * Run sqlite_escape_string() on the given string and surround it
     * with quotes
     */
    public abstract function quote_string($string);

    /**
     * Escape string for sql
     */
    public abstract function escape_string($str);

    /**
     * Close the result set and it's cursors
     *
     * @param $res
     */
    public abstract function res_close($res);

    /**
     * Returns a complete result set as array
     */
    public abstract function res2arr($res, $assoc = true);

    /**
     * Return the next row of the given result set as associative array
     */
    public abstract function res2row($res);

    /**
     * Return the first value from the next row.
     */
    public abstract function res2single($res);

    /**
     * fetch the next row as zero indexed array
     */
    public abstract function res_fetch_array($res);

    /**
     * fetch the next row as assocative array
     */
    public abstract function res_fetch_assoc($res);

    /**
     * Count the number of records in result
     *
     * This function is really inperformant in PDO and should be avoided!
     */
    public abstract function res2count($res);

    /**
     * Count the number of records changed last time
     *
     * Don't work after a SELECT statement in PDO
     */
    public abstract function countChanges($res);

    /**
     * Do not serialize the DB connection
     *
     * @return array
     */
    public function __sleep() {
        $this->db = null;
        return array_keys(get_object_vars($this));
    }

    /**
     * On deserialization, reinit database connection
     */
    public function __wakeup() {
        $init = false;
        $this->initdb($this->dbname, $init);
    }

}

// vim:ts=4:sw=4:et:enc=utf-8:
