<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Textarea
 *
 * @author greg
 */
class Daq_Form_Element_Textarea extends Daq_Form_Element
{
    const EDITOR_NONE = 0;
    const EDITOR_TINY = 1;
    const EDITOR_FULL = 2;
    
    protected $_wysiwyg = false;
    
    public function usesEditor()
    {
        if($this->_wysiwyg > 0) {
            return true;
        } else {
            return false;
        }
    }
    
    public final function getType()
    {
        return "textarea";
    }
    
    public function dump()
    {
        $dump = parent::dump();
        $dump->textarea_wysiwyg = $this->getEditor();
        
        $dump->validation_min_size = $this->getAttr("validation_min_size");
        $dump->validation_max_size = $this->getAttr("validation_max_size");
        
        return $dump;
    }
    
    public function getEditor()
    {
        return $this->_wysiwyg;
    }
    
    public function setEditor($editor)
    {
        $this->_wysiwyg = $editor;
    }
    
    public function overload(array $data) 
    {
        parent::overload($data);
        
        if(isset($data["textarea_wysiwyg"])) {
            $this->setEditor($data["textarea_wysiwyg"]);
        }
        
        if(!empty($data["validation_min_size"])) {
            $this->setAttr("validation_min_size", $data["validation_min_size"]);
        }
        
        if(!empty($data["validation_max_size"])) {
            $this->setAttr("validation_max_size", $data["validation_max_size"]);
        }
        
        if( !empty( $data['validation_min_size'] ) || !empty( $data['validation_max_size'] ) ) {
            $this->addValidator( new Daq_Validate_StringLength( $data['validation_min_size'], $data['validation_max_size'] ) );
        }
    }
    
    public function render() 
    {
        global $wp_version;
        
        $options = array(
            "id" => $this->getName(),
            "name" => $this->getName(),
            "class" => $this->getClasses()
        );
        
        $options += $this->getAttr();
        
        $input = new Daq_Helper_Html("textarea", $options, esc_html($this->getValue()));
        $input->forceLongClosing();
        
        if($this->getEditor() > 0 && function_exists("wp_editor")) {

            if(version_compare($wp_version, "4.3", "<")) {
                add_filter('the_editor_content', 'wp_richedit_pre');
            } else {
                add_filter('the_editor_content', 'format_for_editor');
            }
            
            if($this->getEditor() == self::EDITOR_FULL) {
                $params = array();
            } else {
                $params = array(
                    "quicktags"=>false, 
                    "media_buttons"=>false, 
                    "teeny"=>false,
                    'tinymce' => array(
                        'toolbar1' => 'bold,italic,strikethrough,bullist,numlist,blockquote,justifyleft,justifycenter,justifyright,link,unlink,spellchecker,wp_adv',
                        'theme_advanced_buttons2' => 'formatselect,justifyfull,forecolor,pastetext,pasteword,removeformat,charmap,outdent,indent,undo,redo',
                        
                        'theme_advanced_buttons1' => 'bold,italic,strikethrough,bullist,numlist,blockquote,justifyleft,justifycenter,justifyright,link,unlink,spellchecker,wp_adv',
                        'theme_advanced_buttons2' => 'formatselect,justifyfull,forecolor,pastetext,pasteword,removeformat,charmap,outdent,indent,undo,redo',
                     )
                );
            }
            
            ob_start();
            wp_editor($this->getValue(), $this->getName(), apply_filters("wpjb_editor_params", $params, $this));
            return ob_get_clean();
        } else {
            return $input->render();
        }
    }
    
    public function validate()
    {
        $this->_hasErrors = false;
        
        $value = $this->getValue();
        $value = trim($value);
        $this->setValue($value);
        
        if($this->usesEditor()) {
            $value = strip_tags($value);
        }
        
        $value = trim($value);
        
        if(empty($value) && !$this->isRequired()) {
            return true;
        } else {
            $this->addValidator(new Daq_Validate_Required());
        }
        
        $value = trim($this->getValue());
        
        foreach($this->getFilters() as $filter) {
            $value = $filter->filter($value);
        }
        
        $this->setValue($value);
        
        foreach($this->getValidators() as $validator) {
            if(!$validator->isValid($value)) {
                $this->_hasErrors = true;
                $this->_errors = $validator->getErrors();
            }
        }

        return !$this->_hasErrors;
    }
    
    public function htmlEdit()
    {
        return "tinymce";
    }
}

?>
