<?php

class Wpjb_Singular_Resume extends Wpjb_Shortcode_Abstract {
    
    /**
     * List of forms
     *
     * @var array
     */
    protected $form = array();
    
    /**
     * Which buttons to show on Resume Details page
     *
     * @var array
     */
    protected $show = array("contact"=>0, "purchase"=>0);
    
    /**
     * Registers singular events
     * 
     * This function is run by Wpjb_Singular_Manager::setupListeners()
     * 
     * @see Wpjb_Singular_Manager::setupListeners()
     * 
     * @return void
     */
    public function listen() {
        add_filter( "the_content", array($this, "theContent"));
    }
    
    /**
     * Renders Company details HTML 
     * 
     * This function is executed in the the_content filter, if the current page
     * is company details page then it replaces default content with 
     * the companydetails page content.
     * 
     * @param string $content   HTML Content
     * @return string           HTML Content
     */
    public function theContent($content) {
        if(is_singular('resume') && in_the_loop()) {
            return $this->main(get_the_ID());
        } else {
            return $content;
        }
    }
    
    /**
     * Renders resume details HTML
     * 
     * @param int $post_id  ID of a post / resume to render.
     * @return void
     */
    public function main($post_id) {
        
        if( !wpjb_candidate_have_access( get_the_ID() ) ) {
            
            if( wpjb_conf( "cv_members_have_access" ) == 1 ) {
                $msg = __("Only registered candidates have access to this page.", "wpjobboard");
            } elseif( wpjb_conf( "cv_members_have_access" ) == 2 ) {
                $msg = sprintf( __('Only premium candidates have access to this page. Get your premium account <a href="%s">here</a>', "wpjobboard"), get_the_permalink( wpjb_conf( "urls_link_cand_membership" ) ) );
            }
            
            $this->addError( $msg );
            return wpjb_flash();
        }
        
        $resume = wpjb_get_object_from_post_id($post_id);
        /* @var $resume Wpjb_Model_Resume */
        
        $this->view = new stdClass();
        $this->canView($resume->id);
        
        if(!$this->canBrowse($resume->id)) {
            if(wpjb_conf("cv_privacy") == 1) {
                $this->canViewError();
                return false;
            }
        }
        
        $this->view->form_error = null;
        $this->view->tolock = apply_filters("wpjb_lock_resume", array("user_email", "phone", "user_url"));
        $this->view->current_url = wpjr_link_to("resume", $resume);
        $this->view->resume = $resume;
        
        $this->form = array();
        $this->show = array("contact"=>0, "purchase"=>0);
        
        if($this->getRequest()->get("form") == "contact") {
            $show["contact"] = 1;
        }
        if($this->getRequest()->get("form") == "purchase") {
            $show["purchase"] = 1;
        }
        
        if($this->view->button->contact == 1) {
            $this->form["contact"] = new Wpjb_Form_Resumes_Contact;
        }
        if($this->view->button->purchase == 1) {
            $this->form["purchase"] = new Wpjb_Form_Resumes_Purchase;
        }
        
        $contact = $this->handleContact($resume);
        $purchase = $this->handlePurchase($resume);
        
        if($purchase !== null) {
            return $purchase;
        }
        
        $this->view->f = $this->form;
        $this->view->show = (object)$this->show;
        
        return $this->render("resumes", "resume");
    }
    
    /**
     * Renders contact form
     * 
     * Renders contact form on candidate details page
     * 
     * This function is being called by self::main()
     * 
     * @see self::main()
     * 
     * @param Wpjb_Model_Resume $resume
     * @return void
     */
    protected function handleContact($resume) {
        if(!$this->getRequest()->post("contact")) {
            return;
        }
        
        $valid = $this->form["contact"]->isValid($this->getRequest()->getAll());
        
        if($valid) {
            
            $cf = array();
            foreach($this->form["contact"]->getFields() as $key => $field) {
                $cf[$key] = $field->getValue();
            } 
            
            $mail = Wpjb_Utility_Message::load("notify_candidate_message");
            $mail->setTo($resume->getUser(true)->user_email);
            $mail->addHeader("Reply-To", $this->form["contact"]->value("email"));
            $mail->assign("company", Wpjb_Model_Company::current());
            $mail->assign("resume", $resume);
            $mail->assign("contact_form", $cf);
            $mail->send();
        
            $this->addInfo(__("Your message has been sent.", "wpjobboard"));
            add_action("wp_footer", "wpjb_hide_scroll_hash");
            
        } else {
            $this->show["contact"] = 1;
            $this->view->form_error = __("There are errors in your form", "wpjobboard");
        }
        
    }
    
    /**
     * Renders purchase form
     * 
     * Renders a purchase form on resume details page.
     * 
     * This function is being called by self::main()
     * 
     * @see self::main()
     * 
     * @param Wpjb_Model_Resume $resume
     * @return void
     */
    protected function handlePurchase($resume) {
        
        if(!$this->getRequest()->post("purchase") || !isset($this->form["purchase"])) {
            return;
        }
        
        if($this->form["purchase"]->isValid($this->getRequest()->getAll())) {
            return $this->handlePurchaseValid($resume);
        } else {
            $this->show["purchase"] = 1;
            $this->view->form_error = __("There are errors in your form", "wpjobboard");
        }
    }
    
    /**
     * Handles resume details page access
     * 
     * This function proccesses user request to access selected resume details page.
     * 
     * @param Wpjb_Model_Resume     $resume
     * @return mixed                            null|string purchase form HTML
     */
    protected function handlePurchaseValid($resume) {
        
        list($price_for, $membership_id, $pricing_id) = explode("_", $this->form["purchase"]->value("listing_type"));

        $pricing = new Wpjb_Model_Pricing($pricing_id);
        $hash = md5(uniqid() . "#" . time());
        $granted = false;

        if(get_current_user_id()) {
            $uid = get_current_user_id();
        } else {
            $uid = "UID" . uniqid();
        }

        if($membership_id) {
            $granted = true;
            $membership = new Wpjb_Model_Membership($membership_id);
            $membership->inc($pricing_id);
            $membership->save();

            $resume->addAccessKey($uid, $hash);
        } elseif($pricing->price == 0) {
            $granted = true;
            $resume->addAccessKey($uid, $hash);
        } 

        if($granted && get_current_user_id()) {
            $params = array("hash"=>$hash, "hash_id"=>$uid);
            $message = Wpjb_Utility_Message::load("notify_employer_resume_paid");
            $message->assign("resume", $resume);
            $message->assign("resume_unique_url", wpjr_link_to("resume", $resume, $params));
            $message->setTo(wp_get_current_user()->user_email);
            $message->send();
        }

        if($granted) {
            $this->addInfo(__("Access to resume details has been granted.", "wpjobboard"));
            $this->canView($resume->id);
            $this->canBrowse($resume->id);
            $this->form["contact"] = new Wpjb_Form_Resumes_Contact;
            add_action("wp_footer", "wpjb_hide_scroll_hash");
        } else {
            return $this->paymentForm($resume, $pricing);
        }
    }
    
    /**
     * Renders payment form
     * 
     * This function renders payment form which allows to purchase access to resume
     * details page.
     * 
     * @param Wpjb_Model_Resume     $resume     Resume currently viewed
     * @param Wpjb_Model_Pricing    $pricing    Selected pricing
     * @return string                           Payment form HTML
     */
    protected function paymentForm($resume, $pricing) {
        
        if(Wpjb_Model_Company::current()) {
            $dName = Wpjb_Model_Company::current()->company_name;
            $dMail = wp_get_current_user()->user_email;
        } elseif(wp_get_current_user()) {
            $dName = wp_get_current_user()->display_name;
            $dMail = wp_get_current_user()->user_email;
        } else {
            $dName = "";
            $dMail = "";
        }

        $fullname = apply_filters("wpjb_candidate_name", trim($resume->user->first_name." ".$resume->user->last_name), $resume->id);
        
        $this->view->pricing = $pricing;
        $this->view->gateways = Wpjb_Project::getInstance()->payment->getEnabled();
        $this->view->pricing_item = __("Resume Access", "wpjobboard") . " &quot;" . $fullname . "&quot;";
        $this->view->defaults = new Daq_Helper_Html("span", array(
            "id" => "wpjb-checkout-defaults",
            "class" => "wpjb-none",

            "data-object_id" => $resume->id,
            "data-pricing_id" => $pricing->id,
            "data-fullname" => $dName,
            "data-email" => $dMail,

        ), " ");

        return $this->render("default", "payment");
    }
    
    /**
     * Checks if user can view resume identified by $id
     * 
     * This function verifies user access and generates error message (if any)
     * and defines buttons which current user will see on resume details page.
     * 
     * @param int $id   Resume ID
     * @return void
     */
    protected function canView($id)
    {
        $m = null;
        $premium = false;
        $button = array("contact"=>0, "login"=>0, "register"=>0, "purchase"=>0, "verify"=>0);
        $cv_access = wpjb_conf("cv_access");
        $request = Daq_Request::getInstance();
        
        if(Wpjb_Model_Resume::current() && Wpjb_Model_Resume::current()->id == $id) {
            // candidate can always access his resume
            $premium = true;
        }
        if(wpjr_has_premium_access($id)) {
            // if has valid hash, always allow
            $premium = true;
        }
        if(Wpjb_Model_Company::current() && Wpjb_Model_Company::current()->canViewResume($id)) {
            // employer received at least one application from this candidate
            // and employers can view full applicants resumes
            // this option is enabled in wp-admin / Settings (WPJB) / Resumes Options panel
            $premium = true;
        }
        if(current_user_can('manage_options')) {
            // admin can see anything
            $premium = true;
        }
        

        if($premium) {
            // premium user alsways has access
            $button["contact"] = 1;
            
        } elseif(!get_current_user_id()) {
            // not registered user
            if(in_array($cv_access, array(2,3,4,6))) {
                $m = __("Login or register as Employer to contact this candidate.", "wpjobboard");
                $button["login"] = 1;
                $button["register"] = 1;
            } elseif($cv_access == 5) {
                $m = __("Login or purchase this resume contact details.", "wpjobboard");
                $button["login"] = 1;
                $button["purchase"] = 1;
            } 
            
        } elseif(current_user_can("manage_jobs")) {
            // employer
            $company = Wpjb_Model_Company::current();
            if($cv_access == 4 && !$company->is_verified) {
                $m = __("You need to verify your account before contacting candidate.", "wpjobboard");
                $button["verify"] = 1;
            } elseif($cv_access == 4 && in_array($company->is_verified, array(Wpjb_Model_Company::ACCESS_PENDING, Wpjb_Model_Company::ACCESS_DECLINED))) {
                $m = __("Your account is pending verification or verification was declined.", "wpjobboard");
                $button["none"] = 1;
            } elseif($cv_access == 5 && !$premium) {
                $m = __("Purchase this resume contact details", "wpjobboard");
                $button["purchase"] = 1;
            } elseif($cv_access == 6) {
                $m = __("Before you will be able to see this resume, the candidate needs to apply for at least one of your jobs.", "wpjobboard");
                $button["none"] = 1;
            }
            
        } elseif(get_current_user_id()) {
            // other registered user
            if(in_array($cv_access, array(3,4,6))) {
                $m = __("Incorrect account type. You need to be registered as Employer to contact Candidates", "wpjobboard");
                $button["none"] = 1;
            } elseif($cv_access == 5) {
                $m = __("Purchase this resume contact details", "wpjobboard");
                $button["purchase"] = 1;
            }
        } else {
            // can contact
            $button["contact"] = 1;
        }
        
        if(array_sum($button) == 0) {
            $button["contact"] = 1;
        }
        
        $this->view->c_message = $m;
        $this->view->button = (object)$button;
    }
    
    /**
     * Sets a view error
     * 
     * This function is executed when user does not have access to view resumes,
     * it will show an error if
     * - "Resume Privacy" is set to "Hide Contact Details Only"
     * - "Grant Resumess Access" set to value different then "To All"
     * 
     * @return void
     */
    protected function canViewError() {
        $c = (int)wpjb_conf("cv_privacy")."/".(int)wpjb_conf("cv_access");
        $m = null;

        switch($c) {
            case "0/2":
                $m = __("Only registered members can contact candidates.", "wpjobboard");
                break;
            case "0/3":
                $m = __("Only Employers can contact candidates.", "wpjobboard");
                break;
            case "0/4":
                $m = __("Only <strong>verified</strong> Employers can contact candidates.", "wpjobboard");
                break;
            case "0/5":
                $m = __("Contacting candidaes requires premium access.", "wpjobboard");
                break;
            case "0/6":
                $m = __("Before you will be able to see this resume, the candidate needs to apply for at least one of your jobs.", "wpjobboard");
                break;
        }
        
        if($m) {
            $this->addError($m);
            $this->view->error_message = $m;
        }
    }
    
    /**
     * Checks if user can browse resume details
     * 
     * @uses wpjr_can_browse()
     * 
     * @param  int       $id    Wpjb_Model_Resume::$id
     * @return boolean          True if user has access to resume
     */
    protected function canBrowse($id = null)
    {   
        $can = wpjr_can_browse($id);
        $this->view->can_browse = $can;
        return $can;
    }
    
    /**
     * Returns an error message if current user does not have access to resume details
     * 
     * This function is executed when user does not have access to view resumes,
     * it will show an error if
     * - "Resume Privacy" is set to "Hide Resumes List and Details"
     * - "Grant Resumess Access" set to value different then "To All"
     * 
     * @return mixed    Either NULL or string (error message)
     */
    protected function canBrowseError()
    {
        $error = wpjr_can_browse_err();
        
        if(!is_null($error)) {
            $this->addError($error);
        }
    }
}