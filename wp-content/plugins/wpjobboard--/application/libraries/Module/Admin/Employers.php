<?php
/**
 * Description of Employers
 *
 * @author greg
 * @package 
 */

class Wpjb_Module_Admin_Employers extends Wpjb_Controller_Admin
{

    public function init()
    {
        $this->view->slot("logo", "employers.png");
        $this->_virtual = apply_filters( "wpjb_bulk_actions_functions", array(
           "redirectAction" => array(
               "accept" => array("query"),
               "object" => "employers"
           ),
           "addAction" => array(
               "form" => "Wpjb_Form_Admin_Company",
               "info" => __("New company has been created.", "wpjobboard"),
               "error" => __("There are errors in your form.", "wpjobboard"),
               "url" => wpjb_admin_url("employers", "edit", "%d")
           ),
           "editAction" => array(
               "form" => "Wpjb_Form_Admin_Company",
               "info" => __("Form saved.", "wpjobboard"),
               "error" => __("There are errors in your form.", "wpjobboard")
           ),
           "deleteAction" => array(
               "info" => __("Company has been deleted.", "wpjobboard"),
               "page" => "employers"
           ),
           "_multi" => array(
               "activate" => array(
                   "success" => __("Number of activated employer accounts: {success}", "wpjobboard")
               ),
               "deactivate" => array(
                   "success" => __("Number of deactivated employer accounts: {success}", "wpjobboard")
               ),
               "delete" => array(
                   "success" => __("Number of deleted employer accounts: {success}", "wpjobboard")
               ),
               "approve" => array(
                   "success" => __("Number of approved employer accounts: {success}", "wpjobboard")
               ),
               "decline" => array(
                   "success" => __("Number of declined employer accounts: {success}", "wpjobboard")
               )
           )
       ), "employer" );

       if(Wpjb_Project::getInstance()->conf("cv_access")==2) {
           $this->_virtual['_multi']['approve'] = array('success' => __("Number of approved employer accounts: {success}", "wpjobboard"));
           $this->_virtual['_multi']['decline'] = array('success' => __("Number of declined employer accounts: {success}", "wpjobboard"));
       }
    }
    
    public function indexAction()
    {
        $screen = new Wpjb_Utility_ScreenOptions();
        
        $this->view->screen = $screen;
        $query = $this->_request->get("query");
        
        $this->view->rquery = $this->readableQuery($query);
        
        $param = $this->deriveParams($query, new Wpjb_Model_Company);
        $param["filter"] = $this->_request->get("filter", "all");
        $param["page"] = (int)$this->_request->get("p", 1);
        $param["count"] = $screen->get("employer", "count", 20);
        
        $result = Wpjb_Model_Company::search($param);
        
        $stat = new stdClass();
        $stat->all = Wpjb_Model_Application::search(array_merge($param, array("filter"=>"all", "count_only"=>1)));
        $stat->active = Wpjb_Model_Application::search(array_merge($param, array("filter"=>"active", "count_only"=>1)));
        
        $this->view->data = $result->company;
        $this->view->show = $param["filter"];
        $this->view->current = $param["page"];
        $this->view->total = $result->pages;
        $this->view->param = array("filter"=>$param["filter"], "query"=>$query);
        $this->view->query = $query;
        $this->view->stat = $stat;
        
        $this->view->opt = array(
            Wpjb_Model_Company::ACCESS_PENDING => __("Pending", "wpjobboard"),
            Wpjb_Model_Company::ACCESS_GRANTED => __("Granted", "wpjobboard"),
            Wpjb_Model_Company::ACCESS_DECLINED => __("Declined", "wpjobboard")
        );
    }
    
    public function slug($object = null)
    {
        global $wp_rewrite;

        $instance = Wpjb_Project::getInstance();
        $pedit = '<span id="editable-post-name" title="Click to edit this part of the permalink">[slug]</span>';
        $shortlink = null;
        $slug = "";
        
        if($object) {
            $permalink = $object->url();
        } else {
            $permalink = null;
            $object = new stdClass();
            $object->post_id = null;
        }

        if(!get_option('permalink_structure')) {
            $url = wpjb_link_to("company", $object);
            $slug = null;
        } elseif($instance->env("uses_cpt")) {
            $post = get_post($object->post_id);

            if($post) {
                $shortlink = wp_get_shortlink($post->ID, 'post');
                $slug = $post->post_name;
            }
            
            $pstruct = $wp_rewrite->get_extra_permastruct("company");
            $purl = home_url( user_trailingslashit($pstruct) );

            $url = str_replace("%company%", $pedit, $purl);
        } else {
            $model = new Wpjb_Model_Company();
            $model->company_slug = $pedit;
            $slug = $object->company_slug;
            $url = wpjb_link_to("company", $model);
        }

        if(stripos($url, "[slug]") !== false) {
            $this->view->url = str_replace("[slug]", $slug, $url);
        } else {
            $this->view->url = $url;
        }
        
        $this->view->permalink = $permalink;
        $this->view->shortlink = $shortlink;
        $this->view->slug = $slug;
    }
    
    public function addAction($param = array())
    {
        $this->slug();
        parent::addAction($param);
    }
    
    public function editAction() 
    {
        $employer = new Wpjb_Model_Company($this->_request->getParam("id"));
        
        if(wpjb_conf("uses_cpt") && !$employer->post_id) {
            $employer->cpt();
            $employer->save();
        }
        
        $oldActive = $employer->is_active;
        $oldStatus = $employer->is_verified;
        
        parent::editAction();
        
        $employer = $this->view->form->getObject();
        $this->view->user = new WP_User($employer->user_id);
        
        $this->_activateAccount($oldActive, $employer->id);
        
        if($oldStatus == Wpjb_Model_Company::ACCESS_PENDING && $oldStatus != $employer->is_verified) {
            $mail = Wpjb_Utility_Message::load("notify_employer_verify");
            $mail->setTo($this->view->user->user_email);
            $mail->assign("company", $employer);
            $mail->send();
        }
        
        $this->slug($employer);
    }

    protected function _multiActivate($id)
    {
        $object = new Wpjb_Model_Company($id);
        $old = $object->is_active;
        $object->is_active = 1;
        $object->save();
        
        $this->_activateAccount($old, $id);
        do_action("wpjb_company_saved", $object);
        return true;
    }
    
    protected function _multiDeactivate($id)
    {
        $object = new Wpjb_Model_Company($id);
        $object->is_active = 0;
        $object->save();
        do_action("wpjb_company_saved", $object);
        return true;
    }

    protected function _multiApprove($id)
    {
        $object = new Wpjb_Model_Company($id);
        
        if($object->is_verified == Wpjb_Model_Company::ACCESS_GRANTED) {
            return true;
        }
        
        $object->is_verified = Wpjb_Model_Company::ACCESS_GRANTED;
        $object->save();
        do_action("wpjb_company_saved", $object);
        
        $user = new WP_User($object->user_id);
        
        $mail = Wpjb_Utility_Message::load("notify_employer_verify");
        $mail->setTo($user->user_email);
        $mail->assign("company", $object);
        $mail->send();
        
        return true;
    }

    protected function _multiDecline($id)
    {
        $object = new Wpjb_Model_Company($id);
        
        if($object->is_verified == Wpjb_Model_Company::ACCESS_DECLINED) {
            return true;
        }
        
        $object->is_verified = Wpjb_Model_Company::ACCESS_DECLINED;
        $object->save();
        do_action("wpjb_company_saved", $object);
        
        $user = new WP_User($object->user_id);
        
        $mail = Wpjb_Utility_Message::load("notify_employer_verify");
        $mail->setTo($user->user_email);
        $mail->assign("company", $object);
        $mail->send();
        
        return true;
    }
    
    protected function _multiDelete($id)
    {
        $object = new Wpjb_Model_Company($id);
        $object->delete();
        return true;
    }
    
    protected function _activateAccount($old, $id) {
        $employer  = new Wpjb_Model_Company($id);
        
        if($employer->is_active == 1 && $old != $employer->is_active) {
            $mail = Wpjb_Utility_Message::load("notify_account_approved");
            $mail->setTo($employer->getUser(true)->user_email);
            $mail->assign("user", $employer->getUser(true));
            $mail->assign("login_url", get_permalink(wpjb_conf("urls_link_emp_panel")));
            $mail->send();
        }
    }
    
    public function redirectAction()
    {
        if($this->_request->post("action") == "delete" || $this->_request->post("action2") == "delete") {
            $param = array("users"=>$this->_request->post("item", array()));
            $url = wpjb_admin_url("employers", "remove")."&".  http_build_query($param);
            wp_redirect($url);
            exit;
        }

        parent::redirectAction();
    }
    
    public function removeAction()
    {
        $query = new Daq_Db_Query();
        $query->from("Wpjb_Model_Company t");
        $query->where("t.id IN(?)", $this->_request->get("users"));
        $this->view->list = $query->execute();
        $i = 0;
        
        if($this->isPost() && $this->_request->post("delete_option")) {
            
            $option = $this->_request->post("jobs_option");
            $delete = Wpjb_Model_Company::DELETE_FULL;
            if($this->_request->post("delete_option") == "partial") {
                $delete = Wpjb_Model_Company::DELETE_PARTIAL;
            }
            
            foreach($this->_request->post("users", array()) as $id) {
                
                $q = new Daq_Db_Query;
                $q->select("t.id AS `id`");
                $q->from("Wpjb_Model_Job t");
                $q->where("employer_id = ?", $id);
                $result = $q->fetchAll();
                
                foreach($result as $job) {
                    $job = new Wpjb_Model_job($job->id);
                    if($option == "unassign") {
                        $job->employer_id = 0;
                        $job->save();
                    } else {
                        $job->delete();
                    }
                    unset($job);
                }
                
                $company = new Wpjb_Model_Company($id);
                $company->delete($delete);
                $i++;
            }
            
            if($i > 0) {
                $msg = _n("One user deleted.", "%d users deleted.", $i, "wpjobboard");
                $this->_addInfo(sprintf($msg, $i));
            } else {
                $this->_addError(__("No users to delete", "wpjobboard"));
            }
            
            wp_redirect(wpjb_admin_url("employers"));
            exit;
        }
    }

}

?>