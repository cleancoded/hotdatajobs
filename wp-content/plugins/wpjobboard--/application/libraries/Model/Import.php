<?php
/**
 * Description of Alert
 *
 * @author greg
 * @package 
 */

class Wpjb_Model_Import extends Daq_Db_OrmAbstract
{
    protected $_name = "wpjb_import";

    protected function _init()
    {
        
    }
    
    public function save()
    {
        parent::save();
       
        if(!wp_next_scheduled("wpjb_event_import")) {
            wp_schedule_event(current_time('timestamp'), 'hourly', 'wpjb_event_import');
        }
        
    }
    
    public function delete() 
    {
        $result = parent::delete();
        
        $query = new Daq_Db_Query();
        $query->select("COUNT(*) AS cnt");
        $query->from("Wpjb_Model_Import t");
        $c = $query->fetchColumn();
        
        if(!$c && wp_next_scheduled("wpjb_event_import")) {
            wp_clear_scheduled_hook("wpjb_event_import");
        }
        
        return $result;
    }


    protected function _resolve($tag) 
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
            $t->save();
        } else {
            $t = $result[0];
        }
        
        return $t->id;
    }
    
    public function run()
    {
        $engines = apply_filters("wpjb_import_engines", array(
            "indeed" => "Wpjb_Service_Indeed",
            "careerbuilder" => "Wpjb_Service_CareerBuilder"
        ));
        
        if(array_key_exists($this->engine, $engines)) {
            $teng = $engines[$this->engine];
            $engine = new $teng;
        } else {
            throw new Exception("Engine not found.");
        }
        
        $result = $engine->find($this->toArray());
        foreach($result->item as $item) {
            
            $query = new Daq_Db_Query();
            $query->select();
            $query->from("Wpjb_Model_MetaValue t");
            $query->where("value = ?", $this->engine."-".$item->external_id);
            $query->limit(1);
            
            $r = $query->execute();
            
            if($r) {
                continue;
            }
            
            Wpjb_Model_Job::import($engine->prepare($item, $this));
            
        }
    }
}

?>