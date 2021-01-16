<?php
/**
 * Description of Application
 *
 * @author greg
 * @package 
 */

abstract class Daq_Application_Abstract
{
    /**
     * Controller mask, missing part will be replaced by dispatcher.
     *
     * {@example} Xyz_Controller_Front_*
     * {@example} Xyz_Controller_Front_
     * 
     * @var string
     */
    protected $_controller = null;

    /**
     * Application dispatcher
     * 
     * @var Daq_Router_Abstract
     */
    protected $_router = null;

    /**
     * Application log object
     * 
     * @var Daq_Log
     */
    protected $_log = null;

    /**
     * Path to view files for this application
     * 
     * @var string
     */
    protected $_view = null;
    
    /**
     * Indicates if the application is run in the admin area
     *
     * @var bool
     */
    protected $_isAdmin = false;

    /**
     * True if application was dispatched
     *
     * @var boolean
     */
    protected $_dispatched = false;
    
    protected $_option = array();

    public function isDispatched()
    {
        return $this->_dispatched;
    }

    public function setController($ctrl)
    {
        $this->_controller = $ctrl;
    }

    public function setRouter(Daq_Router $router)
    {
        $this->_router = $router;
    }

    /**
     *
     * @return Daq_Router_Abstract
     */
    public function getRouter()
    {
        return $this->_router;
    }

    public function setLog(Daq_Log $log)
    {
        $this->_log = $log;
    }

    public function setView(Daq_View $view)
    {
        $this->_view = $view;
    }

    public function getView()
    {
        if($this->_view === null) {
            throw new Exception("View not set");
        }

        return $this->_view;
    }

    /**
     * Checks if current application is admin application. Optionally 
     * by passing boolean param to the function you can set application to be
     * admin
     *
     * @param bool $admin
     * @return bool 
     */
    public function isAdmin($admin = null) 
    {
        if(!is_null($admin)) {
            $this->_isAdmin = (bool)$admin;
        }
        
        return $this->_isAdmin;
    }
    
    public function addOption($key, $value)
    {
        $this->_option[$key] = $value;
    }
    
    public function getOption($key)
    {
        if(isset($this->_option[$key])) {
            return $this->_option[$key];
        } else {
            return null;
        }
    }
    
    abstract public function dispatch($path = null, $route = null);
}

?>