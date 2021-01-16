<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Api
 *
 * @author Grzegorz
 */
class Wpjb_Module_Api_Api extends Daq_Controller_Abstract
{
    
    public function getRouter()
    {
        return Wpjb_Project::getInstance()->getApplication("api")->getRouter();
    }
    
    public function getRequest()
    {
        return Daq_Request::getInstance();
    }
    
    public function reply($object)
    {
        header("HTTP/1.1 200 OK");
        echo json_encode($object);
    }
    
    public function accessTokenAction()
    {
        $key = wpjb_conf("android_encrypt_key");
        $iv = substr(hash('sha256', home_url()), 0, 16);
        $auth = $this->getServer("HTTP_X_AUTHORIZATION");
        $type = $this->getServer("HTTP_X_AUTHORIZATION_TYPE", "AES");

	$encryption = new Daq_Helper_Security($iv, $key);
        
        if(!$auth) {
            throw new Exception("Missing authorization code.");
        }
        
        $decrypted = $encryption->decrypt($auth);
        
        if(!$decrypted || !stripos($decrypted, ":")) {
            throw new Exception("Authorization code malformed or could not be decrypted.");
        }
        
        list($login, $password) = explode(":", $decrypted);
        
        $user = get_userdatabylogin($login);
        
        if(!$user) {
            throw new Exception("User with login [$login] does not exist.");
        }
        
        $ok = wp_check_password($password, $user->user_pass, $user->ID);
        
        if(!$ok) {
            throw new Exception("Invalid password.");
        }
        
        $token = get_user_meta($user->ID, "wpjb_access_token", true);
        
        if(!$token) {
            do {
                $token = sha1(time()."/".uniqid()."/".$user->ID."/".rand(0,90000));
            } while(add_user_meta($user->ID, "wpjb_access_token", $token, true) === false);
        }
        
        $this->reply(array("access_token"=>$token));
    }
    
    public function phoneAction()
    {
        $method = strtolower($this->getMethod());
        $action = $method."Action";
        $user = $this->authenticate();
        $access = "";
        
        if($user === null) {
            $access = "public";
        } elseif(user_can($user, "import")) {
            $access = "admin";
        } elseif(user_can($user, "manage_jobs")) {
            $access = "employer";
        } else {
            $access = "user";
        }
        
        $ctrl = new Wpjb_Module_Api_Phone();
        $ctrl->setMethod($method);
        $ctrl->setAccess($access);
        $ctrl->setUser($user);
        $ctrl->userCan($method);
        
        $matched = $this->getRouter()->getMatched();
        $params = array();
        
        foreach($this->getRequest()->get() as $k => $v) {
            if(!empty($k)) {
                $params[$k] = $v;
            }
        }
        foreach($this->getRequest()->post() as $k => $v) {
            if(!empty($k)) {
                $params[$k] = $v;
            }
        }
        
        $this->validateParams($params, $ctrl->conf("//disallowed_params"));
        
        if(!empty($matched["path"])) {
            $params["id"] = (array)explode(",", $matched["path"]);
        } 
        
        $response = $ctrl->$action($params);
        
        $json = json_encode($response);
        
        header("Content-type: application/json; charset=utf-8");
        
        if($this->getRequest()->get("pretty") == "1") {
            echo $this->pretty($json);
        } else {
            echo $json;
        }
    }
    
    public function meAction()
    {
        $user = $this->authenticate();
        $obj = new stdClass();
        $json = json_encode(new stdClass());
        
        if($user) {
            $obj->ID = $user->ID;
            $obj->user_login = $user->user_login;
            $obj->user_nicename = $user->user_nicename;
            $obj->user_email = $user->user_email;
            $obj->user_url = $user->user_url;
            $obj->user_registered = $user->user_registered;
            $obj->user_activation_key = $user->user_activation_key;
            $obj->user_status = $user->user_status;
            $obj->display_name = $user->display_name;
            $obj->allcaps = $user->allcaps;
            
            $json = json_encode($obj);
        }
        
        if($this->getRequest()->get("pretty") == "1") {
            echo $this->pretty($json);
        } else {
            echo $json;
        }
    }
    
    public function alertAction()
    {
        $method = strtolower($this->getMethod());
        $action = $method."Action";
        $user = $this->authenticate();
        $access = "";
        
        if($user === null) {
            $access = "public";
        } elseif(user_can($user, "import")) {
            $access = "admin";
        } elseif(user_can($user, "manage_jobs")) {
            $access = "employer";
        } else {
            $access = "user";
        }
        
        $ctrl = new Wpjb_Module_Api_Alert();
        $ctrl->setMethod($method);
        $ctrl->setAccess($access);
        $ctrl->setUser($user);
        $ctrl->userCan($method);
        
        $matched = $this->getRouter()->getMatched();
        $params = array();
        
        foreach($this->getRequest()->get() as $k => $v) {
            if(!empty($k)) {
                $params[$k] = $v;
            }
        }
        foreach($this->getRequest()->post() as $k => $v) {
            if(!empty($k)) {
                $params[$k] = $v;
            }
        }
        
        $this->validateParams($params, $ctrl->conf("//disallowed_params"));
        
        if(!empty($matched["path"])) {
            $params["id"] = (array)explode(",", $matched["path"]);
        } 
        
        $response = $ctrl->$action($params);
        
        $json = json_encode($response);
        
        header("Content-type: application/json; charset=utf-8");
        
        if($this->getRequest()->get("pretty") == "1") {
            echo $this->pretty($json);
        } else {
            echo $json;
        }
    }
    
    public function applicationsAction()
    {
        $method = strtolower($this->getMethod());
        $action = $method."Action";
        $user = $this->authenticate();
        $access = "";
        
        if($user === null) {
            $access = "public";
        } elseif(user_can($user, "import")) {
            $access = "admin";
        } elseif(user_can($user, "manage_jobs")) {
            $access = "employer";
        } else {
            $access = "user";
        }
        
        $ctrl = new Wpjb_Module_Api_Applications();
        $ctrl->setMethod($method);
        $ctrl->setAccess($access);
        $ctrl->setUser($user);
        $ctrl->userCan($method);
        
        $matched = $this->getRouter()->getMatched();
        $params = array();
        
        foreach($this->getRequest()->get() as $k => $v) {
            if(!empty($k)) {
                $params[$k] = $v;
            }
        }
        foreach($this->getRequest()->post() as $k => $v) {
            if(!empty($k)) {
                $params[$k] = $v;
            }
        }
        
        $this->validateParams($params, $ctrl->conf("//disallowed_params"));
        
        if(!empty($matched["path"])) {
            $params["id"] = (array)explode(",", $matched["path"]);
        } 

        $response = $ctrl->$action($params);
        
        $json = json_encode($response);
        
        header("Content-type: application/json; charset=utf-8");
        
        if($this->getRequest()->get("pretty") == "1") {
            echo $this->pretty($json);
        } else {
            echo $json;
        }
    }


    public function authenticate()
    {
        $key = wpjb_conf("android_encrypt_key");
        $iv = substr(hash('sha256', home_url()), 0, 16);
        $auth = $this->getServer("HTTP_X_AUTHORIZATION");
        $type = $this->getServer("HTTP_X_AUTHORIZATION_TYPE", "AES");

	$encryption = new Daq_Helper_Security($iv, $key);
        
        if(!$auth) {
            return null;
        }
        
        $decrypted = $encryption->decrypt($auth);
        
        if(!$decrypted) {
            throw new Exception("Access token malformed or could not be decrypted.");
        }
        
        $query = new WP_User_Query(
            array(
                'meta_key' => 'wpjb_access_token',
                'meta_value' => $decrypted
            )
	);

	$users = $query->get_results();
        
        if(!isset($users[0])) {
            throw new Exception("Access token does not exist.");
        }

	return $users[0];
    }
    
    public function validateParams($params, $init) 
    {
        foreach(array_keys($params) as $k) {
            if(in_array($k, $init)) {
                throw new Exception("You cannot use param [$k]");
            }
        }
    }
    
    public function pretty($json) 
    {

        $result      = '';
        $pos         = 0;
        $strLen      = strlen($json);
        $indentStr   = '  ';
        $newLine     = "\n";
        $prevChar    = '';
        $outOfQuotes = true;

        for ($i=0; $i<=$strLen; $i++) {

            // Grab the next character in the string.
            $char = substr($json, $i, 1);

            // Are we inside a quoted string?
            if ($char == '"' && $prevChar != '\\') {
                $outOfQuotes = !$outOfQuotes;

            // If this character is the end of an element,
            // output a new line and indent the next line.
            } else if(($char == '}' || $char == ']') && $outOfQuotes) {
                $result .= $newLine;
                $pos --;
                for ($j=0; $j<$pos; $j++) {
                    $result .= $indentStr;
                }
            }

            // Add the character to the result string.
            $result .= $char;

            // If the last character was the beginning of an element,
            // output a new line and indent the next line.
            if (($char == ',' || $char == '{' || $char == '[') && $outOfQuotes) {
                $result .= $newLine;
                if ($char == '{' || $char == '[') {
                    $pos ++;
                }

                for ($j = 0; $j < $pos; $j++) {
                    $result .= $indentStr;
                }
            }

            $prevChar = $char;
        }

        return $result;
    }
    
    public function jobsAction()
    {
        $method = strtolower($this->getMethod());
        $action = $method."Action";
        $user = $this->authenticate();
        $access = "";
        
        if($user === null) {
            $access = "public";
        } elseif(user_can($user, "import")) {
            $access = "admin";
        } elseif(user_can($user, "manage_jobs")) {
            $access = "employer";
        } else {
            $access = "user";
        }
        
        $ctrl = new Wpjb_Module_Api_Jobs();
        $ctrl->setMethod($method);
        $ctrl->setAccess($access);
        $ctrl->setUser($user);
        $ctrl->userCan($method);
        
        $matched = $this->getRouter()->getMatched();
        $params = array();
        
        foreach($this->getRequest()->get() as $k => $v) {
            if(!empty($k)) {
                $params[$k] = $v;
            }
        }
        
        $this->validateParams($params, $ctrl->conf("//disallowed_params"));
        
        if(!empty($matched["path"])) {
            $params["id"] = (array)explode(",", $matched["path"]);
        } 
        
        $response = $ctrl->$action($params);
        
        $json = json_encode($response);
        
        header("Content-type: application/json; charset=utf-8");
        
        if($this->getRequest()->get("pretty") == "1") {
            echo $this->pretty($json);
        } else {
            echo $json;
        }
    }
    
    public function bookmarksAction()
    {
        $method = strtolower($this->getMethod());
        $action = $method."Action";
        $user = $this->authenticate();
        $access = "";
        
        if($user === null) {
            $access = "public";
        } elseif(user_can($user, "import")) {
            $access = "admin";
        } elseif(user_can($user, "manage_jobs")) {
            $access = "employer";
        } else {
            $access = "user";
        }
        
        $ctrl = new Wpjb_Module_Api_Bookmarks();
        $ctrl->setMethod($method);
        $ctrl->setAccess($access);
        $ctrl->setUser($user);
        $ctrl->userCan($method);
        
        $matched = $this->getRouter()->getMatched();
        $params = array();
        
        if($method == "get") {
            foreach($this->getRequest()->get() as $k => $v) {
                if(!empty($k)) {
                    $params[$k] = $v;
                }
            }
        } elseif(in_array($method, array("post", "put"))) {
            foreach($this->getRequest()->post() as $k => $v) {
                if(!empty($k)) {
                    $params[$k] = $v;
                }
            }
        }
        
        $this->validateParams($params, $ctrl->conf("//disallowed_params"));
        
        if($access != "admin") {
            $params["user_id"] = $ctrl->getUser()->ID;
        }
        
        if(!empty($matched["path"])) {
            $params["id"] = (array)explode(",", $matched["path"]);
        } 

        $response = $ctrl->$action($params);
        
        $json = json_encode($response);
        
        header("Content-type: application/json; charset=utf-8");
        
        if($this->getRequest()->get("pretty") == "1") {
            echo $this->pretty($json);
        } else {
            echo $json;
        }
    }
}

?>
