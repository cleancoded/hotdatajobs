<?php

class Wpjb_Shortcode_Employer_Register extends Wpjb_Shortcode_Abstract {
    
    /**
     * Registration Form
     *
     * @var Wpjb_Form_Frontend_Register
     */
    protected $_form = null;
    
    /**
     * Is submitted registration form valid
     *
     * @var boolean
     */
    protected $_valid = null;
    
    /**
     * Class constructor
     * 
     * Creates and instance of Wpjb_Form_Frontend_Register for later use.
     */
    public function __construct() {
        if(!shortcode_exists("wpjb_employer_register")) {
            add_shortcode("wpjb_employer_register", array($this, "main"));
        }
    }
    
    /**
     * Registration Init Action
     * 
     * This action is executed in "init" action and applied self::listen().
     * 
     * This function is run in the "init" so we can safely do a redirect on success.
     * 
     * @see self::listen()
     * @see init action
     * 
     * @return void
     */
    public function onInit() {
        
        if($this->getRequest()->post("_wpjb_action") != "reg_employer") {
            return;
        }
        
        $request = Daq_Request::getInstance();
        $flash = new Wpjb_Utility_Session();

        if(!$this->_form) {
            $this->_form = new Wpjb_Form_Frontend_Register();
        }
        
        $this->_valid = $this->_form->isValid($request->getAll());
        if(!$this->_valid) {
            return;
        }

        $this->_form->save();

        $password = $this->_form->value("user_password");
        $email = $this->_form->value("user_email");
        $username = $this->_form->value("user_login");
        
        if(empty($username)) {
            $username = $email;
        }

        $mail = Wpjb_Utility_Message::load("notify_admin_new_employer");
        $mail->setTo(wpjb_conf("admin_email", get_option("admin_email")));
        $mail->assign("company", $this->_form->getObject() );
        $mail->send();
        
        $mail = Wpjb_Utility_Message::load("notify_employer_register");
        $mail->setTo($email);
        $mail->assign("username", $username);
        $mail->assign("password", $password);
        $mail->assign("login_url", wpjb_link_to("employer_login"));
        $mail->assign("manual_verification", wpjb_conf("employer_approval"));
        $mail->assign("company", $this->_form->getObject() );
        $mail->send();

        if($this->_form->getObject()->user_id) {
            wpjb_apply_trial($this->_form->getObject()->user_id);
        }

        do_action("wpjb_user_registered", "employer", $this->_form->getObject()->id);

        $form = new Wpjb_Form_Login;
        if($form->hasElement("recaptcha_response_field")) {
            $form->removeElement("recaptcha_response_field");
        }
        $user = $form->isValid(array(
            "user_login" => $username,
            "user_password" => $password,
            "remember" => false
        ));

        if(wpjb_conf("urls_after_reg_employer")) {
            wp_redirect(wpjb_conf("urls_after_reg_employer"));
            exit;
        }

        $flash->addInfo(__("You have been registered successfully", "wpjobboard"));
        $flash->save();

        wp_redirect(wpjb_link_to("employer_home"));
        exit;
    }
    
    /**
     * Registers shortcode events
     * 
     * This function is run by Wpjb_Shortcode_Manager::setupListeners()
     * 
     * @see Wpjb_Shortcode_Manager::setupListeners()
     * 
     * @return void
     */
    public function listen() {
        add_action("init", array($this, "onInit"));
    }
    
    /**
     * [wpjb_employer_register] shortcode
     * 
     * This function echoes the [wpjb_employer_register] shortcode.
     * 
     * The class that executes the shortcode you can find in
     * wpjobboard/application/libraries/Shortcode/Employer/Register.php
     * 
     * @see wpjobboard/application/libraries/Shortcode/Employer/Register.php
     * @see Wpjb_Shortcode_Employer_Register
     * 
     * @param array     $atts   Shortcode params
     * @return string           Shortcode HTML
     */
    public function main($atts) {
        
        if(get_current_user_id()) {
            $m = __('You are already logged in, <a href="%s">Logout</a> before creating new account.', "wpjobboard");
            $this->addError(sprintf($m, wpjb_link_to("employer_logout")));
            return $this->flash();
        }

        if(!$this->_form) {
            $this->_form = new Wpjb_Form_Frontend_Register();
        }
        
        $this->view = new stdClass();
        $this->view->errors = array();

        if($this->_valid === false) {
            $this->addError($this->_form->getGlobalError());
        }

        $this->view->page_class = "wpjb-page-company-new";
        $this->view->action = "";
        $this->view->form = $this->_form;
        $this->view->submit = __("Register", "wpjobboard");
        $this->view->buttons = array();
        
        return $this->render("default", "form");
    }
}
