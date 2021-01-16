<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of 410
 *
 * @author Grzegorz
 */

class Wpjb_Upgrade_410 extends Wpjb_Upgrade_Abstract
{
    public function getVersion()
    {
        return "4.1.0";
    }

    public function execute()
    {
        $this->sql();
        
        return;
    }
}
?>
