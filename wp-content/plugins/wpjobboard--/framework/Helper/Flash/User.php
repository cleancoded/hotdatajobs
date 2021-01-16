<?php
/**
 * Flash messages implementation using user meta.
 * 
 * This class is being used for flash messages when logged in user is using
 * job board.
 * 
 * @see Daq_Helper_Flash_Abstract
 * @see add_user_meta
 * @see update_user_meta
 * @see delete_user_meta
 *
 * @author Greg Winiarski
 * @package WPJobBoard
 * @subpackage Framework
 */
class Daq_Helper_Flash_User extends Daq_Helper_Flash_Abstract 
{
    /**
     * Is new?
     * 
     * Value is false if user already has "flash" data saved in user meta, 
     * true otherwise
     * 
     * @var boolean 
     */
    protected $_new = false;
    
    /**
     * User ID
     * 
     * ID of currently logged in user.
     *
     * @var int
     */
    protected $_id = null;

    /**
     * Loads flash data from user meta table
     * 
     * @see get_user_meta
     * 
     * @since 4.0
     * @return void
     */
    public function load() 
    {
        if($this->_loaded) {
            return;
        }
        
        $id = get_current_user_id();
        $flash = get_user_meta($id, $this->_ns, true);
        $this->_id = $id;
        
        if($flash === "") {
            $this->_new = true;
        } 
        
        if(empty($flash)) {
            $this->_info = array();
            $this->_error = array();
        } else {
            $this->_info = $flash["info"];
            $this->_error = $flash["error"];
        }
        
        $this->_loaded = true;
    }
    
    /**
     * Disposes flash messages
     * 
     * Basically clears info[] and error[] arrays.
     * 
     * @since 4.0
     * @return void
     */
    public function dispose() 
    {
        $this->_info = array();
        $this->_error = array();
    }
    
    /**
     * Saves flash data in user meta tables.
     * 
     * @since 4.0
     * @return void
     */
    public function save() 
    {   
        
        $flash = array(
            "info" => $this->_info,
            "error" => $this->_error
        );

        $id = $this->_id;
        
        if(empty($this->_info) && empty($this->_error)) {
            delete_user_meta($id, $this->_ns);
        } elseif($this->_new) {
            add_user_meta($id, $this->_ns, $flash, true);
        } else {
            update_user_meta($id, $this->_ns, $flash);
        }
        

    }
}

?>
