<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CancelMembership
 *
 * @author Grzegorz
 */
class Wpjb_Form_Frontend_CancelMembership extends Daq_Form_Abstract
{    
    public function init() 
    {
        $this->addGroup("default");
        
        $e = $this->create("cancel_membership", "checkbox");
        $e->setLabel(__("Cancel Membership?", "wpjobboard"));
        $e->setRequired(true);
        $e->addOption("1", "1", __("Yes, please cancel my membership.", "wpjobboard"));
        $this->addElement($e, "default");
    }
}

?>
