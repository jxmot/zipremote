<?php
/*
    Make sure the important config files are present.

    This should be the first thing to execute.

    Author: https://github.com/jxmot
    Repository: https://github.com/jxmot/zipremote
*/
define('_APIKEYS_FILE', 0);
define('_IPVALID_FILE', 1);
define('_TZONE_FILE', 2);
define('_ZIPTARG_FILE', 3);

$cfgfiles = ['./apikeys.json','./ipvalid.json','./tzone.json','./ziptargets.json'];
foreach($cfgfiles as $cfile) {
    if(!file_exists($cfile)) exit('ERROR: missing configuration file: ' . $cfile);
}

// NOTE: Edit  - 
//      `example_apikeys.json` and save it as `apikeys.json`
//      `example_ipvalid.json` and save it as `ipvalid.json`
//      `example_ziptargets.json` and save it as `ziptargets.json`

$g_goodkeys = json_decode(file_get_contents($cfgfiles[_APIKEYS_FILE]));
if($g_goodkeys === null) exit('ERROR: bad configuration file: ' . $cfgfiles[_APIKEYS_FILE]);

$g_validips = json_decode(file_get_contents($cfgfiles[_IPVALID_FILE]));
if($g_validips === null) exit('ERROR: bad configuration file: ' . $cfgfiles[_IPVALID_FILE]);

$g_tzone = json_decode(file_get_contents($cfgfiles[_TZONE_FILE]));
if($g_tzone === null) exit('ERROR: bad configuration file: ' . $cfgfiles[_TZONE_FILE]);

$g_ziptarg = json_decode(file_get_contents($cfgfiles[_ZIPTARG_FILE]));
if($g_ziptarg === null) exit('ERROR: bad configuration file: ' . $cfgfiles[_ZIPTARG_FILE]);
?>