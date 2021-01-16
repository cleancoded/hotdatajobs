<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of 430
 *
 * @author Grzegorz
 */

class Wpjb_Upgrade_430 extends Wpjb_Upgrade_Abstract
{
    public function getVersion()
    {
        return "4.3.0";
    }

    public function execute()
    {
        $this->sql();

        return;
    }
}
?>
