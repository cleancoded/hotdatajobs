<?php
/**
 * Description of Category
 *
 * @author greg
 * @package 
 */

class Wpjb_Module_Admin_Category extends Wpjb_Controller_Admin
{
    public function init()
    {
        $this->_virtual = apply_filters( "wpjb_bulk_actions_functions", array(
           "redirectAction" => array(
               "accept" => array(),
               "object" => "category"
           ),
           "addAction" => array(
                "form" => "Wpjb_Form_Admin_Category",
                "info" => __("New category has been created.", "wpjobboard"),
                "error" => __("There are errors in your form.", "wpjobboard"),
                "url" => wpjb_admin_url("category", "edit", "%d")
            ),
            "editAction" => array(
                "form" => "Wpjb_Form_Admin_Category",
                "info" => __("Form saved.", "wpjobboard"),
                "error" => __("There are errors in your form.", "wpjobboard")
            ),
            "_delete" => array(
                "model" => "Wpjb_Model_Tag",
                "info" => __("Category deleted.", "wpjobboard"),
                "error" => __("There are errors in your form.", "wpjobboard")
            ),
            "_multi" => array(
                "delete" => array(
                    "success" => __("Number of deleted categories: {success}", "wpjobboard")
                )
            ),
            "_multiDelete" => array(
                "model" => "Wpjb_Model_Tag"
            )
        ), "category" );
    }

    protected function _multiDelete($id)
    {
        $total = wpjb_find_jobs(array(
            "filter" => "all",
            "count_only" => true,
            "category" => array($id)
        ));

        if($total > 0) {
            $err = __("Cannot delete category identified by ID #{id}. There are still jobs in this category.", "wpjobboard");
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
            $m = sprintf(__("Category #%d deleted.", "wpjobboard"), $id);
            $this->view->_flash->addInfo($m);
        }
        wp_redirect(wpjb_admin_url("category"));
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
                $r[$row->tag_id]->jobs_total = 0;
                $r[$row->tag_id]->resumes_total = 0;
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
        $query->where("t1.type = ?", Wpjb_Model_Tag::TYPE_CATEGORY);
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