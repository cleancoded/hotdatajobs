<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Application
 *
 * @author Grzegorz
 */
class Wpjb_Form_Abstract_Application extends Daq_Form_ObjectAbstract 
{
    protected $_custom = "wpjb_form_apply";
    
    protected $_key = "apply";

    protected $_model = "Wpjb_Model_Application";
    
    public function init()
    {
        $this->_upload = array(
            "path" => wpjb_upload_dir("{object}", "{field}", "{id}", "basedir"),
            "object" => "application",
            "field" => null,
            "id" => wpjb_upload_id($this->getId())
        );
        
        $this->addGroup("apply", __("Apply", "wpjobboard"));

        $e = $this->create("applicant_name");
        $e->addFilter(new Daq_Filter_Trim());
        $e->setLabel(__("Your name", "wpjobboard"));
        $e->setRequired(true);
        $e->setValue($this->_object->applicant_name);
        $this->addElement($e, "apply");

        $e = $this->create("email");
        $e->setLabel(__("Your e-mail address", "wpjobboard"));
        $e->setRequired(true);
        $e->addValidator(new Daq_Validate_Email());
        $e->setValue($this->_object->email);
        $this->addElement($e, "apply");
        
        $e = $this->create("message", "textarea");
        $e->setLabel(__("Message", "wpjobboard"));
        $e->setValue($this->_object->message);
        $this->addElement($e, "apply");
                
        $e = $this->create("file", "file");
        /* @var $e Daq_Form_Element_File */
        $e->setLabel(__("Attachments", "wpjobboard"));
        $e->setUploadPath($this->_upload);
        $e->setRenderer("wpjb_form_field_upload");
        $e->addValidator(new Daq_Validate_File_Ext("pdf,doc,docx,txt"));
        $e->setMaxFiles(10);
        $this->addElement($e, "apply");
    }
}

?>
