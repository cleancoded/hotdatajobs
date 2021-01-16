<?php

/**
 * Description of Login
 *
 * @author greg
 * @package
 */

class Wpjb_Form_Frontend_Register extends Wpjb_Form_Abstract_Company
{
    public function init()
    {
        parent::init();
        
        $e = $this->create("_wpjb_action", "hidden");
        $e->setValue("reg_employer");
        $this->addElement($e, "_internal");
        
        add_filter("wpjb_form_init_company", array($this, "apply"), 9);
        apply_filters("wpjb_form_init_company", $this);
    }

    public function isValid(array $values)
    {
        $isValid = parent::isValid($values);
        
        if($this->hasElement("company_info")) {
            $e = $this->create("company_info_format", "hidden");
            $e->setValue($this->getElement("company_info")->usesEditor() ? "html" : "text");
            $e->setBuiltin(false);
            $this->addElement($e, "_internal");
        }
        
        
        return $isValid;
    }
    
    public function save($append = array())
    {
        
        if(wpjb_conf("employer_approval") == 1) {
            $active = 0; // manual approval
        } else {
            $active = 1;
        }
        
        $append["is_active"] = $active;
        $append["is_public"] = wpjb_conf("employer_is_public", 1);
        
        
        parent::save($append);

        $temp = wpjb_upload_dir("company", "", null, "basedir");
        $finl = dirname($temp)."/".$this->getId();
        wpjb_rename_dir($temp, $finl);
        
        // move transient links
        $this->moveTransients();
        
        do_action("wpjb_company_saved", $this->getObject());
        apply_filters("wpjb_form_save_company", $this);
        
    }
}

?>