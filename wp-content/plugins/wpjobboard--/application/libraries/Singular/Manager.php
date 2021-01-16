<?php

 /**
  * @property Wpjb_Singular_Job $job
  * @property Wpjb_Singular_Resume $resume
  * @property Wpjb_Singular_Company $company
  */

class Wpjb_Singular_Manager {
    
    /**
     * List of registered WPJB singulars
     *
     * @var type array
     */
    protected $_singular = array();
    
    /**
     * Class constructor
     * 
     * Registers all WPJB singulars
     * 
     */
    public function __construct() {
        
        $this->_singular["job"] = new Wpjb_Singular_Job();
        $this->_singular["resume"] = new Wpjb_Singular_Resume();
        $this->_singular["company"] = new Wpjb_Singular_Company();
        
    }
    
    /**
     * Returns singular object
     * 
     * @param string $name  Singular name
     * @return object       Singular object
     */
    public function __get($name) {
        if(isset($this->_singular[$name])) {
            return $this->_singular[$name];
        } else {
            return null;
        }
    }
    
    /**
     * Registers all listeners
     * 
     * This function registers listeners for all singulars
     * 
     * @return void
     */
    public function setupListeners() {
        foreach(array_keys($this->_singular) as $key) {
            $this->_singular[$key]->listen();
        }
    }
    
}
