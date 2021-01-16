<?php
/**
 * Description of Abstract
 *
 * @author greg
 * @package 
 */

abstract class Daq_Controller_Abstract implements Daq_Controller_Interface
{
    /**
     *
     * @var Daq_Request
     */
    protected $_request = null;

    /**
     *
     * @var Daq_Controller_Response
     */
    protected $_response = null;

    /**
     *
     * @var array
     */
    protected $_param = array();

    /**
     *
     * @var string
     */
    public $view = null;

    public function __construct()
    {
        $this->_request = Daq_Request::getInstance();
    }

    /**
     *
     * @param <type> $param
     * @return <type>
     */
    protected function hasParam($param)
    {
        $param = $this->_request->getParam($param, false);
        if($param === false) {
            return false;
        }

        return true;
    }

    protected function getParam($param, $default = null)
    {
        if (isset($this->_param[$key])) {
            return $this->_param[$key];
        } elseif ($this->_request->get($keyName, null) !== null) {
            return $this->_request->get($keyName);
        } elseif ($this->_request->post($keyName) !== null) {
            return $this->_request->post($keyName);
        }

        return $default;
    }

    /**
     * Retrieve a member of the $_SERVER superglobal
     *
     * If no $key is passed, returns the entire $_SERVER array.
     *
     * @param string $key
     * @param mixed $default Default value to use if key not found
     * @return mixed Returns null if key does not exist
     */
    public function getServer($key = null, $default = null)
    {
        if (null === $key) {
            return $_SERVER;
        }

        return (isset($_SERVER[$key])) ? $_SERVER[$key] : $default;
    }

    /**
     * Return the method by which the request was made
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->getServer('REQUEST_METHOD');
    }

    /**
     * Was the request made by POST?
     *
     * @return boolean
     */
    public function isPost()
    {
        if ('POST' == $this->getMethod()) {
            return true;
        }

        return false;
    }

    /**
     * Was the request made by GET?
     *
     * @return boolean
     */
    public function isGet()
    {
        if ('GET' == $this->getMethod()) {
            return true;
        }

        return false;
    }

    /**
     * Was the request made by PUT?
     *
     * @return boolean
     */
    public function isPut()
    {
        if ('PUT' == $this->getMethod()) {
            return true;
        }

        return false;
    }

    /**
     * Was the request made by DELETE?
     *
     * @return boolean
     */
    public function isDelete()
    {
        if ('DELETE' == $this->getMethod()) {
            return true;
        }

        return false;
    }

    /**
     * Was the request made by HEAD?
     *
     * @return boolean
     */
    public function isHead()
    {
        if ('HEAD' == $this->getMethod()) {
            return true;
        }

        return false;
    }

    /**
     * Was the request made by OPTIONS?
     *
     * @return boolean
     */
    public function isOptions()
    {
        if ('OPTIONS' == $this->getMethod()) {
            return true;
        }

        return false;
    }

    /**
     * Is the request a Javascript XMLHttpRequest?
     *
     * Should work with Prototype/Script.aculo.us, possibly others.
     *
     * @return boolean
     */
    public function isXmlHttpRequest()
    {
        return ($this->getServer('X_REQUESTED_WITH') == 'XMLHttpRequest');
    }

    /**
     * Is this a Flash request?
     *
     * @return bool
     */
    public function isFlashRequest()
    {
        $header = strtolower($this->getServer('USER_AGENT'));
        return (strstr($header, ' flash')) ? true : false;
    }

    public function setView(Daq_View $view)
    {
        $this->view = $view;
    }

    public function forward($action) {
        
    }

    public function init()
    {
        
    }

}

?>