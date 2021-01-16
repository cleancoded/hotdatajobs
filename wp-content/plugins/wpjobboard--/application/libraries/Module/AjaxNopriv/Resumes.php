<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Resumes
 *
 * @author greg
 */
class Wpjb_Module_AjaxNopriv_Resumes
{
    protected static function _push($object)
    {
        header("Content-type: application/json; charset=utf-8");
        $object->request = $_REQUEST;
        echo json_encode($object);
        die(PHP_EOL);
    }
    
    protected static function _modify(Wpjb_Model_Resume $resume)
    {
        $cb = self::_canBrowse($resume->id);
        $arr = $resume->toArray();
        
        $public = array("id", "user_id", "category_id", "title", "firstname", 
            "lastname", "headline", "experience", "education", "country", 
            "address", "email", "phone", "website", "is_active", 
            "degree", "years_experience", "created_at" 
        );

        $publish = new stdClass;
        foreach($public as $k) {
            $publish->$k = $resume->$k; 
        }
        
        foreach($arr as $k => $a) {
            if(substr($k, 0, 6) == "field_") {
                $publish->$k = $a;
            }
        }
        
        if(!$cb) {
            $private = array("address", "email", "phone", "website");
            foreach($private as $p) {
                $publish->$p = null;
            }
        }
        
        $t = strtotime($resume->updated_at);
        if($t <= strtotime("1970-01-01 00:00:00")) {
            $t = __("never", "wpjobboard");
        } else {
            $t = date("M, d", $t);
        }
        
        $publish->url = wpjr_link_to("resume", $resume);
        $publish->image = $resume->getAvatarUrl();
        $publish->can_browse = $cb;
        $publish->formatted_last_update = $t;
        
        return $publish;
    }
    
    protected static function _canBrowse($id)
    {
        $access = wpjb_conf("cv_access");
        $hasPriv = false;
        $company = Wpjb_Model_Company::current();

        if($access == 1) {
            $hasPriv = true;
        } elseif($access == 2) {
            // registered members
            if(get_current_user_id()>0) {
                $hasPriv = true;
            }
        } elseif($access == 3) {
            // employers
            if(current_user_can("manage_resumes")) {
                $hasPriv = true;
            }
        } elseif($access == 4) {
            // employers verified
            if(current_user_can("manage_resumes") && $company && $company->is_verified == 1) {
                $hasPriv = true;
            }
        } elseif($access == 5) {
            // premium
            $hasPriv = self::_hasPremiumAccess($id);
        } elseif($access == 6) {
            // Admin Only
            if( array_intersect( array( 'administrator' ), wp_get_current_user()->roles ) ) {
                $hasPriv = true;
            }
        }
        
        return $hasPriv;
    }
    
    protected static function _hasPremiumAccess($id)
    {
        $hash = Daq_Request::getInstance()->get("hash");
        
        if(get_current_user_id() > 0) {
            $query = new Daq_Db_Query();
            $query->from("Wpjb_Model_Payment t");
            $query->where("object_type = ?", Wpjb_Model_Payment::FOR_RESUMES);
            $query->where("object_id = ?", $id);
            $query->where("user_id = ?", get_current_user_id());
            $query->where("is_valid = 1");
            $query->limit(1);
            
            $result = $query->execute();
            
            if(!empty($result)) {
                return true;
            }
        } elseif($hash) {
            
            $query = new Daq_Db_Query();
            $query->from("Wpjb_Model_Payment t");
            $query->where("MD5(CONCAT_WS('|', t.id, t.object_id, t.object_type, t.paid_at)) = ?", $hash);
            $query->where("is_valid = 1");
            $query->limit(1);
            
            $result = $query->execute();
            
            if(!empty($result)) {
                return true;
            }
        }
        
        return false;
    }
    
    public function searchAction()
    {
        $request = Daq_Request::getInstance();
        
        $param = array(
            "query" => $request->post("query"),
            "category" => $request->post("category"),
            "page" => $request->post("page", 1),
            "count" => $request->post("count", wpjb_conf("front_jobs_per_page", 20)),
            "country" => $request->post("country"),
            "location" => $request->post("location"),
            "field" => $request->post("field", array()),
            "sort" => $request->post("sort"),
            "order" => $request->post("order"),
        );
        
        $result = Wpjb_Model_ResumeSearch::search($param);
        $shortcode = new Wpjb_Shortcode_Dynamic();
        $list = $result->resume;
        $result->resume = array();
        $result->html = "";
        foreach($list as $resume) {
            
            $result->resume[] = self::_modify($resume);
            
            $shortcode->view->resume = $resume;
            $result->html .= $shortcode->render("resumes", "index-item");
        }

        self::_push($result);
    }
    
    public function detailsAction()
    {
        $cb = self::_canBrowse();
        
        $id = Daq_Request::getInstance()->post("id");
        $resume = new Wpjb_Model_Resume($id);
        
        $publish = self::_modify($resume);
        self::_push($publish);
    }
}

?>
