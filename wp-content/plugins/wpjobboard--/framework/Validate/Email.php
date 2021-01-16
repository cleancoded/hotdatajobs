<?php
/**
 * Description of Email
 *
 * @author greg
 * @package 
 */

class Daq_Validate_Email
    extends Daq_Validate_Abstract implements Daq_Validate_Interface
{
    public function isValid($value)
    {
        $preg = "/^([a-zA-Z0-9_\-\.\+])+@([a-zA-Z0-9_\-\.])+(\.[a-zA-Z0-9_\-]+)+/";
        if (!preg_match($preg, $value)) {
            $this->setError(__("Email address is invalid", "wpjobboard"));
            return false;
        }
        return true;
    }
}

?>