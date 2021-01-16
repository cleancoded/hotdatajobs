<?php

/**
 * Description of Tag
 *
 * @author Grzegorz Winiarski
 * @package WPJB.Model
 */

class Wpjb_Model_Tag extends Daq_Db_OrmAbstract
{
    const TYPE_CATEGORY = "category";
    const TYPE_TYPE = "type";
    
    
    /**
     * Table name
     *
     * @var string 
     */
    protected $_name = "wpjb_tag";
    
    /**
     * Meta table name
     * 
     * @var string
     */
    protected $_metaTable = "Wpjb_Model_Meta";
    
    /**
     * Meta table object key
     *
     * @var string 
     */
    protected $_metaName = "tag";
    

    protected function _init()
    {
        $this->_reference["tagged"] = array(
            "localId" => "id",
            "foreign" => "Wpjb_Model_Tagged",
            "foreignId" => "tag_id",
            "type" => "ONE_TO_ONE"
        );
    }
    
    public function url()
    {
        return wpjb_link_to($this->type, $this);
    }
    
    public function getCount()
    {
        $count = wpjb_conf("count", array());

        if(!array_key_exists($this->getId(), $count)) {
            return null;
        }

        return $count[$this->getId()];
    }
    
}

?>