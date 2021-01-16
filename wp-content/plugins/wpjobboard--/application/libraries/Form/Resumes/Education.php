<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Experience
 *
 * @author greg
 */
class Wpjb_Form_Resumes_Education extends Daq_Form_ObjectAbstract
{
    protected $_model = "Wpjb_Model_ResumeDetail";
    
    public function init() 
    {
        $e = $this->create("id", "hidden");
        $e->setValue($this->getObject()->id);
        $this->addElement($e, "_internal");
        
        $this->addGroup("default", "");
        
        $e = $this->create("resume_id", "hidden");
        $e->setValue($this->getObject()->resume_id);
        $this->addElement($e, "default");
        
        $e = $this->create("type", "hidden");
        $e->setValue(Wpjb_Model_ResumeDetail::EDUCATION);
        $this->addElement($e, "default");
        
        if($this->getObject()->started_at != "0000-00-00") {
            $date = date("Y-m-d", strtotime($this->getObject()->started_at));
        } else {
            $date = "";
        }
        $e = $this->create("started_at", "text_date");
        $e->setRequired(true);
        $e->setDateFormat(wpjb_date_format());
        $e->setLabel(__("Started", "wpjobboard"));
        $e->setValue($this->getId() ? $date : date("Y-m-d"));
        $e->addValidator(new Daq_Validate_Date_Compare("completed_at", "lt", __("Finished", "wpjobboard"), wpjb_date_format()));
        $e->addClass("wpjb-date-picker");
        $e->setAttr("readonly", "readonly");
        $this->addElement($e, "default");

        if($this->getObject()->completed_at != "0000-00-00") {
            $date = date("Y-m-d", strtotime($this->getObject()->completed_at));
        } else {
            $date = "";
        }
        $e = $this->create("completed_at", "text_date");
        $e->setRequired(true);
        $e->setDateFormat(wpjb_date_format());
        $e->setLabel(__("Finished", "wpjobboard"));
        $e->setValue($this->getId() ? $date : date("Y-m-d", strtotime("today +1 month")));
        $e->addValidator(new Daq_Validate_Date_Compare("started_at", "gt", __("Started", "wpjobboard"), wpjb_date_format()));
        $e->addClass("wpjb-date-picker");
        $e->setAttr("readonly", "readonly");
        $this->addElement($e, "default");
        
        $e = $this->create("is_current", "checkbox");
        $e->addOption(1, 1, __("I am currently studying here", "wpjobboard"));
        $e->setValue($this->getObject()->is_current);
        $this->addElement($e, "default");
        
        $e = $this->create("grantor");
        $e->setValue($this->getObject()->grantor);
        $e->setLabel(__("Institution", "wpjobboard"));
        $this->addElement($e, "default");
        
        $e = $this->create("detail_title");
        $e->setValue($this->getObject()->detail_title);
        $e->setRequired(true);
        $e->setLabel(__("Degree", "wpjobboard"));
        $this->addElement($e, "default");
        
        $e = $this->create("detail_description", "textarea");
        $e->setValue($this->getObject()->detail_description);
        $e->setLabel(__("Description", "wpjobboard"));
        $this->addElement($e, "default");
        
        wp_enqueue_script("wpjb-vendor-datepicker");
        wp_enqueue_style("wpjb-vendor-datepicker");
        
        apply_filters("wpjr_form_init_resume_education", $this);
    }
    
    public function save($append = array()) 
    {
        parent::save($append);
        
        apply_filters("wpjr_form_save_resume_education", $this);
    }
    
}

?>
