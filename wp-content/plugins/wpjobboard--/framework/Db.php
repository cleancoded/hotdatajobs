<?php
/**
 * Description of Db
 *
 * @author greg
 * @package 
 */


class Daq_Db
{
    private static $_instance = null;

    private $_db = null;

    private $_table = array();

    private function __construct()
    {
    }

    /**
     * Returns instance of Daq_Db
     *
     * @return Daq_Db
     */
    public static function getInstance()
    {
        if(self::$_instance === null) {
            self::$_instance = new self;
        }

        return self::$_instance;
    }

    public function quote($value)
    {
        if (is_int($value)) {
            return $value;
        } elseif (is_float($value)) {
            return sprintf('%F', $value);
        }

        return "'" . addcslashes($value, "\000\n\r\\'\"\032") . "'";
    }

    public function quoteInto($text, $value)
    {
        return str_replace('?', $this->quote($value), $text);
    }

    public function setDb($db)
    {
        $this->_db = $db;
    }

    public function getDb()
    {
        return $this->_db;
    }

    public function describeTable($table)
    {
        if(!isset($this->_table[$table])) {
            $this->_table[$table] = $this->_db->get_results("DESCRIBE ".$table);
        }

        return $this->_table[$table];
    }

    public function getFields($table)
    {
        $field = array();
        foreach($this->describeTable() as $row) {
            $field[] = $row->Field;
        }
        return $field;
    }

    public function delete($table, $where)
    {
        $this->_db->query("DELETE FROM `".$table."` WHERE ".$where);
    }
}
?>