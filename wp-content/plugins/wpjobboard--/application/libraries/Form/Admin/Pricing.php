<?php
/**
 * Description of Listing
 *
 * @author greg
 * @package 
 */

class Wpjb_Form_Admin_Pricing extends Daq_Form_ObjectAbstract
{
    protected $_model = "Wpjb_Model_Pricing";

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
        foreach(Wpjb_List_Currency::getList() as $arr) {
            $v = $arr['name'];
            $code = $arr["code"];
            if($arr['symbol'] != null) {
                $v = $arr['symbol'].' '.$v;
            }
            $list[] = array($code, $code, $v);
        }
        return $list;
    }

    public function init()
    {
        
        $this->addGroup( 'config', __( "Pricing Details", "wpjobboard" ), 1 );
        
        $e = $this->create("id", "hidden");
        $e->setValue($this->_object->id);
        $e->addFilter(new Daq_Filter_Int());
        $this->addElement( $e, 'config' );

        $e = $this->create("title", "text");
        $e->setRequired(true);
        $e->setValue($this->_object->title);
        $e->setLabel(__("Listing Title", "wpjobboard"));
        $e->setHint(__('Listing title should be a short name that explains listing details for example "Featured listing".', "wpjobboard"));
        $e->addValidator( new Daq_Validate_StringLength( 1, 120 ) );
        $e->addValidator( new Daq_Validate_StripePlanExists() );
        $e->setOrder(1);
        $this->addElement( $e, 'config' );

        $e = $this->create("price", "text");
        $e->setValue($this->_object->price);
        $e->setLabel(__("Listing Price", "wpjobboard"));
        $e->setHint(__('Listing price, examples of valid values are: "50.00", "140.00".', "wpjobboard"));
        $e->addFilter(new Daq_Filter_Float());
        $e->addValidator(new Daq_Validate_Float(0));
        $e->setOrder(1);
        $this->addElement( $e, 'config' );

        $e = $this->create("currency", "select");
        $e->setValue($this->_object->currency);
        $e->setLabel(__("Currency", "wpjobboard"));
        foreach($this->_currArr() as $c) {
            $e->addOption($c[0], $c[1], $c[2]);
        }
        $e->setOrder(1);
        $this->addElement( $e, 'config' );

        $e = $this->create("is_active", "checkbox");
        $e->setValue($this->_object->is_active);
        $e->setLabel(__("Is Active", "wpjobboard"));
        $e->setHint(__("Only active listings can be used by job posters.", "wpjobboard"));
        $e->addFilter(new Daq_Filter_Float());
        $e->addOption(1, 1, __("Yes", "wpjobboard"));
        $e->setOrder(1);
        $this->addElement( $e, 'config' );
        

        apply_filters("wpja_form_init_listing", $this);

    }
    
    public function save($append = array()) 
    {
        parent::save($append);
        
        apply_filters("wpja_form_save_listing", $this);
    }
}

?>