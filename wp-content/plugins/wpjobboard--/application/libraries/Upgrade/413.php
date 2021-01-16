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

class Wpjb_Upgrade_413 extends Wpjb_Upgrade_Abstract
{
    public function getVersion()
    {
        return "4.1.3";
    }

    public function execute()
    {
        $this->sql();
        
        return;
    }
}
?>
