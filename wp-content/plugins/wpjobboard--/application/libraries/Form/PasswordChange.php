<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PasswordChange
 *
 * @author Grzegorz
 */
class Wpjb_Form_PasswordChange extends Daq_Form_Abstract 
{
    public function init()
    {
        $this->addGroup("old_group", __("Old Password", "wpjobboard"));
        $this->addGroup("new_group", __("New Password", "wpjobboard"));
        
        $e = $this->create("_wpjb_action", "hidden");
        $this->addElement($e, "old_group");
        
        $e = $this->create("old_password", "password");
        $e->setLabel(__("Current Password", "wpjobboard"));
        $e->setRequired(true);
        $e->addValidator(new Wpjb_Validate_Password);
        $this->addElement($e, "old_group");
        
        $e = $this->create("user_password", "password");
        $e->setLabel(__("New Password", "wpjobboard"));
        $e->setRequired(true);
        $e->addValidator(new Daq_Validate_StringLength(4, 32));
        $e->addValidator(new Daq_Validate_PasswordEqual("user_password2"));
        $this->addElement($e, "new_group");
        
        $e = $this->create("user_password2", "password");
        $e->setLabel(__("New Password (retype)", "wpjobboard"));
        $e->setRequired(true);
        $this->addElement($e, "new_group");
    }
    
}

?>
