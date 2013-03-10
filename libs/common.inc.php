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

function getSubdirs($dirpath) {
    if (substr($dirpath, -1, 1) != DIRECTORY_SEPARATOR) {
        $dirpath .= DIRECTORY_SEPARATOR;
    }
    $result = array();
    $handle = opendir($dirpath);
    if ($handle !== false) {
        while (false !== ($file = readdir($handle))) {
            $firstchar = substr($file, 0, 1);
            if (($firstchar != ".") && ($firstchar != "_") && is_dir($dirpath . $file)) {
                $result[] = $file;
            }
        }
        closedir($handle);
    }
    sort($result);
    return $result;
}

function getFiles($dirpath) {
    if (substr($dirpath, -1, 1) != DIRECTORY_SEPARATOR) {
        $dirpath .= DIRECTORY_SEPARATOR;
    }
    $result = array();
    $handle = opendir($dirpath);
    if ($handle !== false) {
        while (false !== ($file = readdir($handle))) {
            $firstchar = substr($file, 0, 1);
            if (($firstchar != ".") && ($firstchar != "_") && is_file($dirpath . $file)) {
                $result[] = $file;
            }
        }
        closedir($handle);
    }
    sort($result);
    return $result;
}

// based of code found on php doc website by eric dot brison at anakeen dot com
function sendImageText($text, $font_size = 3, $bg = 0x00FFFFFF, $color = 0x00FF0000) {
    
    $ts = explode("\n", $text);
    $stringwidth = 0;
    foreach ($ts as $k => $string) { //compute width
        $stringwidth = max($stringwidth, strlen($string));
    }

    // Create image width dependant on width of the string
    $width = imagefontwidth($font_size) * $stringwidth;
    
    // Set height to that of the font
    $height = imagefontheight($font_size) * count($ts);
    $el = imagefontheight($font_size);
    $em = imagefontwidth($font_size);
    
    // Create the image pallette
    $img = imagecreatetruecolor($width, $height);
    
    // Fill background
    imagefilledrectangle($img, 0, 0, $width, $height, $bg);
    
    foreach ($ts as $k => $string) {
        
        // Length of the string
        $len = strlen($string);
        
        // Y-coordinate of character, X changes, Y is static
        $ypos = 0;
        
        // Loop through the string
        for ($i = 0; $i < $len; $i++) {

            // Position of the character horizontally
            $xpos = $i * $em;
            $ypos = $k * $el;

            // Draw character
            imagechar($img, $font_size, $xpos, $ypos, $string, $color);

            // Remove character from string
            $string = substr($string, 1);
        }
    }

    // Return the image
    header("Content-Type: image/png");
    imagepng($img);

    // Remove image
    imagedestroy($img);
}

?>
