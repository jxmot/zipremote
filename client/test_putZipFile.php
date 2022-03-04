<?php
require_once './zipremote.php';
/*
    'bigsite' - this is the site ID, it identifies which server to access
    'key_goes_here' - this is your key, it must match both apikeys.json files
    'uztest3' - this is the unzip target ID, it will provide the path and 
                default file pattern(s) used when extracting files
    'recursmall_sub.zip' - the name of the zip file being uploaded
    '*.log' - an client optional file pattern used when extracting files

    Author: https://github.com/jxmot
    Repository: https://github.com/jxmot/zipremote
*/
// get the files...
$ret = putZipFile('bigsite', 'key_goes_here', 'uztest3', 'recursmall_sub.zip', '*.log');
// did we get a numeric return code?
if(is_numeric($ret)) {
    echo 'Zip upload failed. HTTP error code is : ' . $ret . "\n\n";
} else {
    // we got a file name, it was successful.
    echo 'zip uploaded: ' . ($ret === null ? 'ERROR' : $ret) . "\n";
}
?>