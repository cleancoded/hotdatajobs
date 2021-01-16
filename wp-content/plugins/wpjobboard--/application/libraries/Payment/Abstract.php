<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Abstract
 *
 * @author Grzegorz
 */
abstract class Wpjb_Payment_Abstract {
    
    /**
     * Payment methods default config option
     *
     * @var string
     */
    protected $_config = "wpjb_payment_method";
    
    /**
     * Payment method default configuration values
     *
     * @var Array 
     */
    protected $_default = array();
    
    /**
     * Copy of $_POST global variable
     *
     * @var Array
     */
    protected $_post = array();
    
    /**
     * Copy of $_GET global variable
     *
     * @var Array
     */
    protected $_get = array();
    
    /**
     *
     * @var Wpjb_Model_Payment
     */
    protected $_data = null;
    
    /**
     * Loaded configuration
     *
     * @var array
     */
    protected $_loaded = null;
    
    /**
     * Payment object constructor.
     *
     * $data passed to the constructor is an object that uniquely
     * identifies transaction
     *
     * @param Wpjb_Model_Payment $data
     */
    public function __construct(Wpjb_Model_Payment $data = null) 
    {
        $this->_data = $data;
    }
    
    private function load()
    {
        if(!is_null($this->_loaded)) {
            return;
        }
        
        $conf = get_option($this->_config);
        
        if(isset($conf[$this->getEngine()])) {
            $this->_loaded = $conf[$this->getEngine()];
        }
        
        if(!is_array($this->_loaded)) {
            $this->_loaded = array();
        }
        
        foreach($this->_default as $k => $v) {
            if(!isset($this->_loaded[$k])) {
                $this->_loaded[$k] = $v;
            }
        }
    }
    
    /**
     * Returns payment method configuration option
     * 
     * @param string $option
     * @return mixed
     */
    public function conf($option = null) 
    {
        $this->load();
        
        if($option === null) {
            return $this->_loaded;
        } elseif(isset($this->_loaded[$option])) {
            return $this->_loaded[$option];
        } elseif(isset($this->_default[$option])) {
            return $this->_default[$option];
        }
    }
    
    public function set($option, $value) 
    {
        $this->load();
        $this->_loaded[$option] = $value;
    }
    
    public function save()
    {
        $conf = get_option($this->_config);
        $conf[$this->getEngine()] = $this->_loaded;
        
        update_option($this->_config, $conf);
    }
    
    public function getCustomTitle()
    {
        if($this->conf("title")) {
            return $this->conf("title");
        } else {
            return $this->getTitle();
        }
    }
    
    /**
     * Returns payment engine
     *
     * Short string identyfying payment class. For example if your payment class
     * name is Wpjb_Payment_SuperPayments. This method should return SuperPayments
     *
     * @return string
     */
    abstract public function getEngine();

    /**
     * Return payment title
     *
     * Short payment description, should not containg HTML, just plain text
     *
     * @return string
     */
    abstract public function getTitle();
    
    /**
     * Return form class
     * 
     * Returns class name of a form that contains payment configuration
     * 
     * @return string
     */
    abstract public function getForm();

    /**
     * Renders payment form
     *
     * Returns payment form HTML.
     *
     * return string
     */
    abstract public function render();

    /**
     * Process transaction
     *
     * Usually transaction data is returned by $_POST. The $_POST data is passed
     * to this function as $data. You can use it to verify and handle
     * transaction.
     *
     * In case of error it is recommended to throw some kind of exception. It will
     * cancel processing transaction.
     *
     * @param array $post Equal to $_POST
     * @param array $get Equal to $_GET
     * @return array
     */
    abstract public function processTransaction();
    
    /**
     * Sets current Payment object
     * 
     * @param Wpjb_Model_Payment $payment
     */
    public function setObject(Wpjb_Model_Payment $payment)
    {
        $this->_data = $payment;
    }
    
    /**
     * Returns $this->_data
     * 
     * @return Wpjb_Model_Payment
     */
    public function getObject()
    {
        return $this->_data;
    }
    
    public function bind(array $post, array $get)
    {
        $this->_post = $post;
        $this->_get = $get;
    }
    
    /**
     * Icon displayed in wp-admin / Settings (WPJB) panel
     * 
     * It should be one of wpjb-icon-*
     * 
     * @return string
     */
    public function getIcon() 
    {
        return "wpjb-icon-money";
    }
    
    /**
     * Icon displayed in the frontend form
     * 
     * It should be one of wpjb-icon-*
     * 
     * @return string
     */
    public function getIconFrontend()
    {
        return null;
    }
    
    /**
     * Returns default frontend form class for payment
     * 
     * Default frontend form is being used in a cart when user selects
     * a payment gateway.
     * 
     * @since 4.3.5
     * @return string Default paymeny form class
     */
    public function getFormFrontend()
    {
        return "Wpjb_Form_Payment_Default";
    }
}

