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

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class UnsupportedFileExtensionException extends Exception {
    
}

class InvalidImageFileException extends Exception {
    
}

class FileNotFoundException extends Exception {
    
}

class EmptyFileException extends Exception {
    
}

class UnallowedCharsInFilenameException extends Exception {
    
}

class IncorrectAlbumObjectException extends Exception {
    
}

/**
 * Description of Content
 *
 * @author Piecito
 */
class Content {

    //put your code here

    private $album;
    private $filename;
    private $size;
    private $modificationtime;
    private $contentobj;
    private $width;
    private $height;
    private $image_type;
    private $html_attr;
    private $exif;
    private $mime_type;

    public static function create($album, $filename, $load = false) {
        try {
            return new Content($album, $filename, $load);
        } catch (Exception $unfe) {
//            echo "Exception [" . get_class($album) . "] [" . $filename . "] (" . get_class($unfe) . "): " . $unfe->getMessage() . "\n<br/>";
//            die();
            return null;
        }
    }

    private function __construct($album, $filename, $load = false) {

        // Verify album is an Album object
        if (isset($album) && get_class($album) != 'Album') {
            throw new IncorrectAlbumObjectException();
        }
        $this->album = $album;

        // Filename should not contain any directory separator
        if (strpos($filename, DIRECTORY_SEPARATOR) !== false) {
            throw new UnallowedCharsInFilenameException();
        }

        // Store filename
        $this->filename = $filename;
        $this->extension = strtolower(pathinfo($this->getFullFilename(), PATHINFO_EXTENSION));
        if ($this->extension == 'php' || $this->extension == '' || $this->extension == 'html') {
            throw new UnsupportedFileExtensionException($this->extension);
        }

        // Verify filename is a file and is readable
        if (!is_file($this->getFullFilename()) || !is_readable($this->getFullFilename())) {
            throw new FileNotFoundException($this->getFullFilename());
        }

        // Retrieve size
        $this->size = filesize($this->getFullFilename());

        // Don't keep empty files
        if ($this->size == 0) {
            throw new EmptyFileException();
        }

        // Retrieve last modification timestamp
        $this->modificationtime = filemtime($this->getFullFilename());

        // If load info then read file further
        if ($load) {
            $this->readInfo();
            if (!isset($this->mime_type)) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE); // return mime type ala mimetype extension
                $this->mime_type = finfo_file($finfo, $this->getFullFilename());
                finfo_close($finfo);
            }
        }

//        echo $this->getFullFilename() . "\n<br/>";
    }

    // Returns the object name
    public function getObjName() {
        return $this->filename;
    }

    // Returns the full filename with path
    public function getFullFilename() {
        return $this->album->getFullPath() . DIRECTORY_SEPARATOR . $this->filename;
    }

    public function readInfo() {
        //   $this->contentobj = new Imagick($this->getFullFilename());
        $this->exif = false;
        $result = getimagesize($this->getFullFilename(), $info);
        if ($result === false) {
            return false;
        }
        $this->width = $result[0];
        $this->height = $result[1];
        $this->image_type = $result[2];
        $this->html_attr = $result[3];

        // Retrieve EXIF info if available
        if ((exif_imagetype($this->getFullFilename()) !== false) && (exif_read_data($this->getFullFilename(), 'IFD0') !== false)) {
            $this->exif = exif_read_data($this->getFullFilename(), 'EXIF');
        }
        return true;
    }

    public function isSameFilename($filename) {
        return strcmp($filename, $this->filename) == 0;
    }

    public function getMimeType() {
        return $this->mime_type;
    }

    public function getFileSize() {
        return $this->size;
    }

    private function resizeImage($resize_width, $resize_height) {
        $im = new Imagick($this->getFullFilename());
        $im->scaleImage($resize_width, $resize_height, true);
        $im->setImageFormat('jpeg');
        $im->setImageCompressionQuality(80);
        return $im->getImageBlob();
    }

    public function showResizedImage($width, $height) {
        $cachedir = $this->album->getCachePath($width . 'x' . $height);
        if ($cachedir !== false) {
            if (is_file($cachedir . DIRECTORY_SEPARATOR . $this->filename) &&
                    is_readable($cachedir . DIRECTORY_SEPARATOR . $this->filename) &&
                    (filesize($cachedir . DIRECTORY_SEPARATOR . $this->filename) > 0) &&
                    $this->modificationtime <= filemtime($cachedir . DIRECTORY_SEPARATOR . $this->filename) &&
                    (getimagesize($cachedir . DIRECTORY_SEPARATOR . $this->filename) !== false) ) {
                header("Content-type: image/jpeg");
                header("Content-Length: " . filesize($cachedir . DIRECTORY_SEPARATOR . $this->filename));
                ob_clean();
                flush();
                readfile($cachedir . DIRECTORY_SEPARATOR . $this->filename);
            } else {
                // @todo Add error checked for each step
                $resizedimage = $this->resizeImage($width, $height);
                file_put_contents($cachedir . DIRECTORY_SEPARATOR . $this->filename, $resizedimage);
                header("Content-Length: " . strlen($resizedimage));
                echo $resizedimage;
            }
        } else {
            $resizedimage = $this->resizeImage($width, $height);
            header("Content-type: image/jpeg");
            header("Content-Length: " . strlen($resizedimage));
            echo $resizedimage;
        }
    }

}

?>
