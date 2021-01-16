<?php

class Wpjb_Shortcode_Employer_Panel extends Wpjb_Shortcode_Panel_Abstract {
    
    /**
     * Class constructor
     * 
     * Registers [wpjb_employer_panel] shortcode is not already registered
     * 
     * @since 5.0
     * @return void
     */
    public function __construct() {
        if(!shortcode_exists("wpjb_employer_panel")) {
            add_shortcode("wpjb_employer_panel", array($this, "main"));
        }
    }
    
    /**
     * [wpjb_employer_panel] shortcode
     * 
     * This function echoes the [wpjb_employer_panel] shortcode.
     * 
     * The class that executes the shortcode you can find in
     * wpjobboard/application/libraries/Shortcode/Employer/Panel.php
     * 
     * @see wpjobboard/application/libraries/Shortcode/Employer/Panel.php
     * @see Wpjb_Shortcode_Employer_Panel
     * 
     * @return string   Shortcode HTML
     */
    public function main($atts) {
        $content = apply_filters("wpjb_employer_panel_content", false);

        if($content) {
            return $content;
        }

        $instance = Wpjb_Project::getInstance();
        
        if(get_query_var("wpjb-step")) {
            return $instance->shortcode->wpjb_jobs_list->main(array());
        }

        $pages = $instance->user_manager->getUser("employer")->dashboard->getPages();

        foreach($pages as $key => $page) {
            if(wpjb_is_routed_to($key)) {
                return call_user_func($page["callback"]);
            }
        }

        return "";
    }
    
    /**
     * Logouts Employer
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
        
        if(is_admin() || !wpjb_is_routed_to("employer_logout", "frontend")) {
            return $template;
        }

        $logout = array(
            "redirect_to" => wpjb_link_to("employer_login"),
            "message" => __("You have been logged out.", "wpjobboard")
        );

        $logout = apply_filters("wpjb_logout", $logout, "employer");

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
        
        if( $this->getRequest()->post("_wpjb_action", false) != "wpjb_emp_change_password" || !is_page( Wpjb_Project::getInstance()->conf( "urls_link_emp_panel" ) ) ) {
            return $template; 
        }
        
        $form = new Wpjb_Form_PasswordChange();
        if($this->getRequest()->isPost()) {
            $isValid = $form->isValid($this->getRequest()->getAll());
            if($isValid) {
                wp_update_user(array("ID"=> get_current_user_id(), "user_pass"=>$form->value("user_password")));
                $s = __("Your password has been changed.", "wpjobboard");
                $this->addInfo( $s );
                wp_safe_redirect( wpjb_link_to( "employer_home" ) );
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
        
        if( $this->getRequest()->post("_wpjb_action", false) != "wpjb_emp_remove_account" || !is_page( Wpjb_Project::getInstance()->conf( "urls_link_emp_panel" ) ) ) {
            return $template; 
        }
        
        global $current_user;
                
        $user = Wpjb_Model_Company::current();
        $full = Wpjb_Model_Company::DELETE_FULL;

        $form = new Wpjb_Form_DeleteAccount();
        
        if($this->getRequest()->isPost()) {
            $isValid = $form->isValid($this->getRequest()->getAll());
            if($isValid) {
                $user->delete($full);
                $current_user = null;
                @wp_logout();
                $s = __("Your account has been deleted.", "wpjobboard");
                $this->addInfo($s);
                
                wp_safe_redirect( wpjb_link_to("login") );
                exit; 
            } 
        }
        
        return $template;
    }
    
    public function onInit() {
        
        if($this->getRequest()->post("_wpjb_action") != "delete_job") {
            return;
        }
        
        $request = Daq_Request::getInstance();
        $flash = new Wpjb_Utility_Session();
        $form = new Wpjb_Form_Frontend_DeleteJob($request->post("job_id"));
        $job = $form->getObject();

        if($job->employer_id != Wpjb_Model_Company::current()->id) {
            $flash->addError(__("You do not own this job.", "wpjobboard"));
            return;
        }

        if($form->isValid($request->post())) {
            $flash->addInfo(__("Job has been deleted.", "wpjobboard"));
            $flash->save();
            $job->delete();
            wp_redirect($form->value("redirect_to"));
            exit;
        } else {
            $flash->addError($form->getGlobalError());
        }
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
        add_action( "init", array($this, "onInit"));
    }
    
    /**
     * Display Employer Panel Home
     * 
     * @return string   Shortcode HTML
     */
    public function home() {

        if(!current_user_can("manage_jobs")) {
            return $this->_loginForm(wpjb_link_to("employer_home"));
        }

        if(!$this->_hasAccess("manage_jobs")) {
            return $this->flash();
        }

        add_action("wpjb_employer_panel_after_title", array($this, "afterTitle"), 10, 2);


        $this->view = new stdClass();
        $manager = Wpjb_Project::getInstance()->env("user_manager");
        /* @var $manager Wpjb_User_Manager */

        $dashboard = $manager->buildDashboard("employer", get_the_ID());
        $this->view->dashboard = apply_filters("wpjb_employer_panel_links", $dashboard);

        return $this->render("job-board", "company-home");
    }
    
    /**
     * Display Employer Panel Home
     * 
     * @see wpjb_employer_panel()
     * @example /employer-panel/login/
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
            $form->getElement("redirect_to")->setValue(wpjb_link_to("employer_home"));
        }
        
        if($this->getRequest()->isPost() && $this->getRequest()->post("_wpjb_action")=="login") {
            $form->isValid($this->getRequest()->getAll());
        }

        $this->view = new stdClass();
        $this->view->page_class = "wpjb-page-company-login";
        $this->view->action = "";
        $this->view->form = $form;
        $this->view->submit = __("Login", "wpjobboard");
        $this->view->buttons = array(
            array(
                "tag" => "a", 
                "href" => wpjb_link_to("employer_new"), 
                "html" => __("Not a member? Register", "wpjobboard")
            ),
        );

        $this->view = apply_filters("wpjb_shortcode_login", $this->view, "employer");

        return $this->render("default", "form");
    }
    
    /**
     * Logout Action
     * 
     * Does not do anything as the logout is handled in template_redirect action
     * 
     * @see self::onTemplateRedirect()
     * @example /employer-panel/login/
     * 
     * @return string   Shortcode HTML
     */
    public function logout() {
        return "";
    }
    
    /**
     * Display Employer Panel / Password Change
     * 
     * @see wpjb_employer_panel()
     * @example /employer-panel/password/
     * 
     * @return string   Shortcode HTML
     */
    public function password() {
        
        if(!current_user_can("manage_jobs")) {
            return $this->_loginForm(wpjb_link_to("employer_password"));
        }

        if(!$this->_hasAccess("manage_jobs")) {
            return $this->flash();
        }
        
        $url = wpjb_link_to("employer_edit");
        
        $this->view = new stdClass();
        $this->view->action = "";
        $this->view->submit = __("Change Password", "wpjobboard");
        
        $form = new Wpjb_Form_PasswordChange();
        $form->getElement("_wpjb_action")->setValue("wpjb_emp_change_password");
        if($this->getRequest()->isPost()) {
            $isValid = $form->isValid($this->getRequest()->getAll());
            if(!$isValid) {
                $this->addError($form->getGlobalError());
            }
        }
        
        foreach(array("user_password", "user_password2", "old_password") as $f) {
            if($form->hasElement($f)) {
                $form->getElement($f)->setValue("");
            }
        }
        
        $this->view->breadcrumbs = array(
            array("title"=>__("Home", "wpjobboard"), "url"=>wpjb_link_to("employer_home"), "glyph"=>"wpjb-icon-home"),
            array("title"=>__("Change Password", "wpjobboard"), "url"=>wpjb_link_to("employer_password"), "glyph"=>$this->glyph()),
        );
        
        $this->view->form = $form;
        
        return $this->render("default", "form");
    }
    
    public function afterTitle($lname, $link) {
        if($lname == "job_applications") {
            $title = __( "Unread Applications", "wpjobboard" );
            
            $list = new Daq_Db_Query();
            $list->select("COUNT(*) as `cnt`");
            $list->from("Wpjb_Model_Application t");
            $list->join("t.job t2");
            $list->where("t2.employer_id = ?", Wpjb_Model_Company::current()->id);
            $list->where("status = 1");
            $apps = $list->fetchColumn();

            if( $apps > 0) {
                printf('<span class="wpjb-notify-new" title="%s">%d</span>', $title, $apps);
            }
        }
    }
    
    /**
     * Display Employer Panel / Edit
     * 
     * @see wpjb_employer_panel()
     * @example /employer-panel/edit/
     * 
     * @return string   Shortcode HTML
     */
    public function employerEdit() {
        $company = Wpjb_Model_Company::current();
        
        if(!get_current_user_id()) {
            return $this->_loginForm(wpjb_link_to("employer_edit"));
        }
        
        if(!$this->_hasAccess("manage_jobs")) {
            return $this->flash();
        }

        if(is_null($company)) {
            $id = null;
        } else {
            $id = $company->id;
        }
        
        $form = new Wpjb_Form_Frontend_Company($id);
        
        $this->view = new stdClass();
        $this->view->company = $company;

        if($this->getRequest()->isPost()) {
            if(!$form->isValid($this->getRequest()->getAll())) {
               $this->addError($form->getGlobalError());
            } else {
               $this->addInfo(__("Company information has been saved.", "wpjobboard"));
               $form->save();
            }
        }

        $this->view->breadcrumbs = array(
            array("title"=>__("Home", "wpjobboard"), "url"=>wpjb_link_to("employer_home"), "glyph"=>"wpjb-icon-home"),
            array("title"=>__("Company Profile", "wpjobboard"), "url"=>wpjb_link_to("employer_edit"), "glyph"=>$this->glyph()),
        );
        
        $this->view->page_class = "wpjb-company-edit-form";
        $this->view->action = "";
        $this->view->form = $form;
        $this->view->submit = __("Update", "wpjobboard");
        $this->view->buttons = array( );
        
        if($company) {
            $this->view->buttons[] = array(
                "tag" => "a", 
                "href" => wpjb_link_to("company", $company), 
                "html" => __("View Profile", "wpjobboard")
            );
        }

        
        return $this->render("default", "form");
    }
    
    /**
     * Display Employer Panel / Delete
     * 
     * @see wpjb_employer_panel()
     * @example /employer-panel/delete/
     * 
     * @return string   Shortcode HTML
     */
    public function employerDelete() {
        //global $current_user;
        
        if(!get_current_user_id()) {
            return $this->_loginForm(wpjb_link_to("employer_delete"));
        }
        
        if(!$this->_hasAccess("manage_jobs")) {
            return $this->flash();
        }
        
        //$user = Wpjb_Model_Company::current();
        //$full = Wpjb_Model_Company::DELETE_FULL;
        
        $this->view = new stdClass();
        $this->view->action = "";
        $this->view->submit = __("Delete Account", "wpjobboard");
        
        $form = new Wpjb_Form_DeleteAccount();
        $form->getElement("_wpjb_action")->setValue("wpjb_emp_remove_account");
        if($this->getRequest()->isPost()) {
            $isValid = $form->isValid($this->getRequest()->getAll());
            if(!$isValid) {
                $this->addError($form->getGlobalError());
            }
        }
        
        foreach(array("user_password") as $f) {
            if($form->hasElement($f)) {
                $form->getElement($f)->setValue("");
            }
        }
        
        $this->view->breadcrumbs = array(
            array("title"=>__("Home", "wpjobboard"), "url"=>wpjb_link_to("employer_home"), "glyph"=>"wpjb-icon-home"),
            array("title"=>__("Delete Account", "wpjobboard"), "url"=>wpjb_link_to("employer_delete"), "glyph"=>$this->glyph()),
        );
        
        $this->view->form = $form;
        
        return $this->render("default", "form");
    }
    
    /**
     * Display Employer Panel / Listings
     * 
     * @see wpjb_employer_panel()
     * @example /employer-panel/listings/
     * 
     * @return string   Shortcode HTML
     */
    public function listings() {
        $company = Wpjb_Model_Company::current();
        
        if(!get_current_user_id()) {
            return $this->_loginForm(wpjb_link_to("employer_panel"));
        }
        
        if(!$this->_hasAccess("manage_jobs")) {
            return $this->flash();
        }
        
        if(is_null($company)) {
            $m = __('Please complete your <a href="%s">Employer Profile</a> and then get back to this page.', "wpjobboard");
            $this->addError(sprintf($m, wpjb_link_to("employer_edit")));
            return $this->flash();
        }
        
        $request = Daq_Request::getInstance();
        $browse = $request->get("filter");
        switch($browse) {
            case "pending": $filter = "awaiting"; break;
            case "expired": $filter = "expired"; break;
            case "filled" : $filter = "filled"; break;
            default: 
                $filter = "active";
                $browse = "active";
        }
        
        wp_enqueue_script( "wpjb-manage" );
        
        $this->view = new stdClass();
        $this->view->browse = $browse;

        $page = $request->getParam("page", $request->getParam("pg", 1));
        $count = 20;
        $emp = Wpjb_Model_Company::current();
        $total = new stdClass();
        
        $hide_filled = wpjb_conf("front_hide_filled", false);
        
        $total->active = wpjb_find_jobs(array(
            "filter" => "active",
            "employer_id" => $emp->id,
            "hide_filled" => $hide_filled,
            "count_only" => true,
        ));
        
        $total->expired = wpjb_find_jobs(array(
            "filter" => "expired",
            "employer_id" => $emp->id,
            "hide_filled" => false,
            "count_only" => true
        ));
        
        $total->pending = wpjb_find_jobs(array(
            "filter" => "awaiting",
            "employer_id" => $emp->id,
            "hide_filled" => false,
            "count_only" => true
        ));
        
        $total->filled = wpjb_find_jobs(array(
            "filter" => "filled",
            "employer_id" => $emp->id,
            "hide_filled" => false,
            "count_only" => true
        ));


        $result = wpjb_find_jobs(array(
            "filter" => $filter,
            "employer_id" => $emp->id,
            "hide_filled" => $hide_filled,
            "page" => $page,
            "count" => $count
        ));

        $this->view->total = $total;
        $this->view->result = $result;
        $this->view->jobList = $result->job;
        
        $param = array(
            "filter" => $filter,
            "page" => $page,
            "count" => $count
        );
        
        $this->view->param = $param;
        $this->view->url = wpjb_link_to("employer_panel");
        $this->view->breadcrumbs = array(
            array("title"=>__("Home", "wpjobboard"), "url"=>wpjb_link_to("employer_home"), "glyph"=>"wpjb-icon-home"),
            array("title"=>__("Listings", "wpjobboard"), "url"=>wpjb_link_to("employer_panel"), "glyph"=>$this->glyph()),
        );

        return $this->render("job-board", "company-panel");
    }
    
    /**
     * Display Employer Panel / Job Edit
     * 
     * @see wpjb_employer_panel()
     * @example /employer-panel/job-edit/(int)/
     * 
     * @return string   Shortcode HTML
     */
    public function jobEdit()
    {
        $job = new Wpjb_Model_Job(get_query_var("wpjb-id"));
        $company = Wpjb_Model_Company::current();
        
        if(!get_current_user_id()) {
            return $this->_loginForm(wpjb_link_to("job_edit", $job));
        }
        
        if(!$this->_hasAccess("manage_jobs")) {
            return $this->flash();
        }
        
        if(!Wpjb_Project::getInstance()->conf("front_allow_edition")) {
            $this->addError(__("Your login doesn't allow the editing of existing job postings.", "wpjobboard"));
            return $this->flash();
        }
        
        if(is_null($company)) {
            $m = __('Please complete your <a href="%s">Employer Profile</a> and then get back to this page.', "wpjobboard");
            $this->addError(sprintf($m, wpjb_link_to("employer_edit")));
            return $this->flash();
        }
        
        if(!$job->exists()) {
            $this->addError(__("Job does not exist.", "wpjobboard"));
            return $this->flash();
        }
        
        if($job->employer_id != $company->getId()) {
            $this->addError(__("You do not own this job.", "wpjobboard"));
            return $this->flash();
        }

        $this->view = new stdClass();
        $form = new Wpjb_Form_Frontend_EditJob($job->getId());
        if($this->getRequest()->isPost()) {
            $isValid = $form->isValid($this->getRequest()->getAll());
            if($isValid) {
                $this->addInfo(__("Job has been saved", "wpjobboard"));
                $form->save();
            } else {
                $this->addError($form->getGlobalError());
            }
        }

        $this->view->form = $form;

        $this->view->breadcrumbs = array(
            array("title"=>__("Home", "wpjobboard"), "url"=>wpjb_link_to("employer_home"), "glyph"=>"wpjb-icon-home"),
            array("title"=>__("Listings", "wpjobboard"), "url"=>wpjb_link_to("employer_panel"), "glyph"=>$this->glyph()),
            array("title"=>sprintf(__("Edit Job '%s'", "wpjobboard"), $job->job_title), "url"=>wpjb_link_to("job_edit", $job), "glyph"=>$this->glyph()),
        );
        
        return $this->render("job-board", "job-edit");
    }
    
    /**
     * Display Employer Panel / Job Delete
     * 
     * @see wpjb_employer_panel()
     * @example /employer-panel/job-delete/(int)/
     * 
     * @return string   Shortcode HTML
     */
    public function jobDelete() {

        $job = new Wpjb_Model_Job(get_query_var("wpjb-id"));
        $company = Wpjb_Model_Company::current();
        
        if(!get_current_user_id()) {
            return $this->_loginForm(wpjb_link_to("job_delete", $job));
        }
        
        if(!$this->_hasAccess("manage_jobs")) {
            return $this->flash();
        }
        
        if(!Wpjb_Project::getInstance()->conf("front_allow_edition")) {
            $this->addError(__("Administrator does not allow job postings edition.", "wpjobboard"));
            return $this->flash();
        }
        
        if(is_null($company)) {
            $m = __('Please complete your <a href="%s">Employer Profile</a> and then get back to this page.', "wpjobboard");
            $this->addError(sprintf($m, wpjb_link_to("employer_edit")));
            return $this->flash();
        }
        
        if(!$job->exists()) {
            $this->addError(__("Job does not exist.", "wpjobboard"));
            return $this->flash();
        }
        
        if($job->employer_id != $company->getId()) {
            $this->addError(__("You do not own this job.", "wpjobboard"));
            return $this->flash();
        }
        
        $this->view = new stdClass();
        $this->view->action = "";
        $this->view->submit = __("Delete Job", "wpjobboard");
        
        if(!Wpjb_Project::getInstance()->conf("front_allow_edition")) {
            $this->addError(__("Administrator does not allow job postings edition.", "wpjobboard"));
            return false;
        }
        if($job->employer_id != Wpjb_Model_Company::current()->getId()) {
            $this->addError(__("You are not allowed to access this page.", "wpjobboard"));
            return false;
        }

        $form = new Wpjb_Form_Frontend_DeleteJob($job->getId());
        $form->getElement("redirect_to")->setValue(wpjb_link_to("employer_panel"));
        
        if($this->getRequest()->isPost()) {
            $form->isValid($this->getRequest()->getAll());
        }

        $this->view->form = $form;
        $this->view->breadcrumbs = array(
            array("title"=>__("Home", "wpjobboard"), "url"=>wpjb_link_to("employer_home"), "glyph"=>"wpjb-icon-home"),
            array("title"=>__("Listings", "wpjobboard"), "url"=>wpjb_link_to("employer_panel"), "glyph"=>$this->glyph()),
            array("title"=>sprintf(__("Delete Job '%s'", "wpjobboard"), $job->job_title), "url"=>wpjb_link_to("job_delete", $job), "glyph"=>$this->glyph()),
        );
        
        return $this->render("default", "form");
    }
    
    /**
     * Display Employer Panel / Applications List
     * 
     * @see wpjb_employer_panel()
     * @example /employer-panel/applications/
     * 
     * @return string   Shortcode HTML
     */
    public function applications() {

        $company = Wpjb_Model_Company::current();
        
        if(!get_current_user_id()) {
            return $this->_loginForm(wpjb_link_to("job_applications"));
        }
        
        if(!$this->_hasAccess("manage_jobs")) {
            return $this->flash();
        }
        
        if(is_null($company)) {
            $m = __('Please complete your <a href="%s">Employer Profile</a> and then get back to this page.', "wpjobboard");
            $this->addError(sprintf($m, wpjb_link_to("employer_edit")));
            return $this->flash();
        }

        wp_enqueue_script( "wpjb-manage-apps" );
        
        $this->view = new stdClass();
        $this->view->job = new Wpjb_Model_Job;

        $public_ids = array();
        foreach(wpjb_get_application_status() as $application_status) {
            if($application_status["public"] == 1) {
                $public_ids[] = $application_status["id"];
            }
        }
        
        $query_args = array();
        $filter_status = $public_ids;
        $filter_id = null;
        $filter_page = 1;
        
        $this->view->job_id = Daq_Request::getInstance()->get("job_id");
        $this->view->job_status = Daq_Request::getInstance()->get("job_status");
        
        if($this->view->job_status && in_array($this->view->job_status, $public_ids)) {
            $filter_status = $this->view->job_status;
            $query_args["job_status"] = $this->view->job_status;
        }
        if($this->view->job_id) {
            $filter_id = $this->view->job_id;
            $query_args["job_id"] = $this->view->job_id;
        }
        if(Daq_Request::getInstance()->get("pg")) {
            $filter_page = absint(Daq_Request::getInstance()->get("pg"));
            $query_args["pg"] = $filter_page;
        }
        
        $apps = Wpjb_Model_Application::search(array(
            "status" => $filter_status,
            "job" => $filter_id,
            "owned_by" => Wpjb_Model_Company::current()->id,
            "sort_order" => "applied_at DESC",
            "page" => $filter_page,
            "count" => 20
        ));
        

        $this->view->public_ids = $public_ids;
        $this->view->apps = $apps;
        
        $query = new Daq_Db_Query();
        $query->from("Wpjb_Model_Job t");
        $query->where("employer_id = ?", Wpjb_Model_Company::current()->id);
        $query->order("job_title ASC");
        
        $result = $query->execute();
        $this->view->jobsList = $result;
        $this->view->query_args = $query_args;

        $this->view->page_id = get_the_ID();
        $this->view->url = wpjb_link_to("job_applications");
        
        $breadcrumbs = array(
            array("title"=>__("Home", "wpjobboard"), "url"=>wpjb_link_to("employer_home"), "glyph"=>"wpjb-icon-home"),
            array("title"=>__("Applications", "wpjobboard"), "url"=>wpjb_link_to("job_applications"), "glyph"=>$this->glyph()),
        );
        
        $this->view->breadcrumbs = $breadcrumbs;
        
        return $this->render("job-board", "job-applications");
    }
    
    /**
     * Display Employer Panel / Application Details
     * 
     * @see wpjb_employer_panel()
     * @example /employer-panel/application/(int)/
     * 
     * @return string   Shortcode HTML
     */
    public function application() {
        
        $application = new Wpjb_Model_Application(get_query_var("wpjb-id"));
        $company = Wpjb_Model_Company::current();
        
        
        if(!get_current_user_id()) {
            return $this->_loginForm(wpjb_link_to("job_application", $application));
        }
        
        if(!$this->_hasAccess("manage_jobs")) {
            return $this->flash();
        }
        
        if(is_null($company)) {
            $m = __('Please complete your <a href="%s">Employer Profile</a> and then get back to this page.', "wpjobboard");
            $this->addError(sprintf($m, wpjb_link_to("employer_edit")));
            return $this->flash();
        }

        $public_ids = array();
        foreach(wpjb_get_application_status() as $application_status) {
            if($application_status["public"] == 1) {
                $public_ids[] = $application_status["id"];
            }
        }
        
        if(!$application->exists() || !in_array($application->status, $public_ids)) {
            $this->addError(__('Application does not exist.', "wpjobboard"));
            return $this->flash();
        }
        
        $job = new Wpjb_Model_Job($application->job_id);
        
        if(!$job->exists()) {
            $this->addError(__('Job does not exist.', "wpjobboard"));
            return $this->flash();
        }
        
        if($job->employer_id != $company->id) {
            $this->addError(__('You do not own this job application.', "wpjobboard"));
            return $this->flash();
        }
        
        wp_enqueue_script( "wpjb-manage-apps" );
        
        $apps = Wpjb_Model_Application::search(array(
            "owned_by" => Wpjb_Model_Company::current()->id,
            "job" => Daq_Request::getInstance()->get("job_id"),
            "status" => absint(Daq_Request::getInstance()->get("job_status")),
            "sort_order" => "applied_at ASC",
            "ids_only" => true
        ));

        $app_i = 0;
        $app_older = null;
        $app_newer = null;
        $query_args = array(
            "job_id" => Daq_Request::getInstance()->get("job_id"),
            "job_status" => Daq_Request::getInstance()->get("job_status"),
            "pg" => Daq_Request::getInstance()->get("pg"),
        );
        
        foreach($apps->application as $t_id) {
            $app_i++;
            if($t_id == $application->id) {
                break;
            } 
        }
        if(isset($apps->application[$app_i])) {
            $app_newer = new Wpjb_Model_Application($apps->application[$app_i]);
        }
        if(isset($apps->application[$app_i-2])) {
            $app_older = new Wpjb_Model_Application($apps->application[$app_i-2]);
        }
        
        $this->view = new stdClass();
        $this->view->public_ids = $public_ids;
        $this->view->application = $application;
        $this->view->job = $job;
        $this->view->query_args = $query_args;
        
        $this->view->apps = $apps;
        $this->view->app_i = $app_i;
        $this->view->app_older = $app_older;
        $this->view->app_newer = $app_newer;
        
        $this->view->breadcrumbs = array(
            array("title"=>__("Home", "wpjobboard"), "url"=>wpjb_link_to("employer_home"), "glyph"=>"wpjb-icon-home"),
            array("title"=>__("Applications", "wpjobboard"), "url"=>add_query_arg($query_args, wpjb_link_to("job_applications")), "glyph"=>$this->glyph()),
            array("title"=>$application->applicant_name, "url"=>add_query_arg($query_args, wpjb_link_to("job_application", $application)), "glyph"=>$this->glyph()),
            
        );

        return $this->render("job-board", "job-application");
    }
    
    /**
     * Display Employer Panel / Memberships
     * 
     * @see wpjb_employer_panel()
     * @example /employer-panel/membership/
     * 
     * @return string   Shortcode HTML
     */
    public function membership() {
        
        $company = Wpjb_Model_Company::current();
        
        if(!get_current_user_id()) {
            return $this->_loginForm(wpjb_link_to("membership"));
        }
        
        if(!$this->_hasAccess("manage_jobs")) {
            return $this->flash();
        }
        
        if(is_null($company)) {
            $m = __('Please complete your <a href="%s">Employer Profile</a> and then get back to this page.', "wpjobboard");
            $this->addError(sprintf($m, wpjb_link_to("employer_edit")));
            return $this->flash();
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
                    return $this->render("job-board", "company-products-remove-confirm");
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
        $query->where("price_for = ?", Wpjb_Model_Pricing::PRICE_EMPLOYER_MEMBERSHIP);
        
        $result = $query->execute();
        
        $this->view->result = $result;

        $this->view->breadcrumbs = array(
            array("title"=>__("Home", "wpjobboard"), "url"=>wpjb_link_to("employer_home"), "glyph"=>"wpjb-icon-home"),
            array("title"=>__("Membership", "wpjobboard"), "url"=>wpjb_link_to("membership"), "glyph"=>$this->glyph()),
        );
        
        return $this->render("job-board", "company-products");
    }
    
    /**
     * Display Employer Panel / Membership Purchase
     * 
     * @see wpjb_employer_panel()
     * @example /employer-panel/membership-purchase/(int)/
     * 
     * @return string   Shortcode HTML
     */
    public function membershipPurchase()
    {
        $pricing = new Wpjb_Model_Pricing(get_query_var("wpjb-id"));
        $company = Wpjb_Model_Company::current();
        
        if(!get_current_user_id()) {
            return $this->_loginForm(wpjb_link_to("membership_purchase", $pricing));
        }
        
        if(!$this->_hasAccess("manage_jobs")) {
            return $this->flash();
        }
        
        if(is_null($company)) {
            $m = __('Please complete your <a href="%s">Employer Profile</a> and then get back to this page.', "wpjobboard");
            $this->addError(sprintf($m, wpjb_link_to("employer_edit")));
            return $this->flash();
        }
        
        $this->view = new stdClass();
        $this->view->action = "";
        
        if(!$pricing->exists()) {
            $this->addError(__("Pricing does not exist.", "wpjobboard"));
            return $this->flash();
        }
        
        if($pricing->price_for != Wpjb_Model_Pricing::PRICE_EMPLOYER_MEMBERSHIP || $pricing->is_active == 0) {
            $this->addError(__("Incorrect package ID.", "wpjobboard"));
            return $this->flash();
        }
        
        $this->view->breadcrumbs = array(
            array("title"=>__("Home", "wpjobboard"), "url"=>wpjb_link_to("employer_home"), "glyph"=>"wpjb-icon-home"),
            array("title"=>__("Membership", "wpjobboard"), "url"=>wpjb_link_to("membership"), "glyph"=>$this->glyph()),
            array("title"=>__("Purchase Membership", "wpjobboard"), "url"=>wpjb_link_to("membership_purchase", $pricing), "glyph"=>$this->glyph()),
        );
        
        if($pricing->price == 0) {

            $member = new Wpjb_Model_Membership();
            $member->user_id = wpjb_get_current_user_id("employer");
            $member->package_id = $pricing->id;
            $member->started_at = "0000-00-00";
            $member->expires_at = "0000-00-00";
            $member->deriveFrom($pricing);
            $member->save();
            $member->paymentAccepted();
            
            $this->addInfo(__("Your free membership is now active.", "wpjobboard"));
            return $this->flash();
        } else {
            
            $this->view->pricing = $pricing;
            $this->view->gateways = Wpjb_Project::getInstance()->payment->getEnabled();
            $this->view->pricing_item = __("Membership") . " &quot;" . $pricing->title . "&quot;";
            $this->view->defaults = new Daq_Helper_Html("span", array(
                "id" => "wpjb-checkout-defaults",
                "class" => "wpjb-none",

                "data-pricing_id" => $pricing->id,
                "data-fullname" => Wpjb_Model_Company::current()->company_name,
                "data-email" => wp_get_current_user()->user_email,

            ), " ");

            return $this->render("default", "payment");
        }
        
    }
    
    /**
     * Display Employer Panel / Membership Details
     * 
     * @see wpjb_employer_panel()
     * @example /employer-panel/membership-details/
     * 
     * @return string   Shortcode HTML
     */
    public function membershipDetails()
    {
        $pricing = new Wpjb_Model_Pricing(get_query_var("wpjb-id"));
        $company = Wpjb_Model_Company::current();
        
        if(!get_current_user_id()) {
            return $this->_loginForm(wpjb_link_to("membership_details", $pricing));
        }
        
        if(!$this->_hasAccess("manage_jobs")) {
            return $this->flash();
        }
        
        if(is_null($company)) {
            $m = __('Please complete your <a href="%s">Employer Profile</a> and then get back to this page.', "wpjobboard");
            $this->addError(sprintf($m, wpjb_link_to("employer_edit")));
            return $this->flash();
        }
        
        $this->view = new stdClass();
        
        $this->view->summary = Wpjb_Model_Membership::getPackageSummary($pricing->id, wpjb_get_current_user_id("employer"));
        $this->view->pricing = $pricing;
        
        $this->view->breadcrumbs = array(
            array("title"=>__("Home", "wpjobboard"), "url"=>wpjb_link_to("employer_home"), "glyph"=>"wpjb-icon-home"),
            array("title"=>__("Membership", "wpjobboard"), "url"=>wpjb_link_to("membership"), "glyph"=>$this->glyph()),
            array("title"=>sprintf(__("Package Details: '%s'", "wpjobboard"), $pricing->title), "url"=>wpjb_link_to("membership_details", $pricing), "glyph"=>$this->glyph()),
        );
        
        return $this->render("job-board", "company-product-details");
    }
    
    public function forcePanelLinks($urls) {
        $urls = new stdClass();
        $urls->add = wpjb_link_to("job_add");
        $urls->preview = wpjb_link_to("job_preview");
        $urls->reset = wpjb_link_to("job_reset");
        $urls->save = wpjb_link_to("job_save");

        return $urls;
    }
    
    
    /**
     * Display Employer Panel / Payment History
     * 
     * @see wpjb_employer_panel()
     * @example /employer-panel/payment/
     * 
     * @return string   Shortcode HTML
     */
    public function paymentHistory() {

        if(!get_current_user_id()) {
            return $this->_loginForm(wpjb_link_to("payment"));
        }
        
        if(!$this->_hasAccess("manage_jobs")) {
            return $this->flash();
        }
        
        $company = Wpjb_Model_Company::current();
        
        if(is_null($company)) {
            $m = __('Please complete your <a href="%s">Employer Profile</a> and then get back to this page.', "wpjobboard");
            $this->addError(sprintf($m, wpjb_link_to("employer_edit")));
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
                case 1:
                    $object = new Wpjb_Model_Job($payment->object_id);
                    if( is_object( $object ) && strlen( $object->job_title ) > 0 ) {
                        $this->view->pricing_item = __("Job", "wpjobboard") . " &quot;" . $object->job_title . "&quot;";
                    } else {
                        $this->view->pricing_item = __("Job No Longer Exists", "wpjobboard");
                    }
                    break;
                case 2:
                    $object = new Wpjb_Model_Resume($payment->object_id);
                    if( is_object( $object ) && strlen( $object->getSearch(true)->fullname ) ) {
                        $this->view->pricing_item = __("Resume", "wpjobboard") . " &quot;" . $object->getSearch(true)->fullname . "&quot;";
                    } else {
                        $this->view->pricing_item = __("Resume No Longer Exists", "wpjobboard");
                    }
                    break;
                case 3:
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
        $query->where("user_id = ?", $company->user_id);
        $query->where("object_type IN (?)", array(1,2,3) );
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
        $this->view->url = wpjb_link_to('payment_history');

        $this->view->breadcrumbs = array(
            array("title"=>__("Home", "wpjobboard"), "url"=>wpjb_link_to("employer_home"), "glyph"=>"wpjb-icon-home"),
            array("title"=>__("Payment History", "wpjobboard"), "url"=>wpjb_link_to("payment"), "glyph"=>$this->glyph()),
        );
        
        return $this->render("job-board", "company-payment-history");
    }
    
    /**
     * Not Used Anymore.
     */
    /*public function paymentDetails() {
        
        $company = Wpjb_Model_Company::current();
        $payment = new Wpjb_Model_Payment(get_query_var("wpjb-id"));
        
        if(!get_current_user_id()) {
            return $this->_loginForm(wpjb_link_to("membership"));
        }
        
        if(!$this->_hasAccess("manage_jobs")) {
            return $this->flash();
        }
        
        if(is_null($company)) {
            $m = __('Please complete your <a href="%s">Employer Profile</a> and then get back to this page.', "wpjobboard");
            $this->addError(sprintf($m, wpjb_link_to("employer_edit")));
            return $this->flash();
        }
        
        $this->view = new stdClass();
        
        $this->view->payment = $payment;
        
        $this->view->breadcrumbs = array(
            array("title"=>__("Home", "wpjobboard"), "url"=>wpjb_link_to("employer_home"), "glyph"=>"wpjb-icon-home"),
            array("title"=>__("Payment History", "wpjobboard"), "url"=>wpjb_link_to("payment"), "glyph"=>$this->glyph()),
            array("title"=>sprintf(__("Payment Details: '%s'", "wpjobboard"), $payment->id), "url"=>wpjb_link_to("membership_details", $pricing), "glyph"=>$this->glyph()),
        );
        
        return $this->render("job-board", "company-product-details");
        
    }*/
    
    /**
     * Display job adding steps in employer dashboard
     * 
     * @param type $current_step
     * @param type $view
     */
    public function steps($current_step, $view) {
        $breadcrumbs = array(
            array("title"=>__("Home", "wpjobboard"), "url"=>wpjb_link_to("employer_home"), "glyph"=>"wpjb-icon-home"),
            array("title"=>$view->steps[1], "url"=>$view->urls->add, "glyph"=>$this->glyph()),
            array("title"=>$view->steps[2], "url"=>$view->urls->preview, "glyph"=>$this->glyph()),
            array("title"=>$view->steps[3], "url"=>$view->urls->save, "glyph"=>$this->glyph()),
        );
        
        $total = count($breadcrumbs);
        $bc = array();
        
        for($i=0; $i<$total; $i++) {
            if($i<$current_step+1) {
                $bc[] = $breadcrumbs[$i];
            } else {
                $crumb = $breadcrumbs[$i];
                $crumb["url"] = null;
                $bc[] = $crumb;
            }
        }
        
        echo wpjb_breadcrumbs($bc);
    }
    
    /**
     * Job Add Page In Employer Dashboard
     * 
     * @return type
     */
    public function jobAdd() {
        $company = Wpjb_Model_Company::current();

        if(!get_current_user_id()) {
            return $this->_loginForm(wpjb_link_to("job_add"));
        }
        
        if(!$this->_hasAccess("manage_jobs")) {
            return $this->flash();
        }
        
        if(is_null($company)) {
            $m = __('Please complete your <a href="%s">Employer Profile</a> and then get back to this page.', "wpjobboard");
            $this->addError(sprintf($m, wpjb_link_to("employer_edit")));
            return $this->flash();
        }
        
        add_filter("wpjb_jobs_add_init_urls", array($this, "forcePanelLinks"));
        add_action("wpjb_jobs_add_steps", array($this, "steps"), 10, 2);
        Wpjb_Project::getInstance()->shortcode->wpjb_jobs_add->mode = "panel";
        
        $content = Wpjb_Project::getInstance()->shortcode->wpjb_jobs_add->add();
        
        Wpjb_Project::getInstance()->shortcode->wpjb_jobs_add->mode = "standalone";
        remove_filter("wpjb_jobs_add_init_urls", array($this, "forcePanelLinks"));
        remove_action("wpjb_jobs_add_steps", array($this, "steps"), 10, 2);
        
        return $content;
    }
    
    /**
     * Job Preview Page In Employer Dashboard
     * 
     * @return type
     */
    public function jobPreview() {
        $company = Wpjb_Model_Company::current();
        
        if(!get_current_user_id()) {
            return $this->_loginForm(wpjb_link_to("job_add"));
        }
        
        if(!$this->_hasAccess("manage_jobs")) {
            return $this->flash();
        }
        
        if(is_null($company)) {
            $m = __('Please complete your <a href="%s">Employer Profile</a> and then get back to this page.', "wpjobboard");
            $this->addError(sprintf($m, wpjb_link_to("employer_edit")));
            return $this->flash();
        }
        
        add_filter("wpjb_jobs_add_init_urls", array($this, "forcePanelLinks"));
        add_action("wpjb_jobs_add_steps", array($this, "steps"), 10, 2);
        Wpjb_Project::getInstance()->shortcode->wpjb_jobs_add->mode = "panel";
        
        $content = Wpjb_Project::getInstance()->shortcode->wpjb_jobs_add->preview();
        
        Wpjb_Project::getInstance()->shortcode->wpjb_jobs_add->mode = "standalone";
        remove_filter("wpjb_jobs_add_init_urls", array($this, "forcePanelLinks"));
        remove_action("wpjb_jobs_add_steps", array($this, "steps"), 10, 2);
        
        return $content;
    }
    
    /**
     * Job Save Page In Employer Dashboard
     * 
     * @return type
     */
    public function jobSave() {
        $company = Wpjb_Model_Company::current();
        
        if(!get_current_user_id()) {
            return $this->_loginForm(wpjb_link_to("job_add"));
        }
        
        if(!$this->_hasAccess("manage_jobs")) {
            return $this->flash();
        }
        
        if(is_null($company)) {
            $m = __('Please complete your <a href="%s">Employer Profile</a> and then get back to this page.', "wpjobboard");
            $this->addError(sprintf($m, wpjb_link_to("employer_edit")));
            return $this->flash();
        }
        
        add_filter("wpjb_jobs_add_init_urls", array($this, "forcePanelLinks"));
        add_action("wpjb_jobs_add_steps", array($this, "steps"), 10, 2);
        Wpjb_Project::getInstance()->shortcode->wpjb_jobs_add->mode = "panel";
        
        $content = Wpjb_Project::getInstance()->shortcode->wpjb_jobs_add->save();
        
        Wpjb_Project::getInstance()->shortcode->wpjb_jobs_add->mode = "standalone";
        remove_filter("wpjb_jobs_add_init_urls", array($this, "forcePanelLinks"));
        remove_action("wpjb_jobs_add_steps", array($this, "steps"), 10, 2);
        
        return $content;
    }
    
    public function jobReset() {
        $company = Wpjb_Model_Company::current();
        
        if(!get_current_user_id()) {
            return $this->_loginForm(wpjb_link_to("employer_panel"));
        }
        
        if(!$this->_hasAccess("manage_jobs")) {
            return $this->flash();
        }
        
        if(is_null($company)) {
            $m = __('Please complete your <a href="%s">Employer Profile</a> and then get back to this page.', "wpjobboard");
            $this->addError(sprintf($m, wpjb_link_to("employer_edit")));
            return $this->flash();
        }
        
        add_filter("wpjb_jobs_add_init_urls", array($this, "forcePanelLinks"));
        add_action("wpjb_jobs_add_steps", array($this, "steps"), 10, 2);
        Wpjb_Project::getInstance()->shortcode->wpjb_jobs_add->mode = "panel";
        
        $content = Wpjb_Project::getInstance()->shortcode->wpjb_jobs_add->reset();
        
        Wpjb_Project::getInstance()->shortcode->wpjb_jobs_add->mode = "standalone";
        remove_filter("wpjb_jobs_add_init_urls", array($this, "forcePanelLinks"));
        remove_action("wpjb_jobs_add_steps", array($this, "steps"), 10, 2);
        
        return $content;
    }

}
