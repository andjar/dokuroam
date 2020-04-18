<?php

/**
 * implements a NULL adapter that accepts the standard input and does nothing
 *
 * this is used to suppress followup errors when SQLite is not available or the database files still
 * need conversion
 *
 */
class helper_plugin_sqlite_adapter_null extends helper_plugin_sqlite_adapter {

     /**
      * return name of adapter
      *
      * @return string backend name as defined in helper.php
      */
     public function getName() {
         return DOKU_EXT_NULL;
     }

     /**
      * Registers a User Defined Function for use in SQL statements
      */
     public function create_function($function_name, $callback, $num_args) {
     }

     /**
      * open db
      */
     protected function opendb($init, $sqliteupgrade = false) {
         return false;
     }

     /**
      * close current db
      */
     protected function closedb() {
     }

     /**
      * Execute a raw query
      *
      * @param $sql..
      */
     public function executeQuery($sql) {
        false;
     }

     /**
      * Run sqlite_escape_string() on the given string and surround it
      * with quotes
      */
     public function quote_string($string) {
         return $string;
     }

     /**
      * Escape string for sql
      */
     public function escape_string($str) {
         return $str;
     }

    /**
     * Close the result set and it's cursors
     *
     * @param $res
     * @return bool
     */
    public function res_close($res) {
        return true;
    }

    /**
      * Returns a complete result set as array
      */
     public function res2arr($res, $assoc = true) {
         return array();
     }

     /**
      * Return the next row of the given result set as associative array
      */
     public function res2row($res) {
         return false;
     }

     /**
      * Return the first value from the next row.
      */
     public function res2single($res) {
         return false;
     }

     /**
      * fetch the next row as zero indexed array
      */
     public function res_fetch_array($res) {
         return false;
     }

     /**
      * fetch the next row as assocative array
      */
     public function res_fetch_assoc($res) {
         return false;
     }

     /**
      * Count the number of records in result
      */
     public function res2count($res) {
         return 0;
     }

     /**
      * Count the number of records changed last time
      *
      * Don't work after a SELECT statement in PDO
      */
     public function countChanges($res) {
         return 0;
     }
 }