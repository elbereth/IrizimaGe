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

/**
 * Description of Album class
 * Object describing an Album, which is a folder which can contains sub-folders (Albums) and files (Content)
 * An Album is a folder.
 * The configuration of the album is stored in a hidden sub-folder
 *
 * @author Piecito
 */
class Album {

    /**
     * @var string Path of the album
     */
    private $path;

    /**
     * @var array Array of Albums children of this Album 
     */
    public $subAlbums;
    
    /**
     * @var int Number of Sub-Albums 
     */
    private $subAlbumsNum;

    /**
     * @var array Array of Content items in this Album
     */
    public $content;
    
    /**
     * @var int Number of Content items 
     */
    private $contentNum;
    
    /**
     * @var array Pointer to the IrizimaGe configuration
     */
    private $config;

    /**
     * @var boolean Was the content of the Album loaded or not
     */
    private $contentLoaded;
    
    /**
     * Constructor for Album object
     * @param array $irizConfig Array containing the configuration of IrizimaGe
     * @param string $dirpath Path of the album relative to the configuration item albums_path
     * @param boolean $init True = Load sub-albums and content items (default to False)
     */
    function __construct(&$irizConfig, $dirpath, $init = false) {
        $this->config = &$irizConfig;
        // If $path end on the directory separator we strip it
        if (substr($dirpath, -1, 1) == DIRECTORY_SEPARATOR) {
            $this->path = substr($dirpath, 0, -1);
        } else {
            $this->path = $dirpath;
        }
        $this->contentNum = 0;
        $this->subAlbumsNum = 0;
        $this->subAlbums = array();

        if ($init) {
            $this->loadSubAlbums();
            $this->loadContentList();
        } else {
            $this->contentLoaded = false;
        }
    }

    /**
     * Empty the subAlbums items
     */
    private function resetSubAlbums() {

        unset($this->subAlbums);
        
    }
    
    /**
     * Reload the sub-Albums
     * Go throught the sub-folders of the current Album and load them in
     * Albums object stored in subAlbums[] 
     */
    public function loadSubAlbums() {

        $this->resetSubAlbums();
        $subalbums = getSubdirs($this->getFullPath());
        foreach ($subalbums as $newpath) {
            $this->subAlbums[] = new Album($this->config, $this->path . DIRECTORY_SEPARATOR . $newpath);
            $this->subAlbumsNum++;
        }
    }

    /**
     * Empty the content items
     */
    private function resetContent() {

        unset($this->content);
        
    }
    
    /**
     * Reload the content of the Album
     * Go throught the folder files and load them in a Content object stored in content[]
     * @param boolean $loadinfo If true will load the items informations for each found file
     */
    public function loadContentList($loadinfo = false) {

        $this->resetContent();
        $files = getFiles($this->getFullPath());
        foreach ($files as $file) {
            $newcontent = Content::create($this, $file, $loadinfo);
            if ($newcontent != null) {
                $this->content[] = $newcontent;
                $this->contentNum++;
            }
        }
        $this->contentLoaded = true;
    }

    /**
     * Indicates if there are sub albums
     * @return bool Is there sub-albums?
     */
    public function isSubAlbums() {
        return isset($this->subAlbums);
    }
    
    /**
     * Indicates how many sub albums there are (0 if none)
     * @return int How many sub-Albums
     */
    public function countSubAlbums() {
        if (isset($this->subAlbums)) {
            return $this->subAlbumsNum;
        }
        else {
            return 0;
        }
    }

    /**
     * Indicates how many content items there are (0 if none)
     * @return int How many content items in the Album
     */
    public function countContent() {
        if (isset($this->content)) {
            return $this->contentNum;
        }
        else {
            return 0;
        }
    }
    
    /**
     * Check and return content by $filename
     * @param type $filename
     * @return Content|boolean Return the Content object represented by $filename if it exists in the Album or FALSE
     */
    public function getContent($filename) {
        if ($this->contentLoaded) {
          foreach ($this->content as $content) {
            if ($content->isSameFilename($filename)) {
                return $content;
            }
          }
        } else {
          $newcontent = Content::create($this, $filename, true);
          if ($newcontent != null) {
              return $newcontent;
          }
        }
        return false;
    }
    
    /**
     * Returns the Path of the album relative to the albums_path configuration
     * item.
     * @return string Path of the album (for ex: to be used as GET parameter)
     */
    public function getPath() {
        return $this->path;
    }
    
    /**
     * Returns the full path of the album
     * @return string Full path of the album (to be used for absolute file open)
     */
    public function getFullPath() {
        return $this->config['albums_path'].$this->path;
    }
    
    /**
     * Returns the full path to the cache folder for the album
     * @param string $cachesize Cache size (
     * @return string|boolean Full path to the cache folder to the $cachesize in parameter or boolean FALSE (if not allowed in configuration or folder does not exists and could not be created)
     */
    public function getCachePath($cachesize) {
        $result = false;
        if (in_array($cachesize,$this->config['cache_sizes'],true)) {
            if (!is_dir($this->config['cache_path'].$this->path.DIRECTORY_SEPARATOR.'_'.$cachesize)) {
                $result = mkdir($this->config['cache_path'].$this->path.DIRECTORY_SEPARATOR.'_'.$cachesize,0755,true);
                if ($result) {
                    $result = $this->config['cache_path'].$this->path.DIRECTORY_SEPARATOR.'_'.$cachesize;
                }
            }
            else {
                $result = $this->config['cache_path'].$this->path.DIRECTORY_SEPARATOR.'_'.$cachesize;
            }
        }
        return $result;
    }

}

?>
