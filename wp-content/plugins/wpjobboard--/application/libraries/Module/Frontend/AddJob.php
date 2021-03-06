<?php
/**
 * Description of AddJob
 *
 * @author greg
 * @package 
 */

class Wpjb_Module_Frontend_AddJob extends Wpjb_Controller_Frontend
{

    public function init()
    {
        $instance = Wpjb_Project::getInstance();
        $this->view->steps = array(
            1 => __("Create Ad", "wpjobboard"),
            2 => __("Preview", "wpjobboard"),
            3 => __("Publish", "wpjobboard")
        );

        $urls = new stdClass();
        if(!$instance->shortcodeIs() || $instance->shortcodeIs("wpjb_employer_panel", false)) {
            $urls->add = wpjb_link_to("step_add");
        } else {
            $urls->add = get_the_permalink();
        }
        $urls->preview = wpjb_link_to("step_preview");
        $urls->reset = wpjb_link_to("step_reset");
        $urls->save = wpjb_link_to("step_save");
        $this->view->urls = $urls;
    }

    
    public function sessionSet($key, $value)
    {
        $id = "wpjb_session_".str_replace("-", "_", wpjb_transient_id());
        $transient = wpjb_session()->get($id);
        
        if($transient === false) {
            $transient = array();
        }
        
        $transient[$key] = $value;
        
        wpjb_session()->set($id, $transient, 3600);
    }
    
    public function sessionGet($key, $default = null) 
    {
        $id = "wpjb_session_".str_replace("-", "_", wpjb_transient_id());
        $transient = wpjb_session()->get($id);
        
        if($transient === false) {
            $transient = array();
        }
        
        if(!isset($transient[$key])) {
            return $default;
        } else {
            return $transient[$key];
        }
    }

    private function _canPost()
    {
        $info = wp_get_current_user();
        $isAdmin = true;
        if(!isset($info->wp_capabilities['administrator']) || !$info->wp_capabilities['administrator']) {
            $isAdmin = false;
        }

        if(!$isAdmin && Wpjb_Project::getInstance()->conf("posting_allow")==3) {
            $this->view->_flash->addError(__("Only Admin can post jobs", "wpjobboard"));
            $this->view->canPost = false;
            $this->view->can_post = false;
            return false;
        }

        $employer = Wpjb_Model_Company::current();
        if($employer === null && wpjb_conf("posting_allow")==2) {
            $this->view->_flash->addError(__("Only registered members can post jobs", "wpjobboard"));
            $this->view->canPost = false;
            $this->view->can_post = false;
            return "../default/form";
            return false;
        }
        
        if($employer !== null && $employer->is_active == Wpjb_Model_Company::ACCOUNT_INACTIVE) {
            $this->view->_flash->addError(__("You cannot post jobs. Your account is inactive.", "wpjobboard"));
            $this->view->canPost = false;
            $this->view->can_post = false;
            return false;
        }

        $this->view->canPost = true;
        $this->view->can_post = true;
        return true;
    }
    
    private function _republish()
    {
        $id = $this->_request->get("republish");
        $job = new Wpjb_Model_Job($id);
        
        $info = wp_get_current_user();
        $isAdmin = true;
        if(!isset($info->wp_capabilities) || !$info->wp_capabilities['administrator']) {
            $isAdmin = false;
        }
        
        $company = Wpjb_Model_Company::current();
        if(!$isAdmin && $company->id != $job->employer_id) {
            return;
        }
   
        $arr = $job->toArray();
        unset($arr["meta"]);
        unset($arr["tag"]);
        
        $data = $job->toArray();
        
        if($job->getLogoDir()) {
            $dir = wpjb_upload_dir("job", "company-logo", null,  "basedir");
            $file = $dir."/".basename($job->getLogoDir());
            if(!is_dir($dir)) {
                wp_mkdir_p($dir);
            }
            if(!is_file($file)) {
                copy($job->getLogoDir(), $file);
            }
            
        }
        
        $ignore = array("company_logo");
        
        foreach($job->meta as $k => $value) {
            if($value->conf("type") == "ui-input-file") {
                $ignore[] = $k;
            }
        }

        foreach($data["meta"] as $k => $v) {
            if(in_array($k, $ignore)) {
                // do nothing
            } elseif(count($v["values"]) > 1) {
                $arr[$k] = $v["values"];
            } else {
                $arr[$k] = $v["value"];
            }
        }
        foreach($data["tag"] as $k => $v) {
            $arr[$k] = array();
            foreach($v as $vi) {
                $arr[$k][] = $vi["id"];
            }
        }
        
        return $arr;
    }
    
    private function _company()
    {
        $c = Wpjb_Model_Company::current();
        
        $upload = wpjb_upload_dir("company", "company-logo", $c->id);
        $file = wpjb_glob($upload["basedir"]."/*");
        
        if(isset($file[0])) {
            $file = $upload["basedir"]."/".basename($file[0]);
            $upload = wpjb_upload_dir("job", "company-logo");
            $dir = $upload["basedir"];
            $new_file = $dir."/".basename($file);
            
            if(wp_mkdir_p($dir)) {
                $wpupload = wp_upload_dir();
                $stat = @stat($wpupload["basedir"]);
                $perms = $stat['mode'] & 0007777;
                chmod($dir, $perms);
                
                copy($file, $new_file);

                // Set correct file permissions
                $stat = @stat( dirname( $new_file ) );
                $perms = $stat['mode'] & 0007777;
                $perms = $perms & 0000666;
                @ chmod( $new_file, $perms );
                clearstatcache();
            }


        }
        
        return array(
            "company_name" => $c->company_name,
            "company_email" => $c->getUser(true)->user_email,
            "company_url" => $c->company_website,
            "job_country" => $c->company_country,
            "job_state" => $c->company_state,
            "job_zip_code" => $c->company_zip_code,
            "job_city" => $c->company_location,
        );
    }
    
    public function redirect($path) {
        if(Wpjb_Project::getInstance()->shortcodeIs()) {
            switch($path) {
                case "step_add": return $this->addAction();
                case "step_preview": return $this->previewAction();
                case "step_save": return $this->saveAction();
                case "step_complete": return $this->completeAction();
            }
        } else {
            parent::redirect(wpjb_link_to($path));
        }
    }

    public function resetAction()
    {
        wpjb_recursive_delete(wpjb_upload_dir("job", "", null, "basedir"));
        
        $this->sessionSet("job", null);
        $this->sessionSet("job_id", null);
        
        $this->view->_flash->addInfo(__("Form has been reset.", "wpjobboard"));
        return $this->redirect("step_add");
    }
    
    public function addAction()
    {
        wp_enqueue_script("wpjb-suggest");
        
        $this->view->current_step = 1;
        $this->setTitle($this->view->steps[1]);
        
        $this->view->show_pricing = true;
        $canPost = $this->_canPost();
        if(is_string($canPost)) {
            
            $form = new Wpjb_Form_Login();
            $form->getElement("redirect_to")->setValue(wpjb_link_to("step_add"));
            
            $this->view->action = "";
            $this->view->form = $form;
            $this->view->submit = __("Login", "wpjobboard");
            $this->view->buttons = array(
                array(
                    "tag"=>"a", 
                    "href"=>wpjb_link_to("employer_new"), 
                    "html"=>__("Not a member? Register", "wpjobboard")
                ),
            );
            return $canPost;
        } elseif($canPost !== true) {
            return $canPost;
        }

        $query = new Daq_Db_Query;
        $l = $query->select("*")->from("Wpjb_Model_Pricing t")->execute();
        $listing = array();
        foreach($l as $li) {
            $listing[$li->getId()] = $li;
        }
        $this->view->listing = $listing;
        
        $this->sessionSet("job_id", null);

        $form = new Wpjb_Form_AddJob();
        
        if(!$form->hasElement("listing") && !$form->hasElement("coupon")) {
            $this->view->show_pricing = false;
        }
        
        if($this->_request->get("republish")) {
            $arr = $this->_republish();
        } elseif(Wpjb_Model_Company::current()) {
            $arr = $this->_company();
        } else {
            $arr = array();
        }
       
        $jobArr = $this->sessionGet("job", null);

        if($this->_request->get("listing") && $form->hasElement("listing")) {
            $form->getElement("listing")->setValue($this->_request->get("listing"));
        }
        
        if(is_array($jobArr)) {
            $form->isValid($jobArr);
        } else {
            $form->setDefaults($arr);
        }

        $this->view->form = $form;
        
        return "add";
    }

    public function previewAction()
    {
        if($this->_canPost() !== true) {
            return $this->redirect("step_add");
        }

        $this->view->current_step = 2;
        $this->setTitle($this->view->steps[2]);

        $form = new Wpjb_Form_AddJob();

        if($this->isPost()) {
            $jobArr = $this->_request->getAll();
            $this->sessionSet("job", $jobArr);
        } else {
            $jobArr = $this->sessionGet("job", array());
        }
        
        if(!$form->isValid($jobArr)) {
            $this->view->_flash->addError($form->getGlobalError());
            return $this->redirect("step_add");
        } elseif($this->isPost()) {
            $form->upload(wpjb_upload_dir("{object}", "{field}", "{id}", "basedir"));
        }

        $this->view->job = $form->buildModel();
         
        return "preview";
    }

    public function saveAction()
    {
        if($this->_canPost() !== true) {
            return $this->redirect("step_add");
        }

        $this->view->current_step = 3;
        $this->setTitle($this->view->steps[3]);
        
        $form = new Wpjb_Form_AddJob();
        $id = $this->sessionGet("job_id");

        if($form->hasElement("recaptcha_response_field")) {
            $form->removeElement("recaptcha_response_field");
        }

        // Get $job Wpjb_Model_Job object
        if($id < 1) {
            if($form->isValid($this->sessionGet("job", array()))) {

                $form->save();
                $job = $form->getObject();

                $this->sessionSet("job", null);
                $this->sessionSet("job_id", $job->getId());
            } else {
                return $this->redirect("step_add");
            }
        } else {
            $job = new Wpjb_Model_Job($id);
        }

        // Get $company Wpjb_Model_Company object
        if($job->employer_id>0) {
            $company = new Wpjb_Model_Company($job->employer_id);
        } else {
            $company = null;
        }
        
        

        // decide action
        // - free (!$membership && $price == 0)
        // -- moderation on success
        // -- moderation off success
        // - payment form (!$membership && $price > 0)
        // -- show payment form, no flash
        // - membership ($membership)
        // -- moderation on success
        // -- moderation off succeess
        $job = new Wpjb_Model_Job($job->id);
        $payment = $job->getPayment(true);
        
        $membership_id = $job->membership_id;
        $membership = null;
        
        $pricing_id = $job->pricing_id;
        $pricing = new Wpjb_Model_Pricing($pricing_id);
        
        $price = 0;
        $cart = false;
        $info = array( );
        $moderate = $job->is_active && $job->is_approved;
        
        // Default email and name in payment form
        if(Wpjb_Model_Company::current()) {
            $dName = Wpjb_Model_Company::current()->company_name;
            $dMail = wp_get_current_user()->user_email;
        } elseif(get_current_user_id()>0) {
            $dName = wp_get_current_user()->display_name;
            $dMail = wp_get_current_user()->user_email;
        } else {
            $dName = $job->company_name;
            $dMail = $job->company_email;
        }

        // Figure out used pricing and membership
        if(!$membership_id && $pricing_id) {
            $price = $pricing->getTotal(); 
        }

        if($price && (!is_object($payment) || !$payment->exists())) {
            
            $gateways = Wpjb_Project::getInstance()->payment->getEnabled();
            $engine = "";
            if(isset($gateways[0])) {
                $engine = $gateways[0];
            }
            
            $taxer = new Wpjb_Utility_Taxer();
            $taxer->setPrice($pricing->getPrice());
            $taxer->setDiscount($pricing->getDiscount());

            $payment = new Wpjb_Model_Payment();
            $payment->pricing_id = $pricing->id;
            $payment->object_type = Wpjb_Model_Payment::JOB;
            $payment->object_id = $job->id;
            $payment->user_ip = $_SERVER['REMOTE_ADDR'];
            $payment->user_id = wpjb_get_current_user_id("employer");
            $payment->fullname = $dName;
            $payment->email = $dMail;
            $payment->external_id = ""; 
            $payment->status = 1;
            $payment->message = "";
            $payment->created_at = current_time("mysql", 1);
            $payment->paid_at = "0000-00-00 00-00-00";
            $payment->engine = $engine;
            $payment->payment_sum = $taxer->value->total;
            $payment->payment_paid = 0;
            $payment->payment_discount = $taxer->value->discount;
            $payment->payment_currency = $pricing->currency;
            $payment->params = "";
            $payment->save();
        }

        // Send email notifications
        if($id<1) {
            $mail = Wpjb_Utility_Message::load("notify_admin_new_job");
            $mail->setTo(wpjb_conf("admin_email", get_option("admin_email")));
            $mail->assign("job", $job);
            $mail->assign("payment", $payment);
            $mail->assign("company", $company);
            $mail->send();

            $mail = Wpjb_Utility_Message::load("notify_employer_new_job");
            $mail->setTo($job->company_email);
            $mail->assign("job", $job);
            $mail->assign("payment", $payment);
            $mail->assign("company", $company);
            $mail->send();
        }

        // Execute Actions
        if($id < 1 && $membership_id) {
            $membership = new Wpjb_Model_Membership($membership_id);
            $membership->inc($pricing_id);
            $membership->save();
        }

        if($price > 0) {
                
            $cart = true;
            $this->view->pricing = $pricing;
            $this->view->gateways = Wpjb_Project::getInstance()->payment->getEnabled();
            $this->view->pricing_item = __("Job", "wpjobboard") . " &quot;" . $job->job_title . "&quot;";
            $this->view->defaults = new Daq_Helper_Html("span", array(
                "id" => "wpjb-checkout-defaults",
                "class" => "wpjb-none",

                "data-payment_hash" => $payment->hash(),
                "data-object_id" => $job->id,
                "data-pricing_id" => $pricing->id,
                "data-fullname" => $dName,
                "data-email" => $dMail,

            ), " ");
        } 
        
        // Append Info
        if($price == 0) {
            $cart = false;
            $info[] = __("<strong>Thank you for submitting your job listing.</strong>", "wpjobboard");
            
            if($membership_id) {
                $info[] = sprintf(__("Job posted using '%s' membership.", "wpjobboard"), $pricing->title);
            }
            
            if(!$moderate) {
                $info[] = __("Your job posting is being moderated. We will email you once it will be live.", "wpjobboard");
            } else {
                $info[] = sprintf(__('Your job posting is now live. <a href="%s">Click here to view it</a>.', "wpjobboard"), $job->url());
            }
            
        } 
        
        if($payment->status == 2) {
            $cart = false;
            $info[] = __("<strong>Thank you for submitting your job listing.</strong>", "wpjobboard");
            $info[] = __("Your payment has already been accepted.", "wpjobboard");
        } 
        
        if($cart) {
            $this->view->_flash->addInfo(__("Please use the form below to complete your order.", "wpjobboard"));
            $this->view->_flash->setInfoIcon("wpjb-icon-basket");
        }
        
        $this->view->cart = $cart;
        $this->view->info = $info;
        $this->view->job = $job;
        
        return "save";
    }

    public function completeAction()
    {
        if($this->_canPost() !== true) {
            return $this->redirect("step_add");
        }
        $this->view->current_step = 3;
        $this->view->action = "payment_complete";
        
        return "save";
    }

}

?>