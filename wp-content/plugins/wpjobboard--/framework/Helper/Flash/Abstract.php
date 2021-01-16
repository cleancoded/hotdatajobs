<?php
/**
 * Abstract Flash
 * 
 * This class provides some functionality and interface for "flash" messages, 
 * that is messages informing user about 
 * - error (like form completed incorrectly)
 * - success (submitted data saved in database)
 * 
 * In the frontend this class is used in wpjb_flash() function and in controllers.
 * 
 * @see wpjb_flash()
 *
 * @author Greg Winiarski
 * @package WPJobBoard
 * @subpackage Framework
 */

abstract class Daq_Helper_Flash_Abstract
{
    /**
     * Namespace
     *
     * @var string
     */
    protected $_ns = null;

    /**
     * Should the data be saved automatically
     *
     * @var boolean
     */
    protected $_save = true;

    /**
     * List of success messages
     *
     * @var array
     */
    protected $_info = array();
    
    /**
     * Success icon (one of wpjb-icon-*)
     *
     * @var string
     */
    protected $_info_icon = "wpjb-icon-ok";

    /**
     * List of error messages
     *
     * @var array
     */
    protected $_error = array();
    
    /**
     * Error icon (one of wpjb-icon-*)
     *
     * @var string
     */
    protected $_error_icon = "wpjb-icon-attention";
    
    /**
     * Is data loaded from DB
     *
     * @var boolean
     */
    protected $_loaded = false;

    /**
     * Class constructor
     * 
     * @since 4.0
     * @param string $namespace
     */
    public function __construct($namespace = null)
    {
        $this->_ns = $namespace;
    }

    /**
     * Loads saved flash messages
     * 
     * @since 4.0
     * @return void
     */
    abstract public function load();

    /**
     * Adds info (success) message to info array
     * 
     * @since 4.0
     * @param string $info Success message
     * @return void
     */
    public function addInfo($info)
    {
        $this->load();
        $this->_info[] = $info;
        $this->save();
    }
    
    /**
     * Sets info icon
     * 
     * The passed param should be full icon name starting with wpjb-icon-
     * 
     * @since 4.4.0
     * @param string $icon One of valid wpjb-icon-*
     * @return null;
     */
    public function setInfoIcon($icon)
    {
        $this->_info_icon = $icon;
    }
    
    /**
     * Returns info icon name
     * 
     * @since 4.4.0
     * @return string Info icon name
     */
    public function getInfoIcon()
    {
        return $this->_info_icon;
    }
    
    /**
     * Sets error icon
     * 
     * The passed param should be full icon name starting with wpjb-icon-
     * 
     * @since 4.4.0
     * @param string $icon One of valid wpjb-icon-*
     * @return null;
     */
    public function setErrorIcon($icon)
    {
        $this->_info_icon = $icon;
    }

    /**
     * Returns error icon name
     * 
     * @since 4.4.0
     * @return string Error icon name
     */
    public function getErrorIcon()
    {
        return $this->_error_icon;
    }
    
    /**
     * Returns list of info messages
     * 
     * @return array List of info messages
     */
    public function getInfo()
    {
        $this->load();
        return array_unique($this->_info);
    }

    /**
     * Adds error message to error array
     * 
     * @since 4.0
     * @param string $error Error message
     * @return void
     */
    public function addError($error)
    {
        $this->load();
        $this->_error[] = $error;
        $this->save();
    }

    /**
     * Returns list of error messages
     * 
     * @since 4.0
     * @return array List of error messages
     */
    public function getError()
    {
        $this->load();
        return array_unique($this->_error);
    }

    /**
     * Disposes messages
     * 
     * Clears list of messages and deletes messages saved in DB or session
     * 
     * @since 4.0
     * @return void 
     */
    public function dispose()
    {
        $this->_save = false;
    }

    /**
     * Saves info and error messages.
     * 
     * This function should save messages in DB, session or etc., so on page reload
     * it will be possible to read them.
     * 
     * @return void
     */
    abstract public function save();
}

?>