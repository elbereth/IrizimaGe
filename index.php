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

  // Include libraries
  require_once 'libs/common.inc.php';
  require_once 'libs/Album.class.php';
  require_once 'libs/Config.class.php';
  require_once 'libs/Content.class.php';

  // Include configuration
  require_once 'config.inc.php';

  // Check path exists
  if (!(isset($irizConfig['albums_path']) && is_dir($irizConfig['albums_path']))) {
      // TODO Better error handling
      echo "ERROR (Undefined or invalid albums path)";
      die();
  }

  $itemsperpage = $irizConfig['items_per_line'] * $irizConfig['lines_per_page'];
  if ($itemsperpage <= 0) {
      $itemsperpage = 16;
  }
  
  // Is Album defined?
  if (isset($_GET['album'])) {
      $album = $_GET['album'];
  }
  else {
      $album = '';
  }

  // Is page defined?
  if (isset($_GET['page'])) {
      $page = $_GET['page'];
  }
  else {
      $page = 1;
  }
  
  $curalbum = Album::Create($irizConfig,$album,true);
  
  echo "Current album: ".$curalbum->getFullPath()."<br/>\n";
  echo "Sub-albums present: ".$curalbum->isSubAlbums()."<br/>\n";
  echo "Number of sub-albums: ".$curalbum->countSubAlbums()."<br/>\n";
  
  for ($x = 0; $x < $curalbum->countSubAlbums(); $x++)
  {
      $newquery = new HttpQueryString(false, array('album' => $curalbum->subAlbums[$x]->getPath()));
      echo '-- <a href="index.php?'.$newquery->tostring().'">'.$curalbum->subAlbums[$x]->getFullPath()."</a><br/>\n";
  }
          
  echo "Number of content items: ".$curalbum->countContent()."<br/>\n";
  
  $totalpages = round($curalbum->countContent()/$itemsperpage);
  if ($totalpages == 0) {
      $totalpages = 1;
  }
  
  echo "Page ".$page." of ".$totalpages." (".($curalbum->countContent() % $itemsperpage).")<br/>\n";
  
  if ($page > 1) {
      $newquery = new HttpQueryString(true, array('page' => ($page-1)));
      echo '<a href="index.php?'.$newquery->tostring().'">Previous page</a><br/>'."\n";
      unset($newquery);
  }
  $itemscount = 0;
  for ($x = (($page-1)*$itemsperpage); $x < min(($page*$itemsperpage),$curalbum->countContent()); $x++)
  {
      if ($itemscount > $irizConfig['items_per_line']) {
          $itemscount = 0;
          echo "</div>\n";
      }
      if ($itemscount == 0) {
          echo '<div class="line">'."\n";
      }
      echo '<div class="lineitem">';
      $newquery = new HttpQueryString(false, array('album' => $album,
                                                   'size' => '160x160',
                                                   'obj' => $curalbum->content[$x]->getObjName()));
      echo '<img src="show.php?'.$newquery->tostring().'" /></div>'."\n";
      unset($newquery);
      $itemscount++;
  }
   
  if ($page < $totalpages) {
      $newquery = new HttpQueryString(true, array('page' => ($page+1)));
      echo '<a href="index.php?'.$newquery->tostring().'">Next page</a><br/>'."\n";
      unset($newquery);
  }

  
?>
