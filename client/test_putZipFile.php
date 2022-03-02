<?php
require_once './zipremote.php';

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