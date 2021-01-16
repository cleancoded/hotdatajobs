<?php
/**
 * Description of Abstract
 *
 * @author greg
 * @package 
 */

abstract class Daq_Validate_Db_Abstract
    extends Daq_Validate_Abstract implements Daq_Validate_Interface
{
    protected $_orm = null;

    protected $_field = null;

    protected $_exclude = array();

    public function __construct($orm, $field, array $exclude = array())
    {
        $this->_orm = $orm;
        $this->_field = $field;
        $this->_exclude = $exclude;
    }

    protected function _exist($value)
    {
        $orm = $this->_orm;
        $field = $this->_field;
        $exclude = $this->_exclude;

        $query = new Daq_Db_Query();
        $query->select("t.*")->from("$orm t")->where("t.$field = ?", $value);
        
        foreach($exclude as $k => $v) {
            $query->where("t.$k <> ?", $v);
        }
        $query->limit(1);

        $result = $query->fetch();
        return $result;
    }
}

?>