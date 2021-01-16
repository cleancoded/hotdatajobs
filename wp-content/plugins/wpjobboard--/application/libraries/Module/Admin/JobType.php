<?php
/**
 * Description of JobType
 *
 * @author greg
 * @package 
 */

class Wpjb_Module_Admin_JobType extends Wpjb_Controller_Admin
{
    public function init()
    {
        $this->_virtual = apply_filters( "wpjb_bulk_actions_functions", array(
           "redirectAction" => array(
               "accept" => array(),
               "object" => "jobType"
           ),
           "addAction" => array(
                "form" => "Wpjb_Form_Admin_JobType",
                "info" => __("New job type has been created.", "wpjobboard"),
                "error" => __("There are errors in your form.", "wpjobboard"),
                "url" => wpjb_admin_url("jobType", "edit", "%d")
            ),
            "editAction" => array(
                "form" => "Wpjb_Form_Admin_JobType",
                "info" => __("Form saved.", "wpjobboard"),
                "error" => __("There are errors in your form.", "wpjobboard")
            ),
            "_delete" => array(
                "model" => "Wpjb_Model_Tag",
                "info" => __("Job Type deleted.", "wpjobboard"),
                "error" => __("There are errors in your form.", "wpjobboard")
            ),
            "_multi" => array(
                "delete" => array(
                    "success" => __("Number of deleted job types: {success}", "wpjobboard")
                ),
                "activate" => array(
                    "success" => __("Number of activated job types: {success}", "wpjobboard")
                ),
                "deactivate" => array(
                    "success" => __("Number of deactivated job types: {success}", "wpjobboard")
                )
            ),
        ), "jobType" );
    }

    protected function _multiDelete($id)
    {
        $total = wpjb_find_jobs(array(
            "filter" => "all",
            "count_only" => true,
            "type" => array($id)
        ));

        if($total > 0) {
            $err = __("Cannot delete job type identified by ID #{id}. There are still jobs using this type.", "wpjobboard");
            $err = str_replace("{id}", $id, $err);
            $this->view->_flash->addError($err);
            return false;
        }

        try {
            $model = new Wpjb_Model_Tag($id);
            $model->delete();
            return true;
        } catch(Exception $e) {
            // log error
            return false;
        }
    }
    
    public function deleteAction() 
    {
        $id = $this->_request->getParam("id");
        
        if($this->_multiDelete($id)) {
            $m = sprintf(__("Job Type #%d deleted.", "wpjobboard"), $id);
            $this->view->_flash->addInfo($m);
        }
        
        wp_redirect(wpjb_admin_url("jobType"));
    }
    
    public function indexAction()
    {
        $page = (int)$this->_request->get("p", 1);
        if($page < 1) {
            $page = 1;
        }
        $perPage = $this->_getPerPage();

        $query = Daq_Db_Query::create();
        $query->from("Wpjb_Model_Tagged t");
        $query->select("tag_id, object, COUNT(*) as `total`");
        $query->group("tag_id, object");
        $result = $query->fetchAll();

        $r = array();
        foreach($result as $row) {
            if(!isset($r[$row->tag_id])) {
                $r[$row->tag_id] = new stdClass();
            }
            
            if($row->object == Wpjb_Model_Tagged::TYPE_JOB) {
                $r[$row->tag_id]->jobs_total = $row->total;
            } else {
                $r[$row->tag_id]->resumes_total = $row->total;
            }
            
        }
        
        $this->view->stat = $r;
        
        $query = Daq_Db_Query::create();
        $query->from("Wpjb_Model_Tag t1");
        $query->where("t1.type = ?", Wpjb_Model_Tag::TYPE_TYPE);
        $query->order("t1.title");
        $query->limitPage($page, $perPage);
        $result = $query->execute();

        $total = (int)$query->select("COUNT(*) as `total`")->limit(1)->fetchColumn();
        
        $this->view->current = $page;
        $this->view->total = ceil($total/$perPage);
        $this->view->data = $result;
    }
    
}

?>