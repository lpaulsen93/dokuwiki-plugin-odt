<?php
/**
 * Helper class to read in a CSS style.
 * 
 * This is just a wrapper for the ODT CSS class to make it's
 * functionality available as a DokuWiki helper plugin.
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     LarsDW223
 */

require_once DOKU_PLUGIN . 'odt/ODT/css/cssimportnew.php';

// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

/**
 * Class helper_plugin_odt_cssimport
 * 
 * @package helper\cssimportnew
 */
class helper_plugin_odt_cssimportnew extends DokuWiki_Plugin {
    protected $internal = NULL;

    public function __construct() {
        $this->internal = new cssimportnew();
    }

    /**
     * Get reference to internal cssimportnew object.
     * 
     * @return cssimportnew
     */
    function getInternal($contents) {
        return $this->internal;
    }

    /**
     * @param $contents
     * @return bool
     */
    function importFromString($contents) {
        return $this->internal->importFromString($contents);
    }

    public function setMedia ($media) {
        $this->internal->setMedia($media);
    }

    public function getMedia () {
        return $this->internal->getMedia();
    }

    /**
     * @param $filename
     * @return bool|void
     */
    function importFromFile($filename) {
        return $this->internal->importFromFile($filename);
    }

    /**
     * @return mixed
     */
    public function getRaw () {
        return $this->internal->getRaw();
    }

    /**
     * @param $element
     * @param $classString
     * @param $name
     * @param null $media
     * @return null
     */
    public function getPropertyForElement ($name, iElementCSSMatchable $element) {
        return $this->internal->getPropertyForElement ($name, $element);
    }

    /**
     * @param $dest
     * @param $element
     * @param $classString
     * @param null $media
     */
    public function getPropertiesForElement (&$dest, iElementCSSMatchable $element, helper_plugin_odt_units $units) {
        $this->internal->getPropertiesForElement ($dest, $element, $units->getInternal());
    }

    /**
     * @return string
     */
    public function rulesToString () {
        return $this->internal->rulesToString ();
    }

    /**
     * @param $URL
     * @param $replacement
     * @return string
     */
    public function replaceURLPrefix ($URL, $replacement) {
        return $this->internal->replaceURLPrefix ($URL, $replacement);
    }

    /**
     * @param $callback
     */
    public function adjustLengthValues ($callback) {
        $this->internal->adjustLengthValues ($callback);
    }
}
