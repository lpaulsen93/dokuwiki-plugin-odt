<?php
/**
 * Simple helper class to work with units (e.g. 'px', 'pt', 'cm'...)
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     LarsDW223
 */

// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

/**
 * Class helper_plugin_odt_units
 */
class helper_plugin_odt_units extends DokuWiki_Plugin {
    // Measure units as defined in "Extensible Stylesheet Language (XSL) Version 1.1"
    protected static $xsl_units = array('cm', 'mm', 'in', 'pt', 'pc', 'px', 'em');
    protected static $twips_per_pixel_x = 16;
    protected static $twips_per_pixel_y = 20;
    protected static $twips_per_point   = 20;
    protected static $point_in_cm = 0.035277778;
    protected static $inch_in_cm = 2.54;
    protected static $inch_in_pt = 0.089605556;
    protected static $pc_in_cm = 0.423333336;
    protected static $pc_in_pt = 12;
    protected static $px_per_em = 14;

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
    public function stripDigits ($value) {
        return ltrim ($value, '.0123456789');
    }

    /**
     * Gets only the digits from $value without the unit.
     *
     * @param string|int $value The length value string, e.g. '1cm'.
     * @return string The digits of $value, e.g. '1'
     */
    public function getDigits ($value) {
        $digits = NULL;
        $length = strlen ((string)$value);
        for ($index = 0 ; $index < $length ; $index++ ) {
            if ( is_numeric ($value [$index]) === false && $value [$index] != '.' ) {
                break;
            }
            $digits .= $value [$index];
        }
        return $digits;
    }

    /**
     * Checks if $unit is a valid XSL unit.
     *
     * @param string $unit The unit string, e.g. 'cm'.
     * @return boolean true if valid, false otherwise
     */
    public function isValidXSLUnit($unit) {
        return in_array($unit, self::$xsl_units);
    }

    /**
     * Checks if length value string $value has a valid XSL unit.
     *
     * @param string|int $value The length value string, e.g. '1cm'.
     * @return boolean true if valid, false otherwise
     */
    public function hasValidXSLUnit($value) {
        return in_array($this->stripDigits((string)$value), self::$xsl_units);
    }

    /**
     * Sets the pixel per em unit used for px to em conversion.
     *
     * @param int $value The value to be set.
     */
    public function setPixelPerEm ($value) {
        self::$px_per_em = $value;
    }

    /**
     * Query the pixel per em unit.
     *
     * @return int The current value.
     */
    public function getPixelPerEm () {
        return self::$px_per_em;
    }

    /**
     * Sets the twips per pixel (X axis) used for px to pt conversion.
     *
     * @param int $value The value to be set.
     */
    public function setTwipsPerPixelX ($value) {
        self::$twips_per_pixel_x = $value;
    }

    /**
     * Sets the twips per pixel (Y axis) unit used for px to pt conversion.
     *
     * @param int $value The value to be set.
     */
    public function setTwipsPerPixelY ($value) {
        self::$twips_per_pixel_y = $value;
    }

    /**
     * Query the twips per pixel (X axis) setting.
     *
     * @return int The current value.
     */
    public function getTwipsPerPixelX () {
        return self::$twips_per_pixel_x;
    }

    /**
     * Query the twips per pixel (Y axis) setting.
     *
     * @return int The current value.
     */
    public function getTwipsPerPixelY () {
        return self::$twips_per_pixel_y;
    }

    /**
     * Convert pixel (X axis) to points according to the current settings.
     *
     * @param string|int $pixel String with pixel length value, e.g. '20px'
     * @return string The current value.
     */
    public function pixelToPointsX ($pixel) {
        $pixel = $this->getDigits ((string)$pixel);
        return ($pixel * self::$twips_per_pixel_x / self::$twips_per_point).'pt';
    }

    /**
     * Convert pixel (Y axis) to points according to the current settings.
     *
     * @param string|int $pixel String with pixel length value, e.g. '20px'
     * @return string The current value.
     */
    public function pixelToPointsY ($pixel) {
        $pixel = $this->getDigits ((string)$pixel);
        return ($pixel * self::$twips_per_pixel_y / self::$twips_per_point).'pt';
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
        $unit = $this->stripDigits ($value);
        if ( $unit == 'pt' ) {
            return $value;
        }

        if ( $this->isValidXSLUnit ($unit) === false  ) {
            // Not a vlaid/supported unit. Return original value.
            return $value;
        }

        $value = $this->getDigits ($value);
        switch ($unit) {
            case 'cm':
                $value = ($value/self::$point_in_cm).'pt';
            break;
            case 'mm':
                $value = ($value/(10 * self::$point_in_cm)).'pt';
            break;
            case 'in':
                $value = ($value * self::$inch_in_pt).'pt';
            break;
            case 'pc':
                $value = ($value * self::$pc_in_pt).'pt';
            break;
            case 'px':
                if ( $axis == 'x' || $axis == 'X' ) {
                    $value = $this->pixelToPointsX ($value);
                } else {
                    $value = $this->pixelToPointsY ($value);
                }
            break;
            case 'em':
                $value = $this->pixelToPointsY ($value * $this->getPixelPerEm());
            break;
        }
        return $value;
    }
}
