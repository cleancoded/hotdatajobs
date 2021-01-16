<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Required
 *
 * @author Grzegorz
 */
class Daq_Validate_File_Required 
    extends Daq_Validate_Abstract implements Daq_Validate_Interface
{
    protected $_upload = false;
    
    public function __construct(array $upload) 
    {
        $this->_upload = $upload;
    }
    
    public function isValid($value)
    {
        if(is_array($value) && isset($value[0]["size"]) && $value[0]["size"]>0) {
            return true;
        }
        
        $u = $this->_upload;
        
        $find = array("{object}", "{field}", "{id}");
        $repl = array($u["object"], $u["field"], $u["id"]);
        
        $path = str_replace($find, $repl, $u["path"])."/*";
        $files = glob($path);
        
        if(!is_array($files) || empty($files)) {
            $this->setError(__("You need to upload at least one file.", "wpjobboard"));
            return false;
        }
        
        return true;
    }
}

?>
