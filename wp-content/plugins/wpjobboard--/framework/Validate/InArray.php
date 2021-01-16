<?php
/**
 * Description of InArray
 *
 * @author greg
 * @package 
 */

class Daq_Validate_InArray
    extends Daq_Validate_Abstract implements Daq_Validate_Interface
{
    protected $_allowed = array();

    public function __construct(array $allowed)
    {
        $this->_allowed = $allowed;
    }

    public function isValid($value)
    {
        foreach((array)$value as $v) {
            if(!in_array($v, $this->_allowed)) {
                $this->setError(__("Unrecognized value", "wpjobboard"));
                return false;
            }
        }

        return true;
    }
}

?>