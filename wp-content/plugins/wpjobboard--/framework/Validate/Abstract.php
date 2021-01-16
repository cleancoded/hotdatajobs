<?php
/**
 * Description of Abstract
 *
 * @author greg
 * @package 
 */

abstract class Daq_Validate_Abstract
{
    protected $_value = "";

    protected $_errors = array();

    public function setValue($value)
    {
        $this->_value = $value;
    }

    public function setError($error)
    {
        $this->_errors[] = $error;
    }

    public function getErrors()
    {
        return $this->_errors;
    }
}

?>