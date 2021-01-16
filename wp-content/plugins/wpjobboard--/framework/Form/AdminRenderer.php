<?php
/**
 * Description of Renderer
 *
 * @author greg
 * @package 
 */

class Daq_Form_AdminRenderer
{
    protected function _render($tag, array $option, $inner = null)
    {
        $opList = array();
        foreach($option as $k => $v) {
            $opList[] = $k.'="'.($v).'"';
        }
        $op = join(" ", $opList);

        if($inner === null) {
            return "<$tag $op />";
        } else {
            return "<$tag $op>$inner</$tag>";
        }
    }

    private function _escape($text)
    {
        return htmlentities($text, ENT_NOQUOTES, "UTF-8");
    }

    public function renderInput(Daq_Form_Element $element, $type)
    {
        $opt = array();
        $opt['id'] = $element->getName();
        $opt['name'] = $element->getName();
        $opt['type'] = $type;
        $opt['class'] = "regular-text ".$element->getClasses(true);
        if($type != "file") {
            $opt['value'] = esc_html($element->getValue());
        }

        return $this->_render("input", $opt);
    }

    public function renderBox(Daq_Form_Element $element, $type)
    {
        $opt = array();
        $opt['type'] = $type;
        $opt['class'] = "regular-text";
        if($type != "text") {
            $opt['class'] = "";
        }
        $html = "";
        $options = $element->getOptions();
        $optionCount = count($options);
        
        for($i=0; $i<$optionCount; $i++) {
            $option = $options[$i];
            if($optionCount == 1 || $type == "radio") {
                $opt['name'] = $element->getName();
            } else {
                $opt['name'] = $element->getName()."[".$option['key']."]";
            }
            $opt['id'] = $element->getName()."_".$option['key'];
            $opt['value'] = $option['value'];
            if($opt['value'] == $element->getValue()) {
                $opt['checked'] = "checked";
            } elseif(isset ($opt['checked'])) {
                unset($opt['checked']);
            }
            $opt['value'] = esc_html($opt['value']);
            $html .= '<label for="'.$opt['id'].'">';
            $html .= $this->_render("input", $opt)." ".$option['desc'];
            $html .= '</label>';
            if($i+1 != $optionCount) {
                $html .= '<br />';
            }
        }
        return $html;
    }

    public function renderSelect(Daq_Form_Element $element)
    {
        $optionList = "";
        foreach($element->getOptions() as $option) {
            $opt = array();
            $opt['value'] = $option['value'];
            if($opt['value'] == $element->getValue()) {
                $opt['selected'] = "selected";
            } elseif(isset($opt['selected'])) {
                unset($opt['selected']);
            }
            $opt['value'] = esc_html($opt['value']);
            $optionList .= $this->_render("option", $opt, $option['desc']);
        }

        $opt = array();
        $opt['name'] = $element->getName();
        $opt['id'] = $element->getName();
        $opt['class'] = $element->getClasses(true);
        return $this->_render("select", $opt, $optionList);

    }

    public function renderTextarea(Daq_Form_Element $element)
    {
        $opt = array();
        $opt['id'] = $element->getName();
        $opt['name'] = $element->getName();
        return $this->_render("textarea", $opt, esc_html((string)$element->getValue()));
    }

    public function renderTag(Daq_Form_Element $element)
    {
        $opt = array();

        if($element->hasRenderer()) {
            $callback = $element->getRenderer();
            return call_user_func($callback, $element, array("tag"=>$tag));
        } 
        
        switch($element->getType()) {
            case Daq_Form_Element::TYPE_TEXT:
                return $this->renderInput($element, "text");
                break;
            case Daq_Form_Element::TYPE_RADIO:
                return $this->renderBox($element, "radio");
                break;
            case Daq_Form_Element::TYPE_CHECKBOX:
                return $this->renderBox($element, "checkbox");
                break;
            case Daq_Form_Element::TYPE_SELECT:
                return $this->renderSelect($element);
                break;
            case Daq_Form_Element::TYPE_FILE:
                return $this->renderInput($element, "file");
                break;
            case Daq_Form_Element::TYPE_TEXTAREA:
                return $this->renderTextarea($element);
                break;
            case Daq_Form_Element::TYPE_HIDDEN:
                return $this->renderInput($element, "hidden");
                break;
            case Daq_Form_Element::TYPE_PASSWORD:
                return $this->renderInput($element, "password");
                break;
        }
    }

    public function render(Daq_Form_Element $element)
    {
        $label = true;
        if($element->getType() == Daq_Form_Element::TYPE_CHECKBOX) {
            $label = false;
        }
        if($element->getType() == Daq_Form_Element::TYPE_RADIO) {
            $label = false;
        }
        if($element->getType() == Daq_Form_Element::TYPE_TEXT) {
            $element->addClass("regular-text");
        }


        if($element->hasErrors()) {
            $c = '<tr valign="top" class="error">';
        } else {
            $c = '<tr valign="top">';
        }

        if($element->isRequired()) {
            $req = '<span class="wpjb-red">&nbsp;*</span>';
        } else {
            $req = '';
        }

        $c.= '   <th scope="row">';
        if($label) {
            $c.= '       <label for="'.$element->getName().'">'.$element->getLabel().$req.'</label>';
        } else {
            $c.= '       '.$element->getLabel().$req;
        }
        $c.= '   </th>';
        $c.= '   <td>';
        //$c.= $this->renderTag($element);
        $c.= $element->render().PHP_EOL;
        $c.= '       <br />';

        if($element->hasHint()) {
            $c.= '       <span class="setting-description">'.$this->_escape($element->getHint()).'</span>';
        }

        if($element->hasErrors()) {
            $c.= '       <ul class="updated">';
            $c.= '           <li><strong>Following errors occured</strong></li>';
            foreach($element->getErrors() as $error) {
                $c.= '            <li>'.$error.'</li>';
            }
            $c.= '       </ul>';
        }
        
        $c.= '   </td>';
        $c.= '</tr>';

        return $c;
    }
}

?>