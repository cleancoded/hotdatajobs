<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Xml
 *
 * @author Grzegorz
 */
class Daq_Helper_Xml 
{
    protected $write = null;

    public function __construct($write = null) {
        $this->write = $write;
    }

    private function write($content) {
        if($this->write) {
            file_put_contents($this->write, $content, FILE_APPEND);
        } else {
            echo $content;
        }
    }
    
    public function size()
    {
        return round(filesize($this->write) / 1048576);
    }
    
    public function declaration()
    {
        $this->write('<?xml version="1.0" encoding="UTF-8"?>');
    }
    
    public function open($tag, array $param = null)
    {
        $list = "";
        if(is_array($param)) {
            $list = array();
            foreach($param as $k => $v) {
                $list[] = $k."=\"".esc_html($v)."\"";
            }
            $list = " ".join(" ", $list);
        }
        $this->write("<".$tag.$list.">");
    }

    public function close($tag)
    {
        $this->write("</".$tag.">");
    }


    public function xmlEntities($text, $charset = 'UTF-8')
    {
        return esc_html($text);
    }
    
    public function tagIf($tag, $content, array $param = null)
    {
        if(strlen($content)>0) {
            $this->tag($tag, $content, $param);
        }
    }

    public function tag($tag, $content, array $param = null)
    {
        $this->open($tag, $param);
        $this->write($this->xmlEntities($content));
        $this->close($tag);
    }

    public function tagCIf($tag, $content, array $param = null)
    {
        if(!empty($content)) {
            $this->tagC($tag, $content, $param);
        }
    }

    public function tagC($tag, $content, array $param = null)
    {
        $this->open($tag, $param);
        $this->write("<![CDATA[".$content."]]>");
        $this->close($tag);
    }
}

?>
