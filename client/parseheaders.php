<?php
// obtained at - https://www.php.net/manual/en/reserved.variables.httpresponseheader.php
//
// And edited just a little by - 
//  Author: https://github.com/jxmot
//  Repository: https://github.com/jxmot/zipremote
function parseHeaders($headers)
{
    $head = array();
    foreach($headers as $k => $v) {
        $t = explode(':', $v, 2);
        if(isset($t[1])) {
            $head[trim($t[0])] = trim($t[1]);
        } else {
            $head[] = $v;
            if( preg_match("#HTTP/[0-9\.]+\s+([0-9]+)#",$v,$out)) {
                $head['reponse_code'] = intval($out[1]);
            }
        }
    }
    return $head;
}
?>