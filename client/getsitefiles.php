<?php
/*
    getsitefiles.php - contains the getSiteFiles() function that 
    obtains zip files from a server in predetermined locations.

    Author: https://github.com/jxmot
    Repository: https://github.com/jxmot/zipremote
*/
require_once 'parseheaders.php';

// get the configuration data
$cfgfile  = './gsfcfg.json';
$cfg = null;
if(file_exists($cfgfile)) {
    $cfg = json_decode(file_get_contents($cfgfile));
} else exit;

// valid keys and valid sites
// NOTE: Edit `example_apikeys.json` and save it as `apikeys.json`.
$keyfile  = './apikeys.json';
// NOTE: Edit `example_sites.json` and save it as `sites.json`.
$sitesfile = './sites.json';

/*
    Builds the URL string for the target server.

    Also validates the key and the site ID.
*/
function buildurl($siteid, $key) {
$url = null;

    global $keyfile, $sitesfile;

    if(file_exists($keyfile)) {
        $goodkeys = json_decode(file_get_contents($keyfile));
        if(in_array($key, $goodkeys->keylist) == true) {
            if(file_exists($sitesfile)) {
                $allsites = json_decode(file_get_contents($sitesfile));
                if(isset($siteid) && isset($allsites->list)) {
                    foreach($allsites->list as $site) {
                        // do NOT use `===` in this if(), it will break!
                        if($siteid == $site[0]) {
                            // build the url (not passing params in URL, 
                            // so not much is done here)
                            $url = $site[1];
                            break;
                        }
                    } 
                }
            }
        }
    }
    return $url;
}

/*
    Download a zip file from a predetermined location.

    * Verify the site ID and key
    * Create the URL

    * Create a request header
    * Send the request

    * Receive a zip file or error code and handle it

    Returns:
        null = an operation has failed
        zip file name = success
        number = HTTP response code, the endpoint failed
*/
function getSiteFiles($siteid, $key, $pathid) {
$siteurl = null;
$file = null;
$ret = null;

    global $cfg;
    if(!isset($cfg)) return null;

    if(($siteurl = buildurl($siteid, $key)) === null) return null;

    // create the request header...
    $opts = array(
        'http' => array(
            'header' => "Accept: */*\r\n" .
            "User-Agent: getSiteFiles\r\n" .
            "Cache-Control: no-cache\r\n" .
            "key: " . $key . "\r\n" .
            "pathid: " . $pathid . "\r\n" .
            ((isset($cfg->forcedl)) && $cfg->forcedl === true ? "forcedl: yes" : "forcedl: no") . "\r\n" .
            ((isset($cfg->rmvafter)) && $cfg->rmvafter === true ? "rmvafter: yes" : "rmvafter: no") . "\r\n" .
            "Accept-Encoding: gzip, deflate, br\r\n"
        )
    );

    $context = stream_context_create($opts);
    $filecont = file_get_contents($siteurl, true, $context);

    $pheader = parseHeaders($http_response_header);

    if($pheader['reponse_code'] == 200) {
        // get destination path and file name...
        $file = $cfg->ziploc . $cfg->dirsep . $pheader['zipname'];

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
?>