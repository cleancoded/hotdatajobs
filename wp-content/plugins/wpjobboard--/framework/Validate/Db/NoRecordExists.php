<?php
/**
 * Description of RecordExists
 *
 * @author greg
 * @package
 */

class Daq_Validate_Db_NoRecordExists extends Daq_Validate_Db_Abstract
{
    public function isValid($value)
    {
        $row = $this->_exist($value);
        if($row) {
            $msg = __("Record already exists in the database.", "wpjobboard");
            $this->setError($msg);
            return false;
        }

        return true;
    }
}

?>