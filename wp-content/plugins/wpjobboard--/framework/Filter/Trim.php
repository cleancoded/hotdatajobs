<?php
/**
 * Description of Int
 *
 * @author greg
 * @package
 */

class Daq_Filter_Trim implements Daq_Filter_Interface
{
    protected $_trim = null;
    
    public function __construct($trim = null) 
    {
        $this->_trim = $trim;
    }
    
    public function filter($value)
    {
        if($this->_trim) {
            return trim($value, $this->_trim);
        } else {
            return trim($value);
        }
    }
}

?>