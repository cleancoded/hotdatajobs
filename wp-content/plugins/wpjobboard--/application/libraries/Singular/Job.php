<?php

class Wpjb_Singular_Job extends Wpjb_Shortcode_Abstract {
    
    /**
     * Job application form
     *
     * @var Wpjb_Form_Apply
     */
    protected $form = null;
    
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
        add_action( "init", array($this, "onInit"));
        add_filter( "the_content", array($this, "theContent"));
        
        add_action( "wp_footer", array($this, "googleForJobs"));
        
    }
    
    /**
     * Submits job application
     * 
     * The function is executed in "init" action, it validates the application
     * saves it in DB and redirects a user.
     * 
     * @return void
     */
    public function onInit() {

        $flash = new Wpjb_Utility_Session();
        $job = new Wpjb_Model_Job($this->getRequest()->post("_job_id"));
        
        // check if the request is valid
        if(!$this->validateRequest($job)) {
            return;
        }

        // create new job application form if needed
        $this->form = new Wpjb_Form_Apply();

        $valid = $this->form->isValid($this->getRequest()->getAll());
        
        if(!$valid) {
            return;
        }
        
        $this->form->setJobId($job->getId());
        $this->form->setUserId(get_current_user_id());
        $this->form->save();
        
        // create job application instance
        $application = new Wpjb_Model_Application($this->form->getObject()->id);

        // send notifications
        $this->notifyAdmin($application, $job);
        $this->notifyEmployer($application, $job);
        $this->notifyApplicant($application, $job);

        // redirect if URL set in wp-admin / Settings (WPJB) / Default Pages and URLs
        if(wpjb_conf("urls_after_apply")) {
            wp_redirect(wpjb_conf("urls_after_apply"));
            exit;
        }

        // save success message
        $flash->addInfo(__("Your application has been sent.", "wpjobboard"));
        $flash->save();

        if(get_option('permalink_structure')) {
            $url = trailingslashit( wpjb_link_to("job", $job) . "applied/" . $job->id );
        } else {
            $url = add_query_arg( "applied", $job->id, wpjb_link_to("job", $job));
        }

        // redirect
        wp_redirect( $url . "#wpjb-sent");
        exit;
    }
    
    /**
     * Checks if current request is valid job application submission
     * 
     * The valid job application submission request does have $_POST[_wpjb_action]
     * variable and is executed for active jobs.
     * 
     * @param Wpjb_Model_Job $job
     * @return boolean
     */
    public function validateRequest($job) {
        
        if($this->getRequest()->post("_wpjb_action") != "apply") {
            return false;
        }
        
        if(!$job->exists() || !in_array(Wpjb_Model_Job::STATUS_ACTIVE, $job->status())) {
            $flash = new Wpjb_Utility_Session();
            $flash->addError(__("Cannot apply, the job does not exist or is inactive.", "wpjobboard"));
            $flash->save();
            wp_redirect(wpjb_link_to("job", $job));
            exit;
        }

        $can_apply = apply_filters("wpjb_user_can_apply", true, $job, $this);

        if(!$can_apply) {
            return false;
        } else {
            return true;
        }
    }
    
    /**
     * Sends notify_admin_new_application notification
     * 
     * The notification content you can edit from wp-admin / Settings (WPJB) / Emails panel.
     * 
     * @param Wpjb_Model_Application    $application    Application object
     * @param Wpjb_Model_Job            $job            Job object
     * @return void
     */
    public function notifyAdmin($application, $job) {
        
        $files = array();
        foreach($application->getFiles() as $f) {
            $files[] = $f->dir;
        }

        // notify admin
        $mail = Wpjb_Utility_Message::load("notify_admin_new_application");
        $mail->assign("job", $job);
        $mail->assign("application", $application);
        $mail->assign("resume", Wpjb_Model_Resume::current());
        $mail->addFiles($files);
        $mail->setTo(wpjb_conf("admin_email", get_option("admin_email")));
        $mail->send();
    }
    
    /**
     * Sends notify_employer_new_application notification
     * 
     * The notification content you can edit from wp-admin / Settings (WPJB) / Emails panel.
     * 
     * @param Wpjb_Model_Application    $application    Application object
     * @param Wpjb_Model_Job            $job            Job object
     * @return void
     */
    public function notifyEmployer($application, $job) {
        
        $files = array();
        $public_ids = array();
        $notify = null;
        $user = null;
        
        foreach($application->getFiles() as $f) {
            $files[] = $f->dir;
        }

        foreach(wpjb_get_application_status() as $application_status) {
            if($application_status["public"] == 1) {
                $public_ids[] = $application_status["id"];
            }
        }
        
        if($job->getCompany(true) && $job->getCompany()->user_id) {
            $user = new WP_User($job->getCompany()->user_id);
        }
        
        if($job->company_email) {
            $notify = $job->company_email;
        } elseif($user && $user->user_email) {
            $notify = $user->user_email;
        }
        
        if($notify == wpjb_conf("admin_email", get_option("admin_email"))) {
            return;
        }
        
        if(!in_array($application->status, $public_ids)) {
            return;
        }
        
        $mail = Wpjb_Utility_Message::load("notify_employer_new_application");
        $mail->assign("job", $job);
        $mail->assign("application", $application);
        $mail->assign("resume", Wpjb_Model_Resume::current());
        $mail->addFiles($files);
        $mail->setTo($notify);
        $mail->send();
    }
    
    /**
     * Sends notify_applicant_applied notification
     * 
     * The notification content you can edit from wp-admin / Settings (WPJB) / Emails panel.
     * 
     * @param Wpjb_Model_Application    $application    Application object
     * @param Wpjb_Model_Job            $job            Job object
     * @return void
     */
    public function notifyApplicant($application, $job) {
        $notify = null;
        
        if($application->email) {
            $notify = $application->email;
        } elseif(get_current_user_id() > 0) {
            $notify = wp_get_current_user()->user_email;
        }
        
        if($notify === null) {
            return;
        }
        
        $mail = Wpjb_Utility_Message::load("notify_applicant_applied");
        $mail->setTo($notify);
        $mail->assign("job", $job);
        $mail->assign("application", $application);
        $mail->send();
    }
    
    /**
     * Renders Job details HTML 
     * 
     * This function is executed in the the_content filter, if the current page
     * is job details page then it replaces content with job details page content.
     * 
     * @param string $content   HTML Content
     * @return string           HTML Content
     */
    public function theContent($content) {
        if(is_singular('job') && in_the_loop()) {
            return $this->main(get_the_ID());
        } else {
            return $content;
        }
    }
    
    /**
     * Renders job details HTML
     * 
     * @param int $post_id  ID of a post / job to render.
     * @return void
     */
    public function main($post_id) {
        
        if($this->view !== NULL) { return; }

        if( !wpjb_candidate_have_access( get_the_ID() ) ) {
            
            if( wpjb_conf( "cv_members_have_access" ) == 1 ) {
                $msg = __("Only registered candidates have access to this page.", "wpjobboard");
            } elseif( wpjb_conf( "cv_members_have_access" ) == 2 ) {
                $msg = sprintf( __('Only premium candidates have access to this page. Get your premium account <a href="%s">here</a>', "wpjobboard"), get_the_permalink( wpjb_conf( "urls_link_cand_membership" ) ) );
            }
            
            $this->addError( $msg );
            return wpjb_flash();
        }
        
        $this->view = new stdClass();
        $this->view->members_only = false;
        $this->view->form_error = null;
        
        $job = wpjb_get_object_from_post_id($post_id, "job");
        
        if($job === null) {
            return "";
        }
        
        $inrange = $job->time->job_created_at < time() && $job->time->job_expires_at+86400 > time();
        
        $show_related = (bool)wpjb_conf("front_show_related_jobs");
        $show_expired = (bool)wpjb_conf("front_show_expired");
        $can_apply = true;
        
        if(!$inrange) {
            if(get_post($job->post_id)->post_status === "publish" && !(bool)wpjb_conf("front_show_expired")) {
                $job->cpt();
            }
            $can_apply = false;
        }
        if($show_expired) {
            if(!$inrange) {
                $this->getFlash()->addInfo(__("This job posting expired and applications are no longer accepted.", "wpjobboard"));
            }
            $inrange = true;
        }

        
        $can_apply = apply_filters("wpjb_user_can_apply", $can_apply, $job, $this);
        
        $this->view->show = new stdClass();
        $this->view->show->apply = 0;
        $this->view->show_related = $show_related;

        if($this->getRequest()->get("form") == "apply") {
            $this->view->show->apply = 1;
        }

        $is_active = $job->is_active && $job->is_approved && $inrange;
        $is_admin = current_user_can("edit_pages");
        
        if(!$is_active && !$is_admin) {
            return $this->handleInactive();
        }

        $this->handleView($job);
        $this->handleRelated($job);
        $this->handleApplication($can_apply, $job);
        
        return $this->render("job-board", "single");
    }
    
    /**
     * Sets params for related jobs search
     * 
     * This function is being called by self::main()
     * 
     * You can modify the related jobs search using "wpjb_jobs_related" filter.
     * 
     * @see self::main()
     * @see wpjb_jobs_related filter
     * 
     * @param Wpjb_Model_Job $job
     * @return void
     */
    protected function handleRelated($job) {
        
        $related = array(
            "query" => $job->job_title,
            "page" => 1,
            "count" => 5,
            "id__not_in" => $job->id
        );

        $this->view->related = apply_filters("wpjb_jobs_related", $related, $job);
    }
    
    /**
     * Sets some view variables for job details page
     * 
     * This function is being called by self::main()
     * 
     * The set variables are:
     * - application_url    string or null
     * - job                Wpjb_Model_Job
     * - company            Wpjb_Model_Company or null
     * 
     * @see self::main()
     * @see wpjb_jobs_related filter
     * 
     * @param Wpjb_Model_Job $job
     * @return void
     */
    protected function handleView($job) {
        
        if($job->meta->job_source->value()) {
            $application_url = $job->company_url;
        } else {
            $application_url = null;
        }
        
        $this->view->application_url = apply_filters("wpjb_job_application_url", $application_url, $job);
        $this->view->job = $job;

        if($job->is_filled) {
            $msg = __("This job posting was marked by employer as filled and is probably no longer available", "wpjobboard");
            $this->addInfo($msg);
        }

        if($job->employer_id > 0) {
            $this->view->company = new Wpjb_Model_Company($job->employer_id);
        }
    }
    
    /**
     * Displays content for inactive job
     * 
     * This function is executed when currently viewed job is inactive or does
     * not exist.
     * 
     * @return boolean  Always returns false
     */
    protected function handleInactive() {
        $goback = wpjb_link_to("home");

        if(isset($_SERVER['HTTP_REFERER']) && stripos($_SERVER['HTTP_REFERER'], site_url())===0) {
            $goback = $_SERVER['HTTP_REFERER'];
        }

        $msg = __("Selected job is inactive or does not exist. <a href=\"%s\">Go back</a>.", "wpjobboard");
        $this->addError(sprintf($msg, $goback));
        $this->view->job = null;
        
        wpjb_flash();
        return false;
    }
    
    /**
     * Handles job application form submission
     * 
     * This function creates job application form, sets variables and renders
     * error if any.
     * 
     * Note this function does not handle submission success this is being done
     * in self::onInit() function.
     * 
     * @see self::onInit()
     * 
     * @param boolean           $can_apply  True if current user can apply for a job
     * @param Wpjb_Model_Job    $job        Application user is applying to
     * @return mixed                        False or null
     */
    protected function handleApplication($can_apply, $job) {
        
        $premium_members_only = wpjb_conf( "cv_members_can_apply", 0 );
        $premium_only = false;
        if( isset( $premium_members_only[0] ) && $premium_members_only[0] == 1 ) {
            $premium_only = true;
        }
        
        if( !wp_get_current_user()->ID && ( wpjb_conf( "front_apply_members_only", false ) || $premium_only ) && $can_apply ) {
            $this->view->members_only = true;
            $m = __("Only registered members can apply for jobs.", "wpjobboard");
            $this->view->form_error = $m;
            $this->view->can_apply = $can_apply;
            $this->view->form = $this->form;
            wpjb_flash();
            return false;
        }
        
        // Only premium can apply
        if( $premium_only ) {
            
            $query = new Daq_Db_Query();
            $query->from("Wpjb_Model_Pricing t");
            $query->where("price_for = ?", Wpjb_Model_Pricing::PRICE_CANDIDATE_MEMBERSHIP);

            $result = $query->execute();
            $test = false;
            
            if (!empty($result)) {       
                foreach($result as $pricing) {
                    $summary = Wpjb_Model_Membership::getPackageSummary( $pricing->id, wpjb_get_current_user_id( "candidate") );
                    if( $pricing->is_active == 0 || !is_object($summary) ) { continue; }
                    
                    $package = unserialize( $pricing->meta->package->value() );
                    if( $package['can_apply'] == 1 ) {
                        $test = true;
                    }
                }
            } 
            
            if( !$test ) {
                $this->view->premium_members_only = true;
                $m = __("Only premium members can apply for jobs.", "wpjobboard");
                $this->view->form_error = $m;
                $this->view->can_apply = $can_apply;
                $this->view->form = $this->form;
                wpjb_flash();
                return false;
            }
        }
        
        if(!$this->form) {
            $this->form = new Wpjb_Form_Apply();
            $this->form->getElement("_job_id")->setValue($job->id);
        }
        
        $action = $this->getRequest()->post("_wpjb_action");
        
        if($this->getRequest()->isPost() && $action=="apply" && $can_apply) {
            // if we are here then application is invalid, otherwise user would be redirected to success page
            $this->view->form_error = $this->form->getGlobalError();
            $this->view->show->apply = 1;
        } elseif(Wpjb_Model_Resume::current()) {
            $resume = Wpjb_Model_Resume::current();
            if(!is_null($resume) && $this->form->hasElement("email")) {
                $this->form->getElement("email")->setValue($resume->user->user_email);
            }
            if(!is_null($resume) && $this->form->hasElement("applicant_name")) {
                $this->form->getElement("applicant_name")->setValue($resume->user->first_name." ".$resume->user->last_name);
            }
        }
        
        $this->view->can_apply = $can_apply;
        $this->view->form = $this->form;
    }
    

    
    public function googleForJobs() {
        
        if(!is_singular('job')) {
            return;
        }
        
        if(!$this->view->can_apply) {
            return;
        }
        
        $gconf = wpjb_conf("google_for_jobs");
        $types = array();
        if(isset($gconf["types"]) && is_array($gconf["types"])) {
            $types = $gconf["types"];
        }

        $gfj = new Wpjb_Service_GoogleForJobs();
        $gfj->setTypes($types);
        
        if(isset($gconf["template"]) && $gconf["template"]) {
            $gfj->setTemplate($gconf["template"]);
        }
        
        add_filter("wpjb_google_for_jobs_jsonld", array($gfj, "mapFromConfig"), 10, 2);
        
        echo $gfj->getHtml($this->view->job->id);
    }
    
}
