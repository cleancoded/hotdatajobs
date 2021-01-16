<?php
/**
 * Description of Date
 *
 * @author greg
 * @package 
 */

class Daq_Validate_Date
    extends Daq_Validate_Abstract implements Daq_Validate_Interface
{
    protected $_format = null;

    public function __construct($format = "Y-m-d")
    {
        $this->_format = $format;
    }

    public function isValid($value)
    {
        $time = strtotime($value);
        
        $fr = array(
            "/" => "\/",
            "Y" => "([0-9]{4})",
            "m" => "([0-9]{2})",
            "d" => "([0-9]{2})"
        );
        
        $find = array_keys($fr);
        $repl = array_values($fr);
        
        $pattern = str_replace($find, $repl, $this->_format);
        
        if(!preg_match("/$pattern/", $value)) {
            $this->setError(__("Invalid date format", "wpjobboard"));
            return false;
        }
        return true;
    }
}
?>