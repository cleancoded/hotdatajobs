<?php

/**
 * Description of ${name}
 *
 * @author ${user}
 * @package 
 */
class Wpjb_Form_Admin_XmlImport extends Daq_Form_Abstract
{
    public function init()
    {
        $e = $this->create("file", "file");
        $e->isRequired(true);
        
        $this->addElement($e);
    }
}
?>
