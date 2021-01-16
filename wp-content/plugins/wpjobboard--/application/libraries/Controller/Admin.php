<?php
/**
 * Description of Admin
 *
 * @author greg
 * @package 
 */

class Wpjb_Controller_Admin extends Daq_Controller_Abstract
{
    protected $_virtual = array();

    protected $_perPage = 20;

    protected function _getPerPage()
    {
        return $this->_perPage;
    }

    protected function _addInfo($info)
    {
        $this->view->_flash->addInfo($info);
    }

    protected function _addError($error)
    {
        $this->view->_flash->addError($error);
    }
    
    public function redirect($url)
    {
        $this->view->url = $url;
        $this->view->render("redirect.php");
        
        throw new Daq_Controller_Redirect_Exception();
    }
    
    public function redirectIf($condition, $url)
    {
        if($condition) {
            $this->redirect($url);
        }
    }
    
    public function addAction($param = array())
    {   
        if(!empty($param)) {
            extract($param);
        } else {
            extract($this->_virtual[__FUNCTION__]);
        }
        
        $form = new $form();
        $id = false;
        
        if($this->isPost()) {
            $isValid = $form->isValid($this->_request->getAll());
            if($isValid) {
                $this->_addInfo($info);
                $id = $form->save();
                if(!$id) {
                    $id = $form->getId();
                }
            } else {
                $this->_addError($error);
            }
        }

        $this->redirectIf($id, sprintf(str_replace("%25d", "%d", $url), $id));
        $this->view->form = $form;
    }
    
    public function editAction()
    {
        extract($this->_virtual[__FUNCTION__]);

        $id = $this->_request->getParam("id");
        
        $f = new $form($id);
        if($this->isPost()) {
            $isValid = $f->isValid($this->_request->getAll());
            if($isValid) {
                $this->_addInfo($info);
                $f->save();
                $id = $f->getId();
                $f = new $form($id);
            } else {
                $this->_addError($error);
            }
        }

        $this->view->form = $f;
    }
    
    public function deleteAction() 
    {
        extract($this->_virtual[__FUNCTION__]);
        $id = $this->_request->getParam("id");
        
        if($this->_multiDelete($id)) {
            $m = sprintf($info, $id);
            $this->view->_flash->addInfo($m);
        }
        wp_redirect(wpjb_admin_url($page));
    }
    
    protected function _delete()
    {
        extract($this->_virtual[__FUNCTION__]);
        
        if($this->isPost() && $this->hasParam("delete")) {
            $id = $this->_request->post("id", 0);
            try {
                $model = new $model($id);
                $model->delete();
                $this->_addInfo($info);
            } catch(Exception $e) {
                $this->_addError($e->getMessage());
                // @todo: logging
            }
        }
    }

    protected function _multi()
    {
        extract($this->_virtual[__FUNCTION__]);
        $inArray = array_keys($this->_virtual[__FUNCTION__]);
        $action = $this->_request->post("action");

        $inArray = new Daq_Validate_InArray($inArray);
        $inArray = $inArray->isValid($action);
        $idList = $this->_request->post("item", array());

        $success = 0;
        $fail = 0;

        if($inArray && count($idList)>0) {
            foreach($idList as $id) {
                $do = "_multi".ucfirst($action);
                if(isset($this->_virtual["_multi"][$action]["callback"])) {
                    call_user_func($this->_virtual["_multi"][$action]["callback"], $id);
                    $i++;
                } elseif($this->$do($id)) {
                    $i++;
                }
            }

            $msg = $this->_virtual[__FUNCTION__][$action]['success'];
            $repl = array($success, $fail);
            $find = array("{success}", "{fail}");
            $compiled = str_replace($find, $repl, $msg);
            $this->_addInfo($compiled);
        }
    }

    protected function _multiDelete($id)
    {
        extract($this->_virtual[__FUNCTION__]);
        
        try {
            $model = new $model($id);
            $model->delete();
            return true;
        } catch(Exception $e) {
            // log error
            return false;
        }
    }

    public function redirectAction()
    {
        $user = $this->_virtual[__FUNCTION__];
        
        $param = array();
        $request = $this->_request;
        $accept = $user["accept"];

        if(!isset($user["action"])) {
            $method = null;
        } else {
            $method = $user["action"];
        }
        
        foreach($accept as $a) {
            if($request->post($a)) {
                $param[$a] = $request->post($a);
            }
        }

        if($request->post("action")) {
            $action = $request->post("action");
        } elseif($request->post("action2")) {
            $action = $request->post("action2");
        } else {
            $action = null;
        }

        $url = wpjb_admin_url($user["object"], $method, null, $param);

        if(!$action) {
            wp_redirect($url);
            exit;
        }

        $item = $request->post("item", array());
        $i = 0;
        foreach($item as $id) {
            $do = "_multi".ucfirst($action);
            if(isset($this->_virtual["_multi"][$action]["callback"])) {
                call_user_func($this->_virtual["_multi"][$action]["callback"], $id);
                $i++;
            } elseif($this->$do($id)) {
                $i++;
            }
        }

        $msg = $this->_virtual["_multi"][$action]["success"];
        
        if(!empty($msg)) {
            $this->_addInfo(str_replace("{success}", $i, $msg));
        }

        wp_redirect($url);
        
        exit;
    }
    
    public function deriveParams($query, $object = null)
    {
        $matches = null;
        $param = array("meta"=>array());
        $replace = str_replace(array("[", "]"), " ", $query);
        $replace = "[query:".preg_replace("#([a-zA-Z0-9_]*)\:#", "][\$1:", $replace)."]";
        $replace = str_replace(" ]", "]", $replace);
        preg_match_all("/\[([a-zA-Z0-9_]+):([^\]]+)\]/", $replace, $matches, PREG_SET_ORDER);
        
        foreach($matches as $match) {
            if(!isset($match[2]) || empty($match[2])) {
                continue;
            }
            
            if(isset($object->meta->{$match[1]})) {
                $param["meta"][$match[1]] = $match[2];
            } else {
                $param[$match[1]] = $match[2];
            }
        }
        
        return $param;
    }
    
    public function readableQuery($query)
    {
        $matches = null;
        $param = array();
        $replace = str_replace(array("[", "]"), " ", $query);
        $replace = "[query:".preg_replace("#([a-zA-Z0-9_]*)\:#", "][\$1:", $replace)."]";
        $replace = str_replace(" ]", "]", $replace);
        preg_match_all("/\[([a-zA-Z0-9_]+):([^\]]+)\]/", $replace, $matches, PREG_SET_ORDER);
        
        foreach($matches as $match) {
            if(!isset($match[2]) || empty($match[2])) {
                continue;
            }
            
            $key = $match[1];
            $value = $match[2];
            
            switch($match[1]) {
                case "job_id":
                case "job": 
                    $object = new Wpjb_Model_Job($value);
                    $value = $object->job_title;
                    break;
                case "employer_id":
                    $object = new Wpjb_Model_Company($value);
                    $value = $object->company_name;
                    break;
                    
            }
            
            if(!is_numeric($value)) {
                $value = "'$value'";
            }
            
            if(substr($key, -3) == "_id") {
                $key = str_replace("_id", "", $key);
            }
            
            $key = str_replace("_", " ", $key);
            $key = ucfirst($key);
            
            $param[] = "$key: $value";
        }

        return join(", ", $param);
    }
}

?>