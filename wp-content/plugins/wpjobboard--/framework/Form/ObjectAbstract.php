<?php
/**
 * Description of ObjectAbstract
 *
 * @author greg
 * @package 
 */

abstract class Daq_Form_ObjectAbstract extends Daq_Form_Abstract
{
    protected $_model = null;

    protected $_object = null;
    
    protected $_tags = array();

    public function __construct($id = null, $options = array())
    {
        if($this->_model === null) {
            throw new Exception('$this->_model is null');
        }
        $model = $this->_model;
        
        $this->_object = new $model($id);
        parent::__construct($options);
    }
    
    public function addTag($tag)
    {
        if($tag instanceof Daq_Form_Element) {
            $tag = $tag->getName();
        }
        
        $this->_tags[] = $tag;
    }
    
    public function save($append = array())
    {
        $varList = $this->getValues();
        foreach($this->_object->getFieldNames() as $f) {
            if(isset($varList[$f])) {
                $v = (array)$varList[$f];
                $this->_object->$f = $v[0];
            } elseif(isset($append[$f])) {
                $this->_object->$f = $append[$f];
            } elseif(array_key_exists($f, $varList)) {
                $this->_object->$f = 0;
            }
        }
        
        $oid = $this->_object->save();

        foreach($varList as $k => $val) {
            $f = $this->getElement($k);
            
            if($f->isBuiltin() || !$this->getObject()->meta->$k) {
                continue;
            }
            
            $meta = $this->getObject()->meta->$k;
            $metaId = $meta->id;
            
            if($meta->conf("type") == "ui-input-file") {
                // do not save meta for files
                continue;
            }
            
            $valNew = (array)$val;
            $valOld = $meta->getValues();
            
            if( is_array( $valOld ) || is_object( $valOld ) ) {
                $countC = count($meta->getValues());
            } else {
                $countC = 0;
            }
            
            if( is_array( $val) ) {
                $countN = count($val);
            } else {
                $countN = 1;
            }
            $max = max(array($countC,$countN));
            
            for($i=0; $i<$max; $i++) {
                if(isset($valNew[$i]) && isset($valOld[$i])) {
                    $valOld[$i]->object_id = $oid;
                    $valOld[$i]->value = $valNew[$i];
                    $valOld[$i]->save();
                } elseif(!isset($valNew[$i]) && isset($valOld[$i])) {
                    $valOld[$i]->delete();
                } elseif(isset($valNew[$i]) && !isset($valOld[$i])) {
                    $model = new Wpjb_Model_MetaValue;
                    $model->meta_id = $metaId;
                    $model->object_id = $oid;
                    $model->value = $valNew[$i];
                    $model->save();
                }
            }

        }
        
        foreach($this->_tags as $k) {
            
            if(!$this->hasElement($k)) {
                continue;
            }
            
            $f = $this->getElement($k);
            
            // $update: list of wpjb_tag.id
            $update = (array)$f->getValue();
            
            // $current: list of object wpjb_tag.id
            $current = $this->getObject()->getTagIds($k);
            
            $new = array_diff($update, $current);
            $delete = array_diff($current, $update);

            foreach($new as $id) {
                $tagged = new Wpjb_Model_Tagged;
                $tagged->tag_id = $id;
                $tagged->object = $this->getObject()->getTagName();
                $tagged->object_id = $oid;
                $tagged->save();
                
            }
            
            foreach($delete as $id) {
                foreach($this->getObject()->getTag()->$k as $tag) {
                    if($tag->id == $id) {
                        $tag->getTagged()->delete();
                    }
                }
            }
            
        }

    }
    
    public function loadMeta($key)
    {
        parent::loadMeta($key);
        
        if($this->isNew()) {
            return;
        }
        
        foreach((array)$this->getObject()->meta as $k => $meta) {
            if($this->hasElement($k) && $meta->meta_type == 3) {
                if($this->getElement($k) instanceof Daq_Form_Element_Multi) {
                    $this->getElement($k)->setValue($meta->values());
                } elseif($this->getElement($k)->getType() != "file") {
                    $this->getElement($k)->setValue($meta->value());
                }
            }
        }
    }

    public function isNew() 
    {
        if($this->getObject()->getId()>0) {
            return false;
        } else {
            return true;
        }
    }
    
    public function ifNew($then, $else)
    {
        if($this->isNew()) {
            return $then;
        } else {
            return $else;
        }
    }
    
    public function reinit()
    {
        throw new Exception("No longer required!");
    }

    /**
     *
     * @return Daq_Db_OrmAbstract
     */
    public function getObject()
    {
        return $this->_object;
    }
    
    public function setObject(Daq_Db_OrmAbstract $object)
    {
        $this->_object = $object;
    }
    
    public function getId()
    {
        return $this->getObject()->id;
    }
    
    public function upload($dir)
    {
        foreach($this->getFields() as $field) {
            if($field->getType() == "file" && $field->fileSent()) {
                /* @var $field Daq_Form_Element_File */
                
                $path = $field->getUploadPath();
                
                $find = array("{object}", "{field}", "{id}");
                $repl = array($path["object"], $path["field"], $this->getId());
                $dir = str_replace($find, $repl, $dir);
                
                wp_mkdir_p($dir);

                $field->setDestination($dir);
                $field->upload();
            }
        }
    }
    
    public function moveTransients()
    {
        foreach($this->getFields() as $field) {
            
            if($field->getType() != "file") {
                continue;
            }
            $l = new Wpjb_Utility_Link(array(
                "object" => get_class($this->getObject()),
                "field" => $field->getName(),
                "id" => null
            ));
            $l->move($this->getObject()->id);
        }
    }
}

