<?php
/**
 * Description of Config
 *
 * @author greg
 * @package 
 */

class Wpjb_Module_Admin_Config extends Wpjb_Controller_Admin
{
    public $config = null;
    
    public function init()
    {
        $config = array(
            array(
                "title" => __("Configuration", "wpjobboard"),
                "order" => 0,
                "item" => array(
                    array(
                        "form" => "license",
                        "class" => "Wpjb_Form_Admin_Config_License",
                        "icon" => "wpjb-icon-key",
                        "title" => __("License", "wpjobboard"),
                        "order" => 0
                    ),
                    array(
                        "form" => "main",
                        "class" => "Wpjb_Form_Admin_Config_Main",
                        "icon" => "wpjb-icon-pencil",
                        "title" => __("Common Settings", "wpjobboard"),
                        "order" => 1
                    ),
                    array(
                        "form" => "jobs",
                        "class" => "Wpjb_Form_Admin_Config_Jobs",
                        "icon" => "wpjb-icon-briefcase",
                        "title" => __("Job Board Options", "wpjobboard"),
                        "order" => 2
                    ),
                    array(
                        "form" => "resumes",
                        "class" => "Wpjb_Form_Admin_Config_Resumes",
                        "icon" => "wpjb-icon-user",
                        "title" => __("Resumes Options", "wpjobboard"),
                        "order" => 3
                    ),
                    array(
                        "form" => "taxes",
                        "class" => "Wpjb_Form_Admin_Config_Taxes",
                        "icon" => "wpjb-icon-calc",
                        "title" => __("Taxes", "wpjobboard"),
                        "order" => 4
                    ),
                    array(
                        "form" => "urls",
                        "class" => "Wpjb_Form_Admin_Config_Urls",
                        "icon" => "wpjb-icon-link",
                        "title" => __("Default Pages and URLs", "wpjobboard"),
                        "order" => 5
                    ),
                    array(
                        "form" => "spam",
                        "class" => "Wpjb_Form_Admin_Config_Spam",
                        "icon" => "wpjb-icon-user-secret",
                        "title" => __("Anti-SPAM", "wpjobboard"),
                        "order" => 6
                    ),
                    array(
                        "form" => "android",
                        "class" => "Wpjb_Form_Admin_Config_Android",
                        "icon" => "wpjb-icon-android",
                        "title" => __("Android App", "wpjobboard"),
                        "order" => 100
                    ),
                )
            ),
            array(
                "title" => __("Integrations", "wpjobboard"),
                "order" => 1,
                "item" => array(
                    array(
                        "form" => "facebook",
                        "class" => "Wpjb_Form_Admin_Config_Facebook",
                        "icon" => "wpjb-icon-facebook",
                        "title" => __("Facebook", "wpjobboard"),
                        "order" => 0,
                        "action" => "facebook"
                    ),
                    array(
                        "form" => "twitter",
                        "class" => "Wpjb_Form_Admin_Config_Twitter",
                        "icon" => "wpjb-icon-twitter",
                        "title" => __("Twitter", "wpjobboard"),
                        "order" => 1
                    ),
                    array(
                        "form" => "linkedin",
                        "class" => "Wpjb_Form_Admin_Config_Linkedin",
                        "icon" => "wpjb-icon-linkedin",
                        "title" => __("LinkedIn", "wpjobboard"),
                        "order" => 2
                    ),
                    array(
                        "form" => "google",
                        "class" => "Wpjb_Form_Admin_Config_Google",
                        "icon" => "wpjb-icon-google",
                        "title" => __("Google APIs", "wpjobboard"),
                        "order" => 3
                    ),
                    array(
                        "form" => "google-for-jobs",
                        "class" => "Wpjb_Form_Admin_Config_GoogleForJobs",
                        "icon" => "wpjb-icon-google",
                        "title" => __("Google For Jobs", "wpjobboard"),
                        "order" => 4,
                        "action" => "googleforjobs"
                    ),
                    array(
                        "form" => "recaptcha",
                        "class" => "Wpjb_Form_Admin_Config_Recaptcha",
                        "icon" => "wpjb-icon-shield",
                        "title" => __("reCAPTCHA", "wpjobboard"),
                        "order" => 5
                    ),
                    /*array(
                        "form" => "xing",
                        "class" => "Wpjb_Form_Admin_Config_Xing",
                        "icon" => "wpjb-icon-xing",
                        "title" => __("Xing.com", "wpjobboard"),
                        "order" => 6
                    ),*/
                    array(
                        "form" => "careerbuilder",
                        "class" => "Wpjb_Form_Admin_Config_Careerbuilder",
                        "icon" => "wpjb-icon-puzzle",
                        "title" => __("Careerbuilder.com", "wpjobboard"),
                        "order" => 7
                    ),
                    array(
                        "form" => "indeed",
                        "class" => "Wpjb_Form_Admin_Config_Indeed",
                        "icon" => "wpjb-icon-puzzle",
                        "title" => __("Indeed.com", "wpjobboard"),
                        "order" => 8
                    ),
                    array(
                        "form" => "ziprecruiter",
                        "class" => "Wpjb_Form_Admin_Config_Ziprecruiter",
                        "icon" => "wpjb-icon-puzzle",
                        "title" => __("ZipRecruiter.com", "wpjobboard"),
                        "order" => 9
                    ),
                )
            )
        );
        
        $config = apply_filters("wpjb_config_sections", $config);
        
        uasort($config, array($this, "_order"));
        
        foreach($config as $k => $group) {
            uasort($config[$k]["item"], array($this, "_order"));
        }
        
        $this->config = $config;
        $this->view->config = $this->config;
    }
    
    protected function _order($a, $b)
    {
        if($a["order"] >= $b["order"]) {
            return 1;
        } else {
            return -1;
        }
    }
    
    public function indexAction()
    {

    }

    public function editAction()
    {
        $section = $this->_request->getParam("form");
        $item = null;

        foreach($this->config as $group) {
            foreach($group["item"] as $item) {
                if($item["form"] == $section) {
                    break 2;
                }
            }
        }
        
        if($item === null) {
            return false;
        }
        
        $this->view->show_form = true;
        $this->view->submit_action = "";
        $this->view->submit_title = __("Update", "wpjobboard");
        $this->view->section = $section;
        
        $fList = array();
        
        $class = $item["class"];
        $form = new $class;
        $template = "edit";
        
        if(method_exists($form, "executeInit")) {
            $form->executeInit($this);
        }

        if($this->isPost() && apply_filters("_wpjb_can_save_config", $this)) {

            $isValid = $form->isValid($this->_request->getAll());

            if($isValid) {
                $instance = Wpjb_Project::getInstance();

                foreach($form->getValues() as $k => $v) {
                    $instance->setConfigParam($k, $v);
                }
                
                $instance->saveConfig();
                $this->_addInfo(__("Configuration saved.", "wpjobboard"));
                // if form has postSave
                
                if(method_exists($form, "executePostSave")) {
                    $form->executePostSave($this);
                }
                
            } else {
                $this->_addError(__("There are errors in the form.", "wpjobboard"));
                // if form has postError
            }
        }
        
        if($this->_request->getParam("saventest")) {
            $list = new Daq_Db_Query();
            $list->select("*");
            $list->from("Wpjb_Model_Job t");
            $list->limit(1);
            $result = $list->execute();
            
            if(empty($result)) {
                $this->_addError(__("Twitter: You need to have at least one posted job to send test tweet.", "wpjobboard"));
            } else {
                $job = $result[0];
                try {
                    Wpjb_Service_Twitter::tweetTest($job);
                    $this->_addInfo(__("Tweet has been posted, please check your Twitter account.", "wpjobboard"));
                } catch(Exception $e) {
                    $this->_addError($e->getMessage());
                }
            }
        }
        
        $this->view->form = $form;
        $this->view->fList = $fList;
        
        return $template;
    }
    
    public function healthAction()
    {
        global $wpdb;
        
        $result = $wpdb->get_results("SHOW TABLE STATUS FROM `".DB_NAME."` ");
        $crashed = array();
        
        foreach($result as $r) {
            if(empty($r->Engine)) {
                $crashed[] = $r->Name;
            }
        }
        
        $this->view->crashed = $crashed;
 
    }
    
    public function feedsAction()
    {
        $this->view->agg = array(
            "indeed" => "Indeed",
            "trovit" => "Trovit",
            "simplyhired" => "Simply Hired",
            "juju" => "Juju",
            "xing" => "Xing"
        );
    }
    
    public function paymentAction()
    {
        $engine = $this->_request->get("engine");
        
        if(!Wpjb_Project::getInstance()->payment->hasEngine($engine)) {
            wp_die(__("Selected payment engine does not exist.", "wpjobboard"));
        }
        
        $payment_class = Wpjb_Project::getInstance()->payment->getEngine($engine);
        $payment = new $payment_class;
        
        $form_class = $payment->getForm();
        $form = new $form_class(array(), $payment->conf());
        
        if($this->isPost() && apply_filters("_wpjb_can_save_config", $this)) {
            $isValid = $form->isValid($this->_request->getAll());
 
            if($isValid) {
                foreach($form->getValues() as $k => $v) {
                    $payment->set($k, $v);
                }
                $payment->save();
                
                $this->_addInfo(__("Configuration saved.", "wpjobboard"));
                
            } else {
                $this->_addError(__("There are errors in the form.", "wpjobboard"));
            }
        }
        
        $this->view->title = sprintf(__("Payment Method: %s", "wpjobboard"), $payment->getTitle());
        $this->view->form = $form;
    }
    

    
    public function __call($name, $args) {

        if(stripos($name, "Action") === false) {
            return;
        }
        
        $index = str_replace("Action", "", $name);
        $class = "Wpjb_Module_Admin_Config_Facebook";
        
        if(!class_exists($class)) {
            wp_die("[$class] does not compute");
        }
        
        $controller = new $class;
        $action = $name;
        
        $view = Wpjb_Project::getInstance()->getAdmin()->getView();
        $view->addDir('');
        
        try {
            $controller->setView($view);
            $controller->init();
            $result = $controller->$action();
        } catch(Daq_Controller_Redirect_Exception $e) {
            $result = false;
        }

        if($result !== false) {
            $controller->view->render($result);
        }
        
        return false;
        
    }
    
    public function googleforjobsAction() {
        $this->editAction();
        return "google-for-jobs";
    }
    
}

?>