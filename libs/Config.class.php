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

class ConfigIncorrectKeyException extends Exception {

}

class ConfigNotReadyException extends Exception {

}

/**
 * Description of Config class
 * This object contains the sanitized configuration for IrizimaGe
 * Loads the configuration Array and can check if nothing is missing and if
 * configuration is correct
 *
 * @author Alexandre Devilliers
 */
class Config {

    /**
     * IrizimaGe version
     */
    public $irizimageversion = array(
        'major' => 0,
        'minor' => 0,
        'revision' => 1,
        'extra' => null
    );

    /**
     * @var array List of expected keys in configuration array
     */
    private $defaultconfig = array(
        'albums_path' => '',
        'cache_path' => '',
        'items_per_line' => 4,
        'lines_per_page' => 4,
        'cache_sizes' => array('50x50', '160x160')
    );
    private $isconfigready = false;
    private $isconfigreadymessage = '';
    private $configarray = array();

    /**
     * Initialize & check the configuration array
     * @param array $configarray
     */
    public function __construct(&$configarray) {

        // Verify the configuration is an array
        if (!is_array($configarray)) {
            $this->isconfigreadymessage = 'User configuration is not an array';
        } else {
            // Keep only the required keys
            $this->configarray = array_intersect_key($configarray, $this->defaultconfig);
            // @todo Message to indicate the configuration items that were dropped via array_diff_key
            // Default the values of missing entries
            $this->configarray = array_merge($this->defaultconfig, $this->configarray);
            // @todo Info Message to indicate the configuration items that were defaulted
            // Check album root path exists & is writable
            if (!(is_dir($this->configarray['albums_path']))) {
                $this->isconfigreadymessage = 'albums_path is not a directory';
            } elseif (!(is_writable($this->configarray['albums_path']))) {
                $this->isconfigreadymessage = 'albums_path is not writable';
            }

            // Check cache root path exists & is writable
            elseif (!(is_dir($this->configarray['cache_path']))) {
                $this->isconfigreadymessage = 'cache_path is not a directory';
            } elseif (!(is_writable($this->configarray['cache_path']))) {
                $this->isconfigreadymessage = 'cache_path is not writable';
            }

            // Check items_per_line is an integer and more than 0
            elseif (!is_int($this->configarray['items_per_line'])) {
                $this->isconfigreadymessage = 'items_per_line is not an integer';
            } elseif ($this->configarray['items_per_line'] <= 0) {
                $this->isconfigreadymessage = 'items_per_line must be more than 0';
            }

            // Check lines_per_page is an integer and more than 0
            elseif (!is_int($this->configarray['lines_per_page'])) {
                $this->isconfigreadymessage = 'lines_per_page is not an integer';
            } elseif ($this->configarray['lines_per_page'] <= 0) {
                $this->isconfigreadymessage = 'lines_per_page must be more than 0';
            }

            // Everything was fine, config is ready
            else {
                // Sanity check
                $this->isconfigready = (count($this->defaultconfig) == count($this->configarray));
            }
        }
    }

    /**
     *
     * @param string $key Configuration item key to retrieve
     * @return mixed Value associated with that Key
     * @throws ConfigIncorrectKeyException When the Key is not correct configuration item Key
     * @throws ConfigNotReadyException When the configuration is not ready for queries
     */
    public function getValue($key) {
        if ($this->isconfigready) {
            if (array_key_exists($key, $this->configarray)) {
                return $this->configarray[$key];
            } else {
                throw new ConfigIncorrectKeyException('The requested key ' . $key . ' is not correct.');
            }
        } else {
            throw new ConfigNotReadyException($this->isconfigreadymessage);
        }
    }

    /**
     * Indicates is the configuration is ready to be used (no errors)
     * @param string $message Will contain the message indicating why the configuration is not ready
     * @return bool True is configuration is ready, False otherwise
     */
    public function isReady(&$message) {
        if (!$this->isconfigready) {
            $message = $this->isconfigreadymessage;
        }
        return $this->isconfigready;
    }

    /**
     * @return string IrizimaGe version, formatted for display
     */
    public function getIrizimaGeVersion() {
        $version = $this->irizimageversion['major'] . '.' . $this->irizimageversion['minor'] . $this->irizimageversion['revision'];
        if (is_null($this->irizimageversion['extra'])) {
            return $version;
        } else {
            return $version . ' ' . $this->irizimageversion['extra'];
        }
    }

}

?>
