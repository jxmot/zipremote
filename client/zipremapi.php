<?php
/*
    zipremapi.php - an API endpoint to be called from 
    JavaScript. 

    It sends this: 
        GET zipremapi.php?siteid=bigsite&key=key_goes_here&pathid=recur
            --OR--
        PUT zipremapi.php?siteid=bigsite&key=key_goes_here&pathid=recur&zipname=uptest.zip

    Responds with:
        200 - download successful
        400 - missing parameters, all 3 are required
        405 - invalid method, see the .htaccess file in the `site` folder
        500 - internal or remote internal error

        There is also a JSON response, it will contain additional
        details.

    Author: https://github.com/jxmot
    Repository: https://github.com/jxmot/zipremote
*/
require_once './zipremote.php';

$siteid = null;
$apikey = null;
$pathid = null;
$zipname = null;
$parmcnt = 0;
$ret = null;

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
            if(($_SERVER['REQUEST_METHOD'] === 'PUT') && isset($_REQUEST['zipname'])) {
                $zipname = $_REQUEST['zipname'];
                $parmcnt = $parmcnt + 1;
            }
        }
    }
}
// make sure we've got all of the required parameters...
if($parmcnt !== ($_SERVER['REQUEST_METHOD'] === 'PUT' ? 4 : 3)) {
    $httpresp = '{"msg": "missing parameter(s) '.$parmcnt.'"}';
    header('HTTP/1.0 400 Awful Request');
    header('Content-Type: application/json; charset=utf-8');
    header('Content-Encoding: text');
    header('Content-Length: ' . strlen($httpresp));
    echo $httpresp;
    exit;
}
// The request method determines which parameters 
// are needed
if($_SERVER['REQUEST_METHOD'] === 'GET') {
    $ret = getZipFile($siteid, $apikey, $pathid);
} else {
    if($_SERVER['REQUEST_METHOD'] === 'PUT') {
        // upload a zip file
        $ret = putZipFile($siteid, $apikey, $pathid, $zipname);
    } else {
        $httpresp = '{"msg": "invalid method ' . $_SERVER['REQUEST_METHOD'] . '"}';
        header('HTTP/1.0 405 Method Not Allowed - '.$_SERVER['REQUEST_METHOD']);
        header('Content-Type: application/json; charset=utf-8');
        header('Content-Encoding: text');
        header('Content-Length: ' . strlen($httpresp));
        echo $httpresp;
        exit;
    }
}
// did we get a numeric return code?
if(is_numeric($ret)) {
    $httpresp = '{"msg": "Zip operation failed. HTTP error code is '.$ret.'"}';
    header('HTTP/1.0 '.$ret.' See response');
} else {
    // we got a file name, if it was successful.
    $httpresp = '{"msg": "Zip Result: '.($ret === null ? 'ERROR' : $ret . ' was '.($_SERVER['REQUEST_METHOD'] === 'PUT' ? 'uploaded' : 'downloaded').'.').'"}';
    header('HTTP/1.0 '.($ret === null ? '500' : '200').' See response');
}
// wrap up the HTTP response and send it...
header('Content-Type: application/json; charset=utf-8');
header('Content-Encoding: text');
header('Content-Length: ' . strlen($httpresp));
echo $httpresp;
exit;
?>