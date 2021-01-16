<?php
/**
 * Description of Resume
 *
 * @author greg
 * @package 
 */

class Wpjb_Form_Admin_Resume extends Wpjb_Form_Abstract_Resume
{
    public function init() 
    {
        parent::init();
        
        if($this->isNew()) {
            $slug = "";
        } elseif(Wpjb_Project::getInstance()->env("uses_cpt")) {
            $post = get_post($this->_object->post_id);
            $slug = $post->post_name;
        } else {
            $slug = $this->_object->candidate_slug;
        }
        
        $e = $this->create("candidate_slug", "hidden");
        $e->addClass("wpjb-slug-base");
        $e->setValue($slug);
        $this->addElement($e, "_internal");
        
        $e = $this->create("_slug_type", "hidden");
        $e->setValue("resume");
        $e->addClass("wpjb-slug-type");
        $this->addElement($e, "_internal");
        
        $this->initDetails();
        
        add_filter("wpja_form_init_resume", array($this, "apply"), 9);
        apply_filters("wpja_form_init_resume", $this);
    }
    
    public function save($append = array())
    {
        parent::save($append);
        
        $this->saveDetails();
        
        do_action("wpjb_resume_saved", $this->getObject());
        apply_filters("wpja_form_save_resume", $this);
    }
    
}

?>