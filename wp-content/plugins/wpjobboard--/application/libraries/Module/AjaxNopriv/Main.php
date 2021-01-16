<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Main
 *
 * @author greg
 */
class Wpjb_Module_AjaxNopriv_Main 
{
    
    public static function deleteAction()
    {
        global $blog_id;

        if($blog_id > 1) {
            $bid = "-".$blog_id;
        } else {
            $bid = "";
        }
        
        $request = Daq_Request::getInstance();
        $id = $request->getParam("id");
        $dir = wp_upload_dir();
        $dir = $dir["basedir"]."/wpjobboard{$bid}";
        $response = new stdClass;
        $response->result = false;
        $response->msg = "";
        
        if(!is_file($dir."/".$id)) {
            $response->msg = __("File does not exist.", "wpjobboard");
            echo json_encode($response);
            exit;
        }
        
        $path = explode("/", $id);
        $priv = explode("_", $path[1]);
        
        if($priv[1]=="u" && $priv[2]==get_current_user_id()) {
            
        } elseif($priv[1]=="s" && $priv[2]==wpjb_transient_id()) {
            
        } elseif(isset($path[1]) && is_numeric($path[1]) && self::userOwnsFile($path[0], $path[1])) {
            
        } elseif(current_user_can("edit_pages")) {
            
        } else {
            $response->msg = __("You do not have permissions to delete file $id.", "wpjobboard");
            die(json_encode($response));
        }
        
        $file = $dir."/".$id;
        do {
            if(is_dir($file)) {
                rmdir($file);
            } else {
                unlink($file);
                $tpath = dirname($file);
                $tname = basename($file);
                foreach(wpjb_glob("$tpath/_[_]*[0-9x]*_$tname") as $tfile) {
                    unlink($tfile);
                }
            }
            $file = dirname($file);
            $files = glob($file."/*");
        } while(empty($files));
        
        $response->result = 1;
        die(json_encode($response));
        
    }
    
    public static function unlinkAction()
    {
        $response = new stdClass;
        $response->result = false;
        $response->msg = "-1";
        
        if(!current_user_can("upload_files")) {
            echo json_encode($response);
            exit;
        }
        
        $request = Daq_Request::getInstance();
        $class = $request->post("object");
        $field = $request->post("field");
        $id = $request->post("id");

        $link = new Wpjb_Utility_Link(array(
            "object" => $request->post("object"),
            "field" => $request->post("field"),
            "id" => $request->post("id")
        ));
        $link->remove($request->post("link_id"));
        
        $response->result = 1;
        die(json_encode($response));
    }
    
    public static function validate($file)
    {
        $request = Daq_Request::getInstance();
        $response = new stdClass();
        $response->result = 0;
        $response->msg = "";
        
        $id = null;
        
        $form = $request->post("form");
        $field = $request->post("field");
        
        $part = explode(".", $file['name']);
        if(is_array($part) && isset($part[count($part)-1])) {
            $ext = $part[count($part)-1];
        } else {
            $ext = null;
        }

        if($ext && in_array(strtolower($ext), array("php", "php5", "php4"))) {
            $response->msg = __("Cannot upload PHP files.", "wpjobboard");
            return $response;
        }
        
        if(!class_exists($form)) {
            $response->msg = __("Unknown form parameter.", "wpjobboard");
            return $response;
        }
        
        if(is_numeric($request->post("id"))) {
            $id = $request->post("id");
        }
        
        $form = new $form($id);
        
        if(!$form->hasElement($field) || $form->getElement($field)->getType() != "file") {
            $response->msg = __("Unallowed object.", "wpjobboard");
            return $response;
        }
        
        $field = $form->getElement($field);
        $field->setValue($file);
        
        /* @var $field Daq_Form_Element_File */
        
        if(!$field->validate()) {
            $response->msg = join(". ", $field->getErrors());
            return $response;
        }
        
        $path = $field->getUploadPath();
        $upload = wpjb_upload_dir($path["object"], $path["field"], $form->getId());
        $dir = $upload["basedir"];
        $url = $upload["baseurl"];
        
        $uploaded = count(wpjb_glob("$dir/[!_]*"));
        
        $link = new Wpjb_Utility_Link(array(
            "object" => $request->post("object"),
            "field" => str_replace("-", "_", $path["field"]),
            "id" => $id
        ));

        $uploaded += count($link->getAll());

        if($uploaded >= $field->getMaxFiles()) {
            $response->msg = sprintf(__("File upload limit reached", "wpjobboard"));
            return $response;
        }
        
        return true;
    }
    
    public static function validateLink($link) 
    {
        return self::validate($link);
    }
        
    public static function uploadAction()
    {
        $result = self::validate($_FILES["file"]);
        $response = new stdClass();
        
        if($result !== true) {
            die(json_encode($result));
        }
        
        $request = Daq_Request::getInstance();
        
        if(is_numeric($request->post("id"))) {
            $id = $request->post("id");
        } else {
            $id = null;
        }
        
        $form = $request->post("form");
        $form = new $form($id);
        
        $field = $form->getElement($request->post("field"));
        $field->setValue($_FILES["file"]);
        
        $path = $field->getUploadPath();
        $upload = wpjb_upload_dir($path["object"], $path["field"], $form->getId());
        $dir = $upload["basedir"];
        $url = $upload["baseurl"];
        
        if(!wp_mkdir_p($dir)) {
            $response->msg = sprintf(__("Upload directory %s could not be created.", "wpjobboard"), $dir);
            die(json_encode($response));
        }
        
        $wpupload = wp_upload_dir();
        $stat = @stat($wpupload["basedir"]);
        $perms = $stat['mode'] & 0007777;
        chmod($dir, $perms);
        
        $field->setDestination($dir);
        $filename = $field->upload();
        $filename = basename($filename[0]);
        
        $response->result =  1;
        $response->id = null;
        $response->type = "file";
        $response->name = $filename;
        $response->url = $url."/".$filename;
        $response->path = $upload["dir"]."/{$filename}";
        $response->size = filesize($dir."/".$filename);
        
        do_action("wpjb_file_uploaded", $response);
        
        die(json_encode($response));
    }
    
    public static function linkAction()
    {
        $request = Daq_Request::getInstance();
        $response = array("result"=>false, "links"=>array());
        
        if(!current_user_can("upload_files")) {
            echo json_encode($response);
            exit;
        }
        
        $links = $request->post("links");
        if( !is_array($links) && !is_object($links) ) {
            $links = (array)$links;
        }
        $fclass = $request->post("form");
        
        foreach($links as $link) {

            $post = wp_prepare_attachment_for_js($link["id"]);
            $file = array(
                "name" => $post["filename"],
                "type" => $post["mime"],
                "tmp_name" => get_attached_file( $link["id"] ),
                "error" => UPLOAD_ERR_OK,
                "size" => $post["filesizeInBytes"],
                
            );

            $result = null;
            $form = new $fclass;
            $l = new Wpjb_Utility_Link(array(
                "object" => get_class($form->getObject()),
                "field" => $request->post("field"),
                "id" => $request->post("id"),
            ));
            
            foreach($l->getAll() as $tmp) {
                if($tmp["id"] == $post["id"]) {
                    $result = new stdClass();
                    $result->msg = __("You already added this file.", "wpjobboard");
                    break;
                }
            }
            
            if($post["url"] != $link["url"]) {
                $result = new stdClass();
                $result->msg = "Invalid URL.";
            } 
            
            if($result === null) {
                $result = self::validate($file);
            }
            
            if($result === true) {
                $result = new stdClass();
                $result->result =  1;
                $result->id = $post["id"];
                $result->type = "link";
                $result->name = $post["filename"];
                $result->url = $post["url"];
                $result->path = get_attached_file( $link["id"] );
                $result->size = $post["filesizeInBytes"];
                
                $l->saveLink(array(
                    "id" => $post["id"],
                    "url" => $post["url"]
                ));

            } else {
                $result->result = 0;
            }
            
            $response["links"][] = $result;

            
        }

        //$response->result = 1;
        $response['result'] = 1;
        
        echo json_encode($response);
        exit;
    }
    
    public static function couponAction()
    {
        $r = Daq_Request::getInstance();
        $response = new stdClass();
        $response->result = 0;
        $response->msg = "";

        try {
            $listing = new Wpjb_Model_Pricing($r->getParam("id"));
            $listing->applyCoupon($r->getParam("code"));
        } catch(Wpjb_Model_PricingException $e) {
            $response->msg = $e->getMessage();
            echo json_encode($response);
            die;
        }
        
        $taxer = new Wpjb_Utility_Taxer();
        $taxer->setPrice($listing->getPrice());
        $taxer->setDiscount($listing->getDiscount());
        
        $response->msg = sprintf(__("Coupon '%s' applied.", "wpjobboard"), $listing->getCoupon()->title);
        $response->result = 1;
        $response->price = wpjb_price($taxer->value->price, $listing->currency);
        $response->discount = "-".wpjb_price($taxer->value->discount, $listing->currency);
        $response->tax = wpjb_price($taxer->value->tax, $listing->currency);
        $response->subtotal = wpjb_price($taxer->value->subtotal, $listing->currency);
        $response->total = wpjb_price($taxer->value->total, $listing->currency);
        
        if($listing->getTotal() == 0) {
            $response->is_free = "1";
        } else {
            $response->is_free = "0";
        }
        
        echo json_encode($response);
        die;
    }
    
    public static function subscribeAction()
    {
        $r = Daq_Request::getInstance();
        $response = new stdClass();
        $response->result = 0;
        $response->msg = "";
        
        $criteria = $r->getParam("criteria");
        $criteria["filter"] = "active";
        
        unset($criteria["count"]);
        unset($criteria["page"]);
        
        if(isset($criteria["sort_order"])) {
            unset($criteria["sort_order"]);
        }
        
        if(isset($criteria["query"]) && !empty($criteria["query"])) {
            $criteria["keyword"] = $criteria["query"];
            unset($criteria["query"]);
        }
        
        $request = $r->getAll();
        foreach($criteria as $key => $value) {
            $request[$key] = $value;
        }
        
        $form = new Wpjb_Form_Frontend_Alert();
        //if(!is_email($r->getParam("email"))) {
        if(!$form->isValid($request)) {
            $response->result = 0;
            //$response->msg = __("You provided invalid email address.", "wpjobboard");
            $response->errors = $form->getErrors();
            $response->msg = __("You have an error in your form.", "wpjobboard");
            
            echo json_encode($response);
            die;
        }
        
        try {
            
            if(isset($criteria["query"]) && !empty($criteria["query"])) {
                $criteria["keyword"] = $criteria["query"];
                unset($criteria["query"]);
            }
            
            $alert = new Wpjb_Model_Alert;
            $alert->user_id = wpjb_get_current_user_id();
            $alert->keyword = $criteria["keyword"];
            $alert->email = $r->getParam("email");
            $alert->created_at = date("Y-m-d H:i:s");
            $alert->last_run = "0000-00-00 00:00:00";
            $alert->frequency = $r->getParam("frequency");
            $alert->params = serialize($criteria);
            $alert->save();
            
            $response->result = 1;
            $response->msg = __("Alert was saved successfully.", "wpjobboard");
        } catch(Exception $e) {
            $response->result = 0;
            $response->msg = __("There was an error while saving alert.", "wpjobboard");
        }
        
        echo json_encode($response);
        die;
    }
    
    public static function attachmentsAction() {
        wp_ajax_query_attachments();
    }
    
    public static function userOwnsFile($key, $id)
    {
        $arr = array(
            "job" => "Wpjb_Model_Job",
            "resume" => "Wpjb_Model_Resume",
            "company" => "Wpjb_Model_Company",
            "application" => "Wpjb_Model_Application"
        );
        
        if(!isset($arr[$key])) {
            return false;
        }
        
        $class = $arr[$key];
        $object = new $class($id);
        
        if($key == "job" && $object->employer_id == Wpjb_Model_Company::current()->id) {
            return true;
        } elseif($object->user_id == wpjb_get_current_user_id()) {
            return true;
        }
        
        return false;
    }

}

?>
