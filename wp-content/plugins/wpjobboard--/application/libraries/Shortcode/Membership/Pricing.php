<?php

class Wpjb_Shortcode_Membership_Pricing extends Wpjb_Shortcode_Panel_Abstract {
    
    /**
     * Class constructor
     * 
     * Registers [wpjb_membership_pricing] shortcode is not already registered
     * 
     * @since 5.0
     * @return void
     */
    public function __construct() {
        
        if(!shortcode_exists("wpjb_membership_pricing")) {
            add_shortcode("wpjb_membership_pricing", array($this, "membershipPurchasePage"));
        }
        
        if(!shortcode_exists("wpjb_candidate_membership")) {
            add_shortcode("wpjb_candidate_membership", array($this, "candidateMembershipPurchasePage"));
        }
    }
   
    
    /**
     * [wpjb_membership_pricing] shortcode
     * 
     * This function echoes the [wpjb_membership_pricing] shortcode.
     * 
     * The class that executes the shortcode you can find in
     * wpjobboard/application/libraries/Shortcode/Employer/Panel.php
     * 
     * @see wpjobboard/application/libraries/Shortcode/Membership/Pricing.php
     * @see Wpjb_Shortcode_Membership_Pricing
     * @param array $atts
     * 
     * @return string   Shortcode HTML
     */
    public function membershipPurchasePage($atts) {
        
        $pricing = new Wpjb_Model_Pricing( Daq_Request::getInstance()->get( 'membership_id', null ) );
        $this->view = new stdClass();
        
        if( is_numeric( $pricing->id ) && $pricing->id > 0 ) {
            
            if(!get_current_user_id()) {
                return $this->_loginForm( get_permalink() . '?membership_id=' . $pricing->id);
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
            
            $dName = Wpjb_Model_Company::current()->company_name;
            $dMail = wp_get_current_user()->user_email;
            
            $this->view->pricing = $pricing;
            $this->view->gateways = Wpjb_Project::getInstance()->payment->getEnabled();
            $this->view->pricing_item = __("Membership", "wpjobboard") . " &quot;" . $pricing->title . "&quot;";
            $this->view->defaults = new Daq_Helper_Html("span", array(
                "id" => "wpjb-checkout-defaults",
                "class" => "wpjb-none",

                "data-object_id" => '',
                "data-pricing_id" => $pricing->id,
                "data-fullname" => $dName,
                "data-email" => $dMail,

            ), " ");
            
            $content = $this->render("default", "payment");
            return $content;
        }
        
        $a = shortcode_atts( array(
            'pricings'      => null,
            'featured'      => null,
        ), $atts );

        $query = new Daq_Db_Query();
        $query->from("Wpjb_Model_Pricing t");
        $query->where("t.price_for = ?", Wpjb_Model_Pricing::PRICE_EMPLOYER_MEMBERSHIP);
        if( $a['pricings'] != null ) {
            $query->where("t.id IN(?)", (array)explode(",", $a['pricings'] ) );
        }
        $query->order("price");
        
        $memberships = $query->execute();
        
        $company = Wpjb_Model_Company::current();
        $have_subscription = array();
        if( is_object($company) && $company->id > 0) {
            foreach($memberships as $pricing) {
                $summary = Wpjb_Model_Membership::getPackageSummary($pricing->id, wpjb_get_current_user_id('employer'));
                if(is_object($summary)) {
                    $sub_id = Wpjb_Model_MetaValue::getSingle('membership', 'subscription_id', $summary->id);
                    $sub_status = Wpjb_Model_MetaValue::getSingle('membership', 'subscription_status', $summary->id);
                    $current_user = new stdClass();
                    $current_user->stripe_id = $sub_id->value;
                    $current_user->stripe_status = $sub_status->value;
                    $have_subscription[$pricing->id] = $current_user;
                }
            }
        }
                
        $this->view->memberships = $memberships;
        $this->view->subscriptions = $have_subscription;
        $this->view->featured = $a['featured'];
        $content = $this->render("job-board", "memberships");
        
        return $content; 
    }
    
    public function candidateMembershipPurchasePage($atts) {
        
        $pricing = new Wpjb_Model_Pricing( Daq_Request::getInstance()->get( 'membership_id', null ) );
        $this->view = new stdClass();
        
        if( is_numeric( $pricing->id ) && $pricing->id > 0 ) {
            
            if(!get_current_user_id()) {
                return $this->_candidateLoginForm( get_permalink() . '?membership_id=' . $pricing->id);
            }
            
            /*if(!$this->_hasAccess("manage_jobs")) {
                return $this->flash();
            }*/
            
            $candidate = Wpjb_Model_Resume::current();
            if(is_null($candidate)) {
                $m = __('Please complete your <a href="%s">Candidate Profile</a> and then get back to this page.', "wpjobboard");
                $this->addError(sprintf($m, wpjr_link_to("myresume")));
                return $this->flash();
            }
            
            $dName = Wpjb_Model_Resume::current()->getSearch(true)->fullname;
            $dMail = wp_get_current_user()->user_email;
            
            $this->view->pricing = $pricing;
            $this->view->gateways = Wpjb_Project::getInstance()->payment->getEnabled();
            $this->view->pricing_item = __("Membership", "wpjobboard") . " &quot;" . $pricing->title . "&quot;";
            $this->view->defaults = new Daq_Helper_Html("span", array(
                "id" => "wpjb-checkout-defaults",
                "class" => "wpjb-none",
                "data-object_id" => '',
                "data-pricing_id" => $pricing->id,
                "data-fullname" => $dName,
                "data-email" => $dMail,
            ), " ");
            
            $content = $this->render("default", "payment");
            return $content;
        }
        
        $a = shortcode_atts( array(
            'pricings'      => null,
            'featured'      => null,
        ), $atts );

        
        $query = new Daq_Db_Query();
        $query->from("Wpjb_Model_Pricing t");
        $query->where("t.price_for = ?", Wpjb_Model_Pricing::PRICE_CANDIDATE_MEMBERSHIP);
        if( $a['pricings'] != null ) {
            $query->where("t.id IN(?)", (array)explode(",", $a['pricings'] ) );
        }
        $query->order("price");
        
        $memberships = $query->execute();
        
        $resume = Wpjb_Model_Resume::current();
        $have_subscription = array();
        if( is_object($resume) && $resume->id > 0) {
            foreach($memberships as $pricing) {
                $summary = Wpjb_Model_Membership::getPackageSummary($pricing->id, wpjb_get_current_user_id('candidate'));
                if(is_object($summary)) {
                    $sub_id = Wpjb_Model_MetaValue::getSingle('membership', 'subscription_id', $summary->id);
                    $sub_status = Wpjb_Model_MetaValue::getSingle('membership', 'subscription_status', $summary->id);
                    $current_user = new stdClass();
                    $current_user->stripe_id = $sub_id->value;
                    $current_user->stripe_status = $sub_status->value;
                    $have_subscription[$pricing->id] = $current_user;
                }
            }
        }
                
        $this->view->memberships = $memberships;
        $this->view->subscriptions = $have_subscription;
        $this->view->featured = $a['featured'];
        $content = $this->render("resumes", "memberships");
        
        return $content; 
    }
    
    protected function _candidateLoginForm($redirect) {
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
