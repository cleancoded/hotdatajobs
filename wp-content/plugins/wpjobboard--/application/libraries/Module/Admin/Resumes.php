<?php
/**
 * Description of Resumes
 *
 * @author greg
 * @package 
 */

class Wpjb_Module_Admin_Resumes extends Wpjb_Controller_Admin
{

    public function init()
    {
        $this->view->slot("logo", "candidates.png");
        $this->_virtual = apply_filters( "wpjb_bulk_actions_functions", array(
           "redirectAction" => array(
               "accept" => array("filter", "posted", "query"),
               "object" => "resumes"
           ),
           "editAction" => array(
               "form" => "Wpjb_Form_Admin_Resume",
               "info" => __("Resume has been saved.", "wpjobboard"),
               "error" => __("There are errors in the form.", "wpjobboard")
           ),
           "_multi" => array(
               "activate" => array(
                   "success" => __("Number of activated resumes: {success}", "wpjobboard")
               ),
               "deactivate" => array(
                   "success" => __("Number of deactivated resumes: {success}", "wpjobboard")
               ),
           ),
           "_multiDelete" => array(
               "model" => "Wpjb_Model_Resume"
           )
       ), "resume" );
    }

    public function indexAction()
    {        
        
        $screen = new Wpjb_Utility_ScreenOptions();
        $this->view->screen = $screen;
        $query = $this->_request->get("query");
        
        $this->view->rquery = $this->readableQuery($query);
        
        $param = $this->deriveParams($query, new Wpjb_Model_Resume);
        $param["filter"] = $this->_request->get("filter", "all");
        $param["page"] = (int)$this->_request->get("p", 1);
        $param["count"] = $screen->get("resume", "count", 20);
        
        $result = Wpjb_Model_ResumeSearch::search($param);
        
        $this->view->data = $result->resume;
        $this->view->filter = $param["filter"];
        $this->view->current = $param["page"];
        $this->view->total = $result->pages;
        $this->view->param = array("filter"=>$param["filter"], "query"=>$query);
        $this->view->query = $query;
        
        $stat = new stdClass();
        $stat->total = Wpjb_Model_ResumeSearch::search(array_merge($param, array("filter"=>"all", "count_only"=>1)));
        $stat->active = Wpjb_Model_ResumeSearch::search(array_merge($param, array("filter"=>"active", "count_only"=>1)));
        $stat->inactive = Wpjb_Model_ResumeSearch::search(array_merge($param, array("filter"=>"inactive", "count_only"=>1)));
        $this->view->stat = $stat;
        
    }

    protected function _multiActivate($id)
    {
        $object = new Wpjb_Model_Resume($id);
        $old = $object->is_active;
        $object->is_active = 1;
        $object->save();
                
        $this->_activateAccount($old, $id);
        return true;
    }

    protected function _multiDeactivate($id)
    {
        $object = new Wpjb_Model_Resume($id);
        $object->is_active = 0;
        $object->save();
        return true;
    }
    
    protected function _activateAccount($old, $id) {
        $candidate  = new Wpjb_Model_Resume($id);
        
        if($candidate->is_active == 1 && $old != $candidate->is_active) {
            $mail = Wpjb_Utility_Message::load("notify_account_approved");
            $mail->setTo($candidate->getUser(true)->user_email);
            $mail->assign("user", $candidate->getUser(true));
            $mail->assign("login_url", get_permalink(wpjb_conf("urls_link_cand_panel")));
            $mail->send();
        }
    }
    
    
    public function slug($object = null)
    {
        global $wp_rewrite;

        $instance = Wpjb_Project::getInstance();
        $pedit = '<span id="editable-post-name" title="Click to edit this part of the permalink">[slug]</span>';
        $shortlink = null;
        
        if($object) {
            $permalink = $object->url();
        } else {
            $permalink = null;
            $object = new stdClass();
            $object->post_id = null;
        }

        if(!get_option('permalink_structure')) {
            $url = wpjr_link_to("resume", $object);
            $slug = null;
        } elseif($instance->env("uses_cpt")) {
            $post = get_post($object->post_id);
            
            if($post) {
                $shortlink = wp_get_shortlink($post->ID, 'post');
                $slug = $post->post_name;
            }
            
            $pstruct = $wp_rewrite->get_extra_permastruct("resume");
            $purl = home_url( user_trailingslashit($pstruct) );

            $url = str_replace("%resume%", $pedit, $purl);
        } else {
            $model = new Wpjb_Model_Resume();
            $model->candidate_slug = $pedit;
            $slug = $object->candidate_slug;
            $url = wpjr_link_to("resume", $model);
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

        $form = new Wpjb_Form_Admin_Resume_Create();
        $id = false;
        
        if($this->isPost()) {
            $isValid = $form->isValid($this->_request->getAll());
            if($isValid) {
                $this->_addInfo(__("Candidate has been created.", "wpjobboard"));
                $id = $form->save();
                if(!$id) {
                    $id = $form->getId();
                }
            } else {
                $this->_addError(__("There are errors in your form.", "wpjobboard"));
            }
        }

        $this->redirectIf($id, sprintf(str_replace("%25d", "%d", wpjb_admin_url("resumes", "edit", "%d")), $id));
        
        wp_enqueue_script("wpjb-myresume");
        $form->buildPartials();
        
        $this->view->form = $form;
    }
    
    public function editAction()
    {
        extract($this->_virtual[__FUNCTION__]);

        $id = $this->_request->getParam("id");
        $resume = new Wpjb_Model_Resume($id);
        
        $oldActive = $resume->is_active;
        
        if(wpjb_conf("uses_cpt") && !$resume->post_id) {
            $resume->cpt();
        }
        
        $form = new $form($id);
        $this->slug($resume);
        
        if($this->isPost()) {
            $isValid = $form->isValid($this->_request->getAll());
            if($isValid) {
                $this->_addInfo($info);
                $form->save();
                $id = $form->getId();
            } else {
                $this->_addError($error);
            }
        }
        
        $this->_activateAccount($oldActive, $resume->id);

        wp_enqueue_script("wpjb-myresume");
        $form->buildPartials();
        
        $this->view->form = $form;
        $this->view->resume = $form->getObject();
        $this->view->user = new WP_User($this->view->resume->user_id);
    }
    
    public function redirectAction()
    {
        if($this->_request->post("action") == "delete" || $this->_request->post("action2") == "delete") {
            $param = array("users"=>$this->_request->post("item", array()));
            $url = wpjb_admin_url("resumes", "remove")."&".  http_build_query($param);
            wp_redirect($url);
            exit;
        }

        parent::redirectAction();
    }
    
    public function removeAction()
    {
        $query = new Daq_Db_Query();
        $query->from("Wpjb_Model_Resume t");
        $query->where("t.id IN(?)", $this->_request->get("users"));
        $this->view->list = $query->execute();
        $i = 0;
        
        if($this->isPost() && $this->_request->post("delete_option")) {
            
            $delete = Wpjb_Model_Resume::DELETE_FULL;
            if($this->_request->post("delete_option") == "partial") {
                $delete = Wpjb_Model_Resume::DELETE_PARTIAL;
            }

            foreach($this->_request->post("users", array()) as $id) {
                $resume = new Wpjb_Model_Resume($id);
                $resume->delete($delete);
                $i++;
                
                if($delete == Wpjb_Model_Resume::DELETE_FULL) {
                    $query = new Daq_Db_Query();
                    $query->from("Wpjb_Model_Application t");
                    $query->where("user_id = ?", $resume->user_id);
                    $list = $query->execute();


                    foreach($list as $app) {
                        if($this->_request->get("applications_option") == "delete") {
                            $app->delete();
                        } else {
                            $app->user_id = 0;
                            $app->save();
                        }
                    } //endforeach
                } // endif
                
            } // endforeach
            
            if($i > 0) {
                $msg = _n("One user deleted.", "%d users deleted.", $i, "wpjobboard");
                $this->_addInfo($msg);
            } else {
                $this->_addError(__("No users to delete", "wpjobboard"));
            }
            
            wp_redirect(wpjb_admin_url("resumes"));
            exit;
        }
    }

}

?>