<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Email
 *
 * @author greg
 */
class Wpjb_Module_Ajax_Email
{
    public static function getFile($edit = null) {
        if(!$edit) {
            $request = Daq_Request::getInstance();
            $edit = $request->getParam("edit");
        }
        switch($edit) {
            case "demo":
                $option = "wpjb_email_demo_content";
                $file = "email-demo-content.html";
                $type = "html";
                break;
            case "template":
                $option = "wpjb_email_template";
                $file = "email-template.html";
                $type = "html";
                break;
            case "css":
                $option = "wpjb_email_stylesheet";
                $file = "email-stylesheet.css";
                $type = "css";
                break;
            default:
                exit("-1");
        }
        
        $result = new stdClass();
        $result->option = $option;
        $result->file = $file;
        $result->type = $type;
        
        return $result;
    }
    
    public static function templateAction()
    {
        $path =  Wpjb_Project::getInstance()->getBaseDir() . "/application/config/";
            
        $demo = self::getFile("demo");
        $demo_content = get_option($demo->option);
        if($demo_content === false) {
            $demo_content = file_get_contents($path . $demo->file);
        }
        
        $css = self::getFile("css");
        $css_content = get_option($css->option);
        if($css_content === false) {
            $css_content = file_get_contents($path . $css->file);
        }
        
        $template = self::getFile("template");
        $template_content = get_option($template->option);
        if($template_content === false) {
            $template_content = file_get_contents($path . $template->file);
        }
        
        if(Daq_Request::getInstance()->getParam("beacon")) {
            $demo_content = '<div id="wpjb-preview-beacon"></div>';
        }

        $tpl = new Daq_Tpl_Email();
        $tpl->assign("header", "");
        $tpl->assign("title", "Test Email");
        $tpl->assign("css", $css_content);
        $tpl->assign("logo", "#");
        $tpl->assign("content", $demo_content);
        $tpl->assign("footer", "");
        
        echo $tpl->draw($template_content);
        
        exit;
    }
    
    public static function previewAction() 
    {
        $type = Daq_Request::getInstance()->getParam("type");
        
        if(in_array($type, array("text/html", "text/html-advanced"))) {
            self::previewHtml();
        } else {
            self::previewPlain();
        }
    }
            
    public static function previewHtml()
    {
        $path =  Wpjb_Project::getInstance()->getBaseDir() . "/application/config/";
            
        $css = self::getFile("css");
        $css_content = get_option($css->option);
        if($css_content === false) {
            $css_content = file_get_contents($path . $css->file);
        }
        $css_content .= self::getCustomCSS();
        
        $template = self::getFile("template");
        $template_content = get_option($template->option);
        if($template_content === false) {
            $template_content = file_get_contents($path . $template->file);
        }
        
        $tpl = new Daq_Tpl_Email();
        $tpl->assign("header", "");
        $tpl->assign("title", "Test Email");
        $tpl->assign("css", $css_content);
        $tpl->assign("logo", wpjb_conf("email_logo"));
        $tpl->assign("content", '<div id="wpjb-iframe-beacon"></div>');
        $tpl->assign("footer", wpjb_conf("email_footer"));
        
        echo $tpl->draw($template_content);
        
        exit;
    }
    
    public static function previewPlain()
    {
        ?><!doctype html>
        <html lang="en">
        <head>
          <meta charset="utf-8">

          <title></title>
          <meta name="description" content="">
          <meta name="author" content="">
          <style type="text/css">
              body { 
                  font-family: Helvetica, Arial, sans-ferif;
                  background-color: white;
                  font-size: 14px;
              }
          </style>
        </head>

        <body>
            <div id="wpjb-iframe-beacon"></div>
        </body>
        </html>
        <?php
        exit;
    }
    
    public static function getCustomCSS()
    {
        $css = "\r\n\r\n";
        
        if(wpjb_conf("color_background")) {
            $css .= sprintf('html, table.body, .grey { background: %s }', wpjb_conf("color_background"));
            $css .= "\r\n";
        }
        
        if(wpjb_conf("color_background_body")) {
            $css .= sprintf('.main { background: %s }', wpjb_conf("color_background_body"));
            $css .= "\r\n";
        }
        
        if(wpjb_conf("color_text")) {
            $css .= sprintf('p, li, span { color: %s }', wpjb_conf("color_text"));
            $css .= "\r\n";
        }
        
        if(wpjb_conf("color_link")) {
            $css .= sprintf('a { color: %s }', wpjb_conf("color_link"));
            $css .= "\r\n";
        }
        
        if(wpjb_conf("color_text_header")) {
            $css .= sprintf('h1, h2, h3, h4, h5, h6 { color: %s }', wpjb_conf("color_text_header"));
            $css .= "\r\n";
        }
        
        if(wpjb_conf("color_text_footer")) {
            $css .= sprintf('.footer td, .footer span, .footer a { color: %s }', wpjb_conf("color_text_footer"));
            $css .= "\r\n";
        }
        
        if(wpjb_conf("color_button")) {
            $css .= sprintf('.btn-primary a, .btn-primary table td { background-color: %s }', wpjb_conf("color_button"));
            $css .= "\r\n";
            $css .= sprintf('.btn a, .btn table td { background-color: %s }', wpjb_conf("color_button"));
            $css .= "\r\n";
            $css .= sprintf('.btn-primary a { border-color: %s }', wpjb_conf("color_button"));
            $css .= "\r\n";
            $css .= sprintf('.btn a { border-color: %s }', wpjb_conf("color_button"));
            $css .= "\r\n";
        }
        
        if(wpjb_conf("color_button_text")) {
            $css .= sprintf('.btn a { color: %s }', wpjb_conf("color_button_text"));
            $css .= "\r\n";
        }


        if(!Daq_Request::getInstance()->getParam("tinymce")) {
            return $css;
        }
        
        if(wpjb_conf("color_background_body")) {
            $css .= sprintf('body#tinymce { background: %s }', wpjb_conf("color_background_body"));
            $css .= "\r\n";
        }
        
        return $css;
    }
    
    public static function nl2br($string) 
    {
        $lines = preg_split('/\r\n|\n|\r/', $string);
        $output = "";
        foreach((array)$lines as $line) {
            $line = rtrim($line);
            if($line && $line[strlen($line)-1] == "}") {
                // string ends with '}'
                preg_match('/\{[^\}]+\}/', $line, $matches);
                $index = count($matches)-1;
                if($index>=0 && substr($matches[$index], 0,2)=='{$')  {
                    $output .= $line."<br />";
                } else {
                    $output .= $line;
                }
            } else {
                $output .= $line."<br />";
            }
        }
        return $output;
    }

    public static function removeEmptyP( $content ) {
        $content = str_replace("<p></p>", "", $content);
	$content = preg_replace( array(
		'#<p>\s*<(div|aside|section|article|header|footer)#',
		'#</(div|aside|section|article|header|footer)>\s*</p>#',
		'#</(div|aside|section|article|header|footer)>\s*<br ?/?>#',
		'#<(div|aside|section|article|header|footer)(.*?)>\s*</p>#',
		'#<p>\s*</(div|aside|section|article|header|footer)#',
	), array(
		'<$1',
		'</$1>',
		'</$1>',
		'<$1$2>',
		'</$1',
	), $content );
	return preg_replace('#<p>(\s|&nbsp;)*+(<br\s*/*>)*(\s|&nbsp;)*</p>#i', '', $content);
    }
    
    public static function parseAction()
    {
        $type = Daq_Request::getInstance()->getParam("type");
        
        if($type == "text/html") {
            self::parseHtml();
        } else if( $type == "text/html-advanced") {
            self::parseHtmlAdvanced();
        } else {
            self::parsePlain();
        }
    }
    
    public static function parseHtml()
    {
        $request = Daq_Request::getInstance();
        
        $evars = new Wpjb_List_EmailVars();
        $email = new Wpjb_Model_Email($request->post("template"));

        $data = html_entity_decode(wpautop($request->post("data")));
        
        $tpl = new Daq_Tpl_Email;
        $tpl->assign($evars->getTemplateDemoData($email));
        
        $html = $tpl->draw($data);
        
        $response = new stdClass();
        $response->html = self::removeEmptyP($html);
        
        echo json_encode($response);
        exit;
    }
    
    public static function parseHtmlAdvanced()
    {
        $request = Daq_Request::getInstance();
        
        $evars = new Wpjb_List_EmailVars();
        $email = new Wpjb_Model_Email($request->post("template"));

        $data = $request->post("data");
        
        $tpl = new Daq_Tpl_Email;
        $tpl->assign($evars->getTemplateDemoData($email));
        
        $html = $tpl->draw($data);
        
        $response = new stdClass();
        $response->html = $html;
        
        echo json_encode($response);
        exit;
    }
    
    public static function parsePlain()
    {
        $request = Daq_Request::getInstance();
        
        $evars = new Wpjb_List_EmailVars();
        $email = new Wpjb_Model_Email($request->post("template"));

        $data = $request->post("data");
        
        $tpl = new Daq_Tpl_Email;
        $tpl->assign($evars->getTemplateDemoData($email));
        
        $html = $tpl->draw($data);
        
        $response = new stdClass();
        $response->html = self::nl2br(htmlspecialchars($html));

        echo json_encode($response);
        exit;
    }
    
    public static function downloadAction() 
    {
        $filename = 'email-template.html';
        
        header('Content-disposition: attachment; filename=' . $filename);
        header('Content-type: text/html');
        
        $path =  Wpjb_Project::getInstance()->getBaseDir() . "/application/config/";
            
        $css = self::getFile("css");
        $css_content = get_option($css->option);
        if($css_content === false) {
            $css_content = file_get_contents($path . $css->file);
        }
        $css_content .= self::getCustomCSS();
        
        $template = self::getFile("template");
        $template_content = get_option($template->option);
        if($template_content === false) {
            $template_content = file_get_contents($path . $template->file);
        }
        
        $evars = new Wpjb_List_EmailVars();
        $email = new Wpjb_Model_Email(Daq_Request::getInstance()->getParam("template"));

        $content = new Daq_Tpl_Email();
        $content->assign($evars->getTemplateDemoData($email));
        
        $tpl = new Daq_Tpl_Email();
        $tpl->assign("header", "");
        $tpl->assign("title", "Test Email");
        $tpl->assign("css", "");
        $tpl->assign("logo", wpjb_conf("email_logo"));
        $tpl->assign("content", $content->draw(Daq_Request::getInstance()->getParam("data")));
        $tpl->assign("footer", wpjb_conf("email_footer"));
        
        $html = $tpl->draw($template_content);
        
        include_once Wpjb_Project::getInstance()->getBaseDir() . "/application/vendor/Emogrifier/Emogrifier.php";
        
        $class = "\Pelago\Emogrifier";
        $emogrifier = new $class();
        $emogrifier->setCss($css_content);
        $emogrifier->setHtml($html);
        
        echo $emogrifier->emogrify();
        exit;  
    }
    
    public static function sourceAction() 
    {
        $result = self::getFile();
        $content = get_option($result->option);
        
        if($content === false) {
            $template_file = Wpjb_Project::getInstance()->getBaseDir() . "/application/config/" . $result->file;
            $content = file_get_contents($template_file);
        }
        
        $response = new stdClass();
        $response->content = $content;
        $response->type = $result->type;
        
        echo json_encode($response);
        exit;
    }
    
    public static function plainAction()
    {
        header("Content-Type: text/css");
        
        $result = self::getFile();
        $content = get_option($result->option);
        
        if($content === false) {
            $template_file = Wpjb_Project::getInstance()->getBaseDir() . "/application/config/" . $result->file;
            $content = file_get_contents($template_file);
        }
        
        echo $content;
        echo self::getCustomCSS();
        
        exit;
    }
    
    public static function saveAction()
    {
        $request = Daq_Request::getInstance();
        $result = self::getFile();
        
        update_option($result->option, $request->post("data"));
        
        $response = new stdClass();
        $response->type = $result->type;
        $response->content = $request->post("data");
        
        echo json_encode($response);
        exit;
    }
    
    public static function restoreAction() 
    {
        delete_option(self::getFile()->option);
        self::sourceAction();
    }
}
