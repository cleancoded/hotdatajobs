<?php
/**
 * Description of Index
 *
 * @author greg
 * @package 
 */

class Wpjb_Module_Frontend_Index extends Wpjb_Controller_Frontend
{
    private $_perPage = 20;

    public function init()
    {   
        $this->_perPage = wpjb_conf("front_jobs_per_page", 20);
        $this->view->placeholder = false;
        $this->view->query = null;
        $this->view->pagination = true;
        $this->view->format = null;
        $this->view->atts = array();
    }
    
    public function indexAction()
    {   
        $this->setTitle(get_option('blogname'));
        $this->setCanonicalUrl(Wpjb_Project::getInstance()->getUrl());
        
        $param = array(
            "filter" => "active",
            "page" => $this->_request->get("page", 1),
            "query" => $this->_request->get("query"),
            "location" => $this->_request->get("location"),
            "type" => $this->_request->get("type"),
            "category" => $this->_request->get("category"),
            "count" => $this->_perPage
        );

        $this->view->search_bar = wpjb_conf("search_bar", "disabled");
        $this->view->search_init = array();
        $this->view->param = $param;
        $this->view->url = wpjb_link_to("home");
        $this->view->page_id = Wpjb_Project::getInstance()->conf("link_jobs");
    }

    public function companyAction()
    {
        $company = $this->getObject();
        /* @var $company Wpjb_Model_Employer */

        $text = wpjb_conf("seo_job_employer", __("{company_name}", "wpjobboard"));
        $param = array(
            'company_name' => $this->getObject()->company_name
        );
        $this->setTitle($text, $param);

        if(Wpjb_Model_Company::current() && Wpjb_Model_Company::current()->id==$company->id) {
            // do nothing
        } elseif($company->is_active == Wpjb_Model_Company::ACCOUNT_INACTIVE) {
            $this->view->_flash->addError(__("Company profile is inactive.", "wpjobboard"));
        } elseif(!$company->is_public) {
            $this->view->_flash->addInfo(__("Company profile is hidden.", "wpjobboard"));
        } elseif(!$company->isVisible()) {
            $this->view->_flash->addError(__("Company profile will be visible once employer will post at least one job.", "wpjobboard"));
        }

        $this->view->company = $company;
        $this->view->param = array(
            "filter" => "active",
            "employer_id" => $company->id
        );
    }

    public function categoryAction()
    {
        $object = $this->getObject();
        if($object->type != Wpjb_Model_Tag::TYPE_CATEGORY) {
            $this->view->_flash->addError(__("Category does not exist.", "wpjobboard"));
            return false;
        }
        
        $text = wpjb_conf("seo_category", __("Category: {category}", "wpjobboard"));
        $param = array(
            'category' => $this->getObject()->title
        );

        $this->setCanonicalUrl(wpjb_link_to("category", $this->getObject()));

        $this->view->current_category = $this->getObject();
        $this->setTitle($text, $param);

        $this->view->param = array(
            "filter" => "active",
            "page" => $this->_request->get("page", 1),
            "count" => $this->_perPage,
            "category" => $this->getObject()->id
        );
        
        $this->view->search_bar = wpjb_conf("search_bar", "disabled");
        $this->view->search_init = array("category" => $this->getObject()->id);
        $this->view->url = $object->url();
        
        return "index";
    }

    public function typeAction()
    {
        $object = $this->getObject();
        if($object->type != Wpjb_Model_Tag::TYPE_TYPE) {
            $this->view->_flash->addError(__("Job type does not exist.", "wpjobboard"));
            return false;
        }
        
        $text = wpjb_conf("seo_job_type", __("Job Type: {type}", "wpjobboard"));
        $param = array(
            'type' => $this->getObject()->title
        );
        $this->setCanonicalUrl(wpjb_link_to("type", $this->getObject()));

        $this->view->current_type = $this->getObject();
        $this->setTitle($text, $param);

        $this->view->param = array(
            "filter" => "active",
            "page" => $this->_request->get("page", 1),
            "count" => $this->_perPage,
            "type" => $this->getObject()->id
        );
        
        $this->view->search_bar = wpjb_conf("search_bar", "disabled");
        $this->view->search_init = array("type" => $this->getObject()->id);
        $this->view->url = $object->url();
        
        return "index";
    }
    
    public function searchAction()
    {
        $this->setTitle(wpjb_conf("seo_search_results", __("Search Results", "wpjobboard")));
        
        echo wpjb_jobs_search(array(
            "redirect_to" => wpjb_link_to("search")
        ));
        
        return "[did-shortcode]";
    }

    public function advsearchAction()
    {
        $this->setTitle(wpjb_conf("seo_adv_search", __("Advanced Search", "wpjobboard")));
        
        echo wpjb_jobs_search(array(
            "redirect_to" => wpjb_link_to("search")
        ));
        
        return "[did-shortcode]";
    }

    public function singleAction()
    {

        //$this->applyAction();
    }
    
    public function paymentAction()
    {
        $payment = $this->getObject();
        $button = Wpjb_Project::getInstance()->payment->factory($payment);
        
        $this->setTitle(__("Payment", "wpjobboard"));
        
        if($payment->payment_sum == $payment->payment_paid) {
            $this->view->_flash->addInfo(__("This payment was already processed correctly.", "wpjobboard"));
            return false;
        }
        
        if($payment->object_type == 1) {
            $this->view->job = new Wpjb_Model_Job($payment->object_id);
        }
        
        $this->view->payment = $payment;
        $this->view->button = $button;
        $this->view->currency = Wpjb_List_Currency::getCurrencySymbol($payment->payment_currency);
    }
    
    public function alertAction()
    {
        $this->setTitle(__("Job Alerts", "wpjobboard"));

        $request = Daq_Request::getInstance();
        $form = new Wpjb_Form_Frontend_Alert();

        
        if($this->isPost()) {

            if($form->isValid($request->getAll())) {
            
                $alert = new Wpjb_Model_Alert;
                $alert->user_id = get_current_user_id();
                $alert->keyword = $request->post("keyword");
                $alert->email = $request->post("email");
                $alert->created_at = date("Y-m-d H:i:s");
                $alert->last_run = "0000-00-00 00:00:00";
                $alert->frequency = 1;
                $alert->params = serialize(array("filter"=>"active", "keyword"=>$alert->keyword));
                $alert->save();

                $this->view->_flash->addInfo(__("Alert was added to the database.", "wpjobboard"));
                
                return false;
            } else {
                $this->view->_flash->addError(__("Alert could not be added. There was an error in the form.", "wpjobboard"));
            }
        }
        
        $this->view->action = "";
        $this->view->submit = __("Subscribe", "wpjobboard");
        $this->view->form = $form;
        
        return "../default/form";
    }
    
    public function deleteAlertAction()
    {
        $request = Daq_Request::getInstance();
        $this->setTitle(__("Job Alerts", "wpjobboard"));
        $hash = $request->get("hash");
        
        if(empty($hash)) {
            $this->view->_flash->addError(__("Provided hash code is empty.", "wpjobboard"));
            return false;
        }
        
        $query = new Daq_Db_Query();
        $query->from("Wpjb_Model_Alert t");
        $query->where("MD5(CONCAT(t.id, '|', t.email)) = ?", $hash);
        $query->limit(1);
        
        $result = $query->execute();
        
        if(empty($result)) {
            $this->view->_flash->addError(__("Provided hash code is invalid.", "wpjobboard"));
            return false;
        }
        
        $result[0]->delete();
        
        $this->view->_flash->addInfo(__("Alert deleted.", "wpjobboard"));
        
        return false;
    }
}

?>
