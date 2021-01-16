<?php

class Wpjb_Upgrade_502 extends Wpjb_Upgrade_Abstract
{
    public function getVersion()
    {
        return "5.0.2";
    }

    public function execute()
    {
        $this->sql(); 
        return;
    }
}
