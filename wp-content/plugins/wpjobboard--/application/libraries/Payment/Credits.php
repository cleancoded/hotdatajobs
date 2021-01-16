<?php
/*
 * lwdpt_1228422945_per@jadamspam.pl
 */

/**
 * Description of PayPal
 *
 * @author greg
 */
class Wpjb_Payment_Credits extends Wpjb_Payment_Abstract
{

    /**
     * Job object
     *
     * @var Wpjb_Model_Job
     */
    protected $_data = null;

    public function __construct(Wpjb_Model_Payment $data = null)
    {
        $this->_data = $data;
    }

    public function getEngine()
    {
        return "Credits";
    }

    public function getTitle()
    {
        return "Membership Package Credits";
    }
    
    public function getForm()
    {
        return null;
    }

    /**
     * Procesess PayPal transaction.
     *
     * @param array $ppData
     * @return boolean
     */
    public function processTransaction()
    {        
        return array(
            "external_id" => "",
            "paid" => 0
        );
    }

    public function render()
    {
        return __("Your purchase was processed successfully.", "wpjobboard");
    }
    

}

?>