<?php
/**
 * Description of AdminUrl
 *
 * @author greg
 * @package 
 */

class Daq_Helper_AdminUrl
{
    public function linkTo($page = null, $action = null)
    {
        if($page === null) {
            $page = Daq_Request::getInstance()->get("page");
        }
        if($action !== null) {
            $action = "&amp;action=".($action);
        }
        return "admin.php?page=".($page).$action;
    }
}

?>