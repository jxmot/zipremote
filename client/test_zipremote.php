<?php
/*
    test_zipremote.php - demo code and tests for zipremote.

    php ./test_zipremote.php

    Author: https://github.com/jxmot
    Repository: https://github.com/jxmot/zipremote
*/
require_once "getsitefiles.php";

// get the files...
$ret = getSiteFiles("bigsite", "key_goes_here", "recur");
// did we get a numeric return code?
if(is_numeric($ret)) {
    echo 'Zip retrieval failed. HTTP error code is : ' . $ret . "\n\n";
} else {
    // we got a file name, it was successful.
    echo 'zip downloaded: ' . ($ret === null ? "ERROR" : $ret) . "\n";
}

$ret = getSiteFiles("bigsite", "key_goes_here", "plogs");
if(is_numeric($ret)) {
    echo 'Zip retrieval failed. HTTP error code is : ' . $ret . "\n\n";
} else {
    echo 'zip downloaded: ' . ($ret === null ? "ERROR" : $ret) . "\n";
}

$ret = getSiteFiles("bigsite", "key_goes_here", 11);
if(is_numeric($ret)) {
    echo 'Zip retrieval failed. HTTP error code is : ' . $ret . "\n\n";
} else {
    echo 'zip downloaded: ' . ($ret === null ? "ERROR" : $ret) . "\n";
}
?>