<?php
/**
 * Description of ${name}
 *
 * @author ${user}
 * @package 
 */

class Wpjb_Module_Resumes_Index extends Wpjb_Controller_Frontend
{
    private $_perPage = 20;

    protected function _loginForm($redirect)
    {
        $this->view->_flash->addError(__("Login to access this page.", "wpjobboard"));
        
        $form = new Wpjb_Form_Resumes_Login();
        $form->getElement("redirect_to")->setValue($redirect);

        $this->view->action = "";
        $this->view->form = $form;
        $this->view->submit = __("Login", "wpjobboard");
        $this->view->buttons = array(
            array(
                "tag" => "a", 
                "href" => wpjr_link_to("register"), 
                "html" => __("Not a member? Register", "wpjobboard")
            ),
        );
        
        $this->view = apply_filters("wpjb_shortcode_login", $this->view, "candidate");
        
        return "../default/form";
    }
    
    public function init()
    {
        $this->_perPage = Wpjb_Project::getInstance()->conf("front_jobs_per_page", 20);
        $this->view->baseUrl = Wpjb_Project::getInstance()->getUrl("resumes");
        $this->view->query = null;
        $this->view->format = null;
        $this->view->tolock = apply_filters("wpjb_lock_resume", array("user_email", "phone", "user_url"));
    }

    protected function _canView($id)
    {
        $m = null;
        $premium = false;
        $button = array("contact"=>0, "login"=>0, "register"=>0, "purchase"=>0, "verify"=>0);
        $cv_access = wpjb_conf("cv_access");
        $request = Daq_Request::getInstance();
        
        if(Wpjb_Model_Resume::current() && Wpjb_Model_Resume::current()->id == $id) {
            // candidate can always access his resume
            $premium = true;
        }
        if(wpjr_has_premium_access($id)) {
            // if has valid hash, always allow
            $premium = true;
        }
        if(Wpjb_Model_Company::current() && Wpjb_Model_Company::current()->canViewResume($id)) {
            // employer received at least one application from this candidate
            // and employers can view full applicants resumes
            // this option is enabled in wp-admin / Settings (WPJB) / Resumes Options panel
            $premium = true;
        }
        if(current_user_can('manage_options')) {
            // admin can see anything
            $premium = true;
        }
        

        if($premium) {
            // premium user alsways has access
            $button["contact"] = 1;
            
        } elseif(!get_current_user_id()) {
            // not registered user
            if(in_array($cv_access, array(2,3,4,6))) {
                $m = __("Login or register as Employer to contact this candidate.", "wpjobboard");
                $button["login"] = 1;
                $button["register"] = 1;
            } elseif($cv_access == 5) {
                $m = __("Login or purchase this resume contact details.", "wpjobboard");
                $button["login"] = 1;
                $button["purchase"] = 1;
            }
            
        } elseif(current_user_can("manage_jobs")) {
            // employer
            $company = Wpjb_Model_Company::current();
            if($cv_access == 4 && !$company->is_verified) {
                $m = __("You need to verify your account before contacting candidate.", "wpjobboard");
                $button["verify"] = 1;
            } elseif($cv_access == 4 && in_array($company->is_verified, array(Wpjb_Model_Company::ACCESS_PENDING, Wpjb_Model_Company::ACCESS_DECLINED))) {
                $m = __("Your account is pending verification or verification was declined.", "wpjobboard");
                $button["none"] = 1;
            } elseif($cv_access == 5 && !$premium) {
                $m = __("Purchase this resume contact details", "wpjobboard");
                $button["purchase"] = 1;
            } elseif($cv_access == 6) {
                $m = __("Before you will be able to see this resume, the candidate needs to apply for at least one of your jobs.", "wpjobboard");
                $button["none"] = 1;
            }
            
        } elseif(get_current_user_id()) {
            // other registered user
            if(in_array($cv_access, array(3,4,6))) {
                $m = __("Incorrect account type. You need to be registered as Employer to contact Candidates", "wpjobboard");
                $button["none"] = 1;
            } elseif($cv_access == 5) {
                $m = __("Purchase this resume contact details", "wpjobboard");
                $button["purchase"] = 1;
            }
        } else {
            // can contact
            $button["contact"] = 1;
        }
        
        if(array_sum($button) == 0) {
            $button["contact"] = 1;
        }
        
        $this->view->c_message = $m;
        $this->view->button = (object)$button;
    }
    
    protected function _canViewErr()
    {
        $c = (int)wpjb_conf("cv_privacy")."/".(int)wpjb_conf("cv_access");

        switch($c) {
            case "0/2":
                $m = __("Only registered members can contact candidates.", "wpjobboard");
                break;
            case "0/3":
                $m = __("Only Employers can contact candidates.", "wpjobboard");
                break;
            case "0/4":
                $m = __("Only <strong>verified</strong> Employers can contact candidates.", "wpjobboard");
                break;
            case "0/5":
                $m = __("Contacting candidaes requires premium access.", "wpjobboard");
                break;
            case "0/6":
                $m = __("Before you will be able to see this resume, the candidate needs to apply for at least one of your jobs.", "wpjobboard");
                break;
        }
        
        if($m) {
            $this->view->_flash->addError($m);
            $this->view->error_message = $m;
        }
    }
    
    protected function _canBrowseErr()
    {
        $error = wpjr_can_browse_err();
        
        if(!is_null($error)) {
            $this->view->_flash->addError($error);
        }
    }
    
    protected function _canBrowse($id = null)
    {   
        $can = wpjr_can_browse($id);
        $this->view->can_browse = $can;
        return $can;
    }

    protected function getUserPrivs()
    {   
        if(get_current_user_id() < 1) {
            return -2;
        }
        
        if(!current_user_can("manage_resumes")) {
            $this->view->_flash->addError(__("You need to be registered as Candidate in order to access this page. Your current account type is Employer.", "wpjobboard"));
            return -1;
        }
    }
    
    public function homeAction()
    {
        switch($this->getUserPrivs()) {
            case -1: return false; break;
            case -2: return $this->_loginForm(wpjr_link_to("myresume_home")); break;
        }
        
        $this->setTitle(__("My Dashboard", "wpjobboard"));
        
        $dashboard = array(
            "manage" => array(
                "title" => __("Manage", "wpjobboard"),
                "links" => array(
                    "add" => array(
                        "url" => wpjr_link_to("myresume"),
                        "title" => __("My Resume", "wpjobboard"),
                        "icon" => "wpjb-icon-doc-text"
                    ),
                    "jobs" => array(
                        "url" => wpjr_link_to("myapplications"),
                        "title" => __("My Applications", "wpjobboard"),
                        "icon" => "wpjb-icon-inbox"
                    ),
                    "edit" => array(
                        "url" => wpjr_link_to("mybookmarks"),
                        "title" => __("My Bookmarks", "wpjobboard"),
                        "icon" => "wpjb-icon-bookmark"
                    ),
                )
            ),
            "account" => array(
                "title" => __("Account", "wpjobboard"),
                "links" => array(
                    "logout" => array(
                        "url" => wpjr_link_to("logout"),
                        "title" => __("Logout", "wpjobboard"),
                        "icon" => "wpjb-icon-off"
                    ),
                    "password" => array(
                        "url" => wpjr_link_to("myresume_password"),
                        "title" => __("Change Password", "wpjobboard"),
                        "icon" => "wpjb-icon-asterisk"
                    ),
                    "delete" => array(
                        "url" => wpjr_link_to("myresume_delete"),
                        "title" => __("Delete Account", "wpjobboard"),
                        "icon" => "wpjb-icon-trash"
                    ),
                )
            )
        );
        
        $this->view->dashboard = apply_filters("wpjb_candidate_panel_links", $dashboard);
        
        return "my-home";
    }
    
    public function indexAction()
    {
        $text = wpjb_conf("seo_resumes_name", __("Browse Resumes", "wpjobboard"));
        $this->setTitle($text);
        
        if(!$this->_canBrowse()) {
            $this->_canBrowseErr();
            if(wpjb_conf("cv_privacy") == 1) {
                return false;
            }
        }
        
        $param = array(
            "filter" => "active",
            "page" => $this->_request->get("page", 1),
            "count" => $this->_perPage
        );
       
        $this->view->search_bar = wpjb_conf("cv_search_bar", "disabled");
        $this->view->param = $param;
        $this->view->url = wpjr_link_to("home");
        $this->view->page_id = Wpjb_Project::getInstance()->conf("link_resumes");
        
        return "index";
    }

    public function advsearchAction()
    {
        $this->setTitle(wpjb_conf("seo_resume_adv_search", __("Advanced Search", "wpjobboard")));
        
        echo wpjb_resumes_search(array(
            "redirect_to" => wpjr_link_to("search")
        ));
        
        return "[did-shortcode]";
    }

    public function searchAction()
    {
        $text = wpjb_conf("seo_search_resumes", __("Search Results", "wpjobboard"));
        $param = array(
            'keyword' => Daq_Request::getInstance()->get("query")
        );
        $this->setTitle($text, $param);
        
        echo wpjb_resumes_search(array(
            "redirect_to" => wpjr_link_to("search")
        ));
        
        return "[did-shortcode]";
    }

    public function viewAction()
    {
        $this->view->form_error = null;
        $resume = $this->getObject();
        /* @var $resume Wpjb_Model_Resume */
        
        $this->_canView($resume->id);
        
        if(!$this->_canBrowse($resume->id)) {
            if(wpjb_conf("cv_privacy") == 1) {
                $this->_canViewErr();
                return false;
            }
        }
        
        $fullname = apply_filters("wpjb_candidate_name", trim($resume->user->first_name." ".$resume->user->last_name), $resume->id);
        
        $this->setTitle(wpjb_conf("seo_resumes_view", __("{full_name}", "wpjobboard")), array(
            "full_name" => $fullname,
            "headline" => $resume->headline
        ));

        $this->view->current_url = wpjr_link_to("resume", $resume);
        $this->view->resume = $resume;
        
        $f = array();
        $show = array("contact"=>0, "purchase"=>0);
        
        if($this->_request->get("form") == "contact") {
            $show["contact"] = 1;
        }
        if($this->_request->get("form") == "purchase") {
            $show["purchase"] = 1;
        }
        
        if($this->view->button->contact == 1) {
            $f["contact"] = new Wpjb_Form_Resumes_Contact;
        }
        if($this->view->button->purchase == 1) {
            $f["purchase"] = new Wpjb_Form_Resumes_Purchase;
        }
        
        if($this->_request->post("purchase") && isset($f["purchase"])) {
            $valid = $f["purchase"]->isValid($this->_request->getAll());
            
            if($valid) {
                
                list($price_for, $membership_id, $pricing_id) = explode("_", $f["purchase"]->value("listing_type"));
                
                $pricing = new Wpjb_Model_Pricing($pricing_id);
                $hash = md5(uniqid() . "#" . time());
                $granted = false;
                
                if(get_current_user_id()) {
                    $uid = get_current_user_id();
                } else {
                    $uid = "UID" . uniqid();
                }
                
                if($membership_id) {
                    $granted = true;
                    $membership = new Wpjb_Model_Membership($membership_id);
                    $membership->inc($pricing_id);
                    $membership->save();
                    
                    $resume->addAccessKey($uid, $hash);
                } elseif($pricing->price == 0) {
                    $granted = true;
                    $resume->addAccessKey($uid, $hash);
                } 
                
                if($granted && get_current_user_id()) {
                    $params = array("hash"=>$hash, "hash_id"=>$uid);
                    $message = Wpjb_Utility_Message::load("notify_employer_resume_paid");
                    $message->assign("resume", $resume);
                    $message->assign("resume_unique_url", wpjr_link_to("resume", $resume, $params));
                    $message->setTo(wp_get_current_user()->user_email);
                    $message->send();
                }
                
                if($granted) {
                    $this->view->_flash->addInfo(__("Access to resume details has been granted.", "wpjobboard"));
                    $this->_canView($resume->id);
                    $this->_canBrowse($resume->id);
                    $f["contact"] = new Wpjb_Form_Resumes_Contact;
                    add_action("wp_footer", "wpjb_hide_scroll_hash");
                } else {
                    $title = __("Purchase Resume Access", "wpjobboard");
                    $this->setTitle($title);

                    if(Wpjb_Model_Company::current()) {
                        $dName = Wpjb_Model_Company::current()->company_name;
                        $dMail = wp_get_current_user()->user_email;
                    } elseif(wp_get_current_user()) {
                        $dName = wp_get_current_user()->display_name;
                        $dMail = wp_get_current_user()->user_email;
                    } else {
                        $dName = "";
                        $dMail = "";
                    }
                    
                    $this->view->pricing = $pricing;
                    $this->view->gateways = Wpjb_Project::getInstance()->payment->getEnabled();
                    $this->view->pricing_item = __("Resume Access", "wpjobboard") . " &quot;" . $fullname . "&quot;";
                    $this->view->defaults = new Daq_Helper_Html("span", array(
                        "id" => "wpjb-checkout-defaults",
                        "class" => "wpjb-none",

                        "data-object_id" => $resume->id,
                        "data-pricing_id" => $pricing->id,
                        "data-fullname" => $dName,
                        "data-email" => $dMail,

                    ), " ");

                    return "../default/payment";
                }
                
            } else {
                $show["purchase"] = 1;
                $this->view->form_error = __("There are errors in your form", "wpjobboard");
            }
        }
        
        if($this->_request->post("contact")) {
            $valid = $f["contact"]->isValid($this->_request->getAll());
            if($valid) {
                
                $blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
                $body = $f["contact"]->value("message");
                $body.= "\r\n\r\n------------\r\n";
                $body.= $f["contact"]->value("fullname")." ".$f["contact"]->value("email");
                $headers = array(
                    "Reply-to: ".$f["contact"]->value("email")
                );
                
                wp_mail(
                    $resume->getUser(true)->user_email, 
                    sprintf(__('[%1$s] Message from %2$s', "wpjobboard"), $blogname, $f["contact"]->value("fullname")),
                    $body,
                    $headers
                );
                
                $this->view->_flash->addInfo(__("Your message has been sent.", "wpjobboard"));
                add_action("wp_footer", "wpjb_hide_scroll_hash");
            } else {
                $show["contact"] = 1;
                $this->view->form_error = __("There are errors in your form", "wpjobboard");
            }
        }
        
        $this->view->f = $f;
        $this->view->show = (object)$show;
        
        return "resume";
    }

    public function myresumeAction()
    {
        switch($this->getUserPrivs()) {
            case -1: return false; break;
            case -2: return $this->_loginForm(wpjr_link_to("myresume")); break;
        }
        
        $object = Wpjb_Model_Resume::current();
        if(!is_object($object)) {
            $id = null;
            $this->view->disable_details = false;
        } else {
            $id = $object->getId();
            $this->view->disable_details = false;
        }
        
        $this->setTitle(wpjb_conf("seo_resume_my_resume", __("My Resume Details", "wpjobboard")));
        
        $this->view->breadcrumbs = array(
            array("title"=>__("Home", "wpjobboard"), "url"=>wpjr_link_to("myresume_home"), "glyph"=>"wpjb-icon-home"),
            array("title"=>__("My Resume", "wpjobboard"), "url"=>wpjr_link_to("myresume"), "glyph"=>$this->glyph()),
        );
        
        
        
        $form = new Wpjb_Form_Resume($id);
        if($this->isPost() && !$this->_request->post("remove_image")) {
            $isValid = $form->isValid($this->_request->getAll());
            if($isValid) {
                $this->view->_flash->addInfo(__("Your resume has been saved.", "wpjobboard"));
                $form->save();
            } else {
                $this->view->_flash->addError($form->getGlobalError());
            }
        }

        wp_enqueue_script("wpjb-myresume");
        $form->buildPartials();

        $this->view->resume = $form->getObject();
        $this->view->form = $form;

        
        
        
        
        return "my-resume";
    }
    
    public function redirect($path) {
        if(Wpjb_Project::getInstance()->shortcodeIs()) {
            switch($path) {
                case "myresume_edit_default": 
                    $this->_request->addParam("GET", "slug", "default");
                    return $this->editAction();
                    break;
            }
        } else {
            switch($path) {
                case "myresume_edit_default": $url = wpjr_link_to("myresume_edit", null, array("slug"=>"default")); break;
                default: $url = $path;
            }
            parent::redirect($url);
        }
    }
    
    public function myapplicationsAction() 
    {
        switch($this->getUserPrivs()) {
            case -1: return false; break;
            case -2: return $this->_loginForm(wpjr_link_to("myapplications")); break;
        }
        
        $this->view->breadcrumbs = array(
            array("title"=>__("Home", "wpjobboard"), "url"=>wpjr_link_to("myresume_home"), "glyph"=>"wpjb-icon-home"),
            array("title"=>__("My Applications", "wpjobboard"), "url"=>wpjr_link_to("myapplications"), "glyph"=>$this->glyph()),
        );
        
        $this->setTitle(__("My Applications", "wpjobboard"));
        $request = $this->_request;
        
        $query = new Daq_Db_Query();
        $query->from("Wpjb_Model_Application t");
        $query->where("user_id = ?", get_current_user_id());
        $query->order("t.applied_at DESC");
        
        $total = $query->select("COUNT(*) as cnt")->fetchColumn();
        $page = $request->getParam("page", $request->getParam("pg", 1));
        $perPage = $this->_perPage;
        
        $query->select("*");
        $query->limitPage($page, $perPage);
        $query->join("t.job t2");
                
        $apps = $query->execute();
        
        $result = new stdClass();
        $result->perPage = $perPage;
        $result->total = $total;
        $result->application = $apps;
        $result->count = count($apps);
        $result->pages = ceil($result->total/$result->perPage);
        $result->page = $page;
        
        $this->view->result = $result;
        $this->view->param = array("page"=>$page);
        $this->view->url = wpjr_link_to("myapplications");
        
        
        return "my-applications";
    }
    
    public function mybookmarksAction() 
    {
        switch($this->getUserPrivs()) {
            case -1: return false; break;
            case -2: return $this->_loginForm(wpjr_link_to("mybookmarks")); break;
        }
        
        $this->view->breadcrumbs = array(
            array("title"=>__("Home", "wpjobboard"), "url"=>wpjr_link_to("myresume_home"), "glyph"=>"wpjb-icon-home"),
            array("title"=>__("My Bookmarks", "wpjobboard"), "url"=>wpjr_link_to("mybookmarks"), "glyph"=>$this->glyph()),
        );
        
        
        $this->setTitle(__("My Bookmarks", "wpjobboard"));
        $request = $this->_request;
        
        $query = new Daq_Db_Query();
        $query->from("Wpjb_Model_Shortlist t");
        $query->where("user_id = ?", get_current_user_id());
        $query->where("object = ?", "job");
        $query->order("id DESC");
        
        $total = $query->select("COUNT(*) as cnt")->fetchColumn();
        $page = $request->getParam("page", $request->getParam("pg", 1));
        $perPage = 20;
        
        $query->select("*");
        $query->limitPage($page, $perPage);
                
        $apps = $query->execute();
        
        $result = new stdClass();
        $result->perPage = $perPage;
        $result->total = $total;
        $result->shortlist = $apps;
        $result->count = count($apps);
        $result->pages = ceil($result->total/$result->perPage);
        $result->page = $page;
        
        $this->view->result = $result;
        $this->view->param = array("page"=>$page);
        $this->view->url = wpjr_link_to("mybookmarks");
        
        
        return "my-bookmarks";
    }
    
    
    public function editAction()
    {
        switch($this->getUserPrivs()) {
            case -1: return false; break;
            case -2: return $this->_loginForm(wpjr_link_to("myresume_home")); break;
        }
        
        $this->setTitle(wpjb_conf("seo_resume_my_resume", __("My Resume Details", "wpjobboard")));

        $object = Wpjb_Model_Resume::current();
        if(!is_object($object)) {
            $id = null;
        } else {
            $id = $object->getId();
        }
        
        $form = new Wpjb_Form_Resume($id);
        $part = $this->_request->getParam("slug");
        $groups = array();
        
        if($part) {
            $groups = array_keys($form->getGroups());
            $diff = array_diff($groups, (array)$part);
            $form->removeGroup($diff);
        } 
        
        if(!in_array($part, $groups)) {
            $this->view->_flash->addError(__("Incorrect group name.", "wpjobboard"));
            return false;
        }
        
        $formGroups = $form->getGroups();
        $group = $formGroups[$part];
        
        $this->view->breadcrumbs = array(
            array("title"=>__("Home", "wpjobboard"), "url"=>wpjr_link_to("myresume_home"), "glyph"=>"wpjb-icon-home"),
            array("title"=>__("My Resume", "wpjobboard"), "url"=>wpjr_link_to("myresume"), "glyph"=>$this->glyph()),
            array("title"=>$group->title, "url"=>wpjr_link_to("myresume_edit", null, array("slug"=>$group->getName())), "glyph"=>$this->glyph())
        );
        
        if($this->isPost()) {
            $isValid = $form->isValid($this->_request->getAll());
            if($isValid) {
                $this->view->_flash->addInfo(__("Your resume has been saved.", "wpjobboard"));
                $form->save();
            } else {
                $this->view->_flash->addError($form->getGlobalError());
            }
        }
        
        $this->view->form = $form;
        
        return "my-resume-edit";
    }
    
    public function passwordAction()
    {
        switch($this->getUserPrivs()) {
            case -1: return false; break;
            case -2: return $this->_loginForm(wpjr_link_to("myresume_password")); break;
        }
        
        $url = wpjr_link_to("myresume");
        
        $this->view->breadcrumbs = array(
            array("title"=>__("Home", "wpjobboard"), "url"=>wpjr_link_to("myresume_home"), "glyph"=>"wpjb-icon-home"),
            array("title"=>__("Change Password", "wpjobboard"), "url"=>wpjr_link_to("myresume_password"), "glyph"=>$this->glyph()),
        );
        
        $this->setTitle(__("Change Password", "wpjobboard"));
        $this->view->action = "";
        $this->view->submit = __("Change Password", "wpjobboard");
        
        $form = new Wpjb_Form_PasswordChange();
        if($this->isPost()) {
            $isValid = $form->isValid($this->getRequest()->getAll());
            if($isValid) {
                $result = wp_update_user(array("ID"=> get_current_user_id(), "user_pass"=>$form->value("user_password")));
                $s = __("Your password has been changed. <a href=\"%s\">Go Back &rarr;</a>", "wpjobboard");
                $this->view->_flash->addInfo(sprintf($s, $url));
                return false;
            } else {
                $this->view->_flash->addError(__("There are errors in your form", "wpjobboard"));
            }
        }
        
        foreach(array("user_password", "user_password2", "old_password") as $f) {
            if($form->hasElement($f)) {
                $form->getElement($f)->setValue("");
            }
        }
        
        $this->view->form = $form;
        
        return "../default/form"; 
    }
    
    public function deleteAction()
    {
        global $current_user;
        
        switch($this->getUserPrivs()) {
            case -1: return false; break;
            case -2: return $this->_loginForm(wpjr_link_to("myresume_delete")); break;
        }
        
        $user = Wpjb_Model_Resume::current();
        $full = Wpjb_Model_Resume::DELETE_FULL;
        
        $this->view->breadcrumbs = array(
            array("title"=>__("Home", "wpjobboard"), "url"=>wpjr_link_to("myresume_home"), "glyph"=>"wpjb-icon-home"),
            array("title"=>__("Delete Account", "wpjobboard"), "url"=>wpjr_link_to("myresume_delete"), "glyph"=>$this->glyph()),
        );
        
        $this->setTitle(__("Delete Account", "wpjobboard"));
        $this->view->action = "";
        $this->view->submit = __("Delete Account", "wpjobboard");
        
        $form = new Wpjb_Form_DeleteAccount();
        
        if($this->isPost()) {
            $isValid = $form->isValid($this->getRequest()->getAll());
            if($isValid) {
                $user->delete($full);
                $current_user = null;
                @wp_logout();
                $this->setTitle(__("Account Deleted", "wpjobboard"));
                $s = __("Your account has been deleted. <a href=\"%s\">Go Back &rarr;</a>", "wpjobboard");
                $this->view->_flash->addInfo(sprintf($s, get_home_url()));
                return false;
            } else {
                $this->view->_flash->addError(__("There are errors in your form", "wpjobboard"));
            }
        }
        
        foreach(array("user_password") as $f) {
            if($form->hasElement($f)) {
                $form->getElement($f)->setValue("");
            }
        }
        
        $this->view->form = $form;
        
        return "../default/form"; 
    }

    public function loginAction()
    {
        $object = Wpjb_Model_Resume::current();
        if(is_object($object) && $object->exists()) {
            wp_redirect(wpjr_link_to("myresume"));
        }

        $this->setTitle(__("Login", "wpjobboard"));
        $form = new Wpjb_Form_Resumes_Login();
        $this->view->errors = array();

        if($this->getRequest()->get("goto-job")) {
            $redirect = new Wpjb_Model_Job($this->getRequest()->get("goto-job"));
            $form->getElement("redirect_to")->setValue($redirect->url());
        }
        
        if($this->isPost() && $this->getRequest()->post("_wpjb_action")=="login") {
            $form->isValid($this->getRequest()->getAll());
        }

        $this->view->page_class = "wpjr-page-login";
        $this->view->action = "";
        $this->view->form = $form;
        $this->view->submit = __("Login", "wpjobboard");
        $this->view->buttons = array(
            array(
                "tag" => "a", 
                "href" => wpjr_link_to("register"), 
                "html" => __("Not a member? Register", "wpjobboard")
            ),
        );

        $this->view = apply_filters("wpjb_shortcode_login", $this->view, "candidate");
        
        return array("../default/form", "login");
    }
    
    public function logoutAction()
    {
        wp_logout();
        $this->view->_flash->addInfo(__("You have been logged out", "wpjobboard"));
        $this->redirect(wpjr_url());
    }
    
    public function registerAction()
    {
        if(get_current_user_id()) {
            $m = __('You are already logged in, <a href="%s">Logout</a> before creating new account.', "wpjobboard");
            $this->view->_flash->addError(sprintf($m, wpjr_link_to("logout")));
            return false;
        }
        
        $this->setTitle(__("Register", "wpjobboard"));

        $form = new Wpjb_Form_Resumes_Register();
        $this->view->errors = array();

        if($this->isPost()) {
            $isValid = $form->isValid($this->getRequest()->getAll());
            if(!$isValid) {
                $this->view->_flash->addError($form->getGlobalError());
            }
        }

        $this->view->form = $form;

        return "register";
    }


    public function updateModDate()
    {
        $resume = Wpjb_Model_Resume::current();
        
        if($resume === null) {
            return;
        }
        
        $resume->modified_at = date("Y-m-d H:i:s");
        $resume->save();
    }
}

?>
