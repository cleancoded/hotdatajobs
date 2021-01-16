<?php

class Wpjb_Shortcode_Candidate_Register extends Wpjb_Shortcode_Abstract {
    
    /**
     * Registration Form
     *
     * @var Wpjb_Form_Resumes_Register
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
        if(!shortcode_exists("wpjb_candidate_register")) {
            add_shortcode("wpjb_candidate_register", array($this, "main"));
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
        
        if($this->getRequest()->post("_wpjb_action") != "reg_candidate") {
            return;
        }
        
        $request = Daq_Request::getInstance();
        $flash = new Wpjb_Utility_Session();
        
        if(!$this->_form) {
            $this->_form = new Wpjb_Form_Resumes_Register();
        }
        
        $this->_valid = $this->_form->isValid($request->getAll());
        if(!$this->_valid) {
            return;
        }

        $this->_form->save();

        $url = wpjr_link_to("login");
        
        $password = $this->_form->value("user_password");
        $email = $this->_form->value("user_email");
        $username = $this->_form->value("user_login");
        
        if(empty($username)) {
            $username = $email;
        }
        
        $mail = Wpjb_Utility_Message::load("notify_admin_new_candidate");
        $mail->setTo(wpjb_conf("admin_email", get_option("admin_email")));
        $mail->assign("resume", new Wpjb_Model_Resume( $this->_form->getId() ) );
        $mail->send();
        
        $mail = Wpjb_Utility_Message::load("notify_canditate_register");
        $mail->setTo($email);
        $mail->assign("username", $username);
        $mail->assign("password", $password);
        $mail->assign("login_url", $url);
        $mail->assign("resume", new Wpjb_Model_Resume( $this->_form->getId() ) );
        $mail->assign("manual_verification", wpjb_conf("cv_approval"));
        $mail->send();
        
        if($this->_form->getObject()->user_id) {
            wpjb_apply_trial($this->_form->getObject()->user_id);
        }

        do_action("wpjb_user_registered", "candidate", $this->_form->getObject()->id);

        $form = new Wpjb_Form_Resumes_Login();
        if($form->hasElement("recaptcha_response_field")) {
            $form->removeElement("recaptcha_response_field");
        }

        $form->isValid(array(
            "user_login" => $username,
            "user_password" => $password,
            "remember" => 0
        ));

        if(!$request->get("goto-job") && wpjb_conf("urls_after_reg_candidate")) {
            wp_redirect(wpjb_conf("urls_after_reg_candidate"));
            exit;
        }

        $flash->addInfo(__("You have been registered.", "wpjobboard"));
        $flash->save();

        $redirect = wpjr_link_to("myresume_home");

        if($request->get("goto-job")) {
            $job = new Wpjb_Model_Job($request->get("goto-job"));
            $redirect = $job->url();
        }

        wp_redirect($redirect);
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
     * Display [wpjb_employer_register] shortcode
     * 
     * @return string   Shortcode HTML
     */
    public function main() {
        
        if(get_current_user_id()) {
            $m = __('You are already logged in, <a href="%s">Logout</a> before creating new account.', "wpjobboard");
            $this->addError(sprintf($m, wpjb_link_to("employer_logout")));
            return $this->flash();
        }

        if(!$this->_form) {
            $this->_form = new Wpjb_Form_Resumes_Register();
        }
        
        $this->view = new stdClass();
        $this->view->errors = array();

        if($this->_valid === false) {
            $this->addError($this->_form->getGlobalError());
        }
        
        wp_enqueue_script("wpjb-myresume");
        $this->_form->buildPartials();
        
        $this->view->page_class = "wpjb-page-company-new";
        $this->view->action = "";
        $this->view->form = $this->_form;
        $this->view->submit = __("Register", "wpjobboard");
        $this->view->buttons = array();
        
        return $this->render("resumes", "register");
    }
}
