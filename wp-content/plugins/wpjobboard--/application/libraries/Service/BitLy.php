<?php
/**
 * Description of BitLy
 *
 * @author greg
 * @package 
 */

class Wpjb_Service_BitLy
{
    const API_URL = "http://api.bit.ly/";

    public static function shorten($url)
    {
        $query = array(
            "version" => "2.0.1",
            "longUrl" => $url,
            "login" => Wpjb_Project::getInstance()->conf("api_bitly_login"),
            "apiKey" => Wpjb_Project::getInstance()->conf("api_bitly_key")
        );

        $query = http_build_query($query);

        $response = self::_download(self::API_URL."shorten?".$query);
        $response = json_decode($response);

        if($response->errorCode == 0 && $response->statusCode == "OK") {
            return $response->results->{$url}->shortUrl;
        } else {
            throw new Exception("Bit.ly API: ".$response->errorMessage);
        }
    }

    private static function _download($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }
}

?>