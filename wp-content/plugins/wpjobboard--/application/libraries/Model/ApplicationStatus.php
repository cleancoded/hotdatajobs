<?php

/**
 * Description of Tag
 *
 * @author Grzegorz Winiarski
 * @package WPJB.Model
 */

class Wpjb_Model_ApplicationStatus extends Daq_Db_OrmAbstract
{

    /**
     * Table name
     *
     * @var string 
     */
    protected $_name = "wpjb_applicationstatus";
    
    /**
     * Meta table name
     * 
     * @var string
     */
    //protected $_metaTable = "Wpjb_Model_Meta";
    
    /**
     * Meta table object key
     *
     * @var string 
     */
    //protected $_metaName = "tag";
    

    protected function _init()
    {

    }
    
    public function countApplications() {
        
        $q = new Daq_Db_Query();
        $result = $q->select( "COUNT(*) AS cnt" )->from( "Wpjb_Model_Application t" )->where( "t.status = ?", $this->getId() )->execute();
        
        $count = 0;
        if( isset( $result[0] ) && !empty( $result[0] ) ) {
            $count = $result[0]->cnt;
        }
        
        return $count;
    }
    
    
}

?>