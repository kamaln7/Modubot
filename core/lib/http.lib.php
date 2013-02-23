<?php

abstract class HTTP {

    /**
     * POSTS data to a URL in JSON format.
     * 
     * @param string $url The URL to POST to.
     * @param array $parameters The parameters to pass.
     * @param string $return Returns 
     * @return string Returns what the server outputted.
     */
    public static function post($url, $parameters, $return = true) {
        $ch = curl_init();

        $curl_options = array(
            CURLOPT_HTTPHEADER => array('Content-Type: application/json'),
            CURLOPT_URL => $url,
            CURLOPT_POST => count($parameters),
            CURLOPT_POSTFIELDS => json_encode($parameters),
            CURLOPT_RETURNTRANSFER => ($return == true),
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false
        );

        curl_setopt_array($ch, $curl_options);

        $result = curl_exec($ch);

        curl_close($ch);
        
        if(!$return){
            if(!curl_errno($ch)){
                return true;
            }else {
                return false;
            }
        }
        
        return $result;
    }

    /**
     * GETS the contents of a URL.
     * 
     * @param string $url The URL to GET from.
     * @return string Returns what the server outputted.
     */
    public static function get($url) {
        return file_get_contents($url);
    }

    public static function unparse_url($parsed_url) { 
        $scheme   = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : ''; 
        $host     = isset($parsed_url['host']) ? $parsed_url['host'] : ''; 
        $port     = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : ''; 
        $user     = isset($parsed_url['user']) ? $parsed_url['user'] : ''; 
        $pass     = isset($parsed_url['pass']) ? ':' . $parsed_url['pass']  : ''; 
        $pass     = ($user || $pass) ? "$pass@" : ''; 
        $path     = isset($parsed_url['path']) ? $parsed_url['path'] : ''; 
        $query    = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : ''; 
        $fragment = isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : ''; 
        return "$scheme$user$pass$host$port$path$query$fragment"; 
    } 

}
