<?php

class Wpjb_User_Dashboard {
    
    /**
     * Registered sections with menu items inside
     *
     * @var array
     */
    protected $_menu = array();
    
    /**
     * Registered pages
     * 
     * @var array
     */
    protected $_page = array();
    
    /**
     * Main query_var for this user
     *
     * @var string
     */
    protected $_query_var = null;
    
    /**
     * Default capability for this panel
     *  
     * @var string
     */
    protected $_capability = "read";
    
    /**
     * Sets query var for this dashboard
     * 
     * @param string $var   Query var name
     */
    public function setQueryVar($var) {
        $this->_query_var = $var;
    }
    
    /**
     * Returns main query var for this dashboard
     * 
     * @return string
     */
    public function getQueryVar() {
        return $this->_query_var;
    }
    
    /**
     * Sets default required capability for this dashboard
     * 
     * @param string $capability
     */
    public function setCapability($capability) {
        $this->_capability = $capability;
    }
    
    /**
     * Returns default capability required for this dashboard
     * 
     * @return string
     */
    public function getCapability() {
        return $this->_capability;
    }
    
    /**
     * Registers dashboard section
     * 
     * @return void 
     * @since 4.6.0
     * @param string $name      Section name should contain only: a-z, 0-9 and _
     * @param string $title     Section title visible to users in the dashboard
     */
    public function addMenuSection($name, $title) {
        $this->_menu[$name] = array(
            "title" => $title,
            "links" => array()
        );
    }
    
    /**
     * Registers dashboard button and page
     * 
     * Running this function is equivalent to running
     * self::addButton();
     * self::addPage();
     * 
     * The menu items are added to the sections, so make sure to register sections
     * using self::addMenuSection() first.
     * 
     * If capability param is not provided the required capability will be equal to 
     * user role.
     * 
     * @param string $section       Section name
     * @param string $name          Item name should contain only: a-z, 0-9 and _
     * @param string $title         Item title visible to users in the dashboard
     * @param string $icon          Item icon, should be one of wpjb-icon-*
     * @param mixed  $rewrite       Rewrite rule assigned to this menu item
     * @param mixed  $callback      Callback to function which will render the panel
     * @param string $capability    Required capability to access this menu
     */
    public function addButtonAndPage($section, $name, $title, $icon, $rewrite, $callback, $capability = null) {
        $this->addButton($section, $name, $title, $icon, $capability);
        $this->addPage($name, $rewrite, $callback, $capability);
    }
    
    /**
     * Registers dashboard button
     * 
     * The menu items are added to the sections, so make sure to register sections
     * using self::addMenuSection() first.
     * 
     * If capability param is not provided the required capability will be equal to 
     * user role.
     * 
     * @param string $section       Section name
     * @param string $name          Item name should contain only: a-z, 0-9 and _
     * @param string $title         Item title visible to users in the dashboard
     * @param string $icon          Item icon, should be one of wpjb-icon-*
     * @param string $capability    Required capability to access this menu
     */
    public function addButton($section, $name, $title, $icon, $capability = null) {
        
        if($capability === null) {
            $cap = $this->_capability;
        } else {
            $cap = $capability;
        }
        
        $this->_menu[$section]["links"][$name] = array(
            "title" => $title,
            "icon" => $icon,
            "capability" => $capability
        );
    }
    
    /**
     * Registers dashboard page
     * 
     * @param string $name          Item name should contain only: a-z, 0-9 and _
     * @param mixed  $rewrite       Rewrite rule assigned to this menu item
     * @param mixed  $callback      Callback to function which will render the panel
     * @param string $capability    Required capability to access this menu
     */
    public function addPage($name, $rewrite, $callback, $capability = null) {
        
        if($capability === null) {
            $cap = $this->_capability;
        } else {
            $cap = $capability;
        }
        
        if(is_string($rewrite)) {
            $rewrite = array(
                $this->_query_var => $rewrite
            );
        }
        
        $this->_page[$name] = array(
            "rewrite" => $rewrite,
            "callback" => $callback,
            "capability" => $cap
        );
    }
    
    /**
     * Returns list of registered pages for this dashboard
     * 
     * @return array    List of registerd pages
     */
    public function getPages() {
        return $this->_page;
    }
    
    /**
     * Returns whole dashboard menu
     * 
     * @return array    Dashboard menu
     */
    public function getMenu() {
        return $this->_menu;
    }
    
    /**
     * Returns list of links in the menu.
     * 
     * This function will merge the sections and will return the links only.
     * 
     * @return array    List of links in the menu
     */
    public function getLinks() {
        $links = array();
        foreach($this->_menu as $menu) {
            foreach($menu["links"] as $key => $link) {
                $links[$key] = $link;
            }
        }
        return $links;
    }
    
    /**
     * Sets link callback
     * 
     * Allows to set a callback to function which will generate links in this
     * dashboard.
     * 
     * @param mixed $callback
     * @return void
     */
    public function setLinkCallback($callback) {
        $this->_linkCallback = $callback;
    }
    
    /**
     * Get link callback
     * 
     * Returns a callback function responsible for generating links in this menu.
     * 
     * @return mixed    Callback function
     */
    public function getLinkCallback() {
        return $this->_linkCallback;
    }
    
    /**
     * Links to a page in the Dashboard
     * 
     * @param string $key       URL unique identifier
     * @param mixed $object     Int or an object identifying element you will be linking to
     * @param type $param       Array of params added to the URL
     * @param type $forced_id   ID of a Page
     * @return type
     */
    public function linkTo($key, $object = null, $param = array(), $forced_id = null) {
        return call_user_func($this->getLinkCallback(), $key, $object, $param, $forced_id);
    }
    
}