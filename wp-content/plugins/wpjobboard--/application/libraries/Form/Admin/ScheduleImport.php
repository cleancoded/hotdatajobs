<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ScheduleImport
 *
 * @author Grzegorz
 */
class Wpjb_Form_Admin_ScheduleImport extends Daq_Form_ObjectAbstract
{
    protected $_model = "Wpjb_Model_Import";

    public function init()
    {
        $this->addGroup("default");
        
        $e = $this->create("engine", "select");
        $e->setLabel(__("Import From", "wpjobboard"));
        $e->addOption("indeed", "indeed", __("Indeed", "wpjobboard"));
        $e->addOption("careerbuilder", "careerbuilder", __("CareerBuilder", "wpjobboard"));
        $e->setValue($this->getObject()->engine);
        $this->addElement($e, "default");
        
        $e = $this->create("keyword");
        $e->setLabel(__("Keyword", "wpjobboard"));
        $e->setValue($this->getObject()->keyword);
        $this->addElement($e, "default");
        
        $e = $this->create("category_id", "select");
        $e->setLabel(__("Import Category", "wpjobboard"));
        $e->setHint(__("All jobs will be imported to the category selected above.", "wpjobboard"));
        $e->setValue($this->getObject()->category_id);
        foreach(wpjb_get_categories() as $c) {
            $e->addOption($c->id, $c->id, $c->title);
        }
        $this->addElement($e, "default");
        
        $e = $this->create("country", "select");
        $e->setLabel(__("Job Country", "wpjobboard"));
        $e->addOption("US", "US", __("United States", "wpjobboard"));
        $e->addOption("UK", "UK", __("United Kingdom", "wpjobboard"));
        $e->addOption("AR", "AR", __("Argentina", "wpjobboard"));
        $e->addOption("AU", "AU", __("Australia", "wpjobboard"));
        $e->addOption("AT", "AT", __("Austria", "wpjobboard"));
        $e->addOption("BH", "BH", __("Bahrain", "wpjobboard"));
        $e->addOption("BE", "BE", __("Belgium", "wpjobboard"));
        $e->addOption("BR", "BR", __("Brazil", "wpjobboard"));
        $e->addOption("CA", "CA", __("Canada", "wpjobboard"));
        $e->addOption("CL", "CL", __("Chile", "wpjobboard"));
        $e->addOption("CN", "CN", __("China", "wpjobboard"));
        $e->addOption("CO", "CO", __("Colombia", "wpjobboard"));
        $e->addOption("CZ", "CZ", __("Czech Republic", "wpjobboard"));
        $e->addOption("DK", "DK", __("Denmark", "wpjobboard"));
        $e->addOption("FI", "FI", __("Finland", "wpjobboard"));
        $e->addOption("FR", "FR", __("France", "wpjobboard"));
        $e->addOption("DE", "DE", __("Germany", "wpjobboard"));
        $e->addOption("GR", "GR", __("Greece", "wpjobboard"));
        $e->addOption("HK", "HK", __("Hong Kong", "wpjobboard"));
        $e->addOption("HU", "HU", __("Hungary", "wpjobboard"));
        $e->addOption("IN", "IN", __("India", "wpjobboard"));
        $e->addOption("ID", "ID", __("Indonesia", "wpjobboard"));
        $e->addOption("IE", "IE", __("Ireland", "wpjobboard"));
        $e->addOption("IL", "IL", __("Israel", "wpjobboard"));
        $e->addOption("IT", "IT", __("Italy", "wpjobboard"));
        $e->addOption("JP", "JP", __("Japan", "wpjobboard"));
        $e->addOption("KR", "KR", __("Korea", "wpjobboard"));
        $e->addOption("KW", "KW", __("Kuwait", "wpjobboard"));
        $e->addOption("LU", "LU", __("Luxembourg", "wpjobboard"));
        $e->addOption("MY", "MY", __("Malaysia", "wpjobboard"));
        $e->addOption("MX", "MX", __("Mexico", "wpjobboard"));
        $e->addOption("NL", "NL", __("Netherlands", "wpjobboard"));
        $e->addOption("NZ", "NZ", __("New Zeland", "wpjobboard"));
        $e->addOption("NO", "NO", __("Norway", "wpjobboard"));
        $e->addOption("OM", "OM", __("Oman", "wpjobboard"));
        $e->addOption("PK", "PK", __("Pakistan", "wpjobboard"));
        $e->addOption("PE", "PE", __("Peru", "wpjobboard"));
        $e->addOption("PH", "PH", __("Philippines", "wpjobboard"));
        $e->addOption("PL", "PL", __("Poland", "wpjobboard"));
        $e->addOption("PT", "PT", __("Portugal", "wpjobboard"));
        $e->addOption("QA", "QA", __("Qatar", "wpjobboard"));
        $e->addOption("RO", "RO", __("Romania", "wpjobboard"));
        $e->addOption("RU", "RU", __("Russia", "wpjobboard"));
        $e->addOption("SA", "SA", __("Saudi Arabia", "wpjobboard"));
        $e->addOption("SG", "SG", __("Singapore", "wpjobboard"));
        $e->addOption("ZA", "ZA", __("South Africa", "wpjobboard"));
        $e->addOption("ES", "ES", __("Spain", "wpjobboard"));
        $e->addOption("SE", "SE", __("Sweden", "wpjobboard"));
        $e->addOption("CH", "CH", __("Switzerland", "wpjobboard"));
        $e->addOption("TW", "TW", __("Taiwan", "wpjobboard"));
        $e->addOption("TR", "TR", __("Turkey", "wpjobboard"));
        $e->addOption("AE", "AE", __("United Arab Emirates", "wpjobboard"));
        $e->addOption("VE", "VE", __("Venezuela", "wpjobboard"));
        $e->setValue($this->getObject()->country);
        $this->addElement($e, "default");
        
        $e = $this->create("location");
        $e->setLabel(__("Job Location", "wpjobboard"));
        $e->setValue($this->getObject()->location);
        $this->addElement($e, "default");
        
        $e = $this->create("posted", "select");
        $e->setLabel(__("Posted within", "wpjobboard"));
        $e->setValue($this->getObject()->posted);
        $e->addOption(3, 3, __("3 days", "wpjobboard"));
        $e->addOption(7, 7, __("7 days", "wpjobboard"));
        $e->addOption(30, 30, __("30 days", "wpjobboard"));
        $this->addElement($e, "default");
        
        $e = $this->create("add_max", "select");
        $e->setLabel(__("Add Jobs", "wpjobboard"));
        $e->setHint(__("Maximum number of jobs to add per import.", "wpjobboard"));
        $e->setValue($this->getObject()->add_max);
        $e->addOption(1, 1, 1);
        $e->addOption(5, 5, 5);
        $e->addOption(10, 10, 10);
        $e->addOption(25, 25, 25);
        $this->addElement($e, "default");
        
        apply_filters("wpja_form_init_import", $this);
        
    }
    
    public function save($append = array())
    {
        $append = array();
        
        if($this->isNew()) {
            $append = array(
                "last_run" => "0000-00-00 00:00:00",
                "success" => "0"
            );
        }
        
        parent::save($append);
        
        apply_filters("wpja_form_save_import", $this);
    }
    
}

?>
