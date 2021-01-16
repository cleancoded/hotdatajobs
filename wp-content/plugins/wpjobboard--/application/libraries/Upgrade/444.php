<?php

class Wpjb_Upgrade_444 extends Wpjb_Upgrade_Abstract
{
    public function getVersion()
    {
        return "4.4.4";
    }

    public function execute()
    {
        $this->sql();

        return;
    }
}
