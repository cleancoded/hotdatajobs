<?php
/**
 * Description of Resume
 *
 * @author greg
 * @package 
 */

class Wpjb_Form_Admin_Resume_Create extends Wpjb_Form_Abstract_Resume
{
    public function init() 
    {
        wp_enqueue_script("wpjb-admin-user-register");

        $e = $this->create("_user_id", "hidden");
        $e->setValue(Daq_Request::getInstance()->post("_user_id"));
        $this->addElement($e, "_internal");
        
        $e = $this->create("created_at", "hidden");
        $e->setValue(date("Y-m-d"));
        $this->addElement($e, "_internal");
        
        $e = $this->create("modified_at", "hidden");
        $e->setValue(date("Y-m-d"));
        $this->addElement($e, "_internal");
        
        $this->addGroup("auth", __("User Account", "wpjobboard"), -1);
        
        $e = $this->create("_user_type", "select");
        $e->setValue(Daq_Request::getInstance()->post("_user_type"));
        $e->setLabel(__("User Type", "wpjobboard"));
        $e->addOption("new", "new", __("Create New User", "wpjobboard"));
        $e->addOption("link", "link", __("Select Existing User", "wpjobboard"));
        $e->setOrder(1);
        $this->addElement($e, "auth");
        
        $e = $this->create("_user_link");
        $e->setLabel(__("Select User", "wpjobboard"));
        $e->setHint(__("Start typing user login or email, some suggestions will appear.", "wpjobboard"));
        $e->setAttr("data-discard", "candidate");
        $e->setOrder(2);
        $e->addValidator(new Wpjb_Validate_CreateUser("candidate"));
        $this->addElement($e, "auth");
        
        $e = $this->create("user_login");
        $e->setLabel(__("Username", "wpjobboard"));
        $e->setRequired(true);
        $e->addFilter(new Daq_Filter_Trim());
        $e->addFilter(new Daq_Filter_WP_SanitizeUser());
        $e->addValidator(new Daq_Validate_WP_Username());
        $this->addElement($e, "auth");
        
        $e = $this->create("user_password", "password");
        $e->setLabel(__("Password", "wpjobboard"));
        $e->addFilter(new Daq_Filter_Trim());
        $e->addValidator(new Daq_Validate_StringLength(4, 32));
        $e->addValidator(new Daq_Validate_PasswordEqual("user_password2"));
        $e->setRequired(true);
        $this->addElement($e, "auth");

        $e = $this->create("user_password2", "password");
        $e->setLabel(__("Password (repeat)", "wpjobboard"));
        $e->setRequired(true);
        $this->addElement($e, "auth");
        
        parent::init();
        
        $i = 5;
        foreach(array_keys($this->getGroup("default")->getAll()) as $key) {
            $this->getElement($key)->setOrder($i++);
        }
        $e = $this->create("candidate_slug", "hidden");
        $e->addClass("wpjb-slug-base");
        $e->setValue("");
        $this->addElement($e, "_internal");
        
        $e = $this->create("_slug_type", "hidden");
        $e->setValue("resume");
        $e->addClass("wpjb-slug-type");
        $this->addElement($e, "_internal");
        
        $this->initDetails();
        
        add_filter("wpja_form_init_resume", array($this, "apply"), 9);
        apply_filters("wpja_form_init_resume", $this);
    }
    
    public function isValid(array $values) {
        
        if($this->value("_user_type") == "link" && $this->value("_user_id")) {
            $this->getElement("user_email")->setRequired(false);
            $this->getElement("user_password")->setRequired(false);
            $this->getElement("user_password2")->setRequired(false);
            $this->getElement("user_login")->setRequired(false);
        } else {
            $this->getElement("user_email")->setRequired(true);
            $this->getElement("user_password")->setRequired(true);
            $this->getElement("user_password2")->setRequired(true);
            $this->getElement("user_login")->setRequired(true);
        }
        
        $isValid = parent::isValid($values);
        
        $this->getElement("user_email")->setRequired(true);
        $this->getElement("user_password")->setRequired(true);
        $this->getElement("user_password2")->setRequired(true);
        $this->getElement("user_login")->setRequired(true);
        
        return $isValid;
    }
    
    public function save($append = array())
    {
        if($this->value("_user_type") == "link") {
            $append["user_id"] = $this->value("_user_id");
            
            wp_update_user(array(
                "ID" => $append["user_id"],
                "role" => "subscriber"
            ));
        } else {
            $user_email = $this->getElement("user_email")->getValue();
            
            if(!$this->hasElement("user_login")) {
                $user_login = $user_email;
            } else {
                $user_login = $this->getElement("user_login")->getValue();
            }
            
            $id = wp_insert_user(array(
                "user_login" => $user_login, 
                "user_email" => $user_email, 
                "user_pass" => $this->getElement("user_password")->getValue(),
                "role" => "subscriber"
            ));
            
            $append["user_id"] = $id;
        }
        
        $this->getObject()->user_id = $append["user_id"];
        
        if(wpjb_conf("cv_approval") == 1) {
            $append["is_active"] = 0; // manual approval
        } else {
            $append["is_active"] = 1;
        }
        
        $fullname = trim($this->value("first_name")." ".$this->value("last_name"));
        $candidate_slug = Wpjb_Utility_Slug::generate(Wpjb_Utility_Slug::MODEL_RESUME, $fullname);
        $this->getElement("candidate_slug")->setValue($candidate_slug) ;

        parent::save($append);
        
        $this->saveDetails();
        
        do_action("wpjb_resume_saved", $this->getObject());
        apply_filters("wpja_form_save_resume", $this);
    }
    
}

