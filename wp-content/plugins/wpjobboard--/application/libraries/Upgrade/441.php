<?php

class Wpjb_Upgrade_441 extends Wpjb_Upgrade_Abstract
{
    public function getVersion()
    {
        return "4.4.1";
    }

    public function execute()
    {
        $this->sql();

        return;
    }
}
