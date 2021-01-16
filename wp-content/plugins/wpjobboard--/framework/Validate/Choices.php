<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Choices
 *
 * @author greg
 */
class Daq_Validate_Choices 
    extends Daq_Validate_Abstract implements Daq_Validate_Interface
{
    
    protected $_min = null;
    protected $_max = null;
    
    public function __construct($min = null, $max = null)
    {
        $this->_min = $min;
        $this->_max = $max;
    }
    
    public function isValid($value)
    {
        $value = (array)$value;
        $c = count($value);
        
        if($c < $this->_min && $this->_min !== null) {
            $m = sprintf(__("You need to select at least %d options.", "wpjobboard"), $this->_min);
            $this->setError($m);
            return false;
        }
        
        if($c > $this->_max && $this->_max !== null) {
            $m = sprintf(__("You cannot select more than %d options.", "wpjobboard"), $this->_max);
            $this->setError($m);
            return false;
        }
        
        return true;
    }
}

?>
