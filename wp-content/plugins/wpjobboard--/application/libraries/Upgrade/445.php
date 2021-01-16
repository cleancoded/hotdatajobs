<?php

class Wpjb_Upgrade_445 extends Wpjb_Upgrade_Abstract
{
    public function getVersion()
    {
        return "4.4.5";
    }

    public function execute()
    {
        $this->sql();

        return;
    }
}
