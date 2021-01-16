<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CustomFields
 *
 * @author greg
 */
class Wpjb_Module_Ajax_CustomFields 
{
    protected static function _stdToArray($object)
    {
        if (is_object($object)) {
            // Gets the properties of the given object
            // with get_object_vars function
            $object = get_object_vars($object);
        }

        if (is_array($object)) {
            /*
            * Return array converted to object
            * Using __FUNCTION__ (Magic constant)
            * for recursive call
            */
            return array_map(array(__CLASS__, __METHOD__), $object);
        } else {
            // Return array
            return $object;
        }
	
    }
    
    public function saveAction()
    {
        $request = Daq_Request::getInstance();
        
        $form = $request->post("form");
        $formName = $request->post("form_name");
        
        if($request->post("is_string") == 1 && !is_array($form)) {
            $form = self::_stdToArray(json_decode($form));
        }

        $fields = $form["field"];
        
        foreach($fields as $key => $field) {
            
            $select = Daq_Db_Query::create();
            $select->from("Wpjb_Model_Meta t");
            $select->where("meta_object = ?", $formName);
            $select->where("name = ?", $field["name"]);
            $select->limit(1);
            $result = $select->execute();

            if($field["is_builtin"]) {
                continue;
            }

            if(!empty($result)) {
                $meta = $result[0];
            } else {
                $meta = new Wpjb_Model_Meta;
                $meta->meta_object = $formName;
                $meta->name = $field["name"];
                $meta->meta_type = 3;
            }

            $meta->meta_value = serialize($field);

            if(isset($field["delete_forever"])) {
                $id = $meta->id;
                
                if($meta->conf("type") == "ui-input-file") {
                    $upload = wpjb_upload_dir($meta->meta_object, "", null, "basedir");
                    $upload = dirname($upload)."/*/".$meta->name;
                    Wpjb_Utility_Log::debug($upload);
                    foreach((array)glob($upload) as $rdir) {
                        wpjb_recursive_delete($rdir);
                    }
                    
                } else {
                    $query = Daq_Db_Query::create();
                    $query->from("Wpjb_Model_MetaValue t");
                    $query->where("meta_id = ?", $id);
                    $list = $query->execute();
                    foreach($list as $mv) {
                        $mv->delete();
                    }
                }
                
                if($formName == "job") {
                    $query = Daq_Db_Query::create();
                    $query->from("Wpjb_Model_Meta t");
                    $query->where("meta_object = ?", "job_search");
                    $query->where("name = ?", $field["name"]);
                    $list = $query->execute();
                    
                    foreach($list as $m) {
                        $m->delete();
                        $o = get_option("wpjb_form_job_search");
                        unset($o["field"][$key]);
                        update_option("wpjb_form_job_search", $o);
                    }
                }
                
                unset($form["field"][$key]);
                $meta->delete();
                
            } else {
                $meta->save();
            } 
        }
        
        update_option("wpjb_form_".$formName, $form);

        echo json_encode(array("result"=>1));
        
        die;
    }
}

?>
