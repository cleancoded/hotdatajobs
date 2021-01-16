<?php

/**
 * Description of Tagged
 *
 * @author Grzegorz Winiarski
 * @package WPJB.Model
 */

class Wpjb_Model_Tagged extends Daq_Db_OrmAbstract
{
    const TYPE_JOB = "job";
    const TYPE_RESUME = "resume";
    
    protected $_name = "wpjb_tagged";

    protected function _init()
    {
        $this->_reference["tag"] = array(
            "localId" => "tag_id",
            "foreign" => "Wpjb_Model_Tag",
            "foreignId" => "id",
            "type" => "ONE_TO_ONE"
        );
    }
    
    public function save() 
    {
        parent::save();
        
        Wpjb_Project::scheduleEvent();
    }
    
    public function delete()
    {
        parent::delete();
        
        Wpjb_Project::scheduleEvent();
    }
    
    public static function import($tag)
    {
        if($tag->id) {
            $tid = (int)$tag->id;
        } else {
            $tid = self::_resolve($tag);
        }

        if(self::_exists($tid, $tag)) {
            return;
        }
        
        $tagged = new Wpjb_Model_Tagged;
        $tagged->tag_id = $tid;
        $tagged->object = $tag->object;
        $tagged->object_id = $tag->object_id;
        $tagged->save();
    }
    
    protected static function _exists($tid, $tag)
    {
        $query = new Daq_Db_Query;
        $query->from("Wpjb_Model_Tagged t");
        $query->where("tag_id = ?", $tid);
        $query->where("object = ?", $tag->object);
        $query->where("object_id = ?", $tag->object_id);
        $query->limit(1);
        
        if($query->fetch() === null) {
            return false;
        } else {
            return true;
        }
    }
    
    protected static function _resolve($tag) 
    {
        $query = new Daq_Db_Query();
        $query->select();
        $query->from("Wpjb_Model_Tag t");
        $query->where("type = ?", $tag->type);
        $query->where("slug = ?", $tag->slug);
        $query->limit(1);
        
        $result = $query->execute();
        
        if(empty($result)) {
            $t = new Wpjb_Model_Tag;
            $t->type = $tag->type;
            $t->slug = $tag->slug;
            $t->title = $tag->title;
            
            if(isset($tag->order)) {
                $t->order = $tag->order;
            }
            if(isset($tag->parent_id)) {
                $t->parent_id = $tag->parent_id;
            }
            
            $t->save();
        } else {
            $t = $result[0];
        }
        
        return $t->id;
    }
}

?>