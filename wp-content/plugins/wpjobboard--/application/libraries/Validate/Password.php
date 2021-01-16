<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Password
 *
 * @author Grzegorz
 */
class Wpjb_Validate_Password 
    extends Daq_Validate_Abstract implements Daq_Validate_Interface {
    
    public function isValid($value) 
    {
        $userdata = wp_get_current_user();
        $result = wp_check_password($value, $userdata->user_pass, $userdata->ID);
        
        if($result) {
            return true;
        } else {
            $this->setError(__("Provided password does not match your current password.", "wpjobboard"));
            return false;
        }
    }
}

?>
