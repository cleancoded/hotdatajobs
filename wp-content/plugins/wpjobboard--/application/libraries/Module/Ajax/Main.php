<?php
/**
 * Description of Main
 *
 * @author greg
 * @package 
 */

class Wpjb_Module_Ajax_Main
{
    public static function slugifyAction()
    {
        $list = array("job" => 1, "type" => 1, "category" => 1, 'resume' => 1, 'company' => 1);

        $id = Daq_Request::getInstance()->post("id");
        $title = Daq_Request::getInstance()->post("title");
        $model = Daq_Request::getInstance()->post("object");

        if(!isset($list[$model])) {
            die;
        }

        die(Wpjb_Utility_Slug::generate($model, $title, $id));
    }
    
    public function hideAction()
    {
        if(!current_user_can("edit_pages")) {
            exit(-1);
        }
        
        $request = Daq_Request::getInstance();
        $hide = $request->post("hide");
        $value = $request->post("value");
        
        $allowed = array(
            "activation_message_hide",
            "jobeleon_message_hide"
        );

        if(!in_array($hide, $allowed)) {
            exit(0);
        }

        $config = Wpjb_Project::getInstance();
        $config->setConfigParam($hide, $value);
        $config->saveConfig();
        
        exit(1);
    }

    public function cleanupAction()
    {

    }
    
    public function googleapiAction()
    {
        $address = Daq_Request::getInstance()->getParam("address", "London, United Kingdom");
        
        $query = http_build_query(array(
            "address" => $address,
            "sensor" => "false",
            "key" => wpjb_conf("google_api_key")
        ));
        $url = "https://maps.googleapis.com/maps/api/geocode/json?".$query;
        
        $response = wp_remote_get($url);
        if($response instanceof WP_Error) {
            $result = json_encode(array(
                "status" => "ERROR",
                "error_message"=>$response->get_error_message()
            ));
        } else {
            $result = $response["body"];
        }
        
        echo $result;
        die;
    }
    
    public static function googlejobsAction() {
        
        $request = Daq_Request::getInstance();
        
        if($request->getParam("job_id") > 0) {
            $params = array(
                "filter" => "all",
                "id" => $request->getParam("job_id"),
                "page" => 1,
                "count" => 1
            );
        } else {
            $params = array(
                "filter" => "all",
                "query" => $request->getParam("query"),
                "page" => 1,
                "count" => 20
            );
        }
        
        $jobs = wpjb_find_jobs($params);
        
        $json = new stdClass();
        $json->job = array();
        $gfj = new Wpjb_Service_GoogleForJobs();
        $gfj->setTypes($request->getParam("types", array()));
        $gfj->setTemplate($request->getParam("template"));
        
        add_filter("wpjb_google_for_jobs_jsonld", array($gfj, "mapFromPost"), 10, 2);
        
        foreach($jobs->job as $job) {
            $json->job[] = array(
                "id" => $job->id,
                "job_title" => $job->job_title,
                "url" => $job->url(),
                "admin_url" => wpjb_admin_url("job", "edit", $job->id),
                "jsonld" => $gfj->getHtml($job)
                
            );
        }
        
        echo json_encode($json);
        exit;
    }
    
    public static function googlejobsidAction() {
        
        $request = Daq_Request::getInstance();
        $job = new Wpjb_Model_Job($request->getParam("job_id"));
        
        $json = new stdClass();
        $gfj = new Wpjb_Service_GoogleForJobs();
        $gfj->setTypes($request->getParam("types", array()));
        $gfj->setTemplate($request->getParam("template"));
                
        add_filter("wpjb_google_for_jobs_jsonld", array($gfj, "mapFromPost"), 10, 2);
        
        $json->job = array(
            "id" => $job->id,
            "job_title" => $job->job_title,
            "url" => $job->url(),
            "admin_url" => wpjb_admin_url("job", "edit", $job->id),
            "jsonld" => $gfj->getHtml($job),
            "validate" => $gfj->validateJson($gfj->getJson($job))
        );
        
        
        
        echo json_encode($json);
        exit; 
    }
    
    public static function googlejobssaveAction() {
        $request = Daq_Request::getInstance();
        $gconf = array(
            "jsonld" => $request->getParam("jsonld", array()),
            "types" => $request->getParam("types", array()),
            "is_disabled" => $request->getParam("is_disabled", 0),
            "template" => $request->getParam("template", "")
        );
        
        Wpjb_Project::getInstance()->setConfigParam("google_for_jobs", $gconf);
        Wpjb_Project::getInstance()->saveConfig();
        
        $response = new stdClass();
        $response->result = 1;
        
        echo json_encode($response);
        exit;
                
    }
    
    protected static function _getUserRole($user_id, $discard = "") {
        
        // is employer?
        if($discard == "employer") {
            $query = new Daq_Db_Query();
            $query->select("t.id");
            $query->from("Wpjb_Model_Company t");
            $query->where("user_id = ?", $user_id);
            $query->limit(1);

            $id = $query->fetchColumn();

            if($id) {
                return "employer";
            }
        }
        
        // is candidate?
        if($discard == "candidate") {
            $query = new Daq_Db_Query();
            $query->select("t.id");
            $query->from("Wpjb_Model_Resume t");
            $query->where("user_id = ?", $user_id);
            $query->limit(1);

            $id = $query->fetchColumn();

            if($id) {
                return "candidate";
            }
        }
        
        return "";
    }
    
    public static function usersAction() {
        
        $users = get_users(array(
            "search" => "*".Daq_Request::getInstance()->get("term")."*",
            "number" => 20
           
        ));
        
        $json = array();
        
        foreach($users as $user) {
            $discard = Daq_Request::getInstance()->get("discard");
            $validate = new Wpjb_Validate_CreateUser($discard, $user->ID);
            
            if($validate->isValid(null)) {
                $role = "";
            } else {
                $role = esc_html($discard);
            }
            
            $label = $user->display_name;
            
            if($role != "") {
                $label .= ' <small>('.__("This user already has an account.", "wpjobboard").')</small>';
            }
            
            $object = new stdClass();
            $object->label = $label;
            $object->value = $user->display_name;
            $object->id = $user->ID;
            $object->role = $role;
            $object->hint = sprintf(__("ID: %d; Email: %s", "wpjobboard"), $user->ID, $user->user_email);
            $json[] = $object;
        }
        
        echo json_encode($json);
        exit;
    }
}

?>