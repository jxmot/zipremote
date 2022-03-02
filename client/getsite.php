<?php
/*
    Validates the key and the site ID and returns 
    the ZipRemote site URL if successful.

    Author: https://github.com/jxmot
    Repository: https://github.com/jxmot/zipremote
*/
function getsite($siteid, $key) {
$url = null;

    global $g_goodkeys, $g_allsites;

    // valid key?
    if(isset($key) && (in_array($key, $g_goodkeys->keylist) == true)) {
        // valid site ID?
        if(isset($siteid) && isset($g_allsites->list)) {
            foreach($g_allsites->list as $site) {
                // do NOT use `===` in this if(), it will break!
                // it's because site IDs can be mixed types.
                if($siteid == $site[0]) {
                    // build the url. (not passing params in URL, 
                    // so not much is done here)
                    $url = $site[1];
                    break;
                }
            } 
        }
    }
    return $url;
}
?>