<?php

class Wpjb_Module_Ajax_Applications  
{
    protected static $_disable = null;
    
    public static function rateAction() {
        
        $request = Daq_Request::getInstance();
        $app_id = $request->getParam("application");
        $value = $request->getParam("value");
        
        $application = new Wpjb_Model_Application($app_id);
        
        if(!$application->exists()) {
            echo json_encode(array(
                "result" => "0",
                "message" => __("Application does not exist.", "wpjobboard")
            ));
            exit;
        }
        
        $job = new Wpjb_Model_Job($application->job_id);
        $company = Wpjb_Model_Company::current();
        
        if($job->exists() && $company && $job->employer_id == $company->id) {
            $is_owner = true;
        } else {
            $is_owner = false;
        }
        
        if(!$is_owner && !current_user_can("edit_pages")) {
            echo json_encode(array(
                "result" => "0",
                "message" => __("You do not own this application.", "wpjobboard")
            ));
            exit;
        }
        
        $rating = $application->meta->rating->getFirst();
        $rating->value = absint($value);
        $rating->save();
        
        echo json_encode(array(
            "result" => 1
        ));
        exit;
    }
    
    public static function statusAction() {
        $request = Daq_Request::getInstance();
        
        $app_id = $request->getParam("id");
        $status = $request->getParam("status");
        $notify = $request->getParam("notify");
        
        $application = new Wpjb_Model_Application($app_id);
        
        if(!$application->exists()) {
            echo json_encode(array(
                "result" => "0",
                "message" => __("Application does not exist.", "wpjobboard")
            ));
            exit;
        }
        
        $job = new Wpjb_Model_Job($application->job_id);
        $company = Wpjb_Model_Company::current();
        
        if($job->exists() && $company && $job->employer_id == $company->id) {
            $is_owner = true;
        } else {
            $is_owner = false;
        }
        
        if(!$is_owner && !current_user_can("edit_pages")) {
            echo json_encode(array(
                "result" => "0",
                "message" => __("You do not own this application.", "wpjobboard")
            ));
            exit;
        }
        
        $s = wpjb_get_application_status($status);
        
        if(!$notify) {
            if(isset($s["notify_applicant_email"])) {
                self::$_disable = $s["notify_applicant_email"];
                add_filter( "wpjb_message", array(__CLASS__, "disableNotifications"));
            }
        }
        
        $application->status = absint($status);
        $application->save();
        
        echo json_encode(array(
            "result" => 1,
            "status" => $s
        ));
        exit;
    }
    
    public static function disableNotifications($mail) {
        if($mail["key"]->name == self::$_disable) {
            $mail["is_active"] = 0;
            self::$_disable = null;
            remove_filter( "wpjb_message", array(__CLASS__, __METHOD__));
        }
        return $mail;
    }
}
