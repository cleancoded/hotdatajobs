<?php

class Wpjb_Upgrade_450 extends Wpjb_Upgrade_Abstract
{
    public function getVersion()
    {
        return "4.5.0";
    }

    public function execute()
    {
        $this->sql();

        return;
    }
}
