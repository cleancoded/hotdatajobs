<?php
/**
 * Description of Application
 *
 * @author greg
 * @package
 */

class Wpjb_Form_Admin_Application extends Wpjb_Form_Abstract_Application
{

    public function _exclude()
    {
        if($this->_object->getId()) {
            return array("id" => $this->_object->getId());
        } else {
            return array();
        }
    }

    public function init()
    {
        parent::init();

        $new = !$this->getId();
        
        if($new) {
            $this->getObject()->applied_at = date("Y-m-d");
        }
        
        $this->addGroup("_internal", "");
        
        $query = new Daq_Db_Query();
        $query->select("id, job_title");
        $query->from("Wpjb_Model_Job t");
        $result = $query->fetchAll();
        $e = $this->create("job_id", "select");
        $e->addOption("0", "0", __("- None -", "wpjobboard"));
        $e->setLabel(__("Job", "wpjobboard"));
        if($new) {
            $e->addOption("", "", __("-- Select Job --", "wpjobboard"));
        }
        foreach($result as $o) {
            $e->addOption($o->id, $o->id, $o->job_title);
        }
        $e->setValue($this->_object->job_id);
        $this->addElement($e, "_internal");
        
        if($this->isNew()) {
            $status = wpjb_application_status_default();
        } else {
            $status = $this->_object->status;
        }
        $e = $this->create("status", "select");
        $e->setValue($status);
        $e->setLabel(__("Status", "wpjobboard"));
        $e->addFilter(new Daq_Filter_Int());
        foreach(wpjb_get_application_status() as $key => $status) {
            $e->addOption((string)$key, (string)$key, $status["label"]);
        }
        $this->addElement($e, "_internal");

        if($this->_object->user_id > 0) {
            $user_id = $this->_object->user_id;
            $user_text = get_user_by("id", $this->_object->user_id)->display_name;
        } else {
            $user_id = 0;
            $user_text = __("None", "wpjobboard");
        }
        
        $e = $this->create("user_id", "hidden");
        $e->addFilter(new Daq_Filter_Int());
        $e->setValue($user_id);
        $this->addElement($e, "_internal");
        
        $e = $this->create("user_id_text", "text");
        $e->setAttr("data-target", "user_id");
        $e->setAttr("data-suggest", "wpjb_suggest_user");
        $e->setValue($user_text);
        $this->addElement($e, "_internal");
        
        add_filter("wpja_form_init_application", array($this, "apply"), 9);
        apply_filters("wpja_form_init_application", $this);
    }
    
    public function save($append = array())
    {
        if(!$this->getObject()->getId()) {
            $e = $this->create("applied_at");
            $e->setValue(date("Y-m-d"));
            $this->addElement($e, "application");
        }
        
        $isNew = $this->isNew();
        
        parent::save($append);
        
        if($isNew) {
            $temp = wpjb_upload_dir("application", "", null, "basedir");
            $finl = dirname($temp)."/".$this->getId();
            wpjb_rename_dir($temp, $finl);  
        }
        
        apply_filters("wpja_form_save_application", $this);
    }
}

?>