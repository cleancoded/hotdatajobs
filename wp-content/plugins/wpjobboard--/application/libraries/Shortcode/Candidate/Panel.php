<?php

class Wpjb_Shortcode_Candidate_Panel extends Wpjb_Shortcode_Panel_Abstract {
    
    /**
     * Class constructor
     * 
     * Registers [wpjb_candidate_panel] shortcode if not already registered
     * 
     * @since 5.0
     * @return void
     */
    public function __construct() {
        if(!shortcode_exists("wpjb_candidate_panel")) {
            add_shortcode("wpjb_candidate_panel", array($this, "main"));
        }
    }
    
    /**
     * Logouts Candidate
     * 
     * This function is applied in self::listen() and executed by "template_redirect" action.
     * 
     * @see self::listen()
     * @see template_redirect action
     * 
     * @param string $template
     * @return string
     */
    public function onTemplateRedirect($template) {
        
        if(!wpjb_is_routed_to("logout", "resumes")) {
            return $template;
        }
        
        $logout = array(
            "redirect_to" => wpjr_link_to("login"),
            "message" => __("You have been logged out.", "wpjobboard")
        );

        $logout = apply_filters("wpjb_logout", $logout, "candidate");

        wp_logout();

        if($logout["message"]) {
            $flash = new Wpjb_Utility_Session;
            $flash->addInfo($logout["message"]);
            $flash->save();
        }

        wp_redirect($logout["redirect_to"]);
        exit;
    }
    
    /**
     * Change Password for Candidate
     * 
     * This function is applied in self::listen() and executed by "template_redirect" action.
     * 
     * @see self::listen()
     * @see template_redirect action
     * 
     * @param string $template
     * @return string
     */
    public function onTemplateRedirectPasswordChange($template) {
        
        if( $this->getRequest()->post("_wpjb_action", false) != "wpjb_candidate_change_password" || !is_page( Wpjb_Project::getInstance()->conf( "urls_link_cand_panel" ) ) ) {
            return $template; 
        }
        
        $form = new Wpjb_Form_PasswordChange();
        if($this->getRequest()->isPost()) {
            $isValid = $form->isValid($this->getRequest()->getAll());
            if($isValid) {
                wp_update_user(array("ID"=> get_current_user_id(), "user_pass"=>$form->value("user_password")));
                $s = __("Your password has been changed.", "wpjobboard");
                $this->addInfo( $s );
                wp_safe_redirect( wpjr_link_to( "myresume_home" ) );
                exit; 
            }
        }
        
        return $template;
    }
    
    /**
     * Save new alerts 
     * 
     * This function is applied in self::listen() and executed by "template_redirect" action.
     * 
     * @see self::listen()
     * @see template_redirect action
     * 
     * @param string $template
     * @return string
     */
    public function onTemplateRedirectAddAlert( $template ) {
        
        if( $this->getRequest()->post("_wpjb_action", false) != "wpjb_candidate_add_alert" || !is_page( Wpjb_Project::getInstance()->conf( "urls_link_cand_panel" ) ) ) {
            //var_dump("DUPA"); die;
            return $template; 
        }
        
        //$form = new Wpjb_Form_Alert();
        if($this->getRequest()->isPost()) {
            $all = $this->getRequest()->getAll(); 
              
            if(isset($all['alert'])) {
                $removed = 0;

                foreach($all['alert'] as $id => $params) {
                    
                    // Remove
                    if(isset($params['_delete']) && $params['_delete'] == 1) {
                        $alert = new Wpjb_Model_Alert($id);
                        $alert->delete();
                        $removed++;
                        
                        continue;
                    }

                    $alert = new Wpjb_Model_Alert($params['id']);
                    $alert->frequency = $params['frequency'];
                    $alert->email = $params['email'];
                    if(!$params['id']) {
                        $alert->created_at = current_time("mysql", true);
                    }
                    $alert->params = $params['params'];
                    $alert->user_id = wp_get_current_user()->ID;
                    $alert = apply_filters("wpjb_alert_save", $alert, $id, $params);
                    $alert->save();
                    
                    $this->addInfo( __("Alert Configuration has been saved.", "wpjobboard") );
                }

                if($removed > 0) {
                    $this->addInfo( sprintf(__("%s Alerts has been Removed.", "wpjobboard"), $removed ) );
                }
                
                wp_safe_redirect( wpjr_link_to( "myalerts" ) );
                exit;
            }
        }
        
        return $template;
    }
    
    /**
     * Removes Candidate Account
     * 
     * This function is applied in self::listen() and executed by "template_redirect" action.
     * 
     * @see self::listen()
     * @see template_redirect action
     * 
     * @param string $template
     * @return string
     */
    public function onTemplateRedirectRemoveAccount($template) {
        
        if( $this->getRequest()->post("_wpjb_action", false) != "wpjb_candidate_remove_account" || !is_page( Wpjb_Project::getInstance()->conf( "urls_link_cand_panel" ) ) ) {
            return $template; 
        }
        
        global $current_user;
                
        $user = Wpjb_Model_Resume::current();
        $full = Wpjb_Model_Resume::DELETE_FULL;

        $form = new Wpjb_Form_DeleteAccount();
        
        if($this->getRequest()->isPost()) {
            $isValid = $form->isValid($this->getRequest()->getAll());
            if($isValid) {
                $user->delete($full);
                $current_user = null;
                @wp_logout();
                $s = __("Your account has been deleted.", "wpjobboard");
                $this->addInfo($s);
                
                wp_safe_redirect( wpjr_link_to("login") );
                exit; 
            } 
        }
        
        return $template;
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
        add_action( "template_redirect", array($this, "onTemplateRedirect"));
        add_action( "template_redirect", array($this, "onTemplateRedirectPasswordChange"));
        add_action( "template_redirect", array($this, "onTemplateRedirectRemoveAccount"));
        add_action( "template_redirect", array($this, "onTemplateRedirectAddAlert"));
    }
    
    /**
     * Displays Candidate Panel
     * 
     * This function is executed when [wpjb_candidate_panel] shortcode is being called.
     * 
     * @link https://wpjobboard.net/kb/wpjb_candidate_panel/ documentation
     * 
     * @param array $atts   Shortcode attributes
     * @return void
     */
    public function main($atts = array()) {
        $content = apply_filters("wpjb_candidate_panel_content", false);
    
        if($content) {
            return $content;
        }

        $instance = Wpjb_Project::getInstance();
        $pages = $instance->user_manager->getUser("candidate")->dashboard->getPages();

        foreach($pages as $key => $page) {
            if(wpjb_is_routed_to($key, "resumes")) {
                return call_user_func($page["callback"]);
            }
        }

        return "";
    }
    
    /**
     * Display Candidate Panel Home
     * 
     * @return string   Shortcode HTML
     */
    public function home() {

        if($this->getRequest()->get("goto-job")) {
            $job = new Wpjb_Model_Job($this->getRequest()->get("goto-job"));
            $redirect = $job->url();
        } else {
            $redirect = wpjr_link_to("myresume_home");
        }
        
        if(!current_user_can("manage_resumes")) {
            return $this->_loginForm($redirect);
        }

        if(!$this->_hasAccess("manage_resumes")) {
            return $this->flash();
        }

        $this->view = new stdClass();
        $manager = Wpjb_Project::getInstance()->env("user_manager");
        /* @var $manager Wpjb_User_Manager */

        $dashboard = $manager->buildDashboard("candidate", get_the_ID());
        $this->view->dashboard = apply_filters("wpjb_candidate_panel_links", $dashboard);

        return $this->render("resumes", "my-home");
    }
    
    /**
     * Display Candidae Panel Home
     * 
     * @see wpjb_candidate_panel()
     * @example /candidate-panel/login/
     * 
     * @return string   Shortcode HTML
     */
    public function login() {
        
        if(get_current_user_id()) {
            return $this->home();
        }
        
        $form = new Wpjb_Form_Login();
        
        if($this->getRequest()->get("redirect_to")) {
            $redirect = base64_decode($this->getRequest()->get("redirect_to"));
            $form->getElement("redirect_to")->setValue($redirect);
        } else {
            $form->getElement("redirect_to")->setValue(wpjr_link_to("myresume_home"));
        }
        
        if($this->getRequest()->isPost() && $this->getRequest()->post("_wpjb_action")=="login") {
            $form->isValid($this->getRequest()->getAll());
        }

        $this->view = new stdClass();
        $this->view->page_class = "wpjb-page-company-login";
        $this->view->action = "";
        $this->view->form = $form;
        $this->view->submit = __("Login", "wpjobboard");
        $this->view->buttons = array();
        
        if(wpjb_conf("urls_link_cand_reg") != "0") {
            $this->view->buttons[] = array(
                "tag" => "a", 
                "href" => wpjr_link_to("register"), 
                "html" => __("Not a member? Register", "wpjobboard")
            );
        }

        $this->view = apply_filters("wpjb_shortcode_login", $this->view, "candidate");

        return $this->render("default", "form");
    }
    
    /**
     * Logout Action
     * 
     * Does not do anything as the logout is handled in template_redirect action
     * 
     * @see self::onTemplateRedirect()
     * @example /canditate-panel/logout/
     * 
     * @return string   Shortcode HTML
     */
    public function logout() {
        return "";
    }
    
    /**
     * Display Candidate Panel / Password Change
     * 
     * @see wpjb_candidate_panel()
     * @example /candidate-panel/password/
     * 
     * @return string   Shortcode HTML
     */
    public function password() {
        
        switch($this->getUserPrivs()) {
            case -1: return false; break;
            case -2: return $this->_loginForm(wpjr_link_to("myresume_password")); break;
        }
        
        //$url = wpjr_link_to("myresume_home");
        
        $this->view = new stdClass();
        $this->view->action = "";
        $this->view->submit = __("Change Password", "wpjobboard");
        
        $form = new Wpjb_Form_PasswordChange();
        $form->getElement("_wpjb_action")->setValue("wpjb_candidate_change_password");
        if($this->getRequest()->isPost()) {
            $isValid = $form->isValid($this->getRequest()->getAll());
            if(!$isValid) {
                $this->addError($form->getGlobalError());
            }
            // Change Password in onTemplateRedirectPasswordChange
        }
        
        foreach(array("user_password", "user_password2", "old_password") as $f) {
            if($form->hasElement($f)) {
                $form->getElement($f)->setValue("");
            }
        }
        
        $this->view->breadcrumbs = array(
            array("title"=>__("Home", "wpjobboard"), "url"=>wpjr_link_to("myresume_home"), "glyph"=>"wpjb-icon-home"),
            array("title"=>__("Change Password", "wpjobboard"), "url"=>wpjb_link_to("myresume_password"), "glyph"=>$this->glyph()),
        );
        
        $this->view->form = $form;
        
        return $this->render("default", "form");
    }
    
    /**
     * Display Candidate Panel / Delete
     * 
     * @see wpjb_candidate_panel()
     * @example /canditate-panel/delete/
     * 
     * @return string   Shortcode HTML
     */
    public function delete() {
        //global $current_user;
        
        switch($this->getUserPrivs()) {
            case -1: return false; break;
            case -2: return $this->_loginForm(wpjr_link_to("myresume_delete")); break;
        }
        
        //$user = Wpjb_Model_Resume::current();
        //$full = Wpjb_Model_Resume::DELETE_FULL;
        
        $this->view = new stdClass();
        $this->view->breadcrumbs = array(
            array("title"=>__("Home", "wpjobboard"), "url"=>wpjr_link_to("myresume_home"), "glyph"=>"wpjb-icon-home"),
            array("title"=>__("Delete Account", "wpjobboard"), "url"=>wpjr_link_to("myresume_delete"), "glyph"=>$this->glyph()),
        );

        $this->view->action = "";
        $this->view->submit = __("Delete Account", "wpjobboard");
        
        $form = new Wpjb_Form_DeleteAccount();
        $form->getElement("_wpjb_action")->setValue("wpjb_candidate_remove_account");
        if($this->getRequest()->isPost()) {
            $isValid = $form->isValid($this->getRequest()->getAll());
            if(!$isValid) {
                $this->addError(__("There are errors in your form", "wpjobboard"));
            }
        }
        
        foreach(array("user_password") as $f) {
            if($form->hasElement($f)) {
                $form->getElement($f)->setValue("");
            }
        }
        
        $this->view->form = $form;
        
        return $this->render("default", "form");
    }
    
    protected function getUserPrivs() {   
        if(get_current_user_id() < 1) {
            return -2;
        }
        
        if(!current_user_can("manage_resumes")) {
            $this->addError(__("You need to be registered as Candidate in order to access this page. Your current account type is Employer.", "wpjobboard"));
            return -1;
        }
    }
    
    public function resume() {
        
        switch($this->getUserPrivs()) {
            case -1: return false; break;
            case -2: return $this->_loginForm(wpjr_link_to("myresume")); break;
        }
        
        $this->view = new stdClass();
        
        $object = Wpjb_Model_Resume::current();
        if(!is_object($object)) {
            $id = null;
            $this->view->disable_details = false;
        } else {
            $id = $object->getId();
            $this->view->disable_details = false;
        }
        
        $this->view->breadcrumbs = array(
            array("title"=>__("Home", "wpjobboard"), "url"=>wpjr_link_to("myresume_home"), "glyph"=>"wpjb-icon-home"),
            array("title"=>__("My Resume", "wpjobboard"), "url"=>wpjr_link_to("myresume"), "glyph"=>$this->glyph()),
        );
        
        $form = new Wpjb_Form_Resume($id);
        if($this->getRequest()->isPost()) {
            $isValid = $form->isValid($this->getRequest()->getAll());
            if($isValid) {
                $this->addInfo(__("Your resume has been saved.", "wpjobboard"));
                $form->save();
            } else {
                $this->addError($form->getGlobalError());
            }
        }

        wp_enqueue_script("wpjb-myresume");
        $form->buildPartials();

        $this->view->resume = $form->getObject();
        $this->view->form = $form;

        return $this->render("resumes", "my-resume");
    }
    
    public function applications() 
    {
        switch($this->getUserPrivs()) {
            case -1: return false; break;
            case -2: return $this->_loginForm(wpjr_link_to("myapplications")); break;
        }
        
        $this->view = new stdClass();
        $this->view->query = null;
        $this->view->format = null;
        $this->view->tolock = apply_filters("wpjb_lock_resume", array("user_email", "phone"));
        
        $this->view->breadcrumbs = array(
            array("title"=>__("Home", "wpjobboard"), "url"=>wpjr_link_to("myresume_home"), "glyph"=>"wpjb-icon-home"),
            array("title"=>__("My Applications", "wpjobboard"), "url"=>wpjr_link_to("myapplications"), "glyph"=>$this->glyph()),
        );
        
        $request = $this->getRequest();
        
        $query = new Daq_Db_Query();
        $query->from("Wpjb_Model_Application t");
        $query->where("user_id = ?", get_current_user_id());
        $query->order("t.applied_at DESC");
        
        $total = $query->select("COUNT(*) as cnt")->fetchColumn();
        $page = $request->getParam("page", $request->getParam("pg", 1));
        $perPage = wpjb_conf("front_jobs_per_page", 20);
        
        $query->select("*");
        $query->limitPage($page, $perPage);
        $query->join("t.job t2");
                
        $apps = $query->execute();
        
        $result = new stdClass();
        $result->perPage = $perPage;
        $result->total = $total;
        $result->application = $apps;
        $result->count = count($apps);
        $result->pages = ceil($result->total/$result->perPage);
        $result->page = $page;
        
        $this->view->result = $result;
        $this->view->param = array("page"=>$page);
        $this->view->url = wpjr_link_to("myapplications");
        
        
        return $this->render("resumes", "my-applications");
    }
    
    public function bookmarks() {
        switch($this->getUserPrivs()) {
            case -1: return false; break;
            case -2: return $this->_loginForm(wpjr_link_to("mybookmarks")); break;
        }
        
        $this->view = new stdClass();
        $this->view->query = null;
        $this->view->format = null;
        $this->view->tolock = apply_filters("wpjb_lock_resume", array("user_email", "phone"));
        $this->view->breadcrumbs = array(
            array("title"=>__("Home", "wpjobboard"), "url"=>wpjr_link_to("myresume_home"), "glyph"=>"wpjb-icon-home"),
            array("title"=>__("My Bookmarks", "wpjobboard"), "url"=>wpjr_link_to("mybookmarks"), "glyph"=>$this->glyph()),
        );
        
        $request = $this->getRequest();
        
        $query = new Daq_Db_Query();
        $query->from("Wpjb_Model_Shortlist t");
        $query->where("user_id = ?", get_current_user_id());
        $query->where("object = ?", "job");
        $query->order("id DESC");
        
        $total = $query->select("COUNT(*) as cnt")->fetchColumn();
        $page = $request->getParam("page", $request->getParam("pg", 1));
        $perPage = wpjb_conf("front_jobs_per_page", 20);
        
        $query->select("*");
        $query->limitPage($page, $perPage);
                
        $apps = $query->execute();
        
        $result = new stdClass();
        $result->perPage = $perPage;
        $result->total = $total;
        $result->shortlist = $apps;
        $result->count = count($apps);
        $result->pages = ceil($result->total/$result->perPage);
        $result->page = $page;
        
        $this->view->result = $result;
        $this->view->param = array("page"=>$page);
        $this->view->url = wpjr_link_to("mybookmarks");
        
        
        return $this->render("resumes", "my-bookmarks");
    }
    
    /**
     * Dispaly Candidate Panel / Alerts
     * 
     * @return boolean
     */
    public function alerts() {
        switch($this->getUserPrivs()) {
            case -1: return false; break;
            case -2: return $this->_loginForm(wpjr_link_to("myalerts")); break;
        }
        
        wp_enqueue_script( "wpjb-alert" );
        wp_enqueue_script( "wpjb-manage" );
        
        $this->view = new stdClass();
        $this->view->breadcrumbs = array(
            array("title"=>__("Home", "wpjobboard"), "url"=>wpjr_link_to("myresume_home"), "glyph"=>"wpjb-icon-home"),
            array("title"=>__("My Alerts", "wpjobboard"), "url"=>wpjr_link_to("myalerts"), "glyph"=>$this->glyph()),
        );
        
        //$form = new Wpjb_Form_Alert();
        //$form->getElement("_wpjb_action")->setValue("wpjb_candidate_add_alert");
        /*if($this->getRequest()->isPost()) {
            $isValid = $form->isValid($this->getRequest()->getAll());
            if(!$isValid) {
                var_dump( $form->getErrors() );
                $this->addError($form->getGlobalError());
            }
            // Add alerts in onTemplateRedirectAddAlert
        }*/
        
        $query = new Daq_Db_Query();
        $query->from("Wpjb_Model_Alert t");
        $query->where("user_id = ?", get_current_user_id());
        $query->order("created_at ASC");
        
        $total = $query->select("COUNT(*) as cnt")->fetchColumn();
        $page = $this->getRequest()->getParam("page", $this->getRequest()->getParam("pg", 1));
        $perPage = wpjb_conf("front_jobs_per_page", 20);
        
        $query->select("*");
        $query->limitPage($page, $perPage);
                
        $alerts = $query->execute();
        
        $result = new stdClass();
        $result->perPage = $perPage;
        $result->total = $total;
        $result->alerts = $alerts;
        $result->count = count($alerts);
        $result->pages = ceil($result->total/$result->perPage);
        $result->page = $page;
        
        $this->view->result = $result;
        $this->view->param = array("page"=>$page);
        $this->view->url = wpjr_link_to("myalerts");
        $this->view->alerts = wpjb_candidate_alert_stats();
        
        wpjb_alerts_templates();
        
        return $this->render("resumes", "my-alerts");
    }
    
    /**
     * Display Candidate Panel / Memberships
     * 
     * @see wpjb_candidate_panel()
     * @example /candidate-panel/membership/
     * 
     * @return string   Shortcode HTML
     */
    public function membership() {
        
        switch($this->getUserPrivs()) {
            case -1: return false; break;
            case -2: return $this->_candidateLoginForm(wpjr_link_to("mymembership")); break;
        }
        
        $this->view = new stdClass();
        
        if(Daq_Request::getInstance()->get("action") == "cancel") {   
            $membership_id = Daq_Request::getInstance()->get("id");
            $stripe_subscription_status = Wpjb_Model_MetaValue::getSingle('membership', 'subscription_status', $membership_id);
            if($stripe_subscription_status->value != "-1") {
                $confirm = Daq_Request::getInstance()->get("confirm");

                $membership = new Wpjb_Model_Membership($membership_id);
                $this->view->membership = $membership;

                if($confirm != "yes") {
                    return $this->render("resumes", "candidate-products-remove-confirm");
                }

                $this->addInfo( __( "Your subscription has been canceled.", "wpjobboard" ) );

                $stripe = new Wpjb_Payment_Stripe();
                $stripe_subscription_id = Wpjb_Model_MetaValue::getSingle('membership', 'subscription_id', $membership_id);
                //$stripe_sub_id = $stripe->getSubscription( $sub_id );          
                $stripe->cancelSubsctiption($stripe_subscription_id->value);

                Wpjb_Model_MetaValue::import("membership", "subscription_status", "-1", $membership_id);
            }
        }
        
        $query = new Daq_Db_Query();
        $query->from("Wpjb_Model_Pricing t");
        $query->where("price_for = ?", Wpjb_Model_Pricing::PRICE_CANDIDATE_MEMBERSHIP);
        
        $result = $query->execute();
        
        $this->view->result = $result;

        $this->view->breadcrumbs = array(
            array("title"=>__("Home", "wpjobboard"), "url"=>wpjr_link_to("myresume_home"), "glyph"=>"wpjb-icon-home"),
            array("title"=>__("Membership", "wpjobboard"), "url"=>wpjr_link_to("mymembership"), "glyph"=>$this->glyph()),
        );
        
        return $this->render("resumes", "candidate-memberships");
    }
    
    /**
     * Display Candidate Panel / Payment History
     * 
     * @see wpjb_candidate_panel()
     * @example /candidate-panel/my-payment-history/
     * 
     * @return string   Shortcode HTML
     */
    public function paymentHistory() {

        if(!get_current_user_id()) {
            return $this->_loginForm(wpjr_link_to("mypaymenthistory"));
        }
        
        if(!$this->_hasAccess("manage_resumes")) {
            return $this->flash();
        }
        
        $resume = Wpjb_Model_Resume::current();
        
        if( is_null( $resume ) ) {
            $m = __('Please complete your <a href="%s">Candidate Profile</a> and then get back to this page.', "wpjobboard");
            $this->addError(sprintf($m, wpjr_link_to("myresume")));
            return $this->flash();
        }
        
        wp_enqueue_script( "wpjb-manage" );

        $request = Daq_Request::getInstance();
        
        $pay_now = $request->get('pay_now', null);
        if( isset( $pay_now ) && $pay_now > 0 ) {
                        
            $payment = new Wpjb_Model_Payment( $pay_now );

            $this->view = new stdClass();
            $this->view->pricing = new Wpjb_Model_Pricing($payment->pricing_id);
            $this->view->gateways = Wpjb_Project::getInstance()->payment->getEnabled();
            switch($payment->object_type) {
                case 4:
                    $object = new Wpjb_Model_Pricing($payment->object_id);
                    if( is_object( $object ) && strlen( $object->title ) ) { 
                        $this->view->pricing_item = __("Membership", "wpjobboard") . " &quot;" . $object->title . "&quot;";
                    } else {
                        $this->view->pricing_item = __("Membership No Longer Exists", "wpjobboard");
                    }
                    break;
            }
            
            $this->view->defaults = new Daq_Helper_Html("span", array(
                "id" => "wpjb-checkout-defaults",
                "class" => "wpjb-none",
                "data-payment_hash" => $payment->hash(),
                "data-object_id" => $payment->object_id,
                "data-pricing_id" => $payment->pricing_id,
                "data-fullname" => $payment->fullname,
                "data-email" => $payment->email,
            ), " ");
            
            $content = $this->render("default", "payment");
            return $content;
        } 
        
        $browse = $request->get("filter", 'all');
        
        $query = new Daq_Db_Query();
        $query->from("Wpjb_Model_Payment t");
        $query->where("user_id = ?", $resume->user_id);
        $query->where("object_type IN (?)", array(4) );
        $query->order('t.created_at DESC');
        $all_query = clone $query;
        $complete_query = clone $query;
        $complete_query->where('status = ?', 2);
        $pending_query = clone $query;
        $pending_query->where('status = ?', 1);
        $faild_query = clone $query;
        $faild_query->where('status = ?', 3);
        $refund_query = clone $query;
        $refund_query->where('status = ?', 4);
        

        if($browse == 'completed') {
            $query->where('status = ?', 2);
        } elseif($browse == 'pending') {
            $query->where('status = ?', 1);
        } elseif($browse == 'failed') {
            $query->where('status = ?', 3);
        } elseif($browse == 'refunded') {
            $query->where('status = ?', 4);
        }
        
        $total = new stdClass();
        $total->all = count($all_query->execute());
        $total->completed = count($complete_query->execute());
        $total->pending = count($pending_query->execute());
        $total->failed = count($faild_query->execute());
        $total->refunded = count($refund_query->execute());
        
        
        $current_max = count($query->execute()); 
        $page = $page = $request->getParam("page", $request->getParam("pg", 1));
        $perPage = 20;
        
        $query->limit($perPage, ($page - 1) * $perPage);
        $payments = $query->execute();
        
        $pages = ceil($current_max / $perPage);

        $pagination = false;
        if($pages > 1) {
            $pagination = true;
        }
        
        $result = new stdClass();
        $result->payments = $payments;
        $result->count = count($payments);
        $result->pages = $pages;
        $result->page = $page;
        
        
        
        $this->view = new stdClass();
        $this->view->result = $result;
        $this->view->browse = $browse;
        $this->view->total = $total;
        $this->view->pagination = $pagination;
        //$this->view->form = $form; // Search Form

        // Pagination
        $this->view->url = wpjr_link_to('mypaymenthistory');

        $this->view->breadcrumbs = array(
            array("title"=>__("Home", "wpjobboard"), "url"=>wpjr_link_to("myresume_home"), "glyph"=>"wpjb-icon-home"),
            array("title"=>__("Payment History", "wpjobboard"), "url"=>wpjb_link_to("mypaymenthistory"), "glyph"=>$this->glyph()),
        );
        
        return $this->render("resumes", "candidate-payment-history");
    }
    
    protected function _loginForm($redirect) {
        
        $this->addError(__("Login to access this page.", "wpjobboard"));
        
        $form = new Wpjb_Form_Login();
        $form->getElement("redirect_to")->setValue($redirect);

        $this->view = new stdClass();
        $this->view->page_class = "wpjb-page-resume-login";
        $this->view->action = "";
        $this->view->form = $form;
        $this->view->submit = __("Login", "wpjobboard");
        $this->view->buttons = array();
        
        if(wpjb_conf("urls_link_cand_reg") != "0") {
            $this->view->buttons[] = array(
                "tag" => "a", 
                "href" => wpjr_link_to("register"), 
                "html" => __("Not a member? Register", "wpjobboard")
            );
        }

        $this->view = apply_filters("wpjb_shortcode_login", $this->view, "candidate");
        return $this->render("default", "form");
    }
}