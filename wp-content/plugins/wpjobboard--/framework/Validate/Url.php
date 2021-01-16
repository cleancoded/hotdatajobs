<?php
/**
 * Description of Url
 *
 * @author greg
 * @package 
 */

class Daq_Validate_Url
    extends Daq_Validate_Abstract implements Daq_Validate_Interface
{
    public function isValid($value)
    {
        $err = __('Invalid URL. Provide valid URL', "wpjobboard");
        if(!preg_match("#^(http|https)://[A-z0-9\-\_\./\?&;=,\#\!\%\+]+$#i",$value)) {
            $this->setError($err);
            return false;
        }

        return true;
    }
}

?>