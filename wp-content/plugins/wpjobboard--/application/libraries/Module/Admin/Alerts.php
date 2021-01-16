<?php
/**
 * Description of Payment
 *
 * @author greg
 * @package
 */

class Wpjb_Module_Admin_Alerts extends Wpjb_Controller_Admin
{
    public function init()
    {
        $this->_virtual = apply_filters( "wpjb_bulk_actions_functions", array(
            "redirectAction" => array(
                "accept" => array("query"),
                "object" => "alerts"
            ),
            "addAction" => array(
                "form" => "Wpjb_Form_Alert",
                "info" => __("New alert has been created.", "wpjobboard"),
                "error" => __("There are errors in your form.", "wpjobboard"),
                "url" => wpjb_admin_url("alerts", "edit", "%d")
            ),
            "editAction" => array(
                "form" => "Wpjb_Form_Alert",
                "info" => __("Form saved.", "wpjobboard"),
                "error" => __("There are errors in your form.", "wpjobboard")
            ),
            "deleteAction" => array(
                "info" => __("Alert #%d deleted.", "wpjobboard"),
                "page" => "alerts"
            ),
            "_multiDelete" => array(
                "model" => "Wpjb_Model_Alert"
            ),
            "_multi" => array(
                "delete" => array(
                    "success" => __("Number of deleted alerts: {success}", "wpjobboard")
                ),
            )
        ), "alert" );
    }

    /**
     * Index of all Alerts in wp-admin
     * 
     * @param void
     * @global wpdb $wpdb
     * @return void
     */
    public function indexAction()
    {
        global $wpdb;
        
        wp_enqueue_script("wpjb-admin-apps");
        
        
        $stat = (object)array("all"=>0, "daily"=>0, "weekly"=>0);
        $frequency = array("daily"=>1, "weekly"=>2);
        
        $page = (int)$this->_request->get("p", 1);
        if($page < 1) {
            $page = 1;
        }
        
        $q = $this->_request->get("query");
        $filter = $this->_request->get("filter", "all");
        $sort = $this->_request->get("sort", "created_at");
        $order = $this->_request->get("order", "desc");
        
        $this->view->sort = $sort;
        $this->view->order = $order;
        $this->view->query = $q;
        $this->view->filter = $filter;
        
        $param = array();
        
        if(!empty($q)) {
            $param["query"] = $q;
        }
        if(!empty($filter)) {
            $param["filter"] = $filter;
        } 
        
        $param["sort"] = $sort;
        $param["order"] = $order;
       
        $perPage = $this->_getPerPage();
        
        $query = new Daq_Db_Query();
        $query->select("*")
            ->from("Wpjb_Model_Alert t")
            ->order(esc_sql("$sort $order"))
            ->limitPage($page, $perPage);

        if($q) {
            $query->where("email LIKE ?", "%$q%");
        }
        if($filter && isset($frequency[$filter])) {
            $query->where("frequency = ?", $frequency[$filter]);
        } 
        
        $this->view->data = $query->execute();

        $query = new Daq_Db_Query();
        $total = $query->select("COUNT(*) AS total")
            ->from("Wpjb_Model_Alert t")
            ->limit(1);
        
        if($q) {
            $total->where("email LIKE ?", "%$q%");
        }

        $stat->all = $total->fetchColumn();
        $daily = clone $total;
        $stat->daily = $daily->where("frequency = 1")->fetchColumn();
        $weekly = clone $total;
        $stat->weekly = $weekly->where("frequency = 2")->fetchColumn();

        $this->view->stat = $stat;
        $this->view->param = $param;
        $this->view->current = $page;
        $this->view->total = ceil($stat->all/$perPage);
    }
    
    /**
     * Exports all Alerts to CSV file
     * 
     * @param void
     * @global wpdb $wpdb
     * @return void
     */
    public function exportAction()
    {
        // Begin: indexAction
        global $wpdb;
        
        $stat = (object)array("all"=>0, "daily"=>0, "weekly"=>0);
        $frequency = array("daily"=>1, "weekly"=>2);
        
        $page = (int)$this->_request->get("p", 1);
        if($page < 1) {
            $page = 1;
        }
        
        $q = $this->_request->get("query");
        $filter = $this->_request->get("filter", "all");
        $sort = $this->_request->get("sort", "created_at");
        $order = $this->_request->get("order", "desc");
        
        $this->view->sort = $sort;
        $this->view->order = $order;
        $this->view->query = $q;
        $this->view->filter = $filter;
        
        $param = array();
        
        if(!empty($q)) {
            $param["query"] = $q;
        }
        if(!empty($filter)) {
            $param["filter"] = $filter;
        } 
        
        $param["sort"] = $sort;
        $param["order"] = $order;
       
        $perPage = $this->_getPerPage();

        $query = new Daq_Db_Query();
        $query->select("*")
            ->from("Wpjb_Model_Alert t")
            ->order($wpdb->escape("$sort $order"));

        if($q) {
            $query->where("email LIKE ?", "%$q%");
        }
        if($filter && isset($frequency[$filter])) {
            $query->where("frequency = ?", $frequency[$filter]);
        } 
        
        // End: indexAction
        
        header("Content-type: text/plain; charset=utf-8");
        header('Content-Disposition: attachment; filename="alerts.csv";');
        
        $result = $query->select("t.id AS `id`")->fetchAll();

        $app = new Wpjb_Model_Alert();
        $csv = fopen("php://output", "w");
        $fields = array();

        foreach($app->getFieldNames() as $f) {
            if(!in_array($f, array("user_id", "params"))) {
                $fields[] = $f;
            }
            
        }

        fputcsv($csv, $fields);
        
        
        foreach($result as $r) {
            $app = new Wpjb_Model_Alert($r->id);
            $arr = $app->toArray();
            $param = unserialize($app->params);
            
            unset($arr["user_id"]);
            unset($arr["params"]);
            unset($arr["meta"]);
            
            fputcsv($csv, $arr);
            
            unset($app);
            unset($arr);
        }
        
        fclose($csv);
        
        exit;
    }
    
    public function editAction() {
          
        extract($this->_virtual[__FUNCTION__]);
        
        parent::editAction();
        
        // Is Post
        $form = new $form($this->_request->getParam("id"));
        if($this->isPost()) {
            // Form is valid?
            $isValid = $form->isValid($this->_request->getAll());
            if($isValid) {
                $this->_addInfo($info);
                $form->save();                
            } else {
                $this->_addError($error);
            }  
        }
        
        // Dispaly form
        wp_enqueue_script('wpjb-admin-alert');
        wp_enqueue_style('wpjb-glyphs');

        $params = unserialize($form->getElement("params")->getValue());
        if(!is_array($params) && !is_object($params)) {
            $params = array(0);
        }
        
        /*$fields = array();
        $fields['default_fields'] = array();
        $fields['custom_fields'] = array();
        $job = new Wpjb_Model_Job();
        $allowed_fields = array('keyword', 'location', 'is_featured', 'employer_id', 'job_country', 'job_state', 'job_zip_code', 'job_city', 'category', 'type');
        foreach($form->get_job_subform()->getFields() as $key => $field) {
            
            $f = array(
                'input_name'    => $field->getName(),
                'input_type'    => $field->getType(),
                'input_label'   => $field->getLabel(),
                'options'       => null,
            );
            if( in_array( $f['input_type'], array('select', 'radio', 'checkbox') ) ) {
                $f['options'] = $field->getOptions();
            }
            
            if( isset( $job->meta->{$field->getName()} ) ) {
                $fields['custom_fields'][$field->getName()] = array('value' => base64_encode(json_encode($f)), 'label' => $field->getLabel() );
            } elseif( in_array( $field->getName(), $allowed_fields ) ) {
                $fields['default_fields'][$field->getName()] = array('value' => base64_encode(json_encode($f)), 'label' => $field->getLabel() );
            }
        }
        
        usort($fields['default_fields'], function($a, $b) {
            return $a['label'] >= $b['label'];
        });
        
        usort($fields['custom_fields'], function($a, $b) {
            return $a['label'] >= $b['label'];
        });*/
        $fields = wpjb_get_all_fileds();
        
        $this->view->form = $form;
        $this->view->params = $params;
        $this->view->fields = $fields;
        $this->view->sub_form = $form->get_job_subform();
        
        $uid = $this->view->form->getObject()->user_id;
        if($uid > 0) {
            $this->view->user = new WP_User($uid);
        } else {
            $this->view->user = null;
        }
    }
    
    public function addAction($param = array()) {
        
        extract($this->_virtual[__FUNCTION__]);
        
        parent::addAction();
        
        $form = new $form($this->_request->getParam("id"));
        if($this->isPost()) {
            // Form is valid?
            $isValid = $form->isValid($this->_request->getAll());
            if($isValid) {
                $this->_addInfo($info);
                $form->save();                
            } else {
                $this->_addError($error);
            }  
        }
        
        wp_enqueue_script('wpjb-admin-alert');
        wp_enqueue_style('wpjb-glyphs');
        
        $fields = array();
        $fields['default_fields'] = array();
        $fields['custom_fields'] = array();
        $job = new Wpjb_Model_Job();
        $allowed_fields = array('keyword', 'location', 'is_featured', 'employer_id', 'job_country', 'job_state', 'job_zip_code', 'job_city', 'category', 'type');
        foreach($form->get_job_subform()->getFields() as $key => $field) {
            
            $f = array(
                'input_name'    => $field->getName(),
                'input_type'    => $field->getType(),
                'input_label'   => $field->getLabel(),
                'options'       => null,
            );
            if( in_array( $f['input_type'], array('select', 'radio', 'checkbox') ) ) {
                $f['options'] = $field->getOptions();
            }
            
            if( isset( $job->meta->{$field->getName()} ) ) {
                $fields['custom_fields'][$field->getName()] = array('value' => base64_encode(json_encode($f)), 'label' => $field->getLabel() );
            } elseif( in_array( $field->getName(), $allowed_fields ) ) {
                $fields['default_fields'][$field->getName()] = array('value' => base64_encode(json_encode($f)), 'label' => $field->getLabel() );
            }
        }
        
        usort($fields['default_fields'], function($a, $b) {
            return $a['label'] >= $b['label'];
        });
        
        usort($fields['custom_fields'], function($a, $b) {
            return $a['label'] >= $b['label'];
        });
        
        $this->view->fields = $fields;
        
        
    }   

}

?>