<?php

/**
 * Description of Login
 *
 * @author greg
 * @package
 */

class Wpjb_Form_Resumes_Register extends Wpjb_Form_Abstract_Resume
{

    public function init()
    {
        parent::init();
        
        $this->addGroup("auth", __("User Account", "wpjobboard"), -1);
        $this->addGroup("_internal", "");
        
        $e = $this->create("_wpjb_action", "hidden");
        $e->setValue("reg_candidate");
        $this->addElement($e, "_internal");
        
        $e = $this->create("created_at", "hidden");
        $e->setValue(date("Y-m-d"));
        $this->addElement($e, "_internal");
        
        $e = $this->create("modified_at", "hidden");
        $e->setValue(date("Y-m-d"));
        $this->addElement($e, "_internal");
        
        $e = $this->create("candidate_slug", "hidden");
        $this->addElement($e, "_internal");
        
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
        
        add_filter("wpjr_form_init_resume", array($this, "apply"), 9);
        apply_filters("wpjr_form_init_resume", $this);
        
        if($this->isNew()) {
            apply_filters("wpjr_form_init_register", $this);
        }
        
        $this->initDetails();
        
        if($this->hasElement("firstname")) {
            $this->removeElement("firstname");
        }
        
        if($this->hasElement("lastname")) {
            $this->removeElement("lastname");
        }
    }

    public function save($append = array()) {

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
        
        
        $this->getObject()->user_id = $append["user_id"];
        
        if(wpjb_conf("cv_approval") == 1) {
            $append["is_active"] = 0; // manual approval
        } else {
            $append["is_active"] = 1;
        }
        
        $this->getObject()->is_public = wpjb_conf("cv_is_public", 1);
        
        $fullname = trim($this->value("first_name")." ".$this->value("last_name"));
        $candidate_slug = Wpjb_Utility_Slug::generate(Wpjb_Utility_Slug::MODEL_RESUME, $fullname);
        
        $this->getElement("created_at")->setValue(date("Y-m-d")) ;
        $this->getElement("modified_at")->setValue(date("Y-m-d")) ;
        $this->getElement("candidate_slug")->setValue($candidate_slug) ;

        parent::save($append);
        
        $this->saveDetails();
        
        do_action("wpjb_resume_saved", $this->getObject());
        apply_filters("wpjr_form_save_register", $this);
    }
    
    

}

?>