<?php
/**
 * Description of OrmAbstract
 *
 * @author greg
 * @package 
 */


abstract class Daq_Db_OrmAbstract
{
    protected static $_preload = array();
    
    protected $_reference = array();

    protected $_field = array();

    protected $_trackChanges = true;

    protected $_primary = null;

    protected $_loaded = array();
    
    protected $_meta = null;
    
    protected $_metaName = null;
    
    protected $_metaTable = null;
    
    protected $_tag = null;
    
    protected $_tagTable = null;
    
    protected $_tagName = null;

    protected $_name = null;
    
    protected $_directory = null;
	
    protected $_exists = null;

    public $helper = array();

    protected abstract function _init();

    public function __construct($id = null, $prefix = true)
    {
        $this->_init();
        
        if($prefix) {
            $pFix = Daq_Db::getInstance()->getDb()->prefix;
            $this->_name = $pFix . $this->_name;
        }

        foreach(Daq_Db::getInstance()->describeTable($this->_name) as $row) {
            
            if($row->Null == "NO") {
                $nullable = false;
            } else {
                $nullable = true;
            }
            
            preg_match("/[a-z]+/", $row->Type, $match);
            $type = $match[0];

            if(in_array($type, array("int", "smallint", "tinyint"))) {
                $type = "int";
            } elseif(in_array($type, array("varchar", "char", "text"))) {
                $type = "text";
            } elseif(in_array($type, array("date", "datetime"))) {
                $type = "date";
            } else {
                $type = "unknown";
            }
            
            $this->_field[$row->Field] = array(
                "nullable" => $nullable,
                "type" => $type,
                "value" => null
            );
            if($row->Key == "PRI") {
                $this->_primary = $row->Field;
            }
        }
		
        $this->_exists = false;

        if($id !== null) {
            $this->_load($id);
        }
        $this->_postInit();

        if(!$this->_name) {
            throw new Exception('$_name field is missing');
        }
    }

    protected function _load($id)
    {
        $wp = Daq_Db::getInstance()->getDb();
        $q = "SELECT * FROM ".$this->tableName()." WHERE ".$this->_primary."=".(int)$id;
        $result = $wp->get_row($q, ARRAY_A);
		
        if($result === null) {
            $this->doExists(false);
            $this->fromArray(array($this->_primary => $id));
        } else {
            $this->doExists(true);
            $this->fromArray($result);
        }
    }

    protected function _postInit()
    {
        foreach($this->_field as $key => $arr) {
            $arr['modified'] = false;
            $this->_field[$key] = $arr;
        }
    }
    
    public function doExists($status)
    {
        $this->_exists = (bool)$status;
    }
	
    public function exists() 
    {
        return $this->_exists;
    }

    public function fromArray(array $arr)
    {
        $this->_trackChanges = false;
        foreach($arr as $key => $value) {
            $this->set($key, $value);
        }
        $this->_trackChanges = true;
    }

    public function tableName()
    {
        return $this->_name;
    }

    public function getReference($key)
    {
        if(!isset($this->_reference[$key])) {
            throw new Exception("Unknown reference: $key");
        }

        return $this->_reference[$key];
    }

    public function getReferences()
    {
        return $this->_reference;
    }

    public function getFields()
    {
        return $this->_field;
    }

    public function getFieldNames()
    {
        return array_keys($this->_field);
    }

    public function toArray()
    {
        $arr = array();
        foreach($this->_field as $k => $f) {
            $arr[$k] = $f['value'];
        }
        
        $arr["meta"] = array();
        
        foreach($this->meta() as $k => $v) {
            $arr["meta"][$k] = array(
                "name" => $v->name,
                "title" => $v->conf("title"),
                "value" => join(", ", (array)$v->values()),
                "values" => (array)$v->values(),
            );
        }
        
        return $arr;
    }

    public function set($key, $value)
    {
        if(!isset($this->_field[$key])) {
            throw new Exception("Key [$key] does not exist.");
        }
        
        $this->_field[$key]['value'] = $value;

        if($this->_trackChanges) {
            $this->_field[$key]['modified'] = true;
        }
    }

    public function __set($key, $value)
    {
        if($key == "meta" && $this->_metaName) {
            return $this->_meta;
        }
        
        $this->set($key, $value);
    }

    public function get($key)
    {
        if(isset($this->_field[$key]['value'])) {
            return $this->_field[$key]['value'];
        } else {
            return null;
        }
    }

    public function __get($key)
    {
        if($key == "meta" && $this->_metaName) {
            return $this->meta();
        } elseif($key == "tag" && $this->_tagName) {
            return $this->getTag();
        } elseif($key == "time") {
            return $this->time($this);
        }
        
        return $this->get($key);
    }

    public function getId()
    {
        if(isset($this->_field[$this->_primary]['value'])) {
            return (int)$this->_field[$this->_primary]['value'];
        }
        return 0;
    }

    public function save()
    {
        $table = $this->tableName();
        $wp = Daq_Db::getInstance()->getDb();
        $toSave = array();
        foreach($this->_field as $key => $value) {

            switch($value["type"]) {
                case "int" : $value["value"] = intval($value["value"]); break;
                case "text": $value["value"] = strval($value["value"]); break;
            }

            if($value["nullable"] && $value["value"] == "0") {
                $pk = $this->_primary;
                $pkv = $this->getId();
                $wp->query("UPDATE $table SET $key = NULL WHERE $pk = $pkv");
                continue;
            }
            
            if(empty($value["value"]) && $key==$this->_primary) {
                continue;
            }
            
            if($value['modified']) {
                $toSave[$key] = $value['value'];
            }
        }

        if(empty($toSave)) {
            return $this->getId();
        }

        if($this->exists()) {
            /* @var $wp wpdb */
            $wp->update($table, $toSave, array($this->_primary => $this->getId()));
            $id = $this->getId();
        } else {
            if($this->getId() > 0) {
                $toSave[$this->_primary] = $this->getId();
            }
            $wp->insert($table, $toSave);
            $this->set($this->_primary, $wp->insert_id);
            $id = $wp->insert_id;
        }
	
        $this->_exists = true;
        
        if($this->_metaName) {
            foreach($this->meta as $meta) {
                /* @var $meta Wpjb_Model_Meta */
                if(!$meta->hasOwnerId()) {
                    $meta->setOwnerId($id);
                }
            }
        }

        return $id;

    }

    public function delete()
    {
        if($this->_metaName) {
            foreach($this->meta() as $k => $meta) {
                foreach($meta->getValues() as $mv) {
                    $mv->delete();
                }
            }
        }

        Daq_Db::getInstance()->delete($this->tableName(), "id=".$this->getId());

        if(Daq_Db::getInstance()->getDb()->last_error) {
            throw new Daq_Db_Exception(Daq_Db::getInstance()->getDb()->last_error);
        }
		
	$this->_exists = false;
		
        return true;
    }

    public function addRef($object) 
    {
        $class = get_class($object);
        foreach($this->_reference as $key => $value) {
            if($value['foreign'] == $class) {
                $c1 = $value['localId'];
                $c2 = $value['foreignId'];
                if($object->getId() < 1) {
                    $object->set($c2, $this->get($c1));
                    $this->_loaded[$class] = $object;
                    return;
                }
                if($this->get($c1, -1) == $object->get($c2, -2)) {
                    $this->_loaded[$class] = $object;
                    return;
                }
            }
        }

        throw new Exception("Reference to $class does not exist.");
    }

    public function fakeLoad(Daq_Db_OrmAbstract $object) 
    {
        $class = get_class($object);
        $this->_loaded[$class] = $object;
    }
    
    public function __call($method, $param = array())
    {
        $call = str_replace("get", "", strtolower($method));
        foreach($this->_reference as $key => $value) {
            if($key == $call) {
                if(!isset($this->_loaded[$value['foreign']])) {
                    if(isset($param[0]) && $param[0] == true) {
                        $class = $value['foreign'];
                        $local = $value['localId'];
                        
                        $query = new Daq_Db_Query();
                        $query->select()
                            ->from($class." t")
                            ->where($value['foreignId']." = ?", $this->$local);

                        if(isset($value['with'])) {
                            $query->where($value['with']);
                        }

                        $result = $query->execute();
                        $this->_loaded[$value['foreign']] = new $class;
                        if(!empty($result)) {
                            $this->_loaded[$value['foreign']] = $result[0];
                        }
                    } else {
                        throw new Exception("Object {$value['foreign']} not loaded for class ".__CLASS__);
                    }
                }
                return $this->_loaded[$value['foreign']];
            }
        }
        throw new Exception("Method $method does not exist for class ".__CLASS__);
    }
    
    /**
     * 
     * 
     * Params:
     * - meta_type
     * - field_exclude
     * - visibility: null
     * - empty: true
     * - reload: false
     * 
     * @param type $param
     */
    public function getMeta($param = array())
    {
        $this->meta();
        $list = array();
        
        $p = array();
        $defaults = array(
            "meta_type" => array(),
            "field_type" => array(),
            "field_type_exclude" => array(),
            "visibility" => null,
            "empty" => true
        );
        
        foreach($defaults as $k => $def) {
            if(isset($param[$k])) {
                $p[$k] = $param[$k];
            } else {
                $p[$k] = $def;
            }
        }

        foreach($this->_meta as $key => $meta) {
            /* @var $meta Wpjb_Model_Meta */

            if(!is_null($p["visibility"]) && !in_array($meta->conf("visibility"), (array)$p["visibility"])) {
                continue;
            }

            if($p["meta_type"] && $meta->meta_type!=3) {
                continue;
            }
            
            if(!empty($p["field_type"]) && !in_array($meta->conf("type"), (array)$p["field_type"])) {
                continue;
            }
            
            if(!empty($p["field_type_exclude"]) && in_array($meta->conf("type"), (array)$p["field_type_exclude"])) {
                continue;
            }
            
            if(!$p["empty"] && $meta->conf("type")=="ui-input-file" && empty($this->file->{$key})) {
                continue;
            }
            
            if(!$p["empty"] && $meta->conf("type")!="ui-input-file" && strlen(join("", (array)$meta->values()))==0) {
                continue;
            }
            
            
            $list[$key] = $meta;
        }
        
        uasort($list, array($this, "metaSort"));
        
        $olist = new stdClass();
        foreach($list as $k => $l) {
            $olist->$k = $l;
        }
        
        return $olist;
    }
    
    public function metaSort($a, $b) 
    {
        // @var $a Wpjb_Model_Meta
        $ac = $a->getConfig();
        $ao = $ac["order"];
        
        $bc = $b->getConfig();
        $bo = $bc["order"];
        
        return $ao > $bo;
    }
    
    public function meta($reload = false)
    {
        if($reload) {
            $this->_meta = null;
        }
	
        if($this->_meta !== null) {
            return $this->_meta;
        }
        
        $select = new Daq_Db_Query();
        $select->select("*");
        $select->from("Wpjb_Model_Meta t1");
        $select->where("meta_object = ?", $this->_metaName);
        
        if($this->id > 0) {
            $quote = $select->quoteInto("object_id = ?", $this->id);
            $select->joinLeft("t1.value t2", $quote);
        }
        
        $list = $select->execute();
        $this->_meta = new stdClass();
        foreach($list as $item) {
            
            $key = $item->name;
            $value = null;
            
            if($this->id>0) {
                $value = $item->getValue();
            } 
            
            $item->free();
            $item->setOwnerId($this->getId());
            
            if($key && !isset($this->_meta->$key)) {
                $this->_meta->$key = $item;
            }
            if($this->id>0 && $value && $value->id>0) {
                $this->_meta->$key->addValue($value);
            }
        }
        
        return $this->_meta;
    }
    
    public function addTag(Wpjb_Model_Tag $tag)
    {
        if($this->_tag === null) {
            $this->_tag = new stdClass();
        }
        
        $k = $tag->type;
        
        if(!isset($this->_tag->{$k})) {
            $this->_tag->{$k} = array();
        }
        
        $this->_tag->{$k}[] = $tag;
    }
    
    public function tag($reload = false)
    {
        if($reload) {
            $this->_tag = null;
        }
        
        if($this->_tag !== null) {
            return $this->_tag;
        }
        
        if(!isset(self::$_preload["tags"])) {
            $query = new Daq_Db_Query();
            $query->from("{$this->_tagTable['scheme']} t");
            $result = $query->execute();
            self::$_preload["tags"] = array();
            foreach($result as $tag) {
                self::$_preload["tags"][$tag->getId()] = $tag;
            }
        }
        
        $this->_tag = new stdClass();
        
        $query = new Daq_Db_Query();
        $query->select();
        $query->from("{$this->_tagTable['values']} t1");
        $query->where("t1.object = ?", $this->_tagName);
        $query->where("t1.object_id = ?", $this->id);

        $result = $query->execute();
        
        foreach($result as $tagged) {
            if(!isset(self::$_preload["tags"][$tagged->tag_id])) {
                continue;
            }
            $tag = clone self::$_preload["tags"][$tagged->tag_id];
            $tag->fakeLoad($tagged);
            $o = $tag->type;
            if(!isset($this->_tag->$o)) {
                $this->_tag->$o = array();
            }
            
            $this->_tag->{$o}[] = $tag;
        }
        
        return $this->_tag;
    }
    
    /**
     * 
     * @deprecated since version 4.1.2
     * @return array
     */
    public function _tag() 
    {
        return $this->tag();
    }
    
    public function getTag()
    {
        return $this->tag();
    }
    
    public function getTagIds($name)
    {
        $ids = array();
        $this->tag();
        
        if(!isset($this->_tag->$name)) {
            return $ids;
        }
        
        foreach($this->_tag->$name as $tag) {
            $ids[] = $tag->id;
        }
        
        return $ids;
    }
    
    public function getTagName()
    {
        return $this->_tagName;
    }
    
    public function time() 
    {
        $obj = new stdClass();
        foreach($this->_field as $k => $v) {
            if($v["type"] == "date") {
                $date = new DateTime($v["value"]);
                $obj->$k = $date->format("U");
            }
        }
        
        return $obj;
    }
    
    protected function _getFileUploads($key) 
    {
        $dir = wpjb_upload_dir($key, "", $this->id, "basedir");
        $files = new stdClass();

        foreach(wpjb_glob($dir."/*") as $path) {
            $basename = basename($path);
            $objectname = str_replace("-", "_", $basename);
            $files->$objectname = array();
            foreach(wpjb_glob($dir."/".$basename."/*") as $file) {
                $obj = new stdClass();
                $obj->basename = basename($file);
                $obj->path = $file;
                $obj->url = wpjb_upload_dir($key, $basename, $this->id, "baseurl")."/".$obj->basename;
                $obj->size = filesize($obj->path);
                $files->{$objectname}[] = $obj;
            }
        }
        
        return $files;
    }
    
    protected function _getFileLinks($files = null)
    {
        if($files === null) {
            $files = new stdClass();
        }
        
        foreach($this->meta as $mk => $meta) {
            if($meta->conf("type") != "ui-input-file") {
                continue;
            }

            $link = new Wpjb_Utility_Link(array(
                "object" => get_class($this),
                "field" => $mk,
                "id" => $this->id
            ));

            if($this->exists()) {
                $links = $link->getMetas();
            } else {
                $links = $link->getTransients();
            }

            if(!is_array($links)) {
                $links = array();
            }
            
            foreach($links as $link) {

                if(!isset($files->$mk)) {
                    $files->$mk = array();
                }

                $file = wp_prepare_attachment_for_js($link["id"]);

                $obj = new stdClass();
                $obj->basename = $file["filename"];
                $obj->path = get_attached_file( $link["id"] );
                $obj->url = $link["url"];
                $obj->size = $file["filesizeInBytes"];
                $files->{$mk}[] = $obj;
            }

        }
        
        return $files;
    }

}


?>