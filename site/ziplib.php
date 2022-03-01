<?php
/*
    Zip Library - contains functions for adding files to 
    a zip file. Three methods are provided:

    * zipFiles() - zip files in a single destination (not recursive!)
    * zipFilesRecursive() - recursively add files and keep relative paths
    * unzipFiles() - unzip folders and files

    NOTE: All file related operations will overwrite. This includes
    the creation of zip files and the extraction of contents. 

    BE CAREFUL!

    Author: https://github.com/jxmot
    Repository: https://github.com/jxmot/zipremote
*/
require_once './timezone.php';

/*
*/
function isJSON($string) {
   return is_string($string) && is_array(json_decode($string, true)) && (json_last_error() == JSON_ERROR_NONE) ? true : false;
}

/*
*/
function zipFiles($tozip, $zipfile, $ziptarg) {
$zip = new ZipArchive;
$ret = false;

    if(($zip->open($zipfile, ZipArchive::CREATE | ZipArchive::OVERWRITE)) === true) {
// NOTE: remove_all_path does NOT work in php 5.6! bad zip files are created!
        $options = array('remove_all_path' => true);
        if(($flist = $zip->addGlob($tozip, (GLOB_NOSORT | GLOB_ERR | GLOB_BRACE), $options)) === false) {
            $ret = false;
        } else {
            $comment = '{"time": '. time() . ', "dtime": ' . zonetime('["Ymd","His"]') . ',"count": ' . count($flist) . ',"ziptarg": "' . $ziptarg . '"}';
            $zip->setArchiveComment($comment);
            $ret = true;
        }
        $zip->close();
    } else {
        $ret = false;
    }
    return $ret;
}

/*
*/
function zipFilesRecursive($tozip, $zipfile, $ziptarg) {
$zip = new ZipArchive;
$ret = false;
$count = 0;

    if(($zip->open($zipfile, ZipArchive::CREATE | ZipArchive::OVERWRITE)) === true) {
        // Create recursive directory iterator
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($tozip),
                                                   RecursiveIteratorIterator::LEAVES_ONLY);

        foreach($files as $name => $file) {
            // Skip directories (they would be added automatically)
            if(!$file->isDir()) {
                // Get real and relative path for current file
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($tozip) + 1);
                // Add current file to archive
                $zip->addFile($filePath, $relativePath);
                $count = $count + 1;
            }
        }

        $comment = '{"time": '. time() . ', "dtime": ' . zonetime('["Ymd","His"]') . ',"count": ' . $count . ',"ziptarg": "' . $ziptarg . '"}';
        $zip->setArchiveComment($comment);

        // Zip archive will be created only after closing
        $zip->close();
        $ret = true;
    } else {
        $ret = false;
    }
    return $ret;
}

/*
    unzipFiles() - Unzips a *.zip file to a target folder. It is 
    optional to also pass a file pattern. Such as - 
        *.log
        folder/*.php
        /direct/path/to/*.css

    Arguments:
        $zfile - $zippaths->ziploc . '/' . $zipname
        $uztarg - $ziptarg or some other unzip destination
        $uzpatts - the file pattern(s) - null, a string, or an 
        array of strings, the strings are file patterns to 
        match against the zipped file path+name.
        NOTE: Cannot use glob style patterns!

    Returns: An object containing a result flag and a string 
    describing the cause of the flag's value

    $uzret->res = false
    $uzret->msg = one of the following:
        'could not open file.zip'
        'zip file is empty'
        'pattern(s) [*.a, *.b, *.c, ...] had no matches'

    $uzret->res = true
    $uzret->msg = one of the following:
        'extracted all files'
        'extracted N files' - where 'N' is a number

*/
function unzipFiles($zfile, $uztarg, $uzpatts = null) {
$uzret = new stdClass();
$uzret->res = false;
$uzret->msg = '';
$uzfiles = array();
$zip = new ZipArchive;

    // open...
    $zret = $zip->open($zfile);
    if($zret === true) {
        if($zip->numFiles > 0) {
            if($uzpatts !== null) {
                // if the pattern is a JSON string (it must 
                // be an array) the convert it to an array.
                if(isJSON($uzpatts)) {
                    $uzpatts = json_decode($uzpatts);
                } else {
                    // not JSON...
                    // check $uzpatts to see if it is a comma 
                    // delimited string. If so then convert it 
                    // to an array...
                    if(is_string($uzpatts) && (substr_count($uzpatts, ',') > 0)) {
                        $uzpatts = explode(',', $uzpatts);
                    }
                }
                // check to see if each file is a match to the pattern(s)...
                for($idx = 0; $idx < $zip->numFiles; $idx++) {
                    $currname = $zip->getNameIndex($idx);
                    // finds matches in all paths w/o using FNM_PATHNAME
                    if(is_array($uzpatts)) {
                        for($pidx = 0; $pidx < count($uzpatts); $pidx++) {
                            if(fnmatch($uzpatts[$pidx], $currname, (FNM_NOESCAPE|FNM_PERIOD))) {
                                array_push($uzfiles, $currname);
                            }
                        }
                    } else {
                        if(is_string($uzpatts)) {
                            if(fnmatch($uzpatts, $currname, (FNM_NOESCAPE|FNM_PERIOD))) {
                                array_push($uzfiles, $currname);
                            }
                        } else break;
                    }
                }
            }

            // Extract the file(s)
            if(count($uzfiles) > 0) {
                // extract folders + files that match a pattern...
                $zip->extractTo($uztarg, $uzfiles);
                $uzret->res = true;
                $uzret->msg = 'extracted '.count($uzfiles).' files';
            } else {
                if($uzpatts == null) {
                    // extract all folders + files
                    $zip->extractTo($uztarg);
                    $uzret->res = true;
                    $uzret->msg = 'extracted all files';
                } else {
                    // no matches to pattern
                    $_out = (is_string($uzpatts) ? $uzpatts : '[' . implode($uzpatts, ',') .']'); 
                    $uzret->res = false;
                    $uzret->msg = 'pattern(s) '.$_out.' had no matches';
                }
            }
        } else { // if($zip->numFiles > 0)
            // empty zip file!
            $uzret->res = false;
            $uzret->msg = 'zip file is empty - '.$zfile;
        }
        $zip->close();
    } else {
        // zip file did not open
        $uzret->res = false;
        $uzret->msg = 'could not open '.$zfile;
    }
    return $uzret;
}
?>