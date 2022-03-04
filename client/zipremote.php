<?php
/*
    zipremote.php - For client side use, retrieve or 
    upload a zip archive using ZipRemote on the remote
    server.

    Author: https://github.com/jxmot
    Repository: https://github.com/jxmot/zipremote
*/
require_once './configchk.php';
require_once './parseheaders.php';
require_once './getsite.php';

/*
    Download a zip file from a predetermined location.

    This function will:
        * Verify the site ID and key
        * Create the URL
        * Create a request header
        * Send the request
        * Receive a zip file or error code and handle it

    Returns:
        Failure:
            * null = (one of the following has occurred)
                * the $siteid or $key args are invalid
                * the downloaded zip file could not be saved
            * numeric return code (not 200) = an HTTP error has occurred
        Success:
            * a string containing the path+zipfile name that was saved
*/
function getZipFile($siteid, $key, $pathid) {
$siteurl = null;
$file = null;
$ret = null;

    global $g_cfg;

    // Verify the site ID and key, Create the URL
    if(($siteurl = getsite($siteid, $key)) === null) return null;
    // create the request header...
    $opts = array(
        'http' => array(
            'header' => "Accept: */*\r\n" .
            "User-Agent: getSiteFiles\r\n" .
            "Cache-Control: no-cache\r\n" .
            "key: " . $key . "\r\n" .
            "pathid: " . $pathid . "\r\n" .
            ((isset($g_cfg->forcedl)) && $g_cfg->forcedl === true ? "forcedl: yes" : "forcedl: no") . "\r\n" .
            ((isset($g_cfg->rmvafter)) && $g_cfg->rmvafter === true ? "rmvafter: yes" : "rmvafter: no") . "\r\n" .
            "Accept-Encoding: gzip, deflate, br\r\n"
        )
    );
    // Send the request
    $context = stream_context_create($opts);
    $filecont = @file_get_contents($siteurl, true, $context);
    // Receive a zip file or error code and handle it
    $pheader = parseHeaders($http_response_header);
    if($pheader['reponse_code'] == 200) {
        // get destination path and file name...
        $file = $g_cfg->ziploc . $g_cfg->dirsep . $pheader['zipname'];
        $fret = file_put_contents($file, $filecont);
        if($fret === false) {
            $ret = null;
        } else { 
            $ret = $file; 
        }
    } else {
        $ret = intval($pheader['reponse_code']);
    }
    return $ret;
}

/*
    Upload a zip file to a predetermined location.

    This function will:
        * Verify the site ID and key
        * Create the URL
        * Create a request header
        * Send the request
        * Upload a zip file

    Returns:
        Failure:
            * null = the $siteid or $key args are invalid
            * numeric return code (not 200) = an HTTP error has occurred
        Success:
            * JSON formatted text string = the operation succeeded
*/
function putZipFile($siteid, $key, $pathid, $zipname, $pattern = null, $extract = 'yes') {
$siteurl = null;
$ret = null;

    global $g_cfg;

    // Verify the site ID and key, Create the URL
    if(($siteurl = getsite($siteid, $key)) === null) return null;
    // get the zip file contents
    $data = file_get_contents($g_cfg->ziploc . $g_cfg->dirsep . $zipname);
    // create the request header...
    $opts = array(
        'http' => array(
            'method' => 'PUT',
            'content' => $data,
            'header' => "Accept: */*\r\n" .
            "User-Agent: putSiteFiles\r\n" .
            "Cache-Control: no-cache\r\n" .
            "Content-Type: application/zip\r\n" .
            "Content-Length: " .filesize($g_cfg->ziploc . $g_cfg->dirsep . $zipname). "\r\n".
            "Accept-Encoding: gzip, deflate, br\r\n".
            "Connection: keep-alive\r\n".

            "key: " . $key . "\r\n" .
            "pathid: " . $pathid . "\r\n" .
            ((isset($g_cfg->forcedl)) && $g_cfg->forcedl === true ? "forcedl: yes" : "forcedl: no") . "\r\n" .
            ((isset($g_cfg->rmvafter)) && $g_cfg->rmvafter === true ? "rmvafter: yes" : "rmvafter: no") . "\r\n" .
            "zipname: ".$zipname."\r\n" .
            "extract: ".$extract."\r\n" .
            ($pattern !== null ? "pattern: ".$pattern."\r\n" : '')
        )
    );
    // Send the request, Upload a zip file
    $context = stream_context_create($opts);
    $resp = json_decode(@file_get_contents($siteurl, false, $context));
    // Uploaded a zip file, check rhe error code and handle it
    $pheader = parseHeaders($http_response_header);
    if($pheader['reponse_code'] == 200) {
        $ret = $resp->data[0];
    } else {
        $ret = intval($pheader['reponse_code']);
    }
    return $ret;
}
?>