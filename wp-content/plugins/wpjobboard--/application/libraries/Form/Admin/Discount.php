<?php
/**
 * Description of Listing
 *
 * @author greg
 * @package
 */

class Wpjb_Form_Admin_Discount extends Daq_Form_ObjectAbstract
{
    protected $_model = "Wpjb_Model_Discount";

    public function _exclude()
    {
        if($this->_object->getId()) {
            return array("id" => $this->_object->getId());
        } else {
            return array();
        }
    }

    protected function _currArr()
    {
        $list = array();
        foreach(Wpjb_List_Currency::getList() as $k => $arr) {
            $v = $arr['name'];
            $code = $arr["code"];
            if($arr['symbol'] != null) {
                $v = $arr['symbol'].' '.$v;
            }
            $list[] = array($code, $code, $v);
        }
        return $list;
    }

    protected function _typeArr()
    {
        return array(
            array(1, 1, __('Percentage (%)', "wpjobboard")),
            array(2, 2, __('Fixed amount of money', "wpjobboard")),
        );
    }

    public function init()
    {
        $e = $this->create("id", "hidden");
        $e->setValue($this->_object->id);
        $e->addFilter(new Daq_Filter_Int());
        $this->addElement($e);

        $e = $this->create("discount_for", "select");
        $e->setValue($this->_object->discount_for);
        $e->addOption(Wpjb_Model_Pricing::PRICE_SINGLE_JOB, Wpjb_Model_Pricing::PRICE_SINGLE_JOB, __("Single Job Posting", "wpjobboard"));
        $e->addOption(Wpjb_Model_Pricing::PRICE_EMPLOYER_MEMBERSHIP, Wpjb_Model_Pricing::PRICE_EMPLOYER_MEMBERSHIP, __("Employer Membership", "wpjobboard"));
        $e->addOption(Wpjb_Model_Pricing::PRICE_CANDIDATE_MEMBERSHIP, Wpjb_Model_Pricing::PRICE_CANDIDATE_MEMBERSHIP, __("Candidate Membership", "wpjobboard"));
        $e->setLabel(__("Apply Discount To", "wpjobboard"));
        $this->addElement($e);
        
        $e = $this->create("title");
        $e->setRequired(true);
        $e->setValue($this->_object->title);
        $e->setLabel(__("Discount Title", "wpjobboard"));
        $e->setHint(__('This is the official promotion name that identifies promotion.', "wpjobboard"));
        $e->addValidator(new Daq_Validate_StringLength(1, 120));
        $this->addElement($e);

        $e = $this->create("code");
        $e->setRequired(true);
        $e->setValue($this->_object->code);
        $e->setLabel(__("Code", "wpjobboard"));
        $e->setHint(__('The secret code that client has to know in order to use selected promo code.', "wpjobboard"));
        $e->addValidator(new Daq_Validate_StringLength(1, 20));
        $e->addValidator(new Daq_Validate_Db_NoRecordExists("Wpjb_Model_Discount", "code", $this->_exclude()));
        $this->addElement($e);

        $e = $this->create("discount");
        $e->setRequired(true);
        $e->setValue($this->_object->discount);
        $e->setLabel(__("Discount Value", "wpjobboard"));
        $e->setHint(__('Examples of valid values. "1234.00", "34.00", "45"', "wpjobboard"));
        $e->addValidator(new Daq_Validate_Float(0.01));
        $this->addElement($e);

        $e = $this->create("type", "select");
        $e->setValue($this->_object->type);
        $e->setLabel(__("Discount Type", "wpjobboard"));
        $e->setHint(__("Specifying Discount Value is not enough, you have to also select type of discount you can choose either fixed amount of money or percentage of total price.", "wpjobboard"));
        foreach($this->_typeArr() as $c) {
            $e->addOption($c[0], $c[1], $c[2]);
        }
        $this->addElement($e);

        $e = $this->create("currency", "select");
        $e->setValue($this->_object->currency);
        $e->setLabel(__("Currency", "wpjobboard"));
        foreach($this->_currArr() as $c) {
            $e->addOption($c[0], $c[1], $c[2]);
        }
        $this->addElement($e);
        
        $e = $this->create("expires_at", "text_date");
        $e->setDateFormat(wpjb_date_format());
        $e->setRequired(true);
        $e->setValue($this->ifNew(date("Y-m-d", strtotime("today +1 month")), $this->getObject()->expires_at));
        $e->setLabel(__("Expiration Date", "wpjobboard"));
        $e->setHint(__('Discount expiration date.', "wpjobboard"));
        $this->addElement($e);

        $e = $this->create("used");
        $e->addFilter(new Daq_Filter_Int());
        $e->setValue($this->_object->used);
        $e->setLabel(__("Used", "wpjobboard"));
        $e->setHint(__("Number of times coupon was already used.", "wpjobboard"));
        $e->addValidator(new Daq_Validate_Int);
        $this->addElement($e);

        $e = $this->create("max_uses");
        $e->addFilter(new Daq_Filter_Int());
        $e->setValue($this->_object->max_uses);
        $e->setLabel(__("Max uses", "wpjobboard"));
        $e->setHint(__("Maximum number of times coupon can be used. (Zero equal to unlimited uses.)", "wpjobboard"));
        $e->addValidator(new Daq_Validate_Int);
        $this->addElement($e);

        $e = $this->create("is_active", "checkbox");
        $e->setValue($this->_object->is_active);
        $e->setLabel(__("Is Active", "wpjobboard"));
        $e->setHint(__("Only active discounts can be used by job posters.", "wpjobboard"));
        $e->addFilter(new Daq_Filter_Float());
        $e->addOption(1, 1, __("Yes", "wpjobboard"));
        $this->addElement($e);

        apply_filters("wpja_form_init_discount", $this);
    }

    public function isValid(array $values)
    {
        if($values["type"] == 1) {
            $this->getElement("discount")->addValidator(new Daq_Validate_Float(0, 100));
        }

        return parent::isValid($values);
    }
    
    public function save($append = array()) 
    {
        parent::save($append);
        
        apply_filters("wpja_form_save_discount", $this);
    }
}

?>