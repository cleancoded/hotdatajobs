<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Company
 *
 * @author greg
 */
class Wpjb_Form_Frontend_Company extends Wpjb_Form_Abstract_Company
{
    public function isNew()
    {
        if($this->profileOnly()) {
            return false;
        } else {
            return parent::isNew();
        }
    }
    
    public function profileOnly()
    {
        if(current_user_can("manage_jobs") && Wpjb_Model_Company::current() === null) {
            return true;
        } else {
            return false;
        }
    }


    public function init()
    {
        parent::init();
        
        if($this->profileOnly() && $this->hasElement("user_email")) {
            $user = new WP_User(get_current_user_id());
            $this->getElement("user_email")->addValidator(new Daq_Validate_WP_Email(array("exclude"=>$user->ID)));
            $this->getElement("user_email")->setValue($user->user_email);
        }
        
        add_filter("wpjb_form_init_company", array($this, "apply"), 9);
        remove_filter("wpjb_form_init_company", array(Wpjb_Project::getInstance(), "recaptcha"));
        apply_filters("wpjb_form_init_company", $this);
    }
    
    public function save($append = array()) 
    {
        $append = array();
        
        if($this->profileOnly()) {
            $append["user_id"] = get_current_user_id();
        }
        
        parent::save($append);
        
        do_action("wpjb_company_saved", $this->getObject());
        apply_filters("wpjb_form_save_company", $this);
    }
}

