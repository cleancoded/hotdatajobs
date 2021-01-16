<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Moderation
 *
 * @author greg
 */
class Wpjb_Utility_Moderation 
{
    public static function connect()
    {
        add_filter("authenticate", array(__CLASS__, "authenticate"), 10000);
        add_action("wpjb_user_registered",  array(__CLASS__, "registered"), 10000, 2);
    }
    
    public static function authenticate($user)
    {
        if(!$user instanceof WP_User) {
            return $user;
        }
        
        $class = null;
        
        if(user_can($user, "manage_resumes") && wpjb_conf("cv_login_only_approved")) {
            $class = "Wpjb_Model_Resume";
        }
        
        if(user_can($user, "manage_jobs") && wpjb_conf("employer_login_only_approved")) {
            $class = "Wpjb_Model_Company";
        }
        
        if(user_can($user, "publish_pages")) {
            $class = null;
        }
        
        if($class === null) {
            return $user;
        }
        
        $query = new Daq_Db_Query();
        $query->from("$class t");
        $query->where("user_id = ?", $user->ID);
        $query->limit(1);

        $result = $query->execute();

        if(!isset($result[0]) || $result[0]->is_active != 1) {
            return new WP_Error('wpjb-inactive', __('Cannot Login. Your account was not approved yet.', 'wpjobboard'));
        } else {
            return $user;
        }
        
    }
    
    public static function registered($type, $id)
    {
        if(!isset($_POST["_wpjb_action"])) {
            return;
        }

        $doLogin = true;
        
        if($type == "candidate" && wpjb_conf("cv_login_only_approved")) {
            $object = new Wpjb_Model_Resume($id);
            $doLogin = $object->is_active;
        }
        
        if($type == "employer" && wpjb_conf("employer_login_only_approved")) {
            $object = new Wpjb_Model_Company($id);
            $doLogin = $object->is_active;
        }
        
        if($doLogin) {
            return;
        }
        
        $msg = __("You have been registered. We will notify you by email once your account is approved.", "wpjobboard");
        
        if( $type == "employer" && wpjb_conf("urls_after_reg_employer")) {
            $url = wpjb_conf("urls_after_reg_employer");
        } elseif($type == "candidate" && wpjb_conf("urls_after_reg_candidate")) {
            $url = wpjb_conf("urls_after_reg_candidate");
        } else {
            $url = wpjb_link_to("home");
        }

        if(!empty($msg)) {
            $flash = new Wpjb_Utility_Session();
            $flash->addInfo($msg);
            $flash->save();
        }

        wp_logout();
        wp_redirect($url);
        exit;
    }
}
