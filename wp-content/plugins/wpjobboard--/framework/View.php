<?php
/**
 * Description of View
 *
 * @author greg
 * @package 
 */

class Daq_View
{
    protected $_file = null;

    protected $_param = array();

    protected $_slot = array();

    private $_dir = array();

    private $_helper = array();

    public function __construct($dir = null)
    {
        if($dir !== null) {
            $this->addDir($dir);
        }
    }

    /**
     *
     * @param string $dir 
     * @deprecated use addDir instead
     */
    public function setDir($dir)
    {
        if(!is_dir($dir)) {
            throw new Exception("Template dir [$dir] does not exist.");
        }
        $this->_dir[] = rtrim($dir, "/");
    }
    
    public function addDir($dirs, $top = false) {
        if(!is_array($dirs)) {
            $dirs = array($dirs);
        }
        
        foreach($dirs as $dir) {
            if($top) {
                array_unshift($this->_dir, rtrim($dir, "/"));
            } else {
                array_push($this->_dir, rtrim($dir, "/"));
            }
        }
    }

    public function addHelper($name, $obj)
    {
        $this->_helper[$name] = $obj;
    }
    
    public function getHelper($name)
    {
        return $this->_helper[$name];
    }

    protected function _helper($name)
    {
        if(isset($this->_helper[$name])) {
            return $this->_helper[$name];
        }

        throw new Exception("Helper [$name] not registered.");
    }

    public function set($param, $value)
    {
        $this->_param[$param] = $value;
    }

    public function get($param, $default = null)
    {
        if(isset($this->_param[$param])) {
            return $this->_param[$param];
        }

        return $default;
    }
    
    public function getFile($viewFile)
    {
        $tpath = null;
        if(function_exists("get_stylesheet_directory")) {
            $tpath = get_stylesheet_directory();
        } elseif(defined("TEMPLATEPATH")) {
            $tpath = TEMPLATEPATH;
        }
        
        foreach($this->_dir as $dir) {
            if($tpath) {
                $dir = str_replace("TEMPLATEPATH", $tpath, $dir);
            }

            if(is_file($dir."/".$viewFile)) {
                return $dir."/".$viewFile;
            } elseif(empty($dir) && is_file($viewFile)) {
                return $viewFile;
            } 
        }

        throw new Exception("View file not found in directory list");
    }
    
    public function hasFile($viewFile)
    {
        $tpath = null;
        if(function_exists("get_stylesheet_directory")) {
            $tpath = get_stylesheet_directory();
        } elseif(defined("TEMPLATEPATH")) {
            $tpath = TEMPLATEPATH;
        }
        
        foreach($this->_dir as $dir) {
            if($tpath) {
                $dir = str_replace("TEMPLATEPATH", $tpath, $dir);
            }

            if(is_file($dir."/".$viewFile)) {
                return $dir."/".$viewFile;
            } elseif(empty($dir) && is_file($viewFile)) {
                return $viewFile;
            } 
        }
        
        return false;
    }

    public function render($viewFile, $absolute = false)
    {
        extract($this->_param);
        include $this->getFile($viewFile);
    }

    public function __set($key, $value)
    {
        $this->set($key, $value);
    }

    public function __get($key)
    {
        if(substr($key, 0, 1) == "_") {
            return $this->_helper[substr($key,1)];
        } else {
            return $this->get($key);
        }
    }

    public function slot($key, $value)
    {
        $this->_slot[$key] = $value;
    }

    protected function _renderSlot($slot)
    {
        if(!isset($this->_slot[$slot])) {
            throw new Exception("Slot executed before it was set (probably).");
        }
        return $this->_slot[$slot];
    }

    protected function _include($viewFile)
    {
        $this->render($viewFile);
    }


}

?>