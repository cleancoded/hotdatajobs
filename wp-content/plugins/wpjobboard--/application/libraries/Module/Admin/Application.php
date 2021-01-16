<?php
/**
 * Description of Category
 *
 * @author greg
 * @package
 */

class Wpjb_Module_Admin_Application extends Wpjb_Controller_Admin
{
    protected static $_overrideNotify = array();
    
    public function init()
    {
        $this->view->slot("logo", "user_app.png");
        $this->_virtual = apply_filters( "wpjb_bulk_actions_functions", array(
           "redirectAction" => array(
               "accept" => array("query", "posted", "job", "filter"),
               "object" => "application"
           ),
           "addAction" => array(
                "form" => "Wpjb_Form_Admin_Application",
                "info" => __("New application has been created.", "wpjobboard"),
                "error" => __("There are errors in your form.", "wpjobboard"),
                "url" => wpjb_admin_url("application", "edit", "%d")
            ),
            "editAction" => array(
                "form" => "Wpjb_Form_Admin_Application",
                "info" => __("Form saved.", "wpjobboard"),
                "error" => __("There are errors in your form.", "wpjobboard")
            ),
            "deleteAction" => array(
                "info" => __("Application #%d deleted.", "wpjobboard"),
                "page" => "application"
            ),
            "_delete" => array(
                "model" => "Wpjb_Model_Application",
                "info" => __("Application deleted.", "wpjobboard"),
                "error" => __("There are errors in your form.", "wpjobboard")
            ),
            "_multi" => array(
                "delete" => array(
                    "success" => __("Number of deleted applications: {success}", "wpjobboard")
                ),
            ),
            "_multiDelete" => array(
                "model" => "Wpjb_Model_Application"
            )
        ), "application" );
        
        foreach(wpjb_get_application_status() as $key => $data) {
            $callback = array($this, "_multi".ucfirst($data["key"]));
            $success = "";
            
            if(isset($data["callback"]["multi"]) && is_callable($data["callback"]["multi"])) {
                $callback = $data["callback"]["multi"];
            }
            
            if(isset($data["labels"]["multi_success"]) && !empty($data["labels"]["multi_success"])) {
                $success = $data["labels"]["multi_success"];
            }
            
            $this->_virtual["_multi"][$data["key"]] = array(
                "callback" => $callback,
                "success" => $success
            );
        }
        
        $this->_virtual["_multi"]["print"] = array(
            "callback" => array( $this, "_multiPrint" ),
            "success" => __( "Print file opened in the new tab", "wpjobboard" ),
        );
        
    }

    protected function _multiDelete($id)
    {

        try {
            $model = new Wpjb_Model_Application($id);
            $model->delete();
            return true;
        } catch(Exception $e) {
            // log error
            return false;
        }
    }

    public function indexAction()
    {
        global $wpdb;
        
        $screen = new Wpjb_Utility_ScreenOptions;
        $this->view->screen = $screen;
        
        wp_enqueue_script("wpjb-admin-apps");
        
        $query = $this->_request->get("query");
        
        $this->view->rquery = $this->readableQuery($query);
        $param = $this->deriveParams($query, new Wpjb_Model_Application);
        $param["filter"] = $this->_request->get("filter", "all");
        $param["page"] = (int)$this->_request->get("p", 1);
        $param["count"] = $screen->get("application", "count", 20);
        $param["posted"] = null;
        
        $navigation = array();
        
        $sort_order = "";
        $sort = "applied_at";
        $order = "desc";
        
        if(!isset($param["job"])) {
            $param["job"] = null;
        }
        
        if($this->_request->get("sort") == "__rating") {
            add_filter("wpjb_applications_query", array($this, "sortByRating"));
            $navigation["sort"] = $this->_request->get("sort");
        }

        if($this->_request->get("order")) {
            $order = esc_sql($this->_request->get("order"));
            $navigation["order"] = $order;
        }
        
        $sort_order .= "t1." . $sort . " ".$order;
        
        if($this->_request->get("posted")) {
            $p = $this->_request->get("posted");
            $df = date("Y-m-01 00:00:00", strtotime($p));
            $param["date_from"] = $df;
            $param["date_to"] = date("Y-m-t 23:59:59", strtotime($df));
            //var_dump($param);
            //$param["posted"] = $this->_request->get("posted");
        }
        
        $query_args = $param;
        $query_args["sort_order"] = $sort_order;
        
        $result = Wpjb_Model_Application::search($query_args);
        
        if($this->_request->get("sort") == "__rating") {
            $sort = "__rating";
        }
        
        $this->view->sort = $sort;
        $this->view->order = $order;
        $this->view->search = $param;
        $this->view->data = $result->application;
        $this->view->show = $param["filter"];
        $this->view->current = $param["page"];
        $this->view->total = $result->pages;
        $this->view->param = array("filter"=>$param["filter"], "posted"=>$param["posted"], "job"=>$param["job"], "query"=>$query);
        $this->view->query = $this->_request->get("query");
        $this->view->navi = array_merge($this->view->param, $navigation);
        
        foreach($param as $k=>$v) {
            $this->view->$k = $v;
        }
        
        $name = new Wpjb_Model_Application();
        $name = $name->tableName();
        /* @var $wpdb wpdb */
        $result = $wpdb->get_results("
            SELECT DATE_FORMAT(applied_at, '%Y-%m') as dt
            FROM $name GROUP BY dt ORDER BY applied_at DESC
        ");

        $months = array();
        foreach($result as $r) {
            $months[$r->dt] = date("Y, F", strtotime($r->dt));
        }

        $this->view->months = $months;
        
    }
    
    public function navi() {
        
        $sort_order = "";
        $sort = "applied_at";
        $order = "desc";
        
        if($this->_request->get("sort") == "__rating") {
            add_filter("wpjb_applications_query", array($this, "sortByRating"));
        }

        if($this->_request->get("order")) {
            $order = esc_sql($this->_request->get("order"));
        }
        
        $sort_order .= "t1." . $sort . " ".$order;
        
        $application = new Wpjb_Model_Application($this->_request->get("id"));

        $params1 = $this->deriveParams($this->_request->get("query"), new Wpjb_Model_Application);
        $params2 = array(
            "filter" => $this->_request->get("filter"),
            "sort_order" => $sort_order,
            "ids_only" => true
        );
        
        $apps = Wpjb_Model_Application::search(array_merge($params1, $params2));

        $app_i = 0;
        $app_older = null;
        $app_newer = null;
        $app_args = array(
            "job_id" => Daq_Request::getInstance()->get("job_id"),
            "job_status" => Daq_Request::getInstance()->get("job_status"),
            "pg" => Daq_Request::getInstance()->get("pg"),
        );
        
        $apps_list = array_reverse($apps->application);
        
        foreach($apps_list as $t_id) {
            $app_i++;
            if($t_id == $application->id) {
                break;
            } 
        }
        if(isset($apps_list[$app_i])) {
            $app_newer = new Wpjb_Model_Application($apps_list[$app_i]);
        }
        if(isset($apps_list[$app_i-2])) {
            $app_older = new Wpjb_Model_Application($apps_list[$app_i-2]);
        }
        
        $this->view->apps = $apps;
        $this->view->app_i = $app_i;
        $this->view->app_older = $app_older;
        $this->view->app_newer = $app_newer;
        $this->view->app_args = $app_args;
    }
    
    public function sortByRating($select) {
        
        if($this->_request->get("order") == "asc") {
            $order = "ASC";
        } else {
            $order = "DESC";
        }
        
        $query = new Daq_Db_Query();
        $query->select("t.id");
        $query->from("Wpjb_Model_Meta t");
        $query->where("t.name = ?", "rating");
        $query->where("t.meta_object = ?", "apply");
        $query->limit(1);
        
        $meta_id =  absint($query->fetchColumn());
        
        $select->order("__rating.value $order");
        $select->joinLeft("t1.meta __rating", "(__rating.meta_id = $meta_id)");
        
        return $select;
    }
    
    public function editAction() 
    {
        if($this->isPost()) {
            $s = wpjb_get_application_status($this->_request->post("status"));
            if(isset($s["notify_applicant_email"])) {
                self::$_overrideNotify[$s["notify_applicant_email"]] = absint($this->_request->post("_notify"));
                add_filter( "wpjb_message", array(__CLASS__, "overrideNotify"));
            }
            
        }
        
        parent::editAction();
        
        $uid = $this->view->form->getObject()->user_id;

        if($uid > 0) {
            $this->view->user = new WP_User($uid);

            $query = new Daq_Db_Query();
            $query->select("t.id");
            $query->from("Wpjb_Model_Resume t");
            $query->where("user_id = ?", $uid);
            $query->limit(1);
            $this->view->resumeId = $query->fetchColumn();
        } else {
            $this->view->user = null;
            $this->view->resumeId = null;
        }
        
        $this->navi();
        
    }
    
    protected function _multiNew($id)
    {
        $object = new Wpjb_Model_Application($id);
        $object->status = Wpjb_Model_Application::STATUS_NEW;
        $object->save();
        
        return true;
    }
    
    protected function _multiAccepted($id)
    {
        $object = new Wpjb_Model_Application($id);
        $object->status = Wpjb_Model_Application::STATUS_ACCEPTED;
        $object->save();
        
        return true;
    }
    
    protected function _multiRejected($id)
    {
        $object = new Wpjb_Model_Application($id);
        $object->status = Wpjb_Model_Application::STATUS_REJECTED;
        $object->save();
        
        return true;
    }
    
    protected function _multiRead($id)
    {
        $object = new Wpjb_Model_Application($id);
        $object->status = Wpjb_Model_Application::STATUS_READ;
        $object->save();
        
        return true;
    }
    
    protected function _multiPrint($id)
    {
        $ids = get_transient( 'wpjb_bulkprint' );
        if( $ids === false ) {
            $ids = array();
        }
        
        $ids[] = $id;
        set_transient( 'wpjb_bulkprint', $ids, 60 * 5 );
        
        return true;
    }

    public static function overrideNotify($mail) {
        if(isset(self::$_overrideNotify[$mail["key"]->name])) {
            $mail["is_active"] = self::$_overrideNotify[$mail["key"]->name];
            self::$_overrideNotify = array();
            remove_filter( "wpjb_message", array(__CLASS__, __METHOD__));
        }
        return $mail;
    }
    
}

?>