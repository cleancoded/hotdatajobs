<?php
/**
 * Description of ${name}
 *
 * @author ${user}
 * @package 
 */

class Wpjb_Module_Admin_Custom extends Wpjb_Controller_Admin
{
    public function init()
    {

    }

    private function _sort($a, $b)
    {
        if($a["order"]>$b["order"]) {
            return 1;
        } elseif($a["order"]==$b["order"]) {
            return 0;
        } else {
            return -1;
        }
    }

    private function _getGroup()
    {
        $group = $this->_request->getParam("group");

        uasort($group, array($this, "_sort"));
        foreach($group as $key => $gr) {
            $element = $gr["element"];
            if(is_array($element)) {
                uasort($element, array($this, "_sort"));
            }
            $group[$key]["element"] = $element;
        }

        return $group;
    }

    public function indexAction()
    {
        
    }

    public function editAction()
    {

        
        switch($this->_request->get("form")) {
            case "job" : 
                $form = new Wpjb_Form_AddJob(null, array("display_trashed"=>true)); 
                $this->view->formTitle = __("Add/Edit Job Form", "wpjobboard");
                $this->view->toolbox = "default";
                break;
            case "apply" : 
                $form = new Wpjb_Form_Apply(null, array("display_trashed"=>true)); 
                $this->view->formTitle = __("Apply Online Form", "wpjobboard");
                $this->view->toolbox = "default";
                break;
            case "job-search": 
                $form = new Wpjb_Form_AdvancedSearch(array("display_trashed"=>true)); 
                $form->customFields();
                $this->view->formTitle = __("Advanced Job Search Form", "wpjobboard");
                $this->view->toolbox = "search";
                break;
            case "company": 
                $form = new Wpjb_Form_Frontend_Company(array("display_trashed"=>true), array("display_trashed"=>true));
                $this->view->formTitle = __("Company Form", "wpjobboard");
                $this->view->toolbox = "default";
                break;
            case "resume":
                $form = new Wpjb_Form_Resume(null, array("display_trashed"=>true)); 
                $this->view->formTitle = __("My Resume Form", "wpjobboard");
                $this->view->toolbox = "default";
                break;
            case "resume-search": 
                $form = new Wpjb_Form_ResumesSearch(array("display_trashed"=>true)); 
                $form->customFields();
                $this->view->formTitle = __("Advanced Resume Search Form", "wpjobboard");
                $this->view->toolbox = "search";
                break;
            default: 
                $this->view->formTitle = __("Incorrect Form Name", "wpjobboard");
                return;
        }
        $this->view->form = $form->dump();
        $this->view->formName = str_replace("-", "_", $this->_request->get("form"));
       
    }

    private function _handle($form, $param)
    {
        $this->_forced($form);

        if($this->isPost() && $this->hasParam("reset")) {
            $conf = Wpjb_Project::getInstance();
            $conf->setConfigParam($param, null);
            $conf->saveConfig();
            $this->view->_flash->addInfo(__("Form layout has been reset.", "wpjobboard"));
        }
        elseif($this->isPost()) {
            $conf = Wpjb_Project::getInstance();
            $conf->setConfigParam($param, $this->_getGroup());
            $conf->saveConfig();
            $this->view->_flash->addInfo(__("Form layout has been saved.", "wpjobboard"));
        }

        $form = new $form(null, false);
        $this->view->scheme = $form->getFinalScheme();
    }

    private function _forced($form)
    {
        $arr = array(
            "Wpjb_Form_Apply" => array(),
            "Wpjb_Form_Admin_Resume" => array(),
            "Wpjb_Form_AddJob" => array("job_type", "job_category", "category_id")
        );

        $this->view->forced = $arr[$form];
    }
}

?>
