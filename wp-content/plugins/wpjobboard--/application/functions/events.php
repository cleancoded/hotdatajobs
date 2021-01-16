<?php

function wpjb_event_import()
{
    Wpjb_Utility_Log::cron("Init");
    
    $query = new Daq_Db_Query();
    $query->select();
    $query->from("Wpjb_Model_Import t");
    $query->where("last_run < ?", date("Y-m-d"));
    $query->orWhere("last_run = ?", "0000-00-00 00:00:00");
    $query->order("last_run ASC");
    $query->limit(apply_filters("wpjb_event_import_burst", 1));
    
    $result = $query->execute();
    
    Wpjb_Utility_Log::cron("Found: ".count($result));
    
    foreach($result as $r) {
    
        $import = $r;
        $import->last_run = date("Y-m-d H:i:s");
        $import->success = 0;
        $import->save();

        $import->run();

        $import->success = 1;
        $import->save();
        
        Wpjb_Utility_Log::cron("Executed import {$import->id}");
    }
}

function wpjb_event_expiring_jobs() 
{
    if(wpjb_conf("cron_lock", "1970-01-01") == date("Y-m-d")) {
        return;
    }

    $instance = Wpjb_Project::getInstance();
    $instance->setConfigParam("cron_lock", date("Y-m-d"));
    $instance->saveConfig();

    $dt = date("Y-m-d", strtotime("today +5 day"));

    $result = wpjb_find_jobs(array(
        "expires_from" => $dt,
        "expires_to" => $dt
    ));

    foreach($result->job as $job) {
        $mail = Wpjb_Utility_Message::load("notify_employer_job_expires");
        $mail->assign("job", $job);
        $mail->setTo($job->company_email);
        $mail->send();
    }


}

function wpjb_event_subscriptions_daily() {
    
    if(!Wpjb_Utility_Message::load("notify_job_alerts")->getTemplate()->is_active) {
        return;
    }
    
    $query = Daq_Db_Query::create();
    $query->from("Wpjb_Model_Alert t");
    $query->where("last_run < ?", date("Y-m-d H:i:s", strtotime("now -1 day")));
    $query->where("frequency = 1");
    $query->order("last_run ASC");
    $query->limitPage(1, 20);
    $result = $query->execute();
    
    foreach($result as $alert) {
        
        $params = unserialize($alert->params);
        if( !is_array( $params ) ) {
                $params = array();
        }
        
        $params["date_from"] = $alert->last_run;
        $params["date_to"] = date("Y-m-d H:i:s");
        $params["query"] = "";
        
        if( isset( $params["keyword"] ) && !empty( $params["keyword"] ) ) {
            $params["query"] = $params["keyword"];
        }
        
        if($params["date_from"] == "0000-00-00 00:00:00") {
            $params["date_from"] = date("Y-m-d H:i:s", strtotime("now -1 day"));
        }
         
        $jobs = wpjb_find_jobs($params);
        
        $alert->last_run = date("Y-m-d H:i:s");
        $alert->save();

        if($jobs->total == 0) {
            continue;
        }
        
        $mail = Wpjb_Utility_Message::load("notify_job_alerts");
        $mail->setTo($alert->email);
        $mail->assign("alert", $alert);
        $mail->assign("unsubscribe_url", wpjb_api_url("action/alert", array(
            "delete" => md5($alert->id."|".$alert->email)
        )));
        
        $list = array();
        $log_list = array();
        
        foreach($jobs->job as $j) {
            $list[] = $j->toArray();
            $log_list[] = $j->id;
            unset($j);
        }
        

        $log = unserialize($alert->logs);
        if($log === false) {
            $log = array();
        }
        if(count($log) > 9) {
            array_pop($log);
        }
        array_unshift( $log, array(
            'jobs_count' => count($list),
            'jobs_list'  => $log_list,
            'date'       => date("Y-m-d H:i:s"),
            'status'     => 'Completed',
        ) );
        
        $alert->logs = serialize($log);
        $alert->save();
        
        $mail->assign("jobs", $list);
        $mail->send();
    }
}

function wpjb_event_subscriptions_weekly() {
    
    if(!Wpjb_Utility_Message::load("notify_job_alerts")->getTemplate()->is_active) {
        return;
    }
    
    $query = Daq_Db_Query::create();
    $query->from("Wpjb_Model_Alert t");
    $query->where("last_run < ?", date("Y-m-d H:i:s", strtotime("now -7 day")));
    $query->where("frequency = 2");
    $query->order("last_run ASC");
    $query->limitPage(1, 20);
    $result = $query->execute();
    
    foreach($result as $alert) {
        
        $params = unserialize($alert->params);
        if( !is_array( $params ) ) {
                $params = array();
        }
        
        $params["date_from"] = $alert->last_run;
        $params["date_to"] = date("Y-m-d H:i:s");
        $params["query"] = $params["keyword"];
        
        if($params["date_from"] == "0000-00-00 00:00:00") {
            $params["date_from"] = date("Y-m-d H:i:s", strtotime("now -7 day"));
        }
        
        $alert->last_run = date("Y-m-d H:i:s");
        $alert->save();
        
        $jobs = wpjb_find_jobs($params);
        
        if($jobs->total == 0) {
            continue;
        }
        
        $mail = Wpjb_Utility_Message::load("notify_job_alerts");
        $mail->setTo($alert->email);
        $mail->assign("alert", $alert);
        $mail->assign("unsubscribe_url", wpjb_api_url("action/alert", array(
            "delete" => md5($alert->id."|".$alert->email)
        )));
        
        $list = array();
        $log_list = array();
        
        foreach($jobs->job as $j) {
            $list[] = $j->toArray();
            $log_list[] = $j->id;
            unset($j);
        }
        
        $log = unserialize($alert->logs);
        if($log === false) {
            $log = array();
        }
        if(count($log) > 9) {
            array_pop($log);
        }
        array_unshift( $log, array(
            'jobs_count' => count($list),
            'jobs_list'  => $log_list,
            'date'       => date("Y-m-d H:i:s"),
            'status'     => 'Completed',
        ) );
        
        $alert->logs = serialize($log);
        $alert->save();
        
        $mail->assign("jobs", $list);
        $mail->send();
        
    }
}

function wpjb_cleanup_tmp_dirs() {
    
}

/**
 * Adjust featured_lvel and is_searchable for Candides if their membership expires. 
 */
function wpjb_event_clean_memberships() {
    
    $q = new Daq_Db_Query();
    $memberships = $q->select()->from( "Wpjb_Model_Membership t" )
                               ->join( "t.pricing t2" )  
                               ->where( "t2.price_for = ?", Wpjb_Model_Pricing::PRICE_CANDIDATE_MEMBERSHIP )
                               ->where( "t.expires_at  < ?", date( "Y-m-d" ) )
                               ->execute(); 
    
    foreach( $memberships as $member ) {
        
        $qr = new Daq_Db_Query();
        $resume_arr = $qr->select()->from( "Wpjb_Model_Resume t" )
                                   ->where( "t.user_id = ?", $member->user_id )
                                   ->execute();
        $resume = $resume_arr[0];
        
        $values = wpjb_candidate_membership_package( $resume );
        if( $values != null ) {
            //$resume->is_searchable = $values['is_searchable'];
            $resume->featured_level = $values['featured_level'];
            $resume->save();
        }
    }
}

?>