<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Panel
 *
 * @author greg
 */
abstract class Wpjb_Shortcode_Panel_Abstract extends Wpjb_Shortcode_Abstract {

    public function glyph()
    {
        if(is_rtl()) {
            return "wpjb-icon-left-open";
        } else {
            return "wpjb-icon-right-open";
        }
    }
    
    protected function _hasAccess($capability) {
        if(!current_user_can($capability)) {
            if( $capability == "manage_jobs" ) {
                $m = __("You cannot access this page. Only Employers with valid Company profile can access this page.", "wpjobboard");
            } elseif( $capability == "manage_resumes" ) {
                $m = __("You cannot access this page. Only Candidate with valid Resume can access this page.", "wpjobboard");
            } else {
                $m = __("You cannot access this page. Missing [%s] capability.", "wpjobboard");
            }
            
            $this->addError(sprintf($m, $capability));
            return false;
        }
        
        return true;
    }
    
    protected function _loginForm($redirect)
    {
        $this->addError(__("Login to access this page.", "wpjobboard"));
        
        $form = new Wpjb_Form_Login();
        $form->getElement("redirect_to")->setValue($redirect);

        $this->view = new stdClass();
        $this->view->page_class = "wpjb-page-company-login";
        $this->view->action = "";
        $this->view->form = $form;
        $this->view->submit = __("Login", "wpjobboard");
        $this->view->buttons = array();
        
        if(wpjb_conf("urls_link_emp_reg") != "0") {
            $this->view->buttons[] = array(
                "tag" => "a", 
                "href" => wpjb_link_to("employer_new"), 
                "html" => __("Not a member? Register", "wpjobboard")
            );
        }

        $this->view = apply_filters("wpjb_shortcode_login", $this->view, "employer");
        return $this->render("default", "form");
    }
    

    
    public function logout() {
        
    }
    
    public function delete() {
        
    }
}

