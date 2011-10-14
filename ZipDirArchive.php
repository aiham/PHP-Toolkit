<?php

// Name: ZipDirArchive.php
// Date: 15th October 2011
// Author: Aiham Hammami
// 
// Extension of the default ZipArchive class found in PHP
// Allows for directories and any contained files to be
// added recursively to the zip archive. Can be used with
// either absolute or relative paths.
// 
// Usage:
//
// $zip = new ZipDirArchive();
//
// if ($zip->open('file.zip', ZIPARCHIVE::CREATE) === true) {
//
//   $zip->addDirectory('path/to/dir');
//
//   // or
//
//   $zip->addDirectory('path/to/dir', 'a/different/name');
//
//   $zip->close();
//
// }

class ZipDirArchive extends ZipArchive {

  public function addDirectory ($path, $localname = null) {

    if (!is_string($path) || (!is_null($localname) && !is_string($localname))) {
      throw new InvalidArgumentException();
    } else if (!is_dir($path) || !is_readable($path)) {
      throw new UnexpectedValueException($path . ' is not a valid directory');
    }

    if (mb_substr($path, -1) !== DIRECTORY_SEPARATOR) {
      $path .= DIRECTORY_SEPARATOR;
    }

    if (is_null($localname)) {
      $localname = $path;
    } else if (mb_substr($localname, -1) !== DIRECTORY_SEPARATOR) {
      $localname .= DIRECTORY_SEPARATOR;
    }

    $this->addEmptyDir($localname);

    foreach (glob($path . '*') as $realname) {

      $newname = $localname . basename($realname);

      if (is_dir($realname)) {

        $this->addDirectory($realname, $newname);

      } else {

        $this->addFile($realname, $newname);

      }

    }

  }

}
