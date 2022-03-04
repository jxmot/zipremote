<?php
/*
    Make sure the important config files are present.

    This should be the first thing to execute.

    Author: https://github.com/jxmot
    Repository: https://github.com/jxmot/zipremote
*/
define('_APIKEYS_FILE', 0);
define('_CONFIG_FILE', 1);
define('_SITES_FILE', 2);

$cfgfiles = ['./apikeys.json','./cfg.json','./sites.json'];
foreach($cfgfiles as $cfile) {
    if(!file_exists($cfile)) exit('ERROR: missing configuration file: ' . $cfile);
}

// NOTE: Edit  - 
//      `example_apikeys.json` and save it as `apikeys.json`
//      `example_sites.json` and save it as `sites.json`

$g_cfg = json_decode(file_get_contents($cfgfiles[_CONFIG_FILE]));
if($g_cfg === null) exit('ERROR: bad configuration file: ' . $cfgfiles[_CONFIG_FILE]);

$g_goodkeys = json_decode(file_get_contents($cfgfiles[_APIKEYS_FILE]));
if($g_goodkeys === null) exit('ERROR: bad configuration file: ' . $cfgfiles[_APIKEYS_FILE]);

$g_allsites = json_decode(file_get_contents($cfgfiles[_SITES_FILE]));
if($g_allsites === null) exit('ERROR: bad configuration file: ' . $cfgfiles[_SITES_FILE]);
?>