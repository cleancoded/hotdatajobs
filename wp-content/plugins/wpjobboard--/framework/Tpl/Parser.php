<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Parser
 *
 * @author Grzegorz
 */
class Daq_Tpl_Parser 
{
    protected $_block = array();
    
    public $var = array();
    
    public function assign($variable, $value = null) 
    {
        if (is_array($variable)) {
            $this->var += $variable;
        } else {
            $this->var[$variable] = $value;
        }
    }
    
    protected function _prepare($block)
    {
        if(substr($block[0][0], 1, 2) == "if") {
            $type = "if";
        } elseif(substr($block[0][0], 1, 4) == "loop") {
            $type = "loop";
        } else {
            $type = "?";
        }
        
        $data = array(
            "type" => $type,
            "md5" => md5($block[0][0]),
            "full" => $block[0][0]
        );
        
        $reduce = array();
        foreach($block as $b) {
            if($b[1] !== -1) {
                $reduce[] = $b;
            }
        }
        
        if($type == "if") {
            $data["condition"] = $reduce[1][0];
        } elseif($type == "loop") {
            $data["array"] = $reduce[1][0];
        }
        
        $data["content"] = $reduce[2][0];
        
        return $data;
    }
    
    protected function _parse($block)
    {
        $plist = array(
            "loop" => '\{loop(?: name){0,1}="(\${0,1}[^"]*)"\}(.*|(?R))\{\/loop\}',
            "if" => '\{if(?: condition){0,1}="([^"]*)"\}(.*|(?R))\{\/if\}'
        );
        
        $name = $block["md5"];
        $pattern = "#".implode("|", $plist)."#i";
        $matches = array();

        preg_match_all($pattern, $block["content"], $matches, PREG_OFFSET_CAPTURE|PREG_SET_ORDER);
        
        foreach($matches as $match) {
            $b = $this->_prepare($match);
            $bmd5 = $b["md5"];
            $block["content"] = str_replace($b["full"], "{block=\"$bmd5\"}", $block["content"]);
            $this->_parse($b);
        }
        
        $this->_block[$name] = $block;
    }
    
    protected function _parseVar($v)
    {
        if(stripos($v, '$') === 0) {
            return $this->value(substr($v, 1));
        } else {
            return $v;
        }
    }
    
    protected function _parseCondition($cond)
    {
        $c = array("===", "==", "<=", ">=", "<", ">");
        $cx = null;
        
        foreach($c as $cx) {
            if(stripos($cond, $cx)) {
                $parts = array_map("trim", explode($cx, $cond));
                list($x, $y) = array_map(array($this, "_parseVar"), $parts);
                break;
            }
        }
        
        switch($cx) {
            case "===": return $x === $y;
            case "==" : return $x == $y;
            case "<=" : return $x <= $y;
            case ">=" : return $x >= $y;
            case "<"  : return $x < $y;
            case ">"  : return $x > $y;
        }
        
        return null;
    }
    
    protected function _doBlock($name)
    {
        $block = $this->_block[$name];
        $matches = null;
        $loop = array(null);
        $if = null;
        $content = "";
        $blockc = $block["content"];
        
        if($block["type"] == "loop") {
            $loop = $this->value($block["array"]);
        } elseif($block["type"] == "if") {
            $parts = explode("{else}", $block["content"]);
            $cond = $this->_parseCondition($block["condition"]);
            if($cond) {
               $blockc = $parts[0]; 
            } elseif(isset($parts[1])) {
                $blockc = $parts[1];
            } else {
                $blockc = "";
            }
        }
        
        foreach($loop as $l) {
            
            $c = $blockc;
            $this->assign("value", $l);
            
            // replace variables
            $pattern = '#\{\$([^\}]+)\}#';
            preg_match_all($pattern, $c, $matches, PREG_SET_ORDER);

            foreach($matches as $match) {
                $c = str_replace($match[0], $this->value($match[1]), $c);
            }

            // replace functions
            $pattern = '#\{function="([A-z0-9_]+)\(([^\)]+)\)"\}#'; 
            preg_match_all($pattern, $c, $matches, PREG_SET_ORDER);
            foreach($matches as $match) {
                $callback = $match[1];
                if(function_exists($callback)) {
                    $params = array_map("trim", explode(",", $match[2]));
                    $params = array_map(array($this, "_parseVar"), $params);
                    $result = call_user_func_array($callback, $params);
                } else {
                    $result = "";
                }
                $c = str_replace($match[0], $result, $c);
            }
            
            // replace blocks
            $pattern = '#\{block=\"([^\"}]+)\"\}#';
            preg_match_all($pattern, $c, $matches, PREG_SET_ORDER);

            foreach($matches as $match) {
                $c = str_replace($match[0], $this->_doBlock($match[1]), $c);
            }
            
            $content .= $c;
        }
        
        
        return $content;
    }
    
    public function value($path) 
    {
        $path = explode(".", $path);
        $p = array_shift($path);
        
        if(!isset($this->var[$p])) {
            return null;
        }
        
        $value = $this->var[$p];
        
        if($value instanceof Daq_Db_OrmAbstract) {
            $value = $value->toArray();
        }
        
        foreach($path as $p) {
            if(isset($value[$p])) {
                $value = $value[$p];
            } else {
                break;
            }
        }
        
        return $value;
    }
    
    public function draw($text)
    {   
        $this->_parse(array("type"=>"MAIN", "md5"=>"MAIN", "content"=>$text, "full"=>null));
        $content = $this->_doBlock("MAIN");
        
        return $content;
    }
}

?>
