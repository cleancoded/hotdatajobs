<?php
/**
 * Description of Application
 *
 * @author greg
 * @package
 */

class Wpjb_Form_Alert extends Daq_Form_ObjectAbstract 
{
    
    protected $_custom = "wpjb_form_alert";
    
    protected $_key = "alert";

    protected $_model = "Wpjb_Model_Alert";
    
    public function init() {  
                
        $this->addGroup("alert", __("Alert Information", "wpjobboard"));
        $this->addGroup("params", __("Alert Search Params", "wpjobboard"));
        $this->addGroup("_internal", "");
        
        $e = $this->create("_wpjb_action", "hidden");
        $this->addElement($e, "_internal");
        
        $e = $this->create("id", "hidden");
        $e->setValue($this->_object->id);
        $this->addElement($e, "_internal");
        
        $e = $this->create("last_run", "hidden");
        $e->setValue($this->_object->last_run);
        $this->addElement($e, "_internal");
        
        /*$e = $this->create("keyword");
        $e->addFilter(new Daq_Filter_Trim());
        $e->setLabel(__("Keyword", "wpjobboard"));
        $e->setValue($this->_object->keyword);
        $e->setHint( __("The keyword is searched in job title, job description, and company name.", "wpjobboard") );
        $this->addElement($e, "alert");*/
        
        $e = $this->create("email");
        $e->addFilter(new Daq_Filter_Trim());
        $e->setLabel(__("E-mail", "wpjobboard"));
        $e->setRequired(true);
        $e->setValue($this->_object->email);
        $this->addElement($e, "alert");
        
        $e = $this->create("frequency", "select");
        $e->addFilter(new Daq_Filter_Int());
        $e->setLabel(__("Frequency", "wpjobboard"));
        $e->setRequired(true);
        $e->addOption(1, 1, __("Daily", "wpjobboard"));
        $e->addOption(2, 2, __("Weekly", "wpjobboard"));
        $e->setValue($this->_object->frequency);
        $this->addElement($e, "alert");
        
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
        
        $e = $this->create("created_at", "hidden");
        if($this->_object->created_at) {
            $e->setValue($this->_object->created_at);
        } else {
            if( is_admin() ) {
                $e->setValue(date("Y-m-d H:i:s"));
            } else {
                $e->setValue( date( wpjb_date_format() ) );
            }
        }
        $this->addElement($e, "_internal");
        
        /*$e = $this->create("params");
        $e->setLabel(__("Params", "wpjobboard"));
        $e->setValue($this->_object->params);
        $e->setRenderer( array( &$this, 'render_params' ) );
        $this->addElement($e, "params");*/
        
        $e = $this->create("params", "hidden");
        $e->setLabel(__("Params", "wpjobboard"));
        $e->setValue($this->_object->params);
        $this->addElement($e, "params");
        
        add_filter("wpja_form_init_alert", array($this, "apply"), 9);
        apply_filters("wpja_form_init_alert", $this);
    }
    
    public function save( $append = array() ) {       
        
        $req = Daq_Request::getInstance();
        $subForm = $this->get_job_subform();
               
        $params = array();
        foreach($req->getAll() as $key => $value) {
            if($subForm->hasElement($key) || $key == 'keyword') {
                $params[$key] = $value;
            } elseif ( $key == "alert_email" ) {
                $params["email"] = $value;
            }
        }

        $this->getElement('params')->setValue(serialize($params));
        
        parent::save($append);
        
        apply_filters("wpja_form_save_alert", $this);
    }

    public function render_params( $param_field ) {
        
        $data = unserialize($param_field->getValue());
        $subForm = $this->get_job_subform();
        
        foreach($subForm->getFields() as $field) {

            if(isset($data[$field->getName()]) && ! empty($data[$field->getName()])) {
                $field->setValue($data[$field->getName()]);
            }
            $field->setRequired(false);
        }
        
        ?>
            <input type="hidden"  name="params" id="params" value="<?php echo esc_html($param_field->getValue()) ?>" />
        <?php
        
        daq_form_layout( $subForm, array("exclude_groups" => "_internal, coupon, wpjobboard-am" ) ); 
    }
    
    public function get_job_subform() {
        
        $subForm = new Wpjb_Form_AddJob();
        
        $subForm->removeElement('job_title');
        $subForm->removeElement('job_description');
        $subForm->removeElement('company_name');
        
        $e = new Daq_Form_Element_Text('keyword');
        $e->setLabel(__('Keyword', "wpjobboard"));
        $subForm->addElement($e,'job');
        
        $e = new Daq_Form_Element_Text('location');
        $e->setLabel(__('Location', "wpjobboard"));
        $subForm->addElement($e,'job');
        
        foreach($subForm->getFields() as $field) {
            
            if($field->getType() == "file") {
                $subForm->removeElement($field->getName());
            }
        }
        
        return $subForm;
    }
}

?>