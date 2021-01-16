<?php

class Wpjb_Upgrade_442 extends Wpjb_Upgrade_Abstract
{
    public function getVersion()
    {
        return "4.4.2";
    }

    public function execute()
    {
        $this->sql();

        return;
    }
}
