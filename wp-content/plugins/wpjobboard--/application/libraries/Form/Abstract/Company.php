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
abstract class Wpjb_Form_Abstract_Company extends Daq_Form_ObjectAbstract 
{
    protected $_custom = "wpjb_form_company";
    
    protected $_key = "company";
    
    protected $_model = "Wpjb_Model_Company";
    
    public function init()
    {
        $this->_upload = array(
            "path" => wpjb_upload_dir("{object}", "{field}", "{id}", "basedir"),
            "object" => "company",
            "field" => null,
            "id" => wpjb_upload_id($this->getId())
        );
        
        $user = new WP_User($this->getObject()->user_id);
        
        if($this->isNew())  $this->addGroup("auth", __("User Account", "wpjobboard"), -1);
        $this->addGroup("default", __("Company", "wpjobboard"));
        $this->addGroup("location", __("Location", "wpjobboard"));
        $this->addGroup("_internal", "");

        $e = $this->create("user_login");
        $e->setLabel(__("Username", "wpjobboard"));
        $e->setRequired(true);
        $e->addFilter(new Daq_Filter_Trim());
        $e->addValidator(new Daq_Validate_WP_Username());
        if($this->isNew()) $this->addElement($e, "auth");

        $e = $this->create("user_password", "password");
        $e->setLabel(__("Password", "wpjobboard"));
        $e->addFilter(new Daq_Filter_Trim());
        $e->addValidator(new Daq_Validate_StringLength(4, 32));
        $e->addValidator(new Daq_Validate_PasswordEqual("user_password2"));
        $e->setRequired(true);
        if($this->isNew()) $this->addElement($e, "auth");

        $e = $this->create("user_password2", "password");
        $e->setLabel(__("Password (repeat)", "wpjobboard"));
        $e->setRequired(true);
        if($this->isNew()) $this->addElement($e, "auth");

        $e = $this->create("user_email");
        $e->setLabel(__("E-mail", "wpjobboard"));
        $e->addFilter(new Daq_Filter_Trim());
        $e->addValidator(new Daq_Validate_WP_Email(array("exclude"=>$user->ID)));
        $e->setRequired(true);
        $e->setValue($user->user_email);
        $this->addElement($e, "default");
        
        $e = $this->create("company_name");
        $e->setLabel(__("Company Name", "wpjobboard"));
        $e->setRequired(true);
        $e->setValue($this->_object->company_name);
        $e->addValidator(new Daq_Validate_StringLength(null, 120));
        $this->addElement($e, "default");
        
        $e = $this->create("company_slogan");
        $e->setLabel(__("Company Slogan", "wpjobboard"));
        $e->setRequired(false);
        $e->setValue($this->_object->company_slogan);
        $e->addValidator(new Daq_Validate_StringLength(null, 250));
        $this->addElement($e, "default");
        
        $e = $this->create("company_logo", "file");
        $e->setLabel(__("Company Logo", "wpjobboard"));
        $e->addValidator(new Daq_Validate_File_Default());
        $e->addValidator(new Daq_Validate_File_Ext("jpg,jpeg,gif,png"));
        $e->addValidator(new Daq_Validate_File_Size(300000));
        $e->setUploadPath($this->_upload);
        $e->setRenderer("wpjb_form_field_upload");
        $this->addElement($e, "default");

        $e = $this->create("company_website");
        $e->setLabel(__("Company Website", "wpjobboard"));
        $e->addValidator(new Daq_Validate_Url());
        $e->addFilter(new Daq_Filter_WP_Url());
        $e->setValue($this->_object->company_website);
        $e->addValidator(new Daq_Validate_StringLength(null, 120));
        $this->addElement($e, "default");

        $e = $this->create("company_info", "textarea");
        $e->setLabel(__("Company Info", "wpjobboard"));
        $e->setEditor(Daq_Form_Element_Textarea::EDITOR_TINY);
        $e->setValue($this->_object->company_info);
        $this->addElement($e, "default");
        
        if(!$this->isNew()) {
            $e = $this->create("is_public", "checkbox");
            $e->setLabel(__("Publish Profile", "wpjobboard"));
            $e->addOption(1, 1, __("Allow job seekers to view company profile", "wpjobboard"));
            $e->setValue($this->_object->is_public);
            $e->setBoolean(true);
            $e->addFilter(new Daq_Filter_Int());
            $this->addElement($e, "default");
        }
        
        $def = wpjb_locale();
        $e = $this->create("company_country", "select");
        $e->setLabel(__("Company Country", "wpjobboard"));
        $e->setValue(($this->_object->company_country) ? $this->_object->company_country : $def);
        foreach(Wpjb_List_Country::getAll() as $listing) {
            $e->addOption($listing['code'], $listing['code'], $listing['name']);
        }
        $e->addClass("wpjb-location-country");
        $this->addElement($e, "location");

        $e = $this->create("company_state");
        $e->setLabel(__("Company State", "wpjobboard"));
        $e->setValue($this->_object->company_state);
        $e->addClass("wpjb-location-state");
        $e->addValidator(new Daq_Validate_StringLength(null, 40));
        $this->addElement($e, "location");

        $e = $this->create("company_zip_code");
        $e->setLabel(__("Company Zip-Code", "wpjobboard"));
        $e->addValidator(new Daq_Validate_StringLength(null, 20));
        $e->setValue($this->_object->company_zip_code);
        $this->addElement($e, "location");
        
        $e = $this->create("company_location");
        $e->setLabel(__("Company Location", "wpjobboard"));
        $e->setValue($this->_object->company_location);
        $e->addClass("wpjb-location-city");
        $e->addValidator(new Daq_Validate_StringLength(null, 250));
        $this->addElement($e, "location");
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
        if($this->isNew() && is_admin() && $this->value("_user_id")) {
            $append["user_id"] = $this->value("_user_id");
            
            $user = new WP_User( $append["user_id"] );
            $user->add_role( 'employer' );
            
        } else if($this->isNew()) {

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
                "role" => "employer"
            ));
            
            $append["user_id"] = $id;

        } else {
            
            $names = new Wpjb_Model_User;
            $names = $names->getFieldNames();
            $user = new WP_User($this->getObject()->user_id);
            $userdata = array("ID"=>$user->ID);
            $update = false;
            foreach($names as $key) {
                if($this->hasElement($key) && !in_array($key, array("user_login", "user_pass"))) {
                    $userdata[$key] = $this->value($key);
                    $update = true;
                }
            }

            if($update) {
                wp_update_user($userdata);
            }
        }
        
        
        parent::save($append);

        $temp = wpjb_upload_dir("company", "", null, "basedir");
        $finl = dirname($temp)."/".$this->getId();
        wpjb_rename_dir($temp, $finl);
        
        // move transient links
        $this->moveTransients();
        $this->getObject()->cpt();   
    }
}
