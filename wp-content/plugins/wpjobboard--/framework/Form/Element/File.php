<?php
/**
 * Description of File
 *
 * @author greg
 * @package 
 */

class Daq_Form_Element_File extends Daq_Form_Element
{
    protected $_destination = null;
    
    protected $_upload = array();
    
    protected $_maxFiles = 1;

    public final function getType()
    {
        return "file";
    }
    
    public function setMaxFiles($max) 
    {
        $this->_maxFiles = $max;
    }
    
    public function getMaxFiles()
    {
        return $this->_maxFiles;
    }
    
    public function setDestination($dest)
    {
        $this->_destination = $dest;
    }
    
    public function getDestination()
    {
        return $this->_destination;
    }
    
    public function setUploadPath($object, $directory = null) 
    {
        if(is_array($object)) {
            if(!isset($object["field"]) || empty($object["field"])) {
                $object["field"] = str_replace("_", "-", $this->getName());
            }
            $this->_upload = $object;
            return;
        }
        
        if($directory == null) {
            $directory = str_replace("_", "-", $this->getName());
        }
        
        $this->_upload = array(
            "object" => $object,
            "directory" => $directory
        );
    }
    
    public function getUploadPath($param = null) 
    {
        if($param == null) {
            return $this->_upload;
        }
        
        return $this->_upload[$param];
    }

    public function getExt()
    {
        if(!is_array($this->_value)) {
            return "";
        }
        if(stripos($this->_value['name'], ".") === false) {
            return "";
        }

        $part = explode(".", $this->_value['name']);
        return strtolower($part[count($part)-1]);
    }

    public function fileSent()
    {
        if(is_array($this->_value) && $this->_value['size']>0) {
            return true;
        } else {
            return false;
        }
    }

    public function upload()
    {
        if($this->_destination === null || !is_array($this->_value)) {
            return array();
        }
        
        $uploaded = array();
        
        foreach($this->_value as $value) {
            $sane = remove_accents(sanitize_file_name($value["name"]));
            $i = 1;
            $filename = $sane;
            while(is_file($this->_destination."/".$filename)) {
                $path = pathinfo($this->_destination."/".$sane);
                $filename = $path["filename"]."_".$i.".".$path["extension"];
                $filename = ltrim(remove_accents(sanitize_file_name($filename)), "_");
                $i++;
            }

            $tmpPath = $value['tmp_name'];
            $newPath = rtrim($this->_destination, "/")."/";

            $new_file = $newPath.$filename;

            $result = move_uploaded_file($tmpPath, $new_file);
            $new_file = apply_filters("daq_move_uploaded_file", $new_file, $tmpPath, $result);
            
            // Set correct file permissions
            $stat = @stat( dirname( $new_file ) );
            $perms = $stat['mode'] & 0007777;
            $perms = $perms & 0000666;
            @ chmod( $new_file, $perms );
            clearstatcache();
            
            $uploaded[] = $new_file;
        }
            
        return $uploaded;
    }
    
    public function validate()
    {
        $this->_hasErrors = false;
        
        $values = $this->getValue();
        if(empty($values) && !$this->isRequired()) {
            return true;
        } elseif($this->isRequired()) {
            $required = new Daq_Validate_File_Required($this->getUploadPath());
            if(!$required->isValid($values)) {
                $this->_hasErrors = true;
                $this->_errors = $required->getErrors();
                return false;
            }
        }

        foreach((array)$values as $value) {
            foreach($this->getValidators() as $validator) {
                if(!$validator->isValid($value)) {
                    $this->_hasErrors = true;
                    $this->_errors = $validator->getErrors();
                }
            }
        }

        return !$this->_hasErrors;
    }
    
    public function render()
    {
        $options = array(
            "id" => $this->getName(),
            "name" => $this->getName(),
            "class" => $this->getClasses(),
            "value" => $this->getValue(),
            "type" => "file"
        );

        $options += $this->getAttr();
        
        $input = new Daq_Helper_Html("input", $options);
        
        return $input->render();
    }
    
    public function overload(array $data) 
    {
        if(isset($data["upload_path"]) && $data["upload_path"]) {
            $this->setUploadPath($data["upload_path"]);
        }
        
        if(isset($data["file_size"]) && $data["file_size"]){
            $this->addValidator(new Daq_Validate_File_Size($data["file_size"]));
        }
        if(isset($data["file_ext"]) && $data["file_ext"]) {
            $this->addValidator(new Daq_Validate_File_Ext($data["file_ext"]));
        }
        if(isset($data["file_num"]) &&  $data["file_num"]) {
            $this->setMaxFiles($data["file_num"]);
        }
        
        parent::overload($data);
    }
    
    public function dump()
    {
        $data = parent::dump();
        
        foreach($this->getValidators() as $v) {
            $class = get_class($v);
            if($class == "Daq_Validate_File_Ext") {
                $data->file_ext = $v->getExt();
            } elseif($class == "Daq_Validate_File_Size") {
                $data->file_size = $v->getSize();
            }
        }

        $data->file_num = $this->getMaxFiles();
        
        return $data;
    }
    
    public function setValue($value) 
    {
        $arr = array();
        if(!is_array($value["name"])) {
            $c = 1;
            $arr[] = $value;
        } else {
            $c = count($value["name"]);
            for($i=0; $i<$c; $i++) {
                
                if($value["size"][$i] == 0) {
                    continue;
                }
                
                $arr[] = array(
                    "name" => $value["name"][$i],
                    "type" => $value["type"][$i],
                    "error" => $value["error"][$i],
                    "tmp_name" => $value["tmp_name"][$i],
                    "size" => $value["size"][$i],
                );
            }
        }
        
        parent::setValue($arr);
    }

}

?>