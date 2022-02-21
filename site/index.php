<?php
/*
    PHP Endpoint - Will retrieve a zip file that contains 
    just files or a recursive folder tree with files.

    The locations of the files are predetermined and found 
    in ziptargets.json. See example_ziptargets.json.

    All parameters are passed in via HTTP request header:

        key: your_key_here
        pathid: [can be numeric or a string, 
        forcedl: yes
        rmvafter: yes
    Author: https://github.com/jxmot
    Repository: https://github.com/jxmot/zipremote
*/
require_once './ip_isvalid.php';

// make sure the visitor is allowed...
$ret = ip_isvalid();
if($ret->r === false) {
    header('HTTP/1.0 403 Not Allowed');
    exit;
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
// get parameters from the header...
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
if(($forcedl === false) && ($rmvafter === true)) {
    // this is invalid, if it's not downloaded then why remove it?
    $httpresp = '{"msg": "invalid forced-download & remove-after settings"}';
    header('HTTP/1.0 400 Awful Request');
    header('Content-Type: application/json; charset=utf-8');
    header('Content-Encoding: text');
    header('Content-Length: ' . strlen($httpresp));
    echo $httpresp;
    exit;
}

// NOTE: Edit `example_apikeys.json` and save it as `apikeys.json`.
$keyfile  = './apikeys.json';
// NOTE: Edit `example_ziptargets.json` and save it as `ziptargets.json`.
$pathfile = './ziptargets.json';

$ziptarg = null;
$dlpath = null;
$dlpatt = null;
$zipfile = null;
$found = false;
$zipret = false;

define('_PATHID', 0);
define('_TARGET', 1);
define('_FILEPATT', 2);
define('_ZIPNAME', 3);

header('home: ' . getenv('HOME'));

if(isset($apikey)) {
    if(isset($pathid)) {
        if(file_exists($keyfile)) {
            $goodkeys = json_decode(file_get_contents($keyfile));
            if(in_array($apikey, $goodkeys->keylist) == true) {
                if(file_exists($pathfile)) {
                    $logpaths = json_decode(file_get_contents($pathfile));
    
                    header('pathid: ' . $pathid);
                    header('pathfile: ' . $pathfile);
                    header('dirsep: ' . DIRECTORY_SEPARATOR);
                    header('ziploc: ' . $logpaths->ziploc);
    
                    if(isset($logpaths->locations) && isset($logpaths->ziploc)) {
                        $found = false;
                        foreach($logpaths->locations as $loc) {
                            if($pathid === $loc[_PATHID]) {
                                if(strpos($loc[_TARGET], '//') !== false) {
                                    $dlpath = getenv('HOME') . substr($loc[_TARGET], 1);
                                } else {
                                    $dlpath = $loc[_TARGET];
                                }
                                header('dlpath: ' . $dlpath);

                                $ziptarg = $loc[_TARGET];
                                header('ziptarg: ' . $ziptarg);

                                if(isset($loc[_FILEPATT])) {
                                    $dlpatt = $dlpath . '/' . $loc[_FILEPATT];
                                    header('dlpatt: ' . $dlpatt);
                                } else {
                                    $dlpatt = $dlpath;
                                    header('recur: ' . $dlpatt);
                                }
    
                                $zipfile = $logpaths->ziploc . '/' . $loc[_ZIPNAME] . '.zip';
                                header('zipname: ' . $loc[_ZIPNAME] . '.zip');
                                header('zipfile: ' . $zipfile);
    
                                $found = true;
                                break; 
                            }
                        }

                        if(isset($loc[_FILEPATT])) {
                            $zipret = zipFiles($dlpatt, $zipfile, $ziptarg);
                        } else {
                            $zipret = zipFilesRecursive($dlpatt, $zipfile, $ziptarg);
                        }

                        if($zipret === true) {
                            if($forcedl === false) {
                                $httpresp = '{"found": "' . $found . '","dlpath": "' . $dlpath . '","dlpatt": "' . $dlpatt . '","zipfile": "' . $zipfile . '","pathid": "' . $pathid . '"}';
                                header('HTTP/1.0 200 OK');
                                header('Content-Type: application/json; charset=utf-8');
                                header('Content-Encoding: text');
                                header('Content-Length: ' . strlen($httpresp));
                                echo $httpresp;
                                exit;
                            } else {
                                if(file_exists($zipfile)) {
                                    header('Content-Description: File Transfer');
                                        // types the file as "binary"
                                    header('Content-Type: application/octet-stream');
                                    // types the file as "zip"
                                    //header('Content-Type: application/zip');
    
                                    header('Content-Disposition: attachment; filename="'.basename($zipfile).'"');
                                    header('Expires: 0');
                                    header('Cache-Control: must-revalidate');
                                    header('Pragma: public');
                                    header('Content-Length: ' . filesize($zipfile));
                                    header('HTTP/1.0 200 You got it! ' . $zipfile);
                                    flush(); // Flush system output buffer
                                    $rret = readfile($zipfile);
                                    if($rmvafter == true) {
                                        if($rret == true) {
                                            unlink($zipfile);
                                        }
                                        // does NOT work, file is left behind.
                                        //register_shutdown_function('unlink', $zipfile);
                                    }
                                    exit;
                                } else {
                                    $httpresp = '{"msg": "missing zip file!","data":["'.$dlpatt.'","'.$zipfile.'"]}';
                                    header('HTTP/1.0 404 Something important is missing');
                                }
                            }
                        } else {
                            $action = (isset($loc[_FILEPATT]) ? 'zipFiles' : 'zipFilesRecursive');
                            $httpresp = '{"msg": "'.$action.' failed!","data":["'.$dlpatt.'","'.$zipfile.'"]}';
                            header('HTTP/1.0 500 Something is not working');
                        }
                    } else {
                        $httpresp = '{"msg": "log path locations not set"}';
                        header('HTTP/1.0 500 Something is not working');
                    }
                } else {
                    $httpresp = '{"msg": "local log path file not found"}';
                    header('HTTP/1.0 424 Shit is missing');
                }
            } else {
                $httpresp = '{"msg": "bad key ' . $apikey . '"}';
                header('HTTP/1.0 401 Shame on you!');
            }
        } else {
            $httpresp = '{"msg": "local key file not found"}';
            header('HTTP/1.0 424 Shit is missing');
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