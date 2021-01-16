<?php
/**
 * Description of Company
 *
 * @author greg
 * @package 
 */

class Wpjb_Form_Admin_Company extends Wpjb_Form_Abstract_Company
{
    public function init()
    {
        parent::init();
        
        if($this->isNew()) {
            $this->_register();
            $slug = "";
        } elseif(Wpjb_Project::getInstance()->env("uses_cpt")) {
            $post = get_post($this->_object->post_id);
            $slug = $post->post_name;
        } else {
            $slug = $this->_object->company_slug;
        }
        
        $e = $this->create("company_slug", "hidden");
        $e->addClass("wpjb-slug-base");
        $e->setValue($slug);
        $this->addElement($e, "_internal");
        
        $e = $this->create("_slug_type", "hidden");
        $e->setValue("company");
        $e->addClass("wpjb-slug-type");
        $this->addElement($e, "_internal");
        
        if($this->hasElement("company_name")) {
            $this->getElement("company_name")->addClass("wpjb-slug-pattern");
        }
        
        $e = $this->create("is_active", "checkbox");
        $e->setLabel(__("Activity", "wpjobboard"));
        $e->addOption(1, 1, __("Company account is active", "wpjobboard"));
        $e->setValue($this->_object->id ? $this->_object->is_active : 1);
        $e->addFilter(new Daq_Filter_Int());
        $this->addElement($e, "_internal");

        $opt = array(
            Wpjb_Model_Company::ACCESS_UNSET => "-",
            Wpjb_Model_Company::ACCESS_DECLINED => __("Declined", "wpjobboard"),
            Wpjb_Model_Company::ACCESS_GRANTED => __("Verified", "wpjobboard"),
            Wpjb_Model_Company::ACCESS_PENDING => __("Pending Approval", "wpjobboard"),
        );
        
        $e = $this->create("is_verified", "select");
        $e->setLabel(__("Verification", "wpjobboard"));
        foreach($opt as $k => $v) {
            $e->addOption((string)$k, (string)$k, $v);
        }
        $e->setValue($this->_object->is_verified);
        $e->addFilter(new Daq_Filter_Int());
        $this->addElement($e, "_internal");
        
        add_filter("wpja_form_init_company", array($this, "apply"), 9);
        apply_filters("wpja_form_init_company", $this);
    }
    
    public function isValid(array $values) {

        if(!$this->isNew()) {
            return parent::isValid($values);
        }
        
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
    
    protected function _register() {

        $request = Daq_Request::getInstance();
        
        if(!$this->getGroup("auth") || $request->get("page") != "wpjb-employers" || $request->get("action") != "add") {
            return;
        }
        
        wp_enqueue_script("wpjb-admin-user-register");
        
        $i = 5;
        foreach(array_keys($this->getGroup("auth")->getAll()) as $key) {
            $this->getElement($key)->setOrder($i++);
        }

        $e = $this->create("_user_id", "hidden");
        $e->setValue(Daq_Request::getInstance()->post("_user_id"));
        $this->addElement($e, "_internal");
        
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
        $e->setAttr("data-discard", "employer");
        $e->setOrder(2);
        $e->addValidator(new Wpjb_Validate_CreateUser("employer"));
        $this->addElement($e, "auth");
    }
    
    public function save($append = array()) 
    {
        if($this->isNew()) {
            $append["is_public"] = wpjb_conf("employer_is_public", 1);
        }
        
        parent::save($append);

        do_action("wpjb_company_saved", $this->getObject());
        apply_filters("wpja_form_save_company", $this);
    }
    


}

?>