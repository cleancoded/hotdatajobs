<?php
/**
 * Description of Category
 *
 * @author greg
 * @package 
 */

class Wpjb_Form_Admin_Category extends Daq_Form_ObjectAbstract
{
    protected $_model = "Wpjb_Model_Tag";

    public function _exclude()
    {
        if($this->_object->getId()) {
            return array("id" => $this->_object->getId());
        } else {
            return array();
        }
    }

    public function init()
    {
        $e = $this->create("id", "hidden");
        $e->setValue($this->_object->id);
        $e->addFilter(new Daq_Filter_Int());
        $this->addElement($e);
        
        $e = $this->create("type", "hidden");
        $e->setValue(Wpjb_Model_Tag::TYPE_CATEGORY);
        $this->addElement($e);  

        $e = $this->create("title", "text");
        $e->setRequired(true);
        $e->setValue($this->_object->title);
        $e->setLabel(__("Category Title", "wpjobboard"));
        $e->setHint(__("The name is used to identify the category almost everywhere, for example under the post or in the category widget.", "wpjobboard"));
        $this->addElement($e);

        $e = $this->create("slug", "text");
        $e->setRequired(true);
        $e->setValue($this->_object->slug);
        $e->setLabel(__("Category Slug", "wpjobboard"));
        $e->setHint(__("The “slug” is the URL-friendly version of the name. It is usually all lowercase and contains only letters, numbers, and hyphens.", "wpjobboard"));
        $e->addValidator(new Daq_Validate_Slug());
        $e->addValidator(new Daq_Validate_Db_NoRecordExists("Wpjb_Model_Tag", "slug", $this->_exclude()));
        $this->addElement($e);
        
        $e = $this->create("order", "text");
        $e->setValue($this->_object->order);
        $e->setLabel(__("Order", "wpjobboard"));
        $e->setHint(__("The smaller the number the higher the item will be displayed in the list.", "wpjobboard"));
        $this->addElement($e);

        apply_filters("wpja_form_init_category", $this);
    }
    
    public function save($append = array())
    {
        parent::save($append);
        
        apply_filters("wpja_form_save_category", $this);
    }
}

