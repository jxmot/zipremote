<?php
/*
    Zip Library - contains functions for adding files to 
    a zip file. Two methods are provided:

    * zipFiles() - zip files in a single destination (not recursive!)
    * zipFilesRecursive() - recursively add files and keep relative paths

    Author: https://github.com/jxmot
    Repository: https://github.com/jxmot/zipremote
*/
require_once './timezone.php';
require_once './rightnow.php';

function zipFiles($tozip, $zipfile, $ziptarg) {
$zip = new ZipArchive;
$ret = false;

    if(($zip->open($zipfile, ZipArchive::CREATE | ZipArchive::OVERWRITE)) === true) {
// NOTE: remove_all_path does NOT work in php 5.6! bad zip files are created!
        $options = array('remove_all_path' => true);
        if(($flist = $zip->addGlob($tozip, (GLOB_NOSORT | GLOB_ERR | GLOB_BRACE), $options)) === false) {
            $ret = false;
        } else {
            $comment = '{"time": '. time() . ', "dtime": ' . rightnow('json') . ',"count": ' . count($flist) . ',"ziptarg": "' . $ziptarg . '"}';
            $zip->setArchiveComment($comment);
            $ret = true;
        }
        $zip->close();
    } else {
        $ret = false;
    }
    return $ret;
}

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

        $comment = '{"time": '. time() . ', "dtime": ' . rightnow('json') . ',"count": ' . $count . ',"ziptarg": "' . $ziptarg . '"}';
        $zip->setArchiveComment($comment);

        // Zip archive will be created only after closing
        $zip->close();
        $ret = true;
    } else {
        $ret = false;
    }
    return $ret;
}
?>