<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Redirect
 *
 * @author greg
 */
class Wpjb_Utility_Redirect {
    public function __construct() {
        add_filter("wpjb_logout", array($this, "logout"), 10, 2);
        add_filter("wpjb_login", array($this, "login"), 10, 2);
        
        add_filter("wpjb_cpt_init", array($this, "cpt"), 10, 2);
    }
    
    public function logout($logout, $type) {
        if($type == "employer" && wpjb_conf("urls_after_employer_logout")) {
            $logout["message"] = null;
            $logout["redirect_to"] = wpjb_conf("urls_after_employer_logout");
        } elseif($type == "candidate" && wpjb_conf("urls_after_candidate_logout")) {
            $logout["message"] = null;
            $logout["redirect_to"] = wpjb_conf("urls_after_candidate_logout");
        } 
        return $logout;
    }
    
    public function login($login, $type) {
        if($type == "employer" && wpjb_conf("urls_after_employer_login")) {
            $login["message"] = null;
            $login["redirect_to"] = wpjb_conf("urls_after_employer_login");
        } elseif($type == "candidate" && wpjb_conf("urls_after_candidate_login")) {
            $login["message"] = null;
            $login["redirect_to"] = wpjb_conf("urls_after_candidate_login");
        } 
        return $login;
    }
    
    public function registration() {
        
    }
    
    public function cpt($args, $type) {
        if($type == "job" && wpjb_conf("urls_rewrite_job")) {
            $args["rewrite"]["slug"] = wpjb_conf("urls_rewrite_job");
        } elseif($type == "resume" && wpjb_conf("urls_rewrite_resume")) {
            $args["rewrite"]["slug"] = wpjb_conf("urls_rewrite_resume");
        } elseif($type == "company" && wpjb_conf("urls_rewrite_company")) {
            $args["rewrite"]["slug"] = wpjb_conf("urls_rewrite_company");
        }
        return $args;
    }
}
