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

// This script can only be executed as part of IRIZIMAGE entry points
// That is index.php, show.php & setup.php
if (!defined('IRIZIMAGE') || (IRIZIMAGE !== TRUE)) {
    die('Use <a href="index.php">Index page</a>.'."\n");
}

// Include needed libraries & classes
require_once 'libs/common.inc.php';
require_once 'libs/Album.class.php';
require_once 'libs/Config.class.php';
require_once 'libs/Content.class.php';

// Include user configuration
require_once 'config.inc.php';

// Initialize configuration
$config = new Config($irizConfig);

?>