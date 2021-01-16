<?php
/**
 * Description of Required
 *
 * @author greg
 * @package 
 */

class Daq_Validate_Required
    extends Daq_Validate_Abstract implements Daq_Validate_Interface
{
    public function isValid($value)
    {
        if(is_array($value) && empty($value)) {
            $this->setError(__("Field cannot be empty", "wpjobboard"));
            return false;
        } elseif(is_array($value) && isset($value["size"]) && $value["size"]==0) {
            $this->setError(__("Field cannot be empty", "wpjobboard"));
            return false;
        } elseif(empty($value)) {
            $this->setError(__("Field cannot be empty", "wpjobboard"));
            return false;
        }

        return true;
    }
}

?>