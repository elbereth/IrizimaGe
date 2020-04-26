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

// Define the IRIZIMAGE constant to indicate we are in an allowed script
define('IRIZIMAGE', TRUE);

// Include libraries
require_once 'irizimage.inc.php';

if (!$config->isReady($message)) {
    echo "<strong>ERROR:</strong> Configuration is not ready: $message<br/>\n";
    die();
}

$itemsperpage = $config->getValue('items_per_line') * $config->getValue('lines_per_page');

// Is Album defined?
if (isset($_GET['album'])) {
    $album = $_GET['album'];
} else {
    $album = '';
}

// Is page defined?
if (isset($_GET['page'])) {
    $page = $_GET['page'];
} else {
    $page = 1;
}

$curalbum = Album::Load($config, $album, true);

// Album is not defined/initialized
if (is_null($curalbum)) {
    // This is the root album / redirect
    if ($curalbum == '') {
        echo "<strong>ERROR:</strong> Root album is not configured/initialized.<br/>\n";
        echo 'Run the <a href="setup.php">Setup</a> to fix this.<br/>' . "\n";
    } else { // @todo Redirect to 404
        echo "Album <strong>" . $album . "</strong> do not exist.<br/>\n";
    }
} else {

    echo "Current album: " . $curalbum->getFullPath() . "<br/>\n";
    echo "Sub-albums present: " . $curalbum->isSubAlbums() . "<br/>\n";
    echo "Number of sub-albums: " . $curalbum->countSubAlbums() . "<br/>\n";

    for ($x = 0; $x < $curalbum->countSubAlbums(); $x++) {
        $newquery = new HttpQueryString(false, array('album' => $curalbum->subAlbums[$x]->getPath()));
        echo '-- <a href="index.php?' . $newquery->tostring() . '">' . $curalbum->subAlbums[$x]->getFullPath() . "</a><br/>\n";
    }

    echo "Number of content items: " . $curalbum->countContent() . "<br/>\n";

    $totalpages = round($curalbum->countContent() / $itemsperpage);
    if ($totalpages == 0) {
        $totalpages = 1;
    }

    echo "Page " . $page . " of " . $totalpages . " (" . ($curalbum->countContent() % $itemsperpage) . ")<br/>\n";

    if ($page > 1) {
        $newquery = new HttpQueryString(true, array('page' => ($page - 1)));
        echo '<a href="index.php?' . $newquery->tostring() . '">Previous page</a><br/>' . "\n";
        unset($newquery);
    }
    $itemscount = 0;
    for ($x = (($page - 1) * $itemsperpage); $x < min(($page * $itemsperpage), $curalbum->countContent()); $x++) {
        if ($itemscount > $irizConfig['items_per_line']) {
            $itemscount = 0;
            echo "</div>\n";
        }
        if ($itemscount == 0) {
            echo '<div class="line">' . "\n";
        }
        echo '<div class="lineitem">';
        $newquery = new HttpQueryString(false, array('album' => $album,
            'size' => '160x160',
            'obj' => $curalbum->content[$x]->getObjName()));
        echo '<img src="show.php?' . $newquery->tostring() . '" /></div>' . "\n";
        unset($newquery);
        $itemscount++;
    }

    if ($page < $totalpages) {
        $newquery = new HttpQueryString(true, array('page' => ($page + 1)));
        echo '<a href="index.php?' . $newquery->tostring() . '">Next page</a><br/>' . "\n";
        unset($newquery);
    }
}
?>
