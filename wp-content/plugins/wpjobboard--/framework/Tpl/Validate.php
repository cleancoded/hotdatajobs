<?php
/**
 * Description of Required
 *
 * @author Grzegorz Winiarski
 * @package 
 */

class Daq_Tpl_Validate
    extends Daq_Validate_Abstract implements Daq_Validate_Interface
{
    public function isValid($value)
    {
        try {
            $tpl = new Daq_Tpl_Email;
            $tpl->draw($value);
        } catch(Daq_Tpl_SyntaxException $e) {
            $this->setError($e->getMessage());
            return false;
        } catch(Exception $e) {
            $this->setError(__("Exception ".get_class($e), "wpjobboard"));
            return false;
        }

        return true;
    }
}

?>