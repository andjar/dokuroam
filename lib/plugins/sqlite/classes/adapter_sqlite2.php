<?php
/**
 * Implements specific functions of the PHPs Sqlite Extension
 *
 * This adapter give sqlite2 support, and is based on code of previous
 * versions of the sqlite plugin
 */
class helper_plugin_sqlite_adapter_sqlite2 extends helper_plugin_sqlite_adapter {
    protected $fileextension = '.sqlite';
    protected $db;

    /**
     * return name of adapter
     *
     * @return string adapter name
     */
    public function getName() {
        return DOKU_EXT_SQLITE;
    }

    /**
     * open db
     *
     * @param bool $init          true if this is a new database to initialize
     * @param bool $sqliteupgrade when connecting to a new database:
     *                              false stops connecting to an .sqlite3 db when an .sqlite2 db already exist and warns instead,
     *                              true let connecting so upgrading is possible
     * @return bool true if connecting to sqlite3 db succeed
     */
    public function opendb($init, $sqliteupgrade = false) {
        if($this->isSqlite3db($this->dbfile)) {
            msg("SQLite: failed to open SQLite '".$this->dbname."' database (DB has a sqlite3 format instead of sqlite2 format.)", -1);
            return false;
        }

        $error    = '';
        $this->db = sqlite_open($this->dbfile, 0666, $error);
        if(!$this->db) {
            msg("SQLite: failed to open SQLite '".$this->dbname."' database ($error)", -1);
            return false;
        }

        // register our custom aggregate function
        sqlite_create_aggregate(
            $this->db, 'group_concat',
            array($this, '_sqlite_group_concat_step'),
            array($this, '_sqlite_group_concat_finalize'), 2
        );
        return true;
    }

    /**
     * close current db
     */
    public function closedb() {
        sqlite_close($this->db);
    }

    /**
     * Registers a User Defined Function for use in SQL statements
     */
    public function create_function($function_name, $callback, $num_args) {
        sqlite_create_function($this->db, $function_name, $callback, $num_args);
    }

    /**
     * Execute a query.
     *
     * @param string $sql query
     * @return bool|\SQLiteResult
     */
    public function executeQuery($sql) {
        $err = '';
        $res = @sqlite_query($this->db, $sql, SQLITE_ASSOC, $err);
        if($err) {
            msg($err.':<br /><pre>'.hsc($sql).'</pre>', -1);
            return false;
        } elseif(!$res) {
            msg(
                sqlite_error_string(sqlite_last_error($this->db)).
                    ':<br /><pre>'.hsc($sql).'</pre>', -1
            );
            return false;
        }

        return $res;
    }

    /**
     * Close the result set and it's cursors
     *
     * @param bool|\SQLiteResult $res
     * @return bool
     */
    public function res_close($res) {
        return true; //seems not to be needed in sqlite2?
    }

    /**
     * Returns a complete result set as array
     *
     * @param bool|\SQLiteResult $res
     * @param bool $assoc
     * @return array of arrays of the rows
     */
    public function res2arr($res, $assoc = true) {
        $data = array();

        if(!$res) return $data;
        if(!sqlite_num_rows($res)) return $data;

        sqlite_rewind($res);
        $mode = $assoc ? SQLITE_ASSOC : SQLITE_NUM;
        while(($row = sqlite_fetch_array($res, $mode)) !== false) {
            $data[] = $row;
        }
        return $data;
    }

    /**
     * Return the next row of the given result set as associative array
     *
     * @param bool|\SQLiteResult $res
     * @return array|bool next row or false
     */
    public function res2row($res) {
        if(!$res) return false;
        return sqlite_fetch_array($res, SQLITE_ASSOC);
    }

    /**
     * Return the first value from the next row.
     *
     * @param bool|\SQLiteResult $res
     * @return bool|string
     */
    public function res2single($res) {
        if(!$res) return false;

        return sqlite_fetch_single($res);
    }

    /**
     * Run sqlite_escape_string() on the given string and surround it
     * with quotes
     *
     * @param string $string
     * @return string escaped and quoted string
     */
    public function quote_string($string) {
        return "'".sqlite_escape_string($string)."'";
    }

    /**
     * Escape string for sql
     *
     * @param string $str
     * @return string escaped
     */
    public function escape_string($str) {
        return sqlite_escape_string($str);
    }

    /**
     * Aggregation function for SQLite
     * Callback function called for each row of the result set.
     *
     * @link http://devzone.zend.com/article/863-SQLite-Lean-Mean-DB-Machine
     *
     * @param array &$context   (reference) argument where processed data can be stored
     * @param string $string    column value
     * @param string $separator separator between the values
     */
    public function _sqlite_group_concat_step(&$context, $string, $separator = ',') {
        $context['sep']    = $separator;
        $context['data'][] = $string;
    }

    /**
     * Aggregation function for SQLite
     * Callback function to aggregate the "stepped" data from each row and return it.
     *
     * @link http://devzone.zend.com/article/863-SQLite-Lean-Mean-DB-Machine
     *
     * @param array &$context (reference) data as collected in step callback
     * @return string final result of aggregation
     */
    public function _sqlite_group_concat_finalize(&$context) {
        $context['data'] = array_unique($context['data']);
        return join($context['sep'], $context['data']);
    }

    /**
     * fetch the next row as zero indexed array
     *
     * @param bool|\SQLiteResult $res
     * @return array|bool
     */
    public function res_fetch_array($res) {
        if(!$res) return false;

        return sqlite_fetch_array($res, SQLITE_NUM);
    }

    /**
     * fetch the next row as assocative array
     *
     * @param bool|\SQLiteResult $res
     * @return array|bool
     */
    public function res_fetch_assoc($res) {
        if(!$res) return false;

        return sqlite_fetch_array($res, SQLITE_ASSOC);
    }

    /**
     * Count the number of records in result
     *
     * This function is really inperformant in PDO and should be avoided!
     *
     * @param bool|\SQLiteResult $res
     * @return int
     */
    public function res2count($res) {
        if(!$res) return 0;

        return sqlite_num_rows($res);
    }

    /**
     * Count the number of records changed last time
     *
     * Don't work after a SELECT statement in PDO
     *
     * @param bool|\SQLiteResult $res
     * @return int
     */
    public function countChanges($res) {
        if(!$res) return 0;
        return sqlite_changes($this->db);
    }
}

// vim:ts=4:sw=4:et:enc=utf-8:
