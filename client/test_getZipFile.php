<?php
require_once './zipremote.php';

/*

    'bigsite' - this is the site ID, it identifies which server to access
    'key_goes_here' - this is your key, it must match both apikeys.json files
    'recur' - specifies which "zip target" to zip up, and the name of the zip file

*/
// get the files...
$ret = getZipFile('bigsite', 'key_goes_here', 'recur');
// did we get a numeric return code?
if(is_numeric($ret)) {
    echo 'Zip retrieval failed. HTTP error code is : ' . $ret . "\n\n";
} else {
    // we got a file name, it was successful.
    echo 'zip downloaded: ' . ($ret === null ? 'ERROR' : $ret) . "\n";
}
?>