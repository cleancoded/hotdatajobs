<?php
/**
 * Description of AddJob
 *
 * @author greg
 * @package
 */

class Wpjb_Module_Frontend_Employer extends Wpjb_Controller_Frontend
{

    protected $_employer = null;

    protected function _isLoggedIn()
    {
        return current_user_can("manage_jobs");
    }
    
    public function _handleNew()
    {
        $company = Wpjb_Model_Company::current();
        
        if(is_user_logged_in() && current_user_can("manage_jobs") && is_null($company)) {
            $this->view->_flash->addInfo(__("Please update (if required) and save your profile before continuing.", "wpjobboard"));
            return $this->redirect("employer_edit");
        }
    }
    
    protected function _isCandidate()
    {
        $id = wp_get_current_user()->ID;
        $isCand = $id && !current_user_can("manage_jobs");
        if($isCand) {
            $err = __("You need 'Employer' account in order to access this page. Currently you are logged in as Candidate.", "wpjobboard");
            $this->setTitle(__("Incorrect account type", "wpjobboard"));
            $this->view->_flash->addError($err);
        }
        return $isCand;
    }
    
    protected function _loginForm($redirect)
    {
        $this->view->_flash->addError(__("Login to access this page.", "wpjobboard"));
        
        $form = new Wpjb_Form_Login();
        $form->getElement("redirect_to")->setValue($redirect);

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
        
        return "../default/form";
    }
    
    public function redirect($path) {
        if(Wpjb_Project::getInstance()->shortcodeIs()) {
            switch($path) {
                case "employer_edit": return $this->employereditAction();
            }
        } else {
            parent::redirect(wpjb_link_to($path));
        }
    }
    
    public function afterTitle($lname, $link) 
    {
        if($lname == "applications") {
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
    
    public function homeAction()
    {
        if(!$this->_isLoggedIn()) {
            return $this->_loginForm(wpjb_link_to("employer_home"));
        }
        
        if($this->_isCandidate()) {
            return false;
        }
        
        $this->setTitle(__("Employer Dashboard", "wpjobboard"));
        
        add_action("wpjb_employer_panel_after_title", array($this, "afterTitle"), 10, 2);
        
        
        $manager = Wpjb_Project::getInstance()->env("user_manager");
        /* @var $manager Wpjb_User_Manager */
        
        $dashboard = $manager->buildDashboard("employer", get_the_ID());
        
        
        $this->view->dashboard = apply_filters("wpjb_employer_panel_links", $dashboard);
        
        return "company-home";
    }

    public function registerAction()
    {
        
        if(get_current_user_id()) {
            $m = __('You are already logged in, <a href="%s">Logout</a> before creating new account.', "wpjobboard");
            $this->view->_flash->addError(sprintf($m, wpjb_link_to("employer_logout")));
            return false;
        }

        $this->setTitle(__("Register Employer", "wpjobboard"));

        $form = new Wpjb_Form_Frontend_Register();
        $this->view->errors = array();

        if($this->isPost()) {
            $isValid = $form->isValid($this->getRequest()->getAll());
            if($isValid) {
                // do nothing
            } else {
                $this->view->_flash->addError($form->getGlobalError());
            }
        }

        $this->view->form = $form;
        return "company-new";
    }

    public function loginAction()
    {
        if($this->_isLoggedIn()) {
            $this->redirect("employer_panel");
        }
        
        if($this->_isCandidate()) {
            return false;
        }
        
        $this->setTitle(__("Employer Login", "wpjobboard"));
        $form = new Wpjb_Form_Login();
        
        if($this->getRequest()->get("redirect_to")) {
            $redirect = base64_decode($this->getRequest()->get("redirect_to"));
            $form->getElement("redirect_to")->setValue($redirect);
        } else {
            $form->getElement("redirect_to")->setValue(wpjb_link_to("employer_home"));
        }
        
        if($this->isPost() && $this->getRequest()->post("_wpjb_action")=="login") {
            $form->isValid($this->getRequest()->getAll());
        }

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
        
        return array("../default/form", "company-login");
    }

    public function panelactiveAction()
    {
        if($this->_isCandidate()) {
            return false;
        }
        
        if(!$this->_isLoggedIn()) {
            return $this->_loginForm(wpjb_link_to("employer_panel"));
        }
        
        $hn = $this->_handleNew();
        if($hn) {
            return $hn;
        }
        
        $this->setTitle(__("Listings", "wpjobboard"));
        $this->_panel($this->_request->get("filter"));

        $instance = Wpjb_Project::getInstance();
        $router = $instance->getApplication("frontend")->getRouter();
        
        wp_enqueue_script( "wpjb-manage" );
        
        $this->view->breadcrumbs = array(
            array("title"=>__("Home", "wpjobboard"), "url"=>wpjb_link_to("employer_home"), "glyph"=>"wpjb-icon-home"),
            array("title"=>__("Listings", "wpjobboard"), "url"=>wpjb_link_to("employer_panel"), "glyph"=>$this->glyph()),
        );
        
        $this->view->cDir = $router->linkTo("employer_panel");
        $this->view->routerIndex = "employer_panel";
        return "company-panel";
    }
    
    public function _panel($browse)
    {
        switch($browse) {
            case "pending": $filter = "awaiting"; break;
            case "expired": $filter = "expired"; break;
            case "filled" : $filter = "filled"; break;
            default: 
                $filter = "active";
                $browse = "active";
        }
        
        $this->view->browse = $browse;
        $request = $this->_request;
        
        // count jobs;
        $page = $request->getParam("page", $request->getParam("pg", 1));
        $count = 20;
        $emp = Wpjb_Model_Company::current();
        $total = new stdClass();
        
        $total->active = wpjb_find_jobs(array(
            "filter" => "active",
            "employer_id" => $emp->id,
            "hide_filled" => false,
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
            "hide_filled" => false,
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
        
        
        return "company-panel";
    }

    public function applicationsAction()
    {
        $this->setTitle(__("Job Applications", "wpjobboard"));
        
        if($this->_isCandidate()) {
            return false;
        }
        
        $hn = $this->_handleNew();
        if($hn) {
            return $hn;
        }
        
        if(!$this->_isLoggedIn()) {
            return $this->_loginForm(wpjb_link_to("job_applications"));
        }

        wp_enqueue_script( "wpjb-manage-apps" );
        
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
        
        
        return "job-applications";
    }

    public function applicationAction()
    {
        $application = $this->getObject();
        $job = new Wpjb_Model_Job($application->job_id);
        $emp = $job->getCompany(true);

        if($this->_isCandidate()) {
            return false;
        }
        
        $hn = $this->_handleNew();
        if($hn) {
            return $hn;
        }
        
        $isOwner = false;
        if($emp->user_id > 0 && $emp->user_id == wpjb_get_current_user_id("employer")) {
           $isOwner = true;
        }

        if(!$isOwner) {
            return false;
        }
        
        $public_ids = array();
        foreach(wpjb_get_application_status() as $application_status) {
            if($application_status["public"] == 1) {
                $public_ids[] = $application_status["id"];
            }
        }
        
        if(!$application->exists() || !in_array($application->status, $public_ids)) {
            $this->view->_flash->addError(__("Application does not exist.", "wpjobboard"));
            return false;
        }
        
        if(!$this->_isLoggedIn()) {
            return $this->_loginForm(wpjb_link_to("job_application", $application));
        }

        if($this->isPost()) {
            $application->status = (int)$this->_request->post("status");
            $application->save();
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

        $this->setTitle(__("Application for position: {job_title}", "wpjobboard"), array(
            "job_title" => $job->job_title
        ));
        
        $this->view->public_ids = $public_ids;
        $this->view->application = $application;
        $this->view->job = $job;
        $this->view->query_args = $query_args;
        
        $this->view->apps = $apps;
        $this->view->app_i = $app_i;
        $this->view->app_older = $app_older;
        $this->view->app_newer = $app_newer;
        
        $breadcrumbs = array(
            array("title"=>__("Home", "wpjobboard"), "url"=>wpjb_link_to("employer_home"), "glyph"=>"wpjb-icon-home"),
            array("title"=>__("Applications", "wpjobboard"), "url"=>add_query_arg($query_args, wpjb_link_to("job_applications")), "glyph"=>$this->glyph()),
            array("title"=>$application->applicant_name, "url"=>add_query_arg($query_args, wpjb_link_to("job_application", $application)), "glyph"=>$this->glyph()),
            
        );
        
        $this->view->breadcrumbs = $breadcrumbs;
        
        return "job-application";
    }

    public function editAction()
    {
        $job = $this->getObject();

        if($this->_isCandidate()) {
            return false;
        }
        
        if(!$this->_isLoggedIn()) {
            return $this->_loginForm(wpjb_link_to("job_edit"));
        }
        
        $hn = $this->_handleNew();
        if($hn) {
            return $hn;
        }
        
        $this->setTitle(__("Edit Job", "wpjobboard"));

        if(!Wpjb_Project::getInstance()->conf("front_allow_edition")) {
            $this->view->_flash->addError(__("Administrator does not allow job postings edition.", "wpjobboard"));
            return false;
        }
        if($job->employer_id != Wpjb_Model_Company::current()->getId()) {
            $this->view->_flash->addError(__("You are not allowed to access this page.", "wpjobboard"));
            return false;
        }

        $form = new Wpjb_Form_Frontend_EditJob($job->getId());
        if($this->isPost()) {
            $isValid = $form->isValid($this->getRequest()->getAll());
            if($isValid) {
                $this->view->_flash->addInfo(__("Job has been saved", "wpjobboard"));
                $form->save();
            } else {
                $this->view->_flash->addError($form->getGlobalError());
            }
        }

        $this->view->form = $form;

        if(!in_array(Wpjb_Model_Job::STATUS_EXPIRED, $job->status())) {
            $jobBc = array("title"=>__("Active Listings", "wpjobboard"), "url"=>wpjb_link_to("employer_panel"), "glyph"=>$this->glyph());
        } else {
            $jobBc = array("title"=>__("Expired Listings", "wpjobboard"), "url"=>wpjb_link_to("employer_panel_expired"), "glyph"=>$this->glyph());
        }
        
        $breadcrumbs = array(
            array("title"=>__("Home", "wpjobboard"), "url"=>wpjb_link_to("employer_home"), "glyph"=>"wpjb-icon-home"),
            $jobBc,
            array("title"=>sprintf(__("Edit Job '%s'", "wpjobboard"), $job->job_title), "url"=>wpjb_link_to("job_edit", $job), "glyph"=>$this->glyph()),
        );
        
        $this->view->breadcrumbs = $breadcrumbs;
        
        return "job-edit";
    }
    
    public function removeAction()
    {
        $job = $this->getObject();

        if($this->_isCandidate()) {
            return false;
        }
        
        $hn = $this->_handleNew();
        if($hn) {
            return $hn;
        }
        
        $this->setTitle(__("Delete Job", "wpjobboard"));
        $this->view->action = "";
        $this->view->submit = __("Delete Job", "wpjobboard");
        
        if(!Wpjb_Project::getInstance()->conf("front_allow_edition")) {
            $this->view->_flash->addError(__("Administrator does not allow job postings edition.", "wpjobboard"));
            return false;
        }
        if($job->employer_id != Wpjb_Model_Company::current()->getId()) {
            $this->view->_flash->addError(__("You are not allowed to access this page.", "wpjobboard"));
            return false;
        }

        $form = new Wpjb_Form_Frontend_DeleteJob($job->getId());
        if($this->isPost()) {
            $form->isValid($this->getRequest()->getAll());
        }

        $this->view->form = $form;
        
        if(!in_array(Wpjb_Model_Job::STATUS_EXPIRED, $job->status())) {
            $jobBc = array("title"=>__("Active Listings", "wpjobboard"), "url"=>wpjb_link_to("employer_panel"), "glyph"=>$this->glyph());
            $form->getElement("redirect_to")->setValue(wpjb_link_to("employer_panel"));
        } else {
            $jobBc = array("title"=>__("Expired Listings", "wpjobboard"), "url"=>wpjb_link_to("employer_panel"), "glyph"=>$this->glyph());
            $form->getElement("redirect_to")->setValue(wpjb_link_to("employer_panel"));
        }
        
        $breadcrumbs = array(
            array("title"=>__("Home", "wpjobboard"), "url"=>wpjb_link_to("employer_home"), "glyph"=>"wpjb-icon-home"),
            $jobBc,
            array("title"=>sprintf(__("Delete Job '%s'", "wpjobboard"), $job->job_title), "url"=>wpjb_link_to("job_delete", $job), "glyph"=>$this->glyph()),
        );
        
        $this->view->breadcrumbs = $breadcrumbs;
        
        return "../default/form";
    }
    
    public function passwordAction()
    {
        if($this->_isCandidate()) {
            return false;
        }
        
        if(!$this->_isLoggedIn()) {
            return $this->_loginForm(wpjb_link_to("employer_password"));
        }
        
        $url = wpjb_link_to("employer_edit");
        
        $this->setTitle(__("Change Password", "wpjobboard"));
        $this->view->action = "";
        $this->view->submit = __("Change Password", "wpjobboard");
        
        $form = new Wpjb_Form_PasswordChange();
        if($this->isPost()) {
            $isValid = $form->isValid($this->getRequest()->getAll());
            if($isValid) {
                $result = wp_update_user(array("ID"=> get_current_user_id(), "user_pass"=>$form->value("user_password")));
                $s = __("Your password has been changed. <a href=\"%s\">Go Back &rarr;</a>", "wpjobboard");
                $this->view->_flash->addInfo(sprintf($s, $url));
                return false;
            } else {
                $this->view->_flash->addError($form->getGlobalError());
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
        
        return "../default/form"; 
    }
    
    public function deleteAction()
    {
        global $current_user;
        
        if($this->_isCandidate()) {
            return false;
        }
        
        if(!$this->_isLoggedIn()) {
            return $this->_loginForm(wpjb_link_to("employer_delete"));
        }
        
        $hn = $this->_handleNew();
        if($hn) {
            return $hn;
        }
        
        $user = Wpjb_Model_Company::current();
        $full = Wpjb_Model_Company::DELETE_FULL;
        
        $this->setTitle(__("Delete Account", "wpjobboard"));
        $this->view->action = "";
        $this->view->submit = __("Delete Account", "wpjobboard");
        
        $form = new Wpjb_Form_DeleteAccount();
        
        if($this->isPost()) {
            $isValid = $form->isValid($this->getRequest()->getAll());
            if($isValid) {
                $user->delete($full);
                $current_user = null;
                @wp_logout();
                $this->setTitle(__("Account Deleted", "wpjobboard"));
                $s = __("Your account has been deleted. <a href=\"%s\">Go Back &rarr;</a>", "wpjobboard");
                $this->view->_flash->addInfo(sprintf($s, get_home_url()));
                return false;
            } else {
                $this->view->_flash->addError($form->getGlobalError());
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
        
        return "../default/form"; 
    }
    
    public function membershipAction()
    {
        if($this->_isCandidate()) {
            return false;
        }
        
        if(!$this->_isLoggedIn()) {
            return $this->_loginForm(wpjb_link_to("membership"));
        }
        
        $hn = $this->_handleNew();
        if($hn) {
            return $hn;
        }
        
        $this->setTitle(__("Membership", "wpjobboard"));
        
        $query = new Daq_Db_Query();
        $query->from("Wpjb_Model_Pricing t");
        $query->where("price_for = ?", Wpjb_Model_Pricing::PRICE_EMPLOYER_MEMBERSHIP);
        
        $result = $query->execute();
        $this->view->result = $result;

        $purchase = $this->getRequest()->get("purchase");

        $this->view->breadcrumbs = array(
            array("title"=>__("Home", "wpjobboard"), "url"=>wpjb_link_to("employer_home"), "glyph"=>"wpjb-icon-home"),
            array("title"=>__("Membership", "wpjobboard"), "url"=>wpjb_link_to("membership"), "glyph"=>$this->glyph()),
        );
        
        return "company-products";
    }
    
    public function membershipPurchaseAction()
    {
        $this->view->action = "";
        $pricing = $this->getObject();

        if($this->_isCandidate()) {
            return false;
        }
        
        if(!$this->_isLoggedIn()) {
            return $this->_loginForm(wpjb_link_to("membership_purchase", $this->getObject()));
        }
        
        $hn = $this->_handleNew();
        if($hn) {
            return $hn;
        }
        
        if($pricing->price_for != Wpjb_Model_Pricing::PRICE_EMPLOYER_MEMBERSHIP) {
            $this->view->_flash->addError(__("Incorrect package ID.", "wpjobboard"));
            return false;
        }
        
        if($pricing->is_active == 0) {
            $this->view->_flash->addError(__("Incorrect package ID.", "wpjobboard"));
            return false;
        }
        
        $this->view->breadcrumbs = array(
            array("title"=>__("Home", "wpjobboard"), "url"=>wpjb_link_to("employer_home"), "glyph"=>"wpjb-icon-home"),
            array("title"=>__("Membership", "wpjobboard"), "url"=>wpjb_link_to("membership"), "glyph"=>$this->glyph()),
            array("title"=>__("Purchase Membership", "wpjobboard"), "url"=>wpjb_link_to("membership_purchase", $pricing), "glyph"=>$this->glyph()),
        );
        
        if($pricing->price == 0) {
            $title = __("Activate Membership", "wpjobboard");
            $this->setTitle($title);
            
            $member = new Wpjb_Model_Membership();
            $member->user_id = wpjb_get_current_user_id("employer");
            $member->package_id = $pricing->id;
            $member->started_at = "0000-00-00";
            $member->expires_at = "0000-00-00";
            $member->deriveFrom($pricing);
            $member->save();
            $member->paymentAccepted();
            
            $this->view->_flash->addInfo(__("Your free membership is now active.", "wpjobboard"));
            return false;
        } else {
            $title = __("Purchase Membership", "wpjobboard");
            $this->setTitle($title);
            
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

            return "../default/payment";
        }
        
    }
    
    public function membershipDetailsAction()
    {
        if($this->_isCandidate()) {
            return false;
        }
        
        if(!$this->_isLoggedIn()) {
            return $this->_loginForm(wpjb_link_to("membership_details", $this->getObject()));
        }
        
        $hn = $this->_handleNew();
        if($hn) {
            return $hn;
        }
        
        $pricing = $this->getObject();
        
        $this->view->summary = Wpjb_Model_Membership::getPackageSummary($pricing->id, wpjb_get_current_user_id("employer"));
        $this->view->pricing = $pricing;
        
        $title = sprintf(__("Package Details: '%s'", "wpjobboard"), $pricing->title);
        $this->setTitle($title);
        
        $this->view->breadcrumbs = array(
            array("title"=>__("Home", "wpjobboard"), "url"=>wpjb_link_to("employer_home"), "glyph"=>"wpjb-icon-home"),
            array("title"=>__("Membership", "wpjobboard"), "url"=>wpjb_link_to("membership"), "glyph"=>$this->glyph()),
            array("title"=>$title, "url"=>wpjb_link_to("membership_details", $pricing), "glyph"=>$this->glyph()),
        );
        
        return "company-product-details";
    }

    public function employereditAction()
    {
        if($this->_isCandidate()) {
            return false;
        }
        
        if(!$this->_isLoggedIn()) {
            return $this->_loginForm(wpjb_link_to("employer_edit"));
        }
        
        $this->setTitle(__("Company Profile", "wpjobboard"));

        $emp = Wpjb_Model_Company::current();
        if(is_null($emp)) {
            $id = null;
        } else {
            $id = $emp->id;
        }
        $form = new Wpjb_Form_Frontend_Company($id);
        $this->view->company = $emp;

        if($this->isPost()) {
            if(!$form->isValid($this->getRequest()->getAll())) {
               $this->view->_flash->addError($form->getGlobalError());
            } else {
               $this->view->_flash->addInfo(__("Company information has been saved.", "wpjobboard"));
               $form->save();
            }
        }

        $this->view->breadcrumbs = array(
            array("title"=>__("Home", "wpjobboard"), "url"=>wpjb_link_to("employer_home"), "glyph"=>"wpjb-icon-home"),
            array("title"=>__("Company Profile", "wpjobboard"), "url"=>wpjb_link_to("employer_edit"), "glyph"=>$this->glyph()),
        );
        
        $this->view->form = $form;

        return "company-edit";
    }

    public function logoutAction()
    {
        wp_logout();
        $this->view->_flash->addInfo(__("You have been logged out", "wpjobboard"));
        $this->redirect(wpjb_link_to("home"));
    }

    public function verifyAction()
    {
        $hn = $this->_handleNew();
        if($hn) {
            return $hn;
        }
        
        $this->setTitle(__("Request manual verification", "wpjobboard"));
        $this->view->hide_form = false;
        
        if(!current_user_can("manage_jobs")) {
            $this->view->_flash->addError(__("Only Employers can request verification (your account type is 'Candidate').", "wpjobboard"));
            return false;
        }
        
        $employer = Wpjb_Model_Company::current();
        
        if($employer->is_verified == Wpjb_Model_Company::ACCESS_PENDING) {
            $this->view->_flash->addInfo(__("Your verification request is pending approval.", "wpjobboard"));
            return false;
        }
        
        if($employer->is_verified == Wpjb_Model_Company::ACCESS_GRANTED) {
            $this->view->_flash->addInfo(__("Congratulations! Your company profile was verified successfully. Nothing else to do here.", "wpjobboard"));
            return false;
        }
        
        if($employer->is_verified == Wpjb_Model_Company::ACCESS_DECLINED) {
            $this->view->_flash->addError(__("Your verification request was declined. Please update your profile according to Admin guidelines and try again.", "wpjobboard"));
        }
        
        if($this->_request->post("verify_me") == 1) {
            $employer->is_verified = Wpjb_Model_Company::ACCESS_PENDING;
            $employer->save();
            
            $mail = Wpjb_Utility_Message::load("notify_admin_grant_access");
            $mail->setTo(wpjb_conf("admin_email", get_option("admin_email")));
            $mail->assign("company", $employer);
            $mail->assign("company_edit_url", wpjb_admin_url("employers", "edit", $employer->id));
            $mail->send();
            
            $this->view->hide_form = true;
            
            $this->view->_flash->addInfo(__("Verification request sent.", "wpjobboard"));
        }
        
        return "company-verify";
    }
}

?>