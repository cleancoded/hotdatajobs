<?php
/**
 * Description of UserMeta
 *
 * @author greg
 * @package 
 */

class Wpjb_Model_UserMeta extends Daq_Db_OrmAbstract
{
    //put your code here
    protected $_name = "";

    public function  __construct($id = null)
    {
        $db = Daq_Db::getInstance()->getDb();
        $this->_name = $db->usermeta;
        parent::__construct($id);
    }

    protected function _init() 
    {

    }

    public final function save()
    {
        throw new Exception("This is mock for wp_users table. DO NOT SAVE IT!");
    }

    public final function delete()
    {
        throw new Exception("This is mock for wp_users table. DO NOT USE DELETE METHOD!");
    }


}

?>
