<?php

/*
    IrizimaGe - PHP Image/Media Gallery made easy
    Copyright (C) 2013 Alexandre Devilliers

    This file is part of IrizimaGe.
  
    IrizimaGe is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Foobar is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with IrizimaGe.  If not, see <http://www.gnu.org/licenses/>.
*/

// Include libraries
require_once 'libs/common.inc.php';
require_once 'libs/Album.class.php';
require_once 'libs/Content.class.php';

// Include configuration
require_once 'config.inc.php';

// Check path exists
if (!(isset($irizConfig['albums_path']) && is_dir($irizConfig['albums_path']))) {
    // TODO Better error handling
    sendImageText("ERROR\nUndefined or invalid albums path\n".$irizConfig['albums_path']);
    die();
}

// Retrieve the resize parameter
if (array_key_exists("size", $_GET) && isset($_GET['size'])) {
    $resize = $_GET['size'];
}
// If undefined then use configuration default
else {
    // TODO retrieve default from configuration [hardcoded currently]
    $resize = '800x800';
}

// Retrieve Album Name (path)
if (array_key_exists("album", $_GET) && isset($_GET['album'])) {
    $album_name = $_GET['album'];
}
// If Album Name is undefined return an error image
else {
    sendImageText('No album-name specified!');
    die();
}

// Retrieve object name
if (array_key_exists("obj", $_GET) && isset($_GET['obj'])) {
    $content = $_GET['obj'];
}
// If object-name is undefined return an error image
else {
    sendImageText('No object-name specified!');
    die();
}

// Load album
$curalbum = Album::Create($irizConfig,$album_name, false);

if (is_null($curalbum)) {

    sendImageText('Wrong album parameter!');
    
} else {

// Verify object-name is part of the album
    $contentobj = $curalbum->getContent($content);
    if ($contentobj !== false) {
        if ($contentobj->readInfo()) {
            if ($resize == "download") {
                // TODO hit counter increment
                header("Content-type: " . $contentobj->getMimeType());
                header("Content-Length: " . $contentobj->getFileSize());
                header("Content-Disposition: attachment; filename=" . urlencode($content));
                header("Cache-Control: private");
                readfile($contentobj->getFullFilename());
            } elseif ($resize == "original") {
                header("Content-type: " . $contentobj->getMimeType());
                header("Content-Length: " . $contentobj->getFileSize());
                readfile($contentobj->getFullFilename());
            } else {
                $matched = preg_match("/^\d+x\d+$/", $resize);
                if (!$matched) {
                    sendImageText('Wrong resize parameter!');
                } else {
                    list($resize_width, $resize_height) = explode('x', $resize);
                    $contentobj->showResizedImage($resize_width, $resize_height);
                }
            }
        } else {
            sendImageText('Could not read info from content!');
        }
    } else {
        sendImageText('Content not found in album!');
    }
}
die();
?>