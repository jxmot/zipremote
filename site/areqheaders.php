<?php
/*
    This is intended as a replacement when apache_request_headers() is 
    not available.
 
    Obtained From:
        https://stackoverflow.com/questions/2916232/call-to-undefined-function-apache-request-headers
        https://pastebin.com/2NGARe49 <- original source

    Repository: https://github.com/jxmot/zipremote
*/
if(!function_exists('apache_request_headers')) {
    function apache_request_headers() {
        static $arh = array();
        if(!$arh) {
            $rx_http = '/\AHTTP_/';
            foreach($_SERVER as $key => $val) {
                if(preg_match($rx_http, $key)) {
                    $arh_key = preg_replace($rx_http, '', $key);
                    $rx_matches = array();
                    // do some nasty string manipulations to restore the original letter case
                    // this should work in most cases
                    $rx_matches = explode('_', $arh_key);
                    if(count($rx_matches) > 0 and strlen($arh_key) > 2) {
                        foreach($rx_matches as $ak_key => $ak_val) {
                            $rx_matches[$ak_key] = ucfirst($ak_val);
                        }
                        $arh_key = implode('-', $rx_matches);
                    }
                    $arh[$arh_key] = $val;
                }
            }
        }
        if(isset($_SERVER['CONTENT_TYPE'])) $arh['Content-Type'] = $_SERVER['CONTENT_TYPE'];
        if(isset($_SERVER['CONTENT_LENGTH'])) $arh['Content-Length'] = $_SERVER['CONTENT_LENGTH'];
        return $arh;
    }
}
?>