<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of GoogleMaps
 *
 * @author greg
 */
class Wpjb_Service_GoogleMaps 
{
    
    const GEO_UNSET = 0;
    const GEO_MISSING = 1;
    const GEO_FOUND = 2;
   
    
    public static function locate($addr)
    {        
        $query = http_build_query(array(
            "address" => $addr,
            "sensor" => "false",
            "key" => wpjb_conf("google_api_key")
        ));
        $url = "https://maps.googleapis.com/maps/api/geocode/json?".$query;
        
        $response = wp_remote_get($url);
        if($response instanceof WP_Error) {
            $geo = null;
        } else {
            $geo = json_decode($response["body"]);
        }
        
        $object = new stdClass();
        
        if(!$geo || $geo->status != "OK") {
            $object->geo_status = self::GEO_MISSING;
            $object->geo_latitude = 0;
            $object->geo_longitude = 0;
        } elseif($geo->status == "OK") {
            $object->geo_status = self::GEO_FOUND;
            $object->geo_latitude  = $geo->results[0]->geometry->location->lat;
            $object->geo_longitude = $geo->results[0]->geometry->location->lng;
        } 
        
        return $object;
    }
    
    public function getGeo()
    {
        if($this->geo_status == self::GEO_UNSET) {
            $this->_locate(true); 
        }
        
        if($this->geo_status == self::GEO_FOUND) {
            $response = new stdClass;
            $response->lat = $this->geo_latitude;
            $response->lng = $this->geo_longitude;
            $response->lnglat = $response->lat.",".$response->lng;
            return $response;
        } else {
            return null;
        }
    }
}

?>
