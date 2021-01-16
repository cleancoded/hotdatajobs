<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Message
 *
 * @author greg
 */
class Wpjb_Utility_Message
{
    protected $_header = array();

    protected $_title = null;

    protected $_body = null;

    protected $_file = array();

    protected $_to = null;

    protected $_param = array();
    
    protected $_tpl = null;

    /**
     * Mail template
     *
     * @var Wpjb_Model_Mail
     */
    protected $_template = null;

    /**
     * Object constructor
     * 
     * Loads template message and templating engine.
     * 
     * @param Wpjb_Model_Email $message
     */
    public function __construct(Wpjb_Model_Email $message)
    {
        $this->loadTemplate($message);
        $this->setTpl(new Daq_Tpl_Email);
    }
    
    /**
     * Loads template message
     * 
     * This function can be used to reload message template.
     * 
     * @since 4.3.4
     * @param Wpjb_Model_Email $template
     */
    public function loadTemplate(Wpjb_Model_Email $template)
    {
        $this->_template = $template;
        
        $this->setTitle($this->_template->mail_title);
        $this->setBodyText($this->_template->mail_body_text);
        $this->setBodyHtml($this->_template->mail_body_html);
        $this->setFrom($this->_template->mail_from, $this->_template->mail_from_name);
        $this->setTo($this->_template->mail_from);
        
        if($this->_template->mail_bcc) {
            $this->addHeader("Bcc", $this->_template->mail_bcc);
        }
    }
    
    /**
     * Assigns values to variable name
     * 
     * @param string $var Variable name
     * @param mixed $value Variable
     */
    public function assign($var, $value)
    {
        if($value instanceof Daq_Db_OrmAbstract) {
            $value = $value->toArray();
        }
        
        $this->_tpl->assign($var, $value);
    }
    
    /**
     * Sets Email Templating Engine
     * 
     * @since 4.3.4
     * @param Daq_Tpl_Email $tpl
     */
    public function setTpl($tpl) 
    {
        $this->_tpl = $tpl;
    }
    
    /**
     * Returns instance of Email Template Engine
     * 
     * @since 4.3.4
     * @return Daq_Tpl_Email
     */
    public function getTpl() 
    {
        return $this->_tpl;
    }

    /**
     * Adds a header to message
     * 
     * @param string $key Header name
     * @param string $value Header value
     */
    public function addHeader($key, $value)
    {
        $this->_header[$key] = $value;
    }

    /**
     * Returns list of headers
     * 
     * @return array List of headers
     */
    public function getHeaders()
    {
        return $this->_header;
    }

    /**
     * Adds file to the message
     * 
     * The $files param is a list (array) of absolute paths to the files which
     * will be attached to the email.
     * 
     * @param array $files List of files to attach
     */
    public function addFiles($files) 
    {
        if(!is_array($files)) {
            $files = (array)$files;
        }
        
        $this->_file = $files;
    }
    
    /**
     * Returns list of attached files
     * 
     * @return array
     */
    public function getFiles()
    {
        return $this->_file;
    }

    public function setFrom($email, $name = null)
    {
        if($name == null) {
            $name = $email;
        }

        $this->addHeader("From", "$name <$email>");
    }

    public function setTo($email)
    {
        $this->_to = $email;
    }

    public function getTo()
    {
        return $this->_to;
    }

    public function getTitle()
    {
        return $this->_title;
    }

    public function setTitle($title)
    {
        $this->_title = $title;
    }

    public function getBody()
    {
        return $this->getBodyText();
    }

    public function setBody($body)
    {
        $this->setBodyText($body);
    }

    public function getBodyText()
    {
        return $this->_body_text;
    }

    public function setBodyText($body)
    {
        $this->_body_text = $body;
    }
    
    public function getBodyHtml()
    {
        return $this->_body_html;
    }

    public function setBodyHtml($body)
    {
        $this->_body_html = $body;
    }
    
    public function getTemplate()
    {
        return $this->_template;
    }

    public function setTemplate($template)
    {
        $this->_template = $template;
    }

    protected function _parse($text, $param)
    {
        return $this->_tpl->draw($text);
    }

    protected function _nl2br($string)
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
    
    protected function _br2nl($string)
    {
      return preg_replace('#<br[[:space:]]*/?'.'[[:space:]]*>#',chr(13).chr(10),$string);
    } 
    
    protected function _removeEmptyP( $content ) {
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
    
    public function send()
    {
        apply_filters("wpjb_message_pre_send", $this);
        
        $is_active = $this->_template->is_active;

        if($this->_template->format == "text/plain") {
            $body = $this->getBodyText();
            $body = $this->_nl2br($body);
            $message = $this->_tpl->draw($body);
            $message = $this->_br2nl($message);
            $message = ltrim($message);
        } elseif($this->_template->format == "text/html") {
            $body = $this->getBodyHtml();
            $body = wpautop(html_entity_decode($body));
            $message = $this->_removeEmptyP($this->_html($body));
            $this->addHeader("Content-Type", "text/html");
        } elseif($this->_template->format == "text/html-advanced") {
            $body = $this->getBodyHtml();
            $body = html_entity_decode($body);
            $message = $this->_html($body);
            $this->addHeader("Content-Type", "text/html");
        } else {
            $body = $this->getBodyHtml();
            $message = $this->getTpl()->draw($body);
            $this->addHeader("Content-Type", "text/html");
        }
        
        $to = $this->getTo();
        $subject = $this->_parse($this->getTitle(), $this->_param);
        $header = $this->getHeaders();
        $attachments = $this->getFiles();
        $headers = array();
        foreach($header as $t=>$x) {
            $headers[] = "$t: $x";
        }

        extract(apply_filters("wpjb_message", array(
            "key" => $this->_template,
            "is_active" => $is_active,
            "to" => $to,
            "subject" => $subject,
            "message" => $message,
            "headers" => $headers,
            "attachments" => $attachments
        ), $this));

        if($this->_template->format != "text/plain") {
            add_action("phpmailer_init", array($this, "altBody"));
        }

        if($is_active && !empty($to)) {
            wp_mail($to, $subject, $message, $headers, $attachments);
        }
        
        remove_action( "phpmailer_init", array($this, "altBody"));
    }
    
    public function altBody($phpmailer) {
        $body = $this->getBodyText();
        $body = $this->_nl2br($body);
        $message = $this->_tpl->draw($body);
        $message = $this->_br2nl($message);
        $phpmailer->AltBody = ltrim($message);
    }
    
    protected function _getTemplateFile($edit = null) {
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
    
    protected function _getCustomCSS()
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
        
        return $css;
    }
    
    protected function _html($body)
    {
        $basedir = Wpjb_Project::getInstance()->getBaseDir();
        $path =  $basedir . "/application/config/";
            
        $css = $this->_getTemplateFile("css");
        $css_content = get_option($css->option);
        if($css_content === false) {
            $css_content = file_get_contents($path . $css->file);
        }
        $css_content .= $this->_getCustomCSS();
        
        $template = $this->_getTemplateFile("template");
        $template_content = get_option($template->option);
        if($template_content === false) {
            $template_content = file_get_contents($path . $template->file);
        }
        
        if(version_compare(PHP_VERSION, "5.4.0", ">=")) {
            
            $tpl = new Daq_Tpl_Email();
            $tpl->assign("header", "");
            $tpl->assign("title", "");
            $tpl->assign("css", "");
            $tpl->assign("logo", wpjb_conf("email_logo"));
            $tpl->assign("content", $this->getTpl()->draw($body));
            $tpl->assign("footer", wpjb_conf("email_footer"));

            $html = $this->_removeEmptyP($tpl->draw($template_content));
            
            include_once $basedir . "/application/vendor/Emogrifier/Emogrifier.php";

            $class = "\Pelago\Emogrifier";
            $emogrifier = new $class();
            $emogrifier->setCss($css_content);
            $emogrifier->setHtml($html);

            return $emogrifier->emogrify();
            
        } else {
            
            $tpl = new Daq_Tpl_Email();
            $tpl->assign("header ", "");
            $tpl->assign("title", "");
            $tpl->assign("css", $css_content);
            $tpl->assign("logo", wpjb_conf("email_logo"));
            $tpl->assign("content", $this->getTpl()->draw($body));
            $tpl->assign("footer", wpjb_conf("email_footer"));

            return $this->_removeEmptyP($tpl->draw($template_content));
        }

    }
    
    /**
     * Loads model
     *
     * @param name $key
     * @return Wpjb_Utility_Message
     * @throws Exception 
     */
    public static function load($key)
    {
        $query = new Daq_Db_Query;
        $query->select();
        $query->from("Wpjb_Model_Email t");
        $query->where("name = ?", $key);
        $query->limit(1);
        
        $list = apply_filters("wpjb_message_load_template", $query->execute(), $key);
        
        if(empty($list)) {
            throw new Exception("Email template [$key] does not exist.");
        } else {
            return new self($list[0]);
        }
    }
}
?>
