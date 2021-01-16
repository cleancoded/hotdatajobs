<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Delete
 *
 * @author Grzegorz
 */
class Wpjb_Form_DeleteAccount extends Daq_Form_Abstract 
{
    public function init()
    {
        $this->addGroup("default", "");
        
        $e = $this->create("_wpjb_action", "hidden");
        $this->addElement($e, "default");
        
        $e = $this->create("user_password", "password");
        $e->setLabel(__("Confirm Password", "wpjobboard"));
        $e->setRequired(true);
        $e->addValidator(new Wpjb_Validate_Password);
        $this->addElement($e, "default");
        
        $e = $this->create("delete_account", "checkbox");
        $e->setLabel(__("Delete Your Account?", "wpjobboard"));
        $e->setRequired(true);
        $e->addOption(1, 1, __("Yes, please permanently delete my account along with all my data.", "wpjobboard"));
        $this->addElement($e, "default");
        
    }
    
}

?>
