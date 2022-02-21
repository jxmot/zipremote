<?php
/*
    gsfapi.php - an API endpoint to be called from 
    JavaScript. 

    It sends this: 
        GET gsfapi.php?siteid=bigsite&key=key_goes_here&pathid=recur

    Responds with:
        200 - download successful
        400 - missing parameters, all 3 are required
        500 - internal or remote internal error

        There is also a JSON response, it will contain additional
        details.

    Author: https://github.com/jxmot
    Repository: https://github.com/jxmot/zipremote
*/
require_once "getsitefiles.php";

$siteid = null;
$apikey = null;
$pathid = null;
$parmcnt = 0;

// collect the required parameters...
if(isset($_REQUEST['siteid'])) {
    $siteid = $_REQUEST['siteid'];
    $parmcnt = $parmcnt + 1;
    if(isset($_REQUEST['key'])) {
        $apikey = $_REQUEST['key'];
        $parmcnt = $parmcnt + 1;
        if(isset($_REQUEST['pathid'])) {
            $pathid = $_REQUEST['pathid'];
            $parmcnt = $parmcnt + 1;
        }
    }
}
// make sure we've got all of the required parameters...
if($parmcnt !== 3) {
    $httpresp = '{"msg": "missing parameter(s) '.$parmcnt.'"}';
    header('HTTP/1.0 400 Awful Request');
    header('Content-Type: application/json; charset=utf-8');
    header('Content-Encoding: text');
    header('Content-Length: ' . strlen($httpresp));
    echo $httpresp;
    exit;
}
// get the file(s)...
$ret = getSiteFiles($siteid, $apikey, $pathid);
// did we get a numeric return code?
if(is_numeric($ret)) {
    $httpresp = '{"msg": "Zip retrieval failed. HTTP error code is '.$ret.'"}';
    header('HTTP/1.0 '.$ret.' See response');
} else {
    // we got a file name, if it was successful.
    $httpresp = '{"msg": "Zip Result: '.($ret === null ? 'ERROR' : $ret . ' was downloaded.').'"}';
    header('HTTP/1.0 '.($ret === null ? '500' : '200').' See response');
}
// wrap up the HTTP response and send it...
header('Content-Type: application/json; charset=utf-8');
header('Content-Encoding: text');
header('Content-Length: ' . strlen($httpresp));
echo $httpresp;
exit;
?>