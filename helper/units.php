<?php
/**
 * Simple helper class to work with units (e.g. 'px', 'pt', 'cm'...)
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     LarsDW223
 */

require_once DOKU_PLUGIN . 'odt/ODT/ODTUnits.php';

// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

/**
 * Class helper_plugin_odt_units
 * 
 * @package helper\units
 */
class helper_plugin_odt_units extends DokuWiki_Plugin {
    protected $internal = NULL;

    public function __construct() {
        $this->internal = new ODTUnits();
    }

    /**
     * @return array
     */
    function getMethods() {
        $result = array();
        $result[] = array(
                'name'   => 'getColorValue',
                'desc'   => 'returns the color value for a given CSS color name. Returns "#000000" if the name is unknown',
                'params' => array('name' => 'string'),
                'return' => array('color value' => 'string'),
                );
        return $result;
    }

    /**
     * Strips of the leading digits from $value. So left over will be the unit only.
     *
     * @param int $value The length value string, e.g. '1cm'.
     * @return string The unit of $value, e.g. 'cm'
     */
    public static function stripDigits ($value) {
        return ODTUnits::stripDigits ($value);
    }

    /**
     * Gets only the digits from $value without the unit.
     *
     * @param string|int $value The length value string, e.g. '1cm'.
     * @return string The digits of $value, e.g. '1'
     */
    public static function getDigits ($value) {
        return ODTUnits::getDigits ($value);
    }

    /**
     * Checks if $unit is a valid XSL unit.
     *
     * @param string $unit The unit string, e.g. 'cm'.
     * @return boolean true if valid, false otherwise
     */
    public static function isValidXSLUnit($unit) {
        return ODTUnits::isValidXSLUnit($unit);
    }

    /**
     * Checks if length value string $value has a valid XSL unit.
     *
     * @param string|int $value The length value string, e.g. '1cm'.
     * @return boolean true if valid, false otherwise
     */
    public static function hasValidXSLUnit($value) {
        return ODTUnits::hasValidXSLUnit($value);
    }

    /**
     * Sets the pixel per em unit used for px to em conversion.
     *
     * @param int $value The value to be set.
     */
    public function setPixelPerEm ($value) {
        $this->internal->setPixelPerEm ($value);
    }

    /**
     * Query the pixel per em unit.
     *
     * @return int The current value.
     */
    public function getPixelPerEm () {
        return $this->internal->getPixelPerEm ();
    }

    /**
     * Sets the twips per pixel (X axis) used for px to pt conversion.
     *
     * @param int $value The value to be set.
     */
    public function setTwipsPerPixelX ($value) {
        $this->internal->setTwipsPerPixelX ($value);
    }

    /**
     * Sets the twips per pixel (Y axis) unit used for px to pt conversion.
     *
     * @param int $value The value to be set.
     */
    public function setTwipsPerPixelY ($value) {
        $this->internal->setTwipsPerPixelY ($value);
    }

    /**
     * Query the twips per pixel (X axis) setting.
     *
     * @return int The current value.
     */
    public function getTwipsPerPixelX () {
        return $this->internal->getTwipsPerPixelX ();
    }

    /**
     * Query the twips per pixel (Y axis) setting.
     *
     * @return int The current value.
     */
    public function getTwipsPerPixelY () {
        return $this->internal->getTwipsPerPixelY();
    }

    /**
     * Convert pixel (X axis) to points according to the current settings.
     *
     * @param string|int $pixel String with pixel length value, e.g. '20px'
     * @return string The current value.
     */
    public function pixelToPointsX ($pixel) {
        return $this->internal->pixelToPointsX ($pixel);
    }

    /**
     * Convert pixel (Y axis) to points according to the current settings.
     *
     * @param string|int $pixel String with pixel length value, e.g. '20px'
     * @return string The current value.
     */
    public function pixelToPointsY ($pixel) {
        return $this->internal->pixelToPointsY ($pixel);
    }

    /**
     * Convert length value with valid XSL unit to points.
     *
     * @param string $value  String with length value, e.g. '20px', '20cm'...
     * @param string $axis   Is the value to be converted a value on the X or Y axis? Default is 'y'.
     *        Only relevant for conversion from 'px' or 'em'.
     * @return string The current value.
     */
    public function toPoints ($value, $axis = 'y') {
        return $this->internal->toPoints ($value, $axis);
    }

    public function getInternal() {
        return $this->internal;
    }
}
