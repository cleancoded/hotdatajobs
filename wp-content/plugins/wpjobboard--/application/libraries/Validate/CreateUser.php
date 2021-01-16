<?php

class Wpjb_Validate_CreateUser
    extends Daq_Validate_Abstract implements Daq_Validate_Interface {
    
    /**
     * User type discard
     *
     * @var string
     */
    private $_discard = "";
    
    /**
     * User ID
     *
     * @var int
     */
    private $_user_id = null;
    
    /**
     * Validator constructor
     * 
     * @param string    $discard    User type to discard
     * @param int       $user_id    User ID to validate against
     */
    public function __construct($discard = "", $user_id = null) 
    {
        $this->_discard = $discard;
        $this->_user_id = $user_id;
    }
    
    /**
     * Validates provided value
     * 
     * @param   mixed   $value  Value to validate
     * @return  boolean         True is value is valid
     */
    public function isValid($value) 
    {
        if($this->_user_id) {
            $user_id = $this->_user_id;
        } else {
            $user_id = absint(Daq_Request::getInstance()->post("_user_id"));
        }
        
        if($user_id < 1) {
            $this->setError(__("You need to select a user.", "wpjobboard"));
            return false;
        }
        
        // is employer?
        if($this->_discard == "employer") {
            $query = new Daq_Db_Query();
            $query->select("t.id");
            $query->from("Wpjb_Model_Company t");
            $query->where("user_id = ?", $user_id);
            $query->limit(1);

            $id = $query->fetchColumn();

            if($id) {
                $this->setError(__("This user already has an account.", "wpjobboard"));
                return false;
            }
        }
        
        // is candidate?
        if($this->_discard == "candidate") {
            $query = new Daq_Db_Query();
            $query->select("t.id");
            $query->from("Wpjb_Model_Resume t");
            $query->where("user_id = ?", $user_id);
            $query->limit(1);

            $id = $query->fetchColumn();

            if($id) {
                $this->setError(__("This user already has an account.", "wpjobboard"));
                return false;
            }
        }
        
        return true;
    }
}

