<?php
/*
    PHP Endpoint - There are two base functions that can be 
    performed here:

        1) GET - add files to a zip file on the server and force 
        download the zip file to the client.
            a) zip files can be flat or recursive
            b) file patterns can be used, glob style only.

        2) PUT - upload a zip file from the client and extract 
        its contents.
            a) zip files can be flat or recursive
            b) file patterns can be used, non-glob.

    The locations of the zip fles and the target files are 
    predetermined and found in ziptargets.json. 

    See example_ziptargets.json.

    All parameters are passed in via HTTP request header.

    Author: https://github.com/jxmot
    Repository: https://github.com/jxmot/zipremote
*/
require_once './configchk.php';

// using $dbg_header() to mute some of the 
// stuff going into the response header
function not_header() {};
//$dbg_header = 'header';
$dbg_header = 'not_header';

// Initially this is turned off, this will provide the 
// opportunity to get ZipRemote up and running first.
// Change the following to `true` to enable IP validation.
$ipv = false;
if($ipv === true) {
    require_once './ip_isvalid.php';
    // make sure the visitor is allowed...
    $ret = ip_isvalid();
    if($ret->r === false) {
        header('HTTP/1.0 403 Not Allowed');
        exit;
    }
}

require_once './areqheaders.php';
require_once './ziplib.php';

// defaults
$forcedl = true;
$rmvafter = true;
// don't edit these
$apikey = null;
$pathid = null;
$httpresp = null;

// get the request header...
$reqheader = apache_request_headers();
// get parameters...
//      api key
if(isset($reqheader['KEY'])) {
    $apikey = $reqheader['KEY'];
} else {
    if(isset($_REQUEST['key'])) {
        $apikey = $_REQUEST['key'];
    }
}
//      target path ID
if(isset($reqheader['PATHID'])) {
    if(is_numeric($reqheader['PATHID'])) {
        $pathid = intval($pathid);
    } else {
        $pathid = $reqheader['PATHID'];
    }
} else {
    if(isset($_REQUEST['pathid'])) {
        if(is_numeric($_REQUEST['pathid'])) {
            $pathid = intval($pathid);
        } else {
            $pathid = $_REQUEST['pathid'];
        }
    }
}
// the following parameters are either defaulted (see above) or 
// set in the request header. they are not URL parameters.
//      force downloads to client?
if(isset($reqheader['FORCEDL'])) {
    $forcedl = (($reqheader['FORCEDL'] === 'yes') ? true : false);
}
//      remove the zipfile after all is done?
if(isset($reqheader['RMVAFTER'])) {
    $rmvafter = (($reqheader['RMVAFTER'] === 'yes') ? true : false);
}
// check validity...
if(($forcedl === false) && ($rmvafter === true) && ($_SERVER['REQUEST_METHOD'] === 'GET')) {
    // this is invalid, if it's not downloaded then why remove it?
    $httpresp = '{"msg": "invalid forced-download & remove-after settings"}';
    header('HTTP/1.0 400 Awful Request');
    header('Content-Type: application/json; charset=utf-8');
    header('Content-Encoding: text');
    header('Content-Length: ' . strlen($httpresp));
    echo $httpresp;
    exit;
}

$ziptarg = null;
$zippatt = null;
$zipfile = null;
$found = false;
$zipret = false;
$loc = null;

define('_PATHID', 0);
define('_TARGET', 1);
define('_FILEPATT', 2);
define('_ZIPNAME', 3);

$dbg_header('home: ' . getenv('HOME'));

if(isset($apikey)) {
    if(isset($pathid)) {
        if(in_array($apikey, $g_goodkeys->keylist) == true) {
    
            $dbg_header('pathid: ' . $pathid);
            $dbg_header('pathfile: ' . $pathfile);
            $dbg_header('ziploc: ' . $g_ziptarg->ziploc);
    
            if(isset($g_ziptarg->locations) && isset($g_ziptarg->ziploc)) {
                $found = false;
                foreach($g_ziptarg->locations as $_loc) {
                    $tmp = new ArrayObject($_loc); 
                    $loc = $tmp->getArrayCopy();
                    if($pathid === $loc[_PATHID]) {
                        $dbg_header('zip-target: ' . $loc[_TARGET]);
                        if(strpos($loc[_TARGET], '//') !== false) {
                            $ziptarg = getenv('HOME') . substr($loc[_TARGET], 1);
                        } else {
                            $ziptarg = $loc[_TARGET];
                        }
                        $dbg_header('ziptarg: ' . $ziptarg);
                        $found = true;
                        break; 
                    }
                }
                if($found === true) {
                    // down or up? zip or unzip?
                    if($_SERVER['REQUEST_METHOD'] === 'GET') {
                        if(isset($loc[_ZIPNAME])) {
                            $zipfile = $g_ziptarg->ziploc . '/' . $loc[_ZIPNAME] . '.zip';
                            header('zipname: ' . $loc[_ZIPNAME] . '.zip');
                            $dbg_header('zipfile: ' . $zipfile);
                            if(isset($loc[_FILEPATT])) {
                                $zippatt = $ziptarg . '/' . $loc[_FILEPATT];
                                $dbg_header('zippatt: ' . $zippatt);
                                $zipret = zipFiles($zippatt, $zipfile, $ziptarg);
                            } else {
                                $zippatt = $ziptarg;
                                $dbg_header('recur: ' . $zippatt);
                                $zipret = zipFilesRecursive($zippatt, $zipfile, $ziptarg);
                            }
                            if($zipret === true) {
                                if($forcedl === false) {
                                    $httpresp = '{"found": "' . $found . '","ziptarg": "' . $ziptarg . '","zippatt": "' . $zippatt . '","zipfile": "' . $zipfile . '","pathid": "' . $pathid . '"}';
                                    header('HTTP/1.0 200 OK');
                                    header('Content-Type: application/json; charset=utf-8');
                                    header('Content-Encoding: text');
                                    header('Content-Length: ' . strlen($httpresp));
                                    echo $httpresp;
                                    exit;
                                } else {
                                    // forced download, 
                                    if(file_exists($zipfile)) {
                                        header('Content-Description: File Transfer');
                                        // types the file as "binary"
                                        header('Content-Type: application/octet-stream');
                                        header('Content-Disposition: attachment; filename="'.basename($zipfile).'"');
                                        header('Expires: 0');
                                        header('Cache-Control: must-revalidate');
                                        header('Pragma: public');
                                        header('Content-Length: ' . filesize($zipfile));
                                        header('HTTP/1.0 200 You got it! ' . $zipfile);
                                        flush(); // Flush system output buffer
                                        $rret = readfile($zipfile);
                                        // it's optional to remove the zip file after the download
                                        if($rmvafter == true) {
                                            if($rret == true) {
                                                unlink($zipfile);
                                            }
                                        }
                                        exit;
                                    } else {
                                        $httpresp = '{"msg": "missing zip file!","data":["'.$zippatt.'","'.$zipfile.'"]}';
                                        header('HTTP/1.0 404 Something important is missing');
                                    }
                                }
                            } else {
                                $action = (isset($loc[_FILEPATT]) ? 'zipFiles' : 'zipFilesRecursive');
                                $httpresp = '{"msg": "'.$action.' failed!","data":["'.$zippatt.'","'.$zipfile.'"]}';
                                header('HTTP/1.0 500 Something is not working');
                            }
                        } else {
                            $httpresp = '{"msg": "Invalid path ID, '.$pathid.' is not a download path"}';
                            header('HTTP/1.0 424 Invalid path ID - '.$pathid);
                        }
                    } else { // if($_SERVER['REQUEST_METHOD'] === 'GET')
                        if($_SERVER['REQUEST_METHOD'] === 'PUT') {
                            if(isset($loc[_ZIPNAME])) {
                                $httpresp = '{"msg": "Invalid path ID, '.$pathid.' is not an upload path"}';
                                header('HTTP/1.0 424 Invalid path ID - '.$pathid);
                            } else {
                                $zipfile = $g_ziptarg->ziploc . '/';
                                // did we get the name of the file?
                                if(isset($reqheader['ZIPNAME'])) {
                                    $zipfile = $zipfile . $reqheader['ZIPNAME'];
                                } else $zipfile = $zipfile . 'temp.zip';
                                // if the target destination does not exist
                                // then create it.
                                if(!file_exists($ziptarg)) {
                                    mkdir($ziptarg, 0777, true);
                                }
                                // get the zip file...
                                // https://www.php.net/manual/en/wrappers.php.php
                                // php://input is a read-only stream that allows you to 
                                // read raw data from the request body. php://input is 
                                // not available with enctype="multipart/form-data". 
                                // 
                                // PUT data(like a file) comes in via raw input...
                                $zipdata = fopen('php://input', 'r');
                                $zipout = fopen($zipfile, 'w');
                                // Read the data a chunk at a time and write to the file...
                                while($data = fread($zipdata, 8192)) {
                                    fwrite($zipout, $data);
                                    unset($data);
                                }
                                // Close the file and php://input
                                fclose($zipout);
                                fclose($zipdata);
                                // extraction is optional
                                if((isset($reqheader['EXTRACT'])) && ($reqheader['EXTRACT'] === 'yes')) {
                                    $patt = null;
                                    // a "pattern" can be set by the client in 
                                    // the header. It has priority over the 
                                    // patterns in ziptargets.json
                                    if(isset($reqheader['PATTERN']))
                                        $patt = $reqheader['PATTERN'];
                                    else if(isset($loc[_FILEPATT]))
                                        $patt = $loc[_FILEPATT];
                                    else $patt = null;
                                    $uzret = unzipFiles($zipfile, $ziptarg, $patt);
                                    $httpresp = '{"uzret": '.json_encode($uzret).',"msg": "ULUZ the file!","data":["'.$zipfile.'","'.$ziptarg.'","'.$_SERVER['REQUEST_METHOD'].'"]}';
                                    header('HTTP/1.0 '.($uzret->res === true ? '200 Great!' : '500 '.$uzret->msg));
                                    // it's also optional to remove the zip file after extraction
                                    if($rmvafter == true) {
                                        unlink($zipfile);
                                        // does NOT work, file is left behind:
                                        //register_shutdown_function('unlink', $zipfile);
                                    }
                                } else { //if((isset($reqheader['EXTRACT'])) && ($reqheader['EXTRACT'] === 'yes'))
                                    $httpresp = '{"msg": "UL the file!","data":["'.$zipfile.'","'.$ziptarg.'","'.$_SERVER['REQUEST_METHOD'].'"]}';
                                    header('HTTP/1.0 200 Great!');
                                }
                            }
                        } else { // method is not allowed, usually handled in the closest .htaccess file
                            $httpresp = '{"msg": "invalid method ' . $_SERVER['REQUEST_METHOD'] . '"}';
                            header('HTTP/1.0 405 Not Allowed - '.$_SERVER['REQUEST_METHOD']);
                        }
                    }
                } else {// if($found === true)
                    $httpresp = '{"msg": "invalid pathid ' . $pathid . '"}';
                    header('HTTP/1.0 401 Shame on you!');
                }
            } else {
                $httpresp = '{"msg": "log path locations not set"}';
                header('HTTP/1.0 500 Something is not working');
            }
        } else {
            $httpresp = '{"msg": "bad key ' . $apikey . '"}';
            header('HTTP/1.0 401 Shame on you!');
        }
    } else {
        $httpresp = '{"msg": "missing pathid parameter"}';
        header('HTTP/1.0 400 Awful Request');
    }
} else {
    $httpresp = '{"msg": "missing apikey parameter"}';
    header('HTTP/1.0 400 Awful Request');
}
header('Content-Type: application/json; charset=utf-8');
header('Content-Encoding: text');
header('Content-Length: ' . strlen($httpresp));
echo $httpresp;
exit;
?>