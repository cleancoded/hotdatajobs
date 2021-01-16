<?php
/**
 * Description of Email
 *
 * @author greg
 * @package 
 */

class Wpjb_Form_Admin_Email extends Daq_Form_ObjectAbstract
{
    protected $_model = "Wpjb_Model_Email";

    public function init()
    {
        add_action("media_buttons", array($this, "mediaButtons"), 5);
        add_filter("wpjb_editor_params", array($this, "editorParams"));
        
        add_filter("mce_external_plugins", array($this, "mceExternalPlugins"));
        add_filter('mce_buttons', array($this, 'mceButtons'));
            
        if($this->isNew()) {
            
            $query = new Daq_Db_Query();
            $query->from("Wpjb_Model_Email t");
            $query->where("sent_to <> 4");
            $result = $query->execute();
            
            $e = $this->create("template_parent", "select");
            $e->setLabel(__("Parent Template", "wpjobboard"));
            $e->setEmptyOption(true);
            foreach($result as $tpl) {
                $e->addOption($tpl->name, $tpl->name, $tpl->mail_title);
            }
            $this->addElement($e);
            
            $e = $this->create("template_name");
            $e->setLabel(__("Template Name", "wpjobboard"));
            $e->setRequired(true);
            $e->addValidator(new Daq_Validate_StringLength(1, 15));
            $this->addElement($e);
            
        } else {
            $e = $this->create("id", "hidden");
            $e->setRequired(true);
            $e->setValue($this->_object->id);
            $e->addFilter(new Daq_Filter_Int());
            $e->addValidator(new Daq_Validate_Db_RecordExists($this->_model, "id"));
            $this->addElement($e);
            
            $e = $this->create("template_name");
            $e->setLabel(__("Template Name", "wpjobboard"));
            $e->setAttr("readonly", "readonly");
            $e->setValue($this->getObject()->name);
            $this->addElement($e);
        }
        
        $e = $this->create("is_active", "checkbox");
        $e->setValue($this->_object->is_active);
        $e->setLabel(__("Activity", "wpjobboard"));
        $e->addFilter(new Daq_Filter_Int);
        $e->addOption(1, 1, __("Enable this email notification.", "wpjobboard"));
        $this->addElement($e);

        $e = $this->create("mail_from_name");
        $e->setRequired(true);
        $e->setValue($this->_object->mail_from_name);
        $e->setLabel(__("Sender Name", "wpjobboard"));
        $this->addElement($e);

        $e = $this->create("mail_from");
        $e->setRequired(true);
        $e->setValue($this->_object->mail_from);
        $e->setLabel(__("Sender Email", "wpjobboard"));
        $e->addValidator(new Daq_Validate_Email());
        $this->addElement($e);
        
        $e = $this->create("mail_bcc");
        $e->setValue($this->_object->mail_bcc);
        $e->setLabel(__("BCC", "wpjobboard"));
        $e->setHint(__("List email address to which this email should be sent as hidden copy, separate emails with comma.", "wpjobboard"));
        $this->addElement($e);
        
        $e = $this->create("mail_title");
        $e->setRequired(true);
        $e->setValue($this->_object->mail_title);
        $e->setLabel(__("Email Title", "wpjobboard"));
        $e->addValidator(new Daq_Validate_StringLength(1, 120));
        $this->addElement($e);
        
        $e = $this->create("format", "select");
        $e->addClass("wpjb-mail-body-select");
        $e->setRequired(true);
        $e->setValue($this->_object->format);
        $e->setLabel(__("Email Format", "wpjobboard"));
        $e->addOption("text/plain", "text/plain", __("Plain Text", "wpjobboard"));
        $e->addOption("text/html", "text/html", __("HTML", "wpjobboard"));
        $e->addOption("text/html-advanced", "text/html-advanced", __("HTML (Advanced)", "wpjobboard"));
        $this->addElement($e);
        
        $e = $this->create("mail_body_text", "textarea");
        $e->addClass("wpjb-mail-body");
        $e->setValue($this->_object->mail_body_text);
        $e->setLabel(__("Body Text", "wpjobboard"));
        $e->setRenderer(array($this, "plainText"));
        $this->addElement($e);
        
        $e = $this->create("mail_body_html", "textarea");
        $e->addClass("wpjb-mail-body");
        $e->setValue($this->_object->mail_body_html);
        $e->setLabel(__("Body HTML", "wpjobboard"));
        $e->setEditor(Daq_Form_Element_Textarea::EDITOR_FULL);
        $this->addElement($e);
        
        $e = $this->create("mail_body_html_advanced");
        $e->addClass("wpjb-mail-body-html-advanced");
        $e->setValue($this->_object->mail_body_html);
        $e->setLabel(__("Body HTML", "wpjobboard"));
        $e->setRenderer(array($this, "aceBody"));
        $this->addElement($e);
        
        apply_filters("wpja_form_init_email", $this);
    }
    
    public function plainText($e) {
        $input = '<div class="wpjb-plain-text-media-buttons wp-media-buttons">';
        $input.= '<a href="#" class="button wpjb-email-text-insert-var"><span class="dashicons dashicons-book-alt"></span> '.__("Insert Variable", "wpjobboard").'</a>';
        $input.= '<a href="#" class="button wpjb-email-text-preview"><span class="dashicons dashicons-search"></span> '.__("Preview", "wpjobboard").'</a>';
        $input.= '</div>';
        $input.= $e;
        return $input;
    }
    
    public function isValid(array $values) 
    {
        if($values["format"] == "text/plain") {
            $this->getElement("mail_body_text")->setRequired(true);
        } elseif($values["format"] == "text/html") {
            $this->getElement("mail_body_html")->setRequired(true);
        } else {
            $this->getElement("mail_body_html_advanced")->setRequired(true);
        }
        
        return parent::isValid($values);
    }
    
    public function save($append = array()) 
    {
        if($this->isNew()) {
            $append["name"] = $this->value("template_parent") . "-" . $this->value("template_name");
            $append["sent_to"] = 5;
        }
        
        if($this->value("format") == "text/html-advanced") {
            $this->getElement("mail_body_html")->setValue($this->value("mail_body_html_advanced"));
        }
        
        parent::save($append);
        
        apply_filters("wpja_form_save_email", $this);
    }
    
    public function mediaButtons($editor)
    {
        echo '<button type="button" class="button wpjb-email-template-media wpjb-email-html-insert-var"><span class="wp-media-buttons-icon icon-variable"></span> Insert Variable</button>';
        echo '<button type="button" id="wpjb-email-html-preview" class="button wpjb-email-template-media"><span class="wp-media-buttons-icon icon-preview"></span> Preview</button>';
    }
    
    public function editorParams($params) 
    {
        if(!isset($params["tinymce"]) || !is_array($params["tinymce"])) {
            $params["tinymce"] = array();
        }
        
        $params["tinymce"]["content_css"] = admin_url("admin-ajax.php?action=wpjb_email_plain&edit=css&tinymce=1");

        return $params;
    }
    
    public function mceButtons($buttons) {
    
        array_push($buttons, "separator", "wpjbbutton");
        return $buttons;
    }
    
    public function mceExternalPlugins($plugin_array) {
        $plugin_array['wpjbbutton'] = plugins_url()."/wpjobboard/public/js/wpjb-tinymce-button.js";
        
        return $plugin_array;
    }
    
    public function aceBody(Daq_Form_Element $e) {
        wp_enqueue_script("wpjb-ace");

        $html = new Daq_Helper_Html("div", array(
            "id" => $e->getName(),
            
        ), esc_html($e->getValue()));
        $html->forceLongClosing(true);
        
        $input = '<div class="wpjb-plain-text-media-buttons wp-media-buttons">';
        $input.= '<a href="#" class="button wpjb-email-advanced-insert-var"><span class="dashicons dashicons-book-alt"></span> '.__("Insert Variable", "wpjobboard").'</a>';
        $input.= '<a href="#" class="button wpjb-email-advanced-preview"><span class="dashicons dashicons-search"></span> '.__("Preview", "wpjobboard").'</a>';
        $input.= '</div>';
        $input.= '<textarea name="'.$e->getName().'" style="display:none">'.esc_html($e->getValue()).'</textarea>';
        $input.= $html->render();
        
        return $input;
    }
    
}



?>