<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Action
 *
 * @author Grzegorz
 */
class Wpjb_Module_Api_Action extends Daq_Controller_Abstract
{
    public function getRequest()
    {
        return Daq_Request::getInstance();
    }
    
    public function reply($reply)
    {
        $redirect_to = $this->getRequest()->getParam("redirect_to");
        
        if($this->isXmlHttpRequest()) {
            
            echo json_encode($reply);
            
        } else {
            
            if($reply->status != 200) {
                $this->view->_flash->addError($reply->message);
            } else {
                $this->view->_flash->addInfo($reply->message);
            }
            
            wp_redirect($redirect_to);
            exit;
        }
    }
    
    public function bookmarkAction()
    {
        $object = $this->getRequest()->getParam("object");
        $object_id = $this->getRequest()->getParam("object_id");
        $user_id = wpjb_get_current_user_id("candidate");
        $do = $this->getRequest()->getParam("do");
        
        $reply = new stdClass();
        $reply->status = 0;
        $reply->message = "";
        
        $allowed = array();
        
        if(current_user_can("manage_resumes")) {
            $allowed[] = "job";
            $allowed[] = "company";
        } 
        
        if(current_user_can("manage_jobs")) {
            $allowed[] = "resume";
        }
        
        if(!in_array($do, array("post", "delete"))) {
            $reply->message = __("Invalid action", "wpjobboard");
            $this->reply($reply);
            return;
        }
        
        if(!$user_id) {
            $reply->message = __("Only registered members can create bookmarks.", "wpjobboard");
            $this->reply($reply);
            return;
        }
        
        if(!in_array($object, $allowed)) {
            $reply->message = __("Invalid object type", "wpjobboard");
            $this->reply($reply);
            return;
        }
        
        switch($object) {
            case "job": $item = new Wpjb_Model_Job($object_id); break;
            case "company": $item = new Wpjb_Model_Company($object_id); break;
            case "resume": $item = new Wpjb_Model_Resume($object_id); break;
        }
        
        if(!$item->exists() && $do=="post") {
            $reply->message = __("Object with given ID does not exist.", "wpjobboard");
            $this->reply($reply);
            return;
        }
        
        $query = new Daq_Db_Query;
        $query->from("Wpjb_Model_Shortlist t");
        $query->where("object = ?", $object);
        $query->where("object_id = ?", $object_id);
        $query->where("user_id = ?", $user_id);
        $query->limit(1);
        
        $list = $query->execute();
        
        if($do=="delete") {
            if(isset($list[0])) {
                $list[0]->delete();
                $reply->status = 200;
                $reply->message = __("Bookmark deleted.", "wpjobboard");
            } else {
                $reply->message = __("Bookmark does not exist.", "wpjobboard");
            }
        }
        
        if($do=="post") {
            if(!isset($list[0])) {
                
                $sh = new Wpjb_Model_Shortlist();
                $sh->object_id = $object_id;
                $sh->object = $object;
                $sh->user_id = $user_id;
                $sh->shortlisted_at = date("Y-m-d");
                $sh->save();
                
                $reply->status = 200;
                $reply->message = __("Bookmark created.", "wpjobboard");
            } else {
                $reply->message = __("Bookmark already exists.", "wpjobboard");
            }
        }

        $this->reply($reply);
    }
    
    public function jobAction()
    {
        $id = $this->getRequest()->getParam("id");
        $do = $this->getRequest()->getParam("do");
        
        $job = new Wpjb_Model_Job($id);
        $reply = new stdClass();
        $reply->status = 0;
        $reply->message = "";
        
        if($job->employer_id != Wpjb_Model_Company::current()->id) {
            $reply->message = __("You cannot edit this job.", "wpjobboard");
            $this->reply($reply);
            return;
        }
        
        if($do == "fill") {
            
            $job->is_filled = 1;
            $job->save();
            
            $reply->status = 200;
            $reply->message = sprintf(__("Job '%s' has been filled.", "wpjobboard"), $job->job_title);
            
        } elseif($do == "unfill") {
            
            $job->is_filled = 0;
            $job->save();
            
            $reply->status = 200;
            $reply->message = sprintf(__("Job '%s' has been unfilled.", "wpjobboard"), $job->job_title);
        }
        
        $this->reply($reply);
    }
    
    public function applicationAction()
    {
        $id = $this->getRequest()->getParam("id");
        $status = $this->getRequest()->getParam("status");
        
        $application = new Wpjb_Model_Application($id);
        $job = new Wpjb_Model_Job($application->job_id);
        $emp = $job->getCompany(true);
        
        $reply = new stdClass();
        $reply->status = 0;
        $reply->message = "";

        if($emp->user_id < 1 || $emp->user_id != wpjb_get_current_user_id("employer")) {
            $reply->message = __("You are not allowed to access this page.", "wpjobboard");
            $this->reply($reply);
            return;
        }

        if(is_numeric($status)) {
            
            if(!array_key_exists($status, wpjb_application_status())) {
                $reply->message = __("Invalid status given.", "wpjobboard");
                $this->reply($reply);
                return;
            }
            
            $application->status = $status;
            $application->save();

            $t = wpjb_application_status($status);

            $reply->status = 200;
            $reply->message = sprintf(__("Application was marked as %s.", "wpjobboard"), $t);
        }

        $this->reply($reply);
        
        
    }
    
    public function alertAction()
    {
        $request = Daq_Request::getInstance();
        $hash = $request->get("delete");
        
        if(empty($hash)) {
            wp_die(__("<strong>ERROR</strong> Provided hash code is empty.", "wpjobboard"), __("Alerts", "wpjobboard"));
        }
        
        $query = new Daq_Db_Query();
        $query->from("Wpjb_Model_Alert t");
        $query->where("MD5(CONCAT(t.id, '|', t.email)) = ?", $hash);
        $query->limit(1);
        
        $result = $query->execute();
        
        if(empty($result)) {
            wp_die(__("<strong>ERROR</strong> Provided hash code is invalid.", "wpjobboard"), __("Alerts", "wpjobboard"));
        }
        
        $result[0]->delete();
        
        wp_die(__("<strong>SUCCESS</strong> Alert deleted.", "wpjobboard"), __("Alerts", "wpjobboard"));
        
    }
    
    public function detailAction()
    {
        $request = Daq_Request::getInstance();
        $reply = new stdClass();
        $reply->status = 0;
        $reply->message = "";
        $id = $request->get("id");
        
        $detail = new Wpjb_Model_ResumeDetail($id);
        
        if($detail->resume_id != Wpjb_Model_Resume::current()->id) {
            $reply->message = __("It seems this resume detail does not belong to you!", "wpjobboard");
            $this->reply($reply);
            return;
        }
        
        $detail->delete();

        $resume = Wpjb_Model_Resume::current();
        
        if($resume === null) {
            $resume->modified_at = date("Y-m-d H:i:s");
            $resume->save();
        }
        
        
        $reply->status = 200;
        $reply->message = __("Resume detail deleted.", "wpjobboard");

        $this->reply($reply);
    }
    

}

?>
