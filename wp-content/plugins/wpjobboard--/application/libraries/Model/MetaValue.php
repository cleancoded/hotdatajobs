<?php

/**
 * Description of Meta Value
 *
 * @author Grzegorz Winiarski
 * @package WPJB.Model
 */

class Wpjb_Model_MetaValue extends Daq_Db_OrmAbstract
{
    protected $_name = "wpjb_meta_value";

    protected function _init()
    {
    }
    
    public static function import($meta_object, $meta_name, $meta_value, $object_id)
    {
        $q = Daq_Db_Query::create();
        $meta_id = $q->select()->from("Wpjb_Model_Meta t")
                               ->where("t.name = ?", $meta_name)
                               ->where("t.meta_object = ?", $meta_object)
                               ->fetchColumn(); 
        
        if(!isset($meta_id) || empty($meta_id) || !is_numeric($meta_id) ) {
            return null; 
        }
        
        $q = Daq_Db_Query::create();
        $mv_id = $q->select()->from("Wpjb_Model_MetaValue t")
                             ->where("t.meta_id = ?", $meta_id)
                             ->where("t.object_id = ?", $object_id)
                             ->fetchColumn();
        
        $mv = new Wpjb_Model_MetaValue($mv_id);
        $mv->meta_id = $meta_id;
        $mv->object_id = $object_id;
        if( is_array( $meta_value ) || is_object( $meta_value ) ) {
            $mv->value = json_encode( $meta_value );
        } else {
            $mv->value = $meta_value;
        }
        $mv->save();
        
        return $mv->id;
    }
    
    public static function getSingle($meta_object, $meta_name, $object_id, $is_single = false) {
        $q = Daq_Db_Query::create();
        $meta_id = $q->select()->from("Wpjb_Model_Meta t")
                               ->where("t.name = ?", $meta_name)
                               ->where("t.meta_object = ?", $meta_object)
                               ->fetchColumn(); 
        
        if(!isset($meta_id) || empty($meta_id) || !is_numeric($meta_id) ) {
            return null; 
        }
        
        $q = Daq_Db_Query::create();
        $mv_id = $q->select()->from("Wpjb_Model_MetaValue t")
                             ->where("t.meta_id = ?", $meta_id)
                             ->where("t.object_id = ?", $object_id)
                             ->fetchColumn();
        
        if($is_single) {
            $tmp = new Wpjb_Model_MetaValue($mv_id);
            return $tmp->value;
        } else {
            return new Wpjb_Model_MetaValue($mv_id);
        }
    }
}

?>