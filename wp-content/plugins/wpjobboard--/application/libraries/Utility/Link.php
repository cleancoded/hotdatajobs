<?php

/**
 * This class handles "uploading" links.
 * 
 * If user decides to link to a file from media library instead of uploading
 * it from his computer then this class will handle storing the link either 
 * in transient or meta (if the form is already saved in DB).
 * 
 * @since 4.4.1
 * @access public
 * @author Grzegorz Winiarski
 * @package WPJB
 */

class Wpjb_Utility_Link 
{
    /**
     * Transient key name parts
     *
     * @var array
     */
    protected $_name = array();
    
    /**
     * Transient name
     *
     * @var string
     */
    protected $_transient = null;


    /**
     * Creates an instance of Wpjb_Utility_Link
     * 
     * @param array $name         Transient key name parts
     * @param string $transient   Custom transient name
     */
    
    public function __construct($name, $transient = null) 
    {
       $this->_name["object"] = $name["object"];
       $this->_name["field"] = $name["field"];
       
       if(is_numeric($name["id"])) {
           $this->_name["id"] = absint($name["id"]);
       } else {
           $this->_name["id"] = null;
       }
       
       if($transient === null) {
           $this->_transient = "wpjb_file_link_".str_replace("-", "_", wpjb_transient_id());
       } else {
           $this->_transient = sprintf($transient, str_replace("-", "_", wpjb_transient_id()));
       }
    }
    
    /**
     * Return unique key name for this link
     * 
     * The unique key is generated from name params provided in __construct($name)
     * 
     * @since 4.4.1
     * @return string
     */
    public function getName()
    {
        $object = $this->_name["object"];
        $field = $this->_name["field"];
        $id = $this->_name["id"];
        
        return "$object.$id.$field";
    }
    
    /**
     * Return all links
     * 
     * Returns links saved for this field in transients and in meta
     * 
     * @since 4.4.1
     * @return array List of links
     */
    public function getAll()
    {
        $tr = $this->getTransients();
        $mt = $this->getMetas();
        
        if(!is_array($tr)) {
            $tr = array();
        }
        
        if(!is_array($mt)) {
            $mt = array();
        }
        
        return $tr + $mt;
    }
    
    /**
     * Returns list of links in transient
     * 
     * This function returns list of links for this field stored in transient
     * 
     * @since 4.4.1
     * @return array List of links stored in transient
     */
    public function getTransients()
    {
        $transient = get_transient($this->_transient);

        $object = $this->_name["object"];
        $id = wpjb_upload_id($this->_name["id"]);
        $field = $this->_name["field"];
        
        $tname = "$object.$id.$field";
        
        if($transient === false) {
            return array();
        }
        if(!isset($transient[$tname])) {
            return array();
        }

        return $transient[$tname];
    }
    
    /**
     * Returns list of links in meta data
     * 
     * This function returns list of links for this field stored in meta data.
     * 
     * @since 4.4.1
     * @return array List of links stored in transient
     */
    public function getMetas()
    {
        $oclass = $this->_name["object"];
        $fname = $this->_name["field"];
        
        $object = new $oclass($this->_name["id"]);
        
        if(!isset($object->meta->$fname)) {
            return array();
        }
        
        $meta = $object->meta->$fname;
        $mv = $meta->getFirst();
        
        $data = maybe_unserialize($mv->value);
        
        if($data === null) {
            $data = array();
        }
        
        return $data;
    }
    
    /**
     * Save link in transient or meta
     * 
     * $link = array(
     *  "id => ...,
     *  "url" => ...,
     * );
     * 
     * @since 4.4.1
     * @param array $link
     */
    public function saveLink($link) 
    {
        if(!is_numeric($this->_name["id"])) {
            // save in transient
            $this->_saveTransient($link);
        } else {
            // save in meta
            $this->_saveMeta($link);
        }
    }
    
    /**
     * Save link in transient.
     * 
     * This function is executed by self::saveLink()
     * 
     * @since 4.4.1
     * @param array $link
     */
    protected function _saveTransient($link) 
    {
        $transient = wpjb_session()->get($this->_transient);;
        
        $object = $this->_name["object"];
        $id = wpjb_upload_id($this->_name["id"]);
        $field = $this->_name["field"];
        
        $tname = "$object.$id.$field";
        
        if(array_key_exists($tname, (array)$transient)) {
            $transient[$tname][] = $link;
        } else {
            $transient[$tname] = array($link);
        }
        
        wpjb_session()->set($this->_transient, $transient);
    }
    
    /**
     * Save link in meta value.
     * 
     * This function is executed by self::saveLink()
     * 
     * @since 4.4.1
     * @param array $link
     */
    protected function _saveMeta($link)
    {
        $oclass = $this->_name["object"];
        $fname = $this->_name["field"];
        
        $object = new $oclass($this->_name["id"]);
        
        if(!isset($object->meta->$fname)) {
            return;
        }
        
        $meta = $object->meta->$fname;
        $mv = $meta->getFirst();
        
        $data = maybe_unserialize($mv->value);
        
        if($data === null) {
            $data = array();
        }
        
        $data[] = $link;
        $mv->value = serialize($data);
        $mv->save();
    }
    
    /**
     * Remove link from transient or meta
     * 
     * @since 4.4.1
     * @param int $postId ID of post to remove
     */
    public function remove($postId)
    {
        if(!is_numeric($this->_name["id"])) {
            // remove from transient
            $this->removeTransient($postId);
        } else {
            // remove from meta
            $this->removeMeta($postId);
        }
    }
    
    /**
     * Remove link from transient
     * 
     * @since 4.4.1
     * @param int $postId ID of post to remove
     */
    public function removeTransient($postId) 
    {
        $transient = wpjb_session()->get($this->_transient);

        $object = $this->_name["object"];
        $id = wpjb_upload_id($this->_name["id"]);
        $field = $this->_name["field"];
        
        $tname = "$object.$id.$field";

        if(array_key_exists($tname, $transient)) {
            $newt = array();
            foreach($transient[$tname] as $tr) {
                if($tr["id"] != $postId) {
                    $newt[] = $tr;
                }
            }
            
            if(empty($newt)) {
                unset($transient[$tname]);
            } else {
                $transient[$tname] = $newt;
            }
            
            wpjb_session()->set($this->_transient, $transient);
        }
    }
    
    /**
     * Remove link from meta
     * 
     * @since 4.4.1
     * @param int $postId ID of post to remove
     */
    public function removeMeta($postId)
    {
        $oclass = $this->_name["object"];
        $fname = $this->_name["field"];
        
        $object = new $oclass($this->_name["id"]);
        
        if(!isset($object->meta->$fname)) {
            return;
        }
        
        $meta = $object->meta->$fname;
        $mv = $meta->getFirst();
        
        $data = maybe_unserialize($mv->value);
        
        if($data === null) {
            $data = array();
        }
        
        $ndata = array();
        foreach($data as $link) {
            if($link["id"] != $postId) {
                $ndata[] = $link;
            }
        }

        $mv->value = serialize($ndata);
        $mv->save();
    }
    
    /**
     * Move transients for this object to meta data
     * 
     * Copies all transient data for $object.$field to meta data. This will be run 
     * usually when object is saved in DB.
     * 
     * @param int $toId Object id
     */
    public function move($toId) 
    {
        $transients = $this->getTransients();
        
        foreach($transients as $tr) {
            $this->removeTransient($tr["id"]);
        }
        
        $id = $this->_name["id"];
        $this->_name["id"] = absint($toId);
        
        foreach($transients as $tr) {
            $this->_saveMeta($tr);
        }
        
        $this->_name["id"] = $id;
    }
    
    public function test($link)
    {
        $this->_saveMeta($link);
    }
}