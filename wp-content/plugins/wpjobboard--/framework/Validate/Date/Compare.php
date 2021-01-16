<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Compare
 *
 * @author Grzegorz
 */
class Daq_Validate_Date_Compare 
    extends Daq_Validate_Abstract implements Daq_Validate_Interface
{
    protected $_compare = "";
    
    protected $_operator = "";
    
    protected $_label = "";
    
    protected $_format = null;
    
    public function __construct($compare, $operator, $label = null, $format = "Y-m-d")
    {
        $this->_compare = $compare;
        $this->_operator = $operator;
        $this->_label = $label;
        $this->_format = $format;
    }
    
    public function isValid($value)
    {
        $date_custom = trim(Daq_Request::getInstance()->post($this->_compare));
        $date = date_create_from_format($this->_format, $date_custom);
        
        if($date instanceof DateTime) {
            $date = $date->format("Y-m-d");
        } else {
            $date = $date_custom;
        }
        
        if(empty($date)) {
            return true;
        }
        
        $d1 = strtotime($date);
        $d2 = strtotime($value);
        
        $op = $this->_operator;
        
        $label = $this->_label;
        
        if($label === null) {
            $label = $this->_compare;
        }
        
        switch($op) {
            case "lt":
                if($d2 >= $d1) {
                    $this->setError(sprintf(__("Date has to be earlier than date in field '%s'", "wpjobboard"), $label));
                    return false;
                } else {
                    return true;
                }
                break;
            case "gt":
                if($d2 <= $d1) {
                    $this->setError(sprintf(__("Date has to be later than date in field '%s'", "wpjobboard"), $label));
                    return false;
                } else {
                    return true;
                }
                break;
            default:
                throw new Exception("Invalid operator.");
        }
    }
}

?>
