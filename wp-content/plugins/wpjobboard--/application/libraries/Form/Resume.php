<?php
/**
 * Description of Resume
 *
 * @author greg
 * @package
 */

class Wpjb_Form_Resume extends Wpjb_Form_Abstract_Resume
{

    public function init()
    {
        parent::init();
        $this->removeElement("is_approved");
        $this->removeElement("status");
        $this->removeElement("is_active");
        $this->removeElement("created_at");
        
        if($this->isNew() && current_user_can("manage_resumes")) {
            $user = new WP_User(get_current_user_id());
            $default = array("first_name", "last_name", "user_email", "user_url");
            foreach($default as $key) {
                if($this->hasElement($key)) {
                    $this->getElement($key)->setValue($user->$key);
                }
            }
            
            if($this->hasElement("user_email")) {
                $this->getElement("user_email")->removeValidator("Daq_Validate_WP_Email");
                $this->getElement("user_email")->addValidator(new Daq_Validate_WP_Email(array("exclude"=>$user->ID)));
            }
        }
        
        $this->initDetails();
        
        add_filter("wpjr_form_init_resume", array($this, "apply"), 9);
        apply_filters("wpjr_form_init_resume", $this);
    }
    

    
    public function save($append = array())
    {
        if($this->hasElement("modified_at")) {
            $this->removeElement("modified_at");
        }

        $append = array("modified_at" => current_time("mysql", true));
        
        if($this->isNew()) {
            $title = trim($this->value("first_name")." ".$this->value("last_name"));
            $append["user_id"] = get_current_user_id();
            $append["candidate_slug"] = Wpjb_Utility_Slug::generate("resume", $title, $this->getId());
            $append["created_at"] = current_time("mysql", true);
        }

        parent::save($append);
        
        $this->saveDetails();
        
        apply_filters("wpjr_form_save_resume", $this);
        
    }
    
}

?>