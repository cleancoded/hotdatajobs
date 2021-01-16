<?php

class Wpjb_Shortcode_Apply_Form extends Wpjb_Shortcode_Abstract
{
    /**
     * Class constructor
     * 
     * Registers [wpjb_apply_form] shortcode if not already registered
     * 
     * @since 5.0
     * @return void
     */
    public function __construct() {
        if(!shortcode_exists("wpjb_apply_form")) {
            add_shortcode("wpjb_apply_form", array($this, "main"));
        }
    }
    
    /**
     * Displays General Job Application Form
     * 
     * This function is executed when [wpjb_apply_form] shortcode is being called.
     * 
     * @link https://wpjobboard.net/kb/shortcode_wpjb_apply_form/ documentation
     * 
     * @param array     $atts   Shortcode attributes
     * @return string           Shortcode HTML
     */
    public function main($atts = array()) {
        $params = shortcode_atts(array(
            "job_id" => null
        ), $atts);

        $request = Daq_Request::getInstance();
        $job = new Wpjb_Model_Job($params["job_id"]);

        if($params["job_id"] && !$job->exists()) {
            $this->addError(__("Job does not exist.", "wpjobboard"));
            return $this->flash();
        }

        wp_enqueue_script("jquery");
        wp_enqueue_script("wpjb-js");
        wp_enqueue_script("wpjb-plupload");
        wp_enqueue_style("wpjb-css");

        $form = new Wpjb_Form_Apply();
        $form->getElement("_wpjb_action")->setValue("apply_general");

        $can_apply = true;

        if($request->post("_wpjb_action")=="apply_general" && $can_apply) {

            if($form->isValid($request->getAll())) {
                // send
                $var = $form->getValues();

                $user = null;
                if($job->exists() && $job->user_id) {
                    $user = new WP_User($job->user_id);
                }

                if($job->exists()) {
                    $form->setJobId($job->getId());
                }

                $user_id = null;
                if(wpjb_get_current_user_id("candidate")) {
                    $user_id = wpjb_get_current_user_id("candidate");
                }

                $form->setUserId($user_id);

                $form->save();
                $application = new Wpjb_Model_Application($form->getObject()->id);

                // notify employer
                $files = array();
                foreach($application->getFiles() as $f) {
                    $files[] = $f->dir;
                }

                // notify admin
                $mail = Wpjb_Utility_Message::load("notify_admin_general_application");
                $mail->assign("application", $application);
                $mail->assign("resume", Wpjb_Model_Resume::current());
                $mail->addFiles($files);
                $mail->setTo(wpjb_conf("admin_email", get_option("admin_email")));
                $mail->send();

                // notify employer
                $public_ids = array();
                foreach(wpjb_get_application_status() as $application_status) {
                    if($application_status["public"] == 1) {
                        $public_ids[] = $application_status["id"];
                    }
                }
                $notify = null;
                if($job->exists() && $job->company_email) {
                    $notify = $job->company_email;
                } elseif($user && $user->user_email) {
                    $notify = $user->user_email;
                }
                if($notify == wpjb_conf("admin_email", get_option("admin_email")) || !in_array($application->status, $public_ids)) {
                    $notify = null;
                }
                $mail = Wpjb_Utility_Message::load("notify_employer_new_application");
                $mail->assign("job", $job);
                $mail->assign("application", $application);
                $mail->assign("resume", Wpjb_Model_Resume::current());
                $mail->addFiles($files);
                $mail->setTo($notify);
                if($notify !== null) {
                    $mail->send();
                }

                // notify applicant
                $notify = null;
                if(isset($var["email"]) && $var["email"]) {
                    $notify = $var["email"];
                } elseif(wp_get_current_user()->ID > 0) {
                    $notify = wp_get_current_user()->user_email;
                }
                $mail = Wpjb_Utility_Message::load("notify_applicant_applied");
                $mail->setTo($notify);
                $mail->assign("job", $job);
                $mail->assign("application", $application);
                if($notify !== null) {
                    $mail->send();
                }

                $info = __('Your application has been sent, <a href="%s">send another application</a>.', "wpjobboard");

                $this->addInfo(sprintf($info, get_permalink()));
                $form = new Wpjb_Form_Apply();

                return $this->flash();

            } else {
                $this->addError($form->getGlobalError());
            }

        } elseif(Wpjb_Model_Resume::current()) {
            $resume = Wpjb_Model_Resume::current();
            if(!is_null($resume) && $form->hasElement("email")) {
                $form->getElement("email")->setValue($resume->user->user_email);
            }
            if(!is_null($resume) && $form->hasElement("applicant_name")) {
                $form->getElement("applicant_name")->setValue($resume->user->first_name." ".$resume->user->last_name);
            }
        }

        $this->view = new stdClass();
        $this->view->form = $form;
        $this->view->submit = __("Send Application", "wpjobboard");
        $this->view->action = "";
        $this->view->shortcode = true;

        return $this->render("default", "form");
    }
}
