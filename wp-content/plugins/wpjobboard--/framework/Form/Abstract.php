<?php
/**
 * Description of Element
 *
 * @author greg
 * @package
 */

abstract class Daq_Form_Abstract
{
    private $_default = "_default";
    
    protected $_options = array();
    
    protected $_field = array();

    protected $_errors = array();

    protected $_renderer = null;

    protected $_group = array();
    
    protected $_css = array();
    
    protected $_custom = null;
    
    protected $_key = null;
    
    protected $_overload = null;
    
    protected $_upload = null;
    
    private $_order = 1;

    /**
     * Form constructor
     * 
     * Allowed params:
     * - renderer: default: Daq_Form_AdminRenderer
     * - display_trashed: default=false
     *
     * @param Array $options 
     */
    public function __construct($options = array())
    {
        $defaults = array(
            "renderer" => new Daq_Form_AdminRenderer,
            "display_trashed" => false,
        );
        
        $this->_options = array_merge($defaults, (array)$options);
        $this->_renderer = $this->_options["renderer"];
        
        if(!empty($this->_custom)) {
            $this->_overload = new Daq_Form_Overload($this->_custom);
        }
        
        $this->init();
        
        /*
        if(!empty($this->_custom)) {
            $this->loadGroups();
            $this->loadMeta($this->_key);
        }
         * 
         */
    }
    
    public function apply()
    {
        if(!empty($this->_custom)) {
            $this->loadGroups();
            $this->loadMeta($this->_key);
        }
        
        return $this;
    }
    
    public function __get($key) 
    {
        if($key == "fieldset") {
            return $this->_group;
        }
    }

    public function loadGroups()
    {
        foreach((array)$this->_overload->getGroup() as $g) {
            if(!isset($this->fieldset[$g["name"]]) && !$g["is_builtin"]) {
                $this->addGroup($g["name"], $g["title"], $g["order"]);
            }
        }
    }
    
    public function loadMeta($key)
    {
        $list = array(
            "ui-input-label" => "label",
            "ui-input-text" => "text",
            "ui-input-radio" => "radio",
            "ui-input-checkbox" => "checkbox",
            "ui-input-select" => "select",
            "ui-input-file" => "file",
            "ui-input-textarea" => "textarea",
            "ui-input-hidden" => "hidden",
            "ui-input-password" => "password",
        );
        
        $query = Daq_Db_Query::create();
        $query->from("Wpjb_Model_Meta t");
        $query->where("meta_object = ?", $key);
        $query->where("meta_type = 3");
        $row = $query->execute();
        
        foreach($row as $meta) {
            $data = unserialize($meta->meta_value);
            if($this->_upload) {
                $data["upload_path"] = $this->_upload;
            }
            
            $tag = $list[$data["type"]];          
            $e = $this->create($meta->name, $tag);
            $e->overload($data);
            $e->setBuiltin(false);
            $this->addElement($e, $data["group"]);
        }
    }
    
    public function addElement(Daq_Form_Element $field, $group = null)
    {
        if($group === null) {
            $group = $this->_default;
        }
        
        if($field->getOrder()<1) {
            $field->setOrder($this->_order);
            $this->_order++;
        }
        
        $ol = null;
        $visibility = null;
        $trashed = $field->isTrashed();
        if($this->_overload && $this->_overload->hasField($field)) {
            $ol = $this->_overload->getField($field);
            
            if($this->_upload) {
                $ol["upload_path"] = $this->_upload;
            }
            
            $field->overload($ol);
            $group = $ol["group"];
            $trashed = isset($ol["is_trashed"]) && $ol["is_trashed"];
            
            if(isset($ol["visibility"]) && is_numeric($ol["visibility"])) {
                $visibility = $ol["visibility"];
            }
        }
        
        if($trashed && !$this->_options["display_trashed"]) {
            return;
        }
        
        if($visibility == 2 && !is_admin()) {
            return;
        }

        if($trashed) {
            $group = "_trashed";
        } elseif(!isset($this->fieldset[$group]) && !$this->_overload) {
            $this->addGroup($group, "");
        } elseif(!isset($this->fieldset[$group]) && $this->_overload->hasGroup($group)) {
            $g = $this->_overload->getGroup($group);
            $this->addGroup($group, $g["title"], $g["order"]);
            $group = $g["name"];
        } elseif(!isset($this->fieldset[$group])) {
            $group = "_trashed";
            $field->setTrashed(true);
        } 

        if($group == "_trashed" && !isset($this->fieldset["_trashed"])) {
            $this->addGroup("_trashed", "_trashed", 9000);
        }
        
        $this->fieldset[$group]->add($field);
    }

    public function addGroup($key, $title = "", $order = null)
    {   
        if($key == "") {
            echo "<pre>";
            debug_print_backtrace();
            echo "</pre>";
        }
        if(is_null($order)) {
            $order = $this->_order++;
        }
        
        if($this->_overload && $this->_overload->hasGroup($key)) {
            $init = $this->_overload->getGroup($key);
        } else {
            $init = array(
                "name" => $key,
                "title" => $title,
                "order" => $order,
                "is_builtin" => true,
                "is_trashed" => false,
            );
        }
        
        $this->_group[$key] = new Daq_Form_Fieldset($init);
    }

    public function hasElement($name)
    {
        if($this->getElement($name) === null) {
            return false;
        } else {
            return true;
        }
    }

    public function getElement($name)
    {
        foreach($this->fieldset as $k => $v) {
            if($this->fieldset[$k]->has($name)) {
                return $this->fieldset[$k]->get($name);
            }
        }
        
        return null;
    }

    /**
     * Return all form elements
     *
     * @deprecated
     * @return array
     */
    public function getElements()
    {
        throw new Exception("This method is obsolate!");
    }

    public function removeElement($name)
    {
        foreach($this->fieldset as $k => $v) {
            if($this->fieldset[$k]->has($name)) {
                $this->fieldset[$k]->remove($name);
                return true;
            }
        }
        return false;
    }

    /**
     * Returns group object or null
     * 
     * @since 4.4.3
     * @param string $name Group name
     * @return Daq_Form_Fieldset Group object
     */
    public function getGroup($name)
    {
        if(isset($this->_group[$name])) {
            return $this->_group[$name];
        } else {
            return null;
        }
    }
    
    /**
     * Returns all form groups
     *
     * @return array
     */
    public function getGroups()
    {
        return $this->_group;
    }
    
    /**
     * Remove one or more groups
     *
     * @param mixed $group String or array of group names
     */
    public function removeGroup($group)
    {
        $group = (array)$group;
        foreach($group as $g) {
            if(isset($this->_group[$g])) {
                unset($this->_group[$g]);
            }
        }
    }
    
    /**
     * Returns all groups that have at least one element
     * 
     * @return array
     */
    public function getNonEmptyGroups()
    {
        $groups = array();
        foreach($this->fieldset as $g) {
            
            if($g->hasVisibleElements()) {
                $groups[] = $g;
            }
        }

        return $groups;
    }
    
    public function getFields()
    {
        $fields = array();
        foreach($this->fieldset as $group) {
            $fields += $group->getAll();
        }
        return $fields;
    }

    /**
     * Validates the form
     *
     * @param array $values
     * @return boolean
     */
    public function isValid(array $values)
    {
        $isValid = true;

        foreach($this->getFields() as $field)
        {
            
            $value = null;
            if(isset($values[$field->getName()])) {
                $value = $values[$field->getName()];
            } elseif($field->getType() == "checkbox") {
                $value = null;
            }

            if($field->getType() == Daq_Form_Element::TYPE_FILE) {
                if(isset($_FILES[$field->getName()])) {
                    $field->setValue($_FILES[$field->getName()]);
                }
            } else {
                $field->setValue($value);
            }

            if(!$field->validate()) {
                $isValid = false;
                $this->_errors[$field->getName()] = array();
                foreach($field->getErrors() as $error) {
                    $this->_errors[$field->getName()][] = $error;
                }
            }
        }
        
        return $isValid;
    }

    public function getErrors()
    {
        return $this->_errors;
    }

    public function hasErrors()
    {
        return !empty($this->_errors);
    }

    public function getValues()
    {
        $arr = array();
        foreach($this->getFields() as $field) {
            $arr[$field->getName()] = $field->getValue();
        }

        return $arr;
    }

    protected function _sort($a, $b)
    {
        $r1 = $a->getOrder();
        $r2 = $b->getOrder();
        
        if($r1>$r2) {
            return 1;
        } else {
            return -1;
        }
    }
    
    public function getReordered()
    {
        $fieldset = $this->fieldset;
        usort($fieldset, array($this, "_sort"));
        
        $trashed = false;
        if(isset($this->_options["display_trashed"]) && $this->_options["display_trashed"]) {
            $trashed = true;
        }

        $fs = array();
        foreach($fieldset as $f) {
            if(((!$f->isEmpty() || $f->isAlwaysVisible()) || $trashed) && $f->getName()!="_internal") {     
                $fs[$f->getName()] = $f;
            }
        }
        
        return $fs;
    }
    
    public function render($options = array())
    {
        if(isset($options["group"])) {
            $groups = array($this->fieldset[$options["group"]]);
        } else {
            $groups = $this->getReordered();
        }
        
        return $this->renderHidden()."\r\n".daq_form_layout_config($groups);
    }

    public function renderGroup($group)
    {
        if(isset($this->fieldset[$group])) {
            return $this->render($this->fieldset[$group]);
        }

        return null;
    }

    public function renderHidden()
    {
        $html = "";
        foreach($this->fieldset as $fieldset) {
            foreach($fieldset->getAll() as $field) {
                if($field->getType() === Daq_Form_Element::TYPE_HIDDEN) {
                    $html .= $field->render();
                } 
            }
        }
        return $html;
    }

    public function setRenderer($renderer)
    {
        $this->_renderer = $renderer;
    }

    public function getRenderer()
    {
        return $this->_renderer;
    }
    
    public function dump()
    {
        $arr = array();
        foreach($this->getReordered() as $group) {
            /* @var $group Daq_Form_Fieldset */
            
            if($group->getName() == "_internal") {
                continue;
            }

            $std = new stdClass();
            $std->title = $group->title;
            $std->name = $group->getName();
            $std->order = $group->getOrder();
            $std->type = "ui-input-group";
            $std->is_builtin = (int)$group->meta->is_builtin;
            $std->is_trashed = $group->isTrashed();
            $std->field = array();
            
            foreach($group->getReordered() as $field) {
                /* @var $field Daq_Form_Element */
                $std->field[] = $field->dump();
            }
            
            $arr[] = $std;
        }
        
        return $arr;
    }
    
    /**
     * Fail safe method to get field value
     *
     * @param string $element
     * @return mixed String or array depending on the field type
     */
    public function value($element)
    {
        if(!$this->hasElement($element)) {
            return null;
        }
        
        return $this->getElement($element)->getValue();
    }
    
    /**
     *
     * @param string $name
     * @param string $type
     * @return Daq_Form_Element 
     */
    public function create($name, $type = "text")
    {
        
       if(!$type) {
            $type = "text";
        }
        
        $type = str_replace("_", " ", $type);
        $type = ucwords($type);
        $type = str_replace(" ", "_", $type);
        
        $class = "Daq_Form_Element_".$type;
        
        return new $class($name);
    }

    public function getFieldValue($field, $default = null) 
    {
        if($this->hasElement($field)) {
            return $this->getElement($field)->getValue();
        } else {
            return $default;
        }
    }
    
    public function getOptions() {
        return $this->_options;
    }
    
    public function getOption($name) {
        foreach($this->_options as $key => $option) {
            if($name == $key) {
                return $option;
            }
        }
        
        return null;
    }
    
    public function getGlobalError() {
        return apply_filters("wpjb_form_global_error", __("There are errors in your form.", "wpjobboard"), $this);
    }
    
    abstract function init();
}

?>