<?php
/**
 * Description of Router
 *
 * @author greg
 * @package 
 */

class Daq_Router
{
    protected $_delimiter = "/";

    protected $_route = array();

    protected $_matched = null;

    protected $_patternList = array(
        'string' => '([A-z0-9\-\,]+)',
        'int' => '([0-9]+)',
        'slug' => '([^/]+)'
    );

    public function __construct(array $route)
    {
        $this->_route = $route;
    }
    
    public function getRoute($name) 
    {
        if(isset($this->_route[$name])) {
            return $this->_route[$name];
        } else {
            return null;
        }
        
    }
    
    public function getRoutes() 
    {
        return $this->_route;
    }
    
    public function forceRoute($route) 
    {
        $this->_matched = $route;
    }

    /**
     * Returns matched route, from the last use of self::match() method,
     * or throws an exception if match() was not used yet.
     *
     * @throws Exception If url is not resolved
     * @return array
     */
    public function getMatched()
    {
        if(is_null($this->_matched)) {
            throw new Exception("URL not resolved! Use ".__CLASS__."::".__METHOD__."() first.");
        }

        return $this->_matched;
    }
    
    /**
     * Checks if route is resovled.
     * 
     * That is if the self::match() function was run.
     * 
     * @return boolean
     */
    public function isResolved()
    {
        if(is_null($this->_matched)) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Checks if URL path routes to given route.
     *
     * @param string $route Name or controller.action
     */
    public function isRoutedTo($route)
    {
        $match = $this->getMatched();
        $x = stripos($route, ".");
        $path = $match['module'].".".$match['action'];

        if($x && $route == $path) {
            // controller.action
            return true;
        } elseif(!$x && $route == $match['route']) {
            // match by key
            return true;
        } else {
            return false;
        }

    }

    /**
     *
     * @return array ['module', 'action', 'object']
     */
    public function match($path = "/")
    {
        if(strlen($path)==0) {
            $path = "/";
        }

        $result = $this->_match($path);
        $schema = $this->_route[$result['route']];

        $route = array(
            'param' => array(),
            'route' => $result['route'],
            'object' => null
        );

        if(isset($schema['model'])) {
            $obj = $schema['model'];
            // @todo
            $route['object'] = new stdClass;
            $route['object']->objClass = $obj;
        }

        if(isset($result['param']['module'])) {
            $route['module'] = $result['param']['module'];
            unset($result['param']['module']);
        } else {
            $route['module'] = $schema['module'];
        }

        if(isset($result['param']['action'])) {
            $route['action'] = $result['param']['action'];
            unset($result['param']['action']);
        } else {
            $route['action'] = $schema['action'];
        }

        $path = "";
        foreach((array)$result['param'] as $key => $value) {
            if($key!="*") {
                $path .= "/".$key."/".$value;
            } else {
                $path .= "/".trim($value, "/");
            }
        }
        $route['path'] = trim($path, "/");
        $part = explode("/", $route['path']);
        $pLen = count($part);
        for($i=0; $i<$pLen; $i+=2) {
            $value = "";
            if(isset($part[$i+1])) {
                $value = $part[$i+1];
            }
            $route['param'][$part[$i]] = $value;
        }

        $this->_matched = $route;
        return $route;
    }

    public function _match($path)
    {
        $match = array();
        $pathPart = explode($this->_delimiter, $path);

        foreach($this->_route as $key => $value) {

            if(!isset($value['pattern'])) {
                continue;
            }

            $pattern = $value['pattern'];
            $keyList = array();
            foreach($value as $k => $param) {
                if(stripos($k, "param.") !== false) {
                    $k = str_replace("param.", "", $k);
                    $keyList[] = $k;
                    $repl = "";
                    if(array_key_exists($param, $this->_patternList)) {
                        $repl = $this->_patternList[$param];
                    }
                    $pattern = str_replace("(".$k.")", $repl, $pattern);
                }
            }
            
            $pattern = str_replace("*", "([A-z0-9\-_/]*)", $pattern);

            if(strlen($pattern)>0) {
                $rawPattern = $pattern;
                $pattern = "/^".str_replace("/", "\/", $pattern)."/";
                $result = preg_match_all($pattern, $path, $match);
                //echo $pattern." <b>|</b> ".$result."<br/>";
                //echo $rawPattern ."==". $path."<br/>";
                if($result ) {
                    //print_r($match);
                    $buffer = array('route'=>$key, 'param'=>array());
                    $cnt = count($match);
                    for($i=1; $i<$cnt; $i++) {
                        if(isset($keyList[$i-1])) {
                            $buffer['param'][$keyList[$i-1]] = $match[$i][0];
                        } elseif(strlen($match[$i][0])>0) {
                            $buffer['param']['*'] = $match[$i][0];
                        }
                    }
                    return $buffer;
                }
            }


        }
        //return $match;
    }

    public function linkTo($key, $object = null, $param = array())
    {
        $param2 = array();
        if($object instanceof Daq_Db_OrmAbstract) {
            foreach($object->getFieldNames() as $k) {
                $param2[$k] = $object->$k;
            }
        }

        $route = $this->_route[$key];
        $pattern = $route["pattern"];
        
        foreach($param as $k => $v) {
            if(stripos($pattern, "($k)") === false) {
                $pattern = str_replace("/*", "/$k/($k)/*", $pattern);
            }
        }
        
        $list = array();
        foreach(($param+$param2) as $k => $v) {
            $list["($k)"] = $v;
        }
        $list["*"] = "";

        
        return strtr($pattern, $list);
    }

}

?>