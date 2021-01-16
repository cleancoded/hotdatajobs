<?php

class Wpjb_Singular_Company extends Wpjb_Shortcode_Abstract {
    
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
        if(is_singular('company') && in_the_loop()) {
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
        
        if( !wpjb_candidate_have_access( get_the_ID() ) ) {
            
            if( wpjb_conf( "cv_members_have_access" ) == 1 ) {
                $msg = __("Only registered candidates have access to this page.", "wpjobboard");
            } elseif( wpjb_conf( "cv_members_have_access" ) == 2 ) {
                $msg = sprintf( __('Only premium candidates have access to this page. Get your premium account <a href="%s">here</a>', "wpjobboard"), get_the_permalink( wpjb_conf( "urls_link_cand_membership" ) ) );
            }
            
            $this->addError( $msg );
            return wpjb_flash();
        }
        
        $company = wpjb_get_object_from_post_id($post_id, "company");
        /* @var $company Wpjb_Model_Employer */

        if(Wpjb_Model_Company::current() && Wpjb_Model_Company::current()->id==$company->id) {
            // do nothing
        } elseif($company->is_active == Wpjb_Model_Company::ACCOUNT_INACTIVE) {
            $this->addError(__("Company profile is inactive.", "wpjobboard"));
        } elseif(!$company->is_public) {
            $this->addInfo(__("Company profile is hidden.", "wpjobboard"));
        } elseif(!$company->isVisible()) {
            $this->addError(__("Company profile will be visible once employer will post at least one job.", "wpjobboard"));
        }

        $page = 1;
        if($this->getRequest()->get("pg") > 1) {
            $page = $this->getRequest()->get("pg");
        }
        
        $this->view = new stdClass();
        $this->view->company = $company;
        $this->view->param = array(
            "filter" => "active",
            "employer_id" => $company->id,
            "page" => $page
        );
        
        return $this->render("job-board", "company");
    }
}