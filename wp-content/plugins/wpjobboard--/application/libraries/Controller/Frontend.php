<?php
/**
 * Description of Frontend
 *
 * @author greg
 * @package 
 */

class Wpjb_Controller_Frontend extends Daq_Controller_Abstract
{
    private $_object = null;

    public function __construct()
    {
        wp_enqueue_script("jquery");

        if(Wpjb_Project::getInstance()->conf("mode")==1) {
            add_filter("wp_title", array($this, "_injectTitle"));
        }
        
        parent::__construct();
    }
    
    public function glyph()
    {
        if(is_rtl()) {
            return "wpjb-icon-left-open";
        } else {
            return "wpjb-icon-right-open";
        }
    }

    public function _injectTitle()
    {
        if(strlen(Wpjb_Project::getInstance()->title)>0) {
            return esc_html(Wpjb_Project::getInstance()->title)." \r\n";
        }
    }

    public function setCanonicalUrl($url)
    {
        Wpjb_Project::getInstance()->setEnv("canonical", $url);
    }

    public function setObject(Daq_Db_OrmAbstract $object)
    {
        $this->_object = $object;
    }

    /**
     * Returns object resolved during request dispatch
     *
     * @return Daq_Db_OrmAbstract
     * @throws Exception If trying to get object before it was set
     */
    public function getObject()
    {
        if(!$this->_object instanceof Daq_Db_OrmAbstract) {
            throw new Exception("Object is not instanceof Daq_Db_OrmAbstract");
        }
        return $this->_object;
    }

    /**
     *
     * @param <type> $module
     * @return Daq_Router
     */
    protected function _getRouter($module = "frontend")
    {
        return Wpjb_Project::getInstance()->getApplication($module)->getRouter();
    }

    /**
     * Returns Current Request Object
     * 
     * @return Daq_Request
     */
    public function getRequest()
    {
        return $this->_request;
    }

    public function isMember()
    {
        $info = wp_get_current_user();
        $isAdmin = true;
        if($info->ID > 0) {
            return true;
        } else {
            return false;
        }
    }

    protected function _isUserAdmin()
    {
        $info = wp_get_current_user();
        $isAdmin = true;
        if(!isset($info->wp_capabilities) || !$info->wp_capabilities['administrator']) {
            $isAdmin = false;
        }
        return $isAdmin;
    }
    
    public function setTitle($text, $param = array())
    {
        $k = array();
        $v = array();
        foreach($param as $key => $value) {
            $k[] = "{".$key."}";
            $v[] = esc_html($value);
        }

        $title = apply_filters("wpjb_set_title", rtrim(str_replace($k, $v, $text))." ");
        
        Wpjb_Project::getInstance()->title = $title;
    }
    
    public function redirect($url) 
    {
        do_action("wpjb_redirect", $url, $this);
        
        $this->view->_flash->save();
        wp_redirect($url);
        exit;
    }
    
    public function redirectIf($condition, $url) 
    {
        if($condition) {
            $this->redirect($url);
        }
    }
    
    public function readableQuery($params, $form1, $form2) 
    {
        $param = array();
        $ignore = array("page", "page_id", "pg", "results");
        
        foreach($params as $k => $value) {
            
            if(is_string($value)) {
                $value = trim($value);
            }
            
            if(empty($value)) {
                continue;
            }
            
            if(in_array($k, $ignore)) {
                continue;
            }
            
            
            switch($k) {
                case "job_id":
                case "job": 
                    $object = new Wpjb_Model_Job($value);
                    $value = $object->job_title;
                    break;
                case "employer_id":
                    $object = new Wpjb_Model_Company($value);
                    $value = $object->company_name;
                    break;
                case "country":
                    $country = Wpjb_List_Country::getByCode($value);
                    $value = $country["name"];
                case "type":
                case "category":
                    $data = (array)$value;
                    $value = array();
                    foreach((array)$data as $v) {
                        $object = new Wpjb_Model_Tag($v);
                        $value[$v] = $object->title;
                    }
                    break;
                case "posted":
                    $arr = array(
                        1 => __("Today", "wpjobboard"),
                        2 => __("Since Yesterday", "wpjobboard"),
                        7 => __("Less than 7 days ago", "wpjobboard"),
                        30=> __("Less than 30 days ago", "wpjobboard")
                    );
                    $value = $arr[$value];
                    break;
                    
            }
            
            if(!is_array($value)) {
                $value = array($value);
            }
            
            if(substr($k, -3) == "_id") {
                $key = str_replace("_id", "", $k);
            } else {
                $key = $k;
            }
            
            if($form1->hasElement($key)) {
               $label = $form1->getElement($key)->getLabel(); 
            } elseif($form2->hasElement($key)) {
                $label = $form2->getElement($key)->getLabel(); 
            } else {
                $label = ucfirst(str_replace("_", " ", $key));
            }
            
            $param[$k] = array("param"=>$label, "value"=>$value);
        }

        return $param;
    }
    
}

?>