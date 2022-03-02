<?php
require_once './zipremote.php';

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