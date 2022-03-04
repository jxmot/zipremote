<?php
/*
    ip_isvalid() - Checks an IP address against a list of 
    valid IP addresses.

    Params:
        $_ip - can be `null` or an IP address. If `null` 
            then `REMADDR` will be checked.

    Returns: An object, where `r` is either `true` (the 
        IP was found) or `false`. And `n` will contain 
        the associated name found in the `ipvalid.json`
        file.

    NOTES: 
        This is only a simple security method, it should
        not be used where security is crucial.

        And it may not be suitable for use with a high 
        volume of IP traffic.

    USAGE: 
        // this would typically be one of the first 
        // things executed in a PHP end point.
        require_once './ip_isvalid.php';
        $ret = ip_isvalid();
        if($ret->r === false) {
            header('HTTP/1.0 403 Not Allowed');
            exit;
        } else {
            echo $ret->n . " is here!\n\n";
            // valid IP, continue....
        }

    Author: https://github.com/jxmot
    Repository: https://github.com/jxmot/zipremote
*/
define('REMADDR',   ((isset($_SERVER['REMOTE_ADDR']) === true) ? $_SERVER['REMOTE_ADDR']  : 'none'));

/*
*/
function ip_isvalid($_ip = null) {
global $g_validips;

$ip = '';
$ret = new stdClass();
$ret->r = false;
$ret->n = '';

    if(($ip = ($_ip === null ? REMADDR : $_ip)) !== 'none') {
        foreach($g_validips->list as $vip) {
            if($ip === $vip[0]) {
                $ret->r = true;
                $ret->n = $vip[1];
                break;
            }
        }
    }
    return $ret;
}
?>