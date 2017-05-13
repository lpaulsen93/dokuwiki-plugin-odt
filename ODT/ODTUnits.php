<?php
/**
 * Simple class to work with units (e.g. 'px', 'pt', 'cm'...)
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     LarsDW223
 */

/**
 * Class helper_plugin_odt_units
 */
class ODTUnits {
    // All static variables with fixed values
    // Measure units as defined in "Extensible Stylesheet Language (XSL) Version 1.1"
    protected static $xsl_units = array('cm', 'mm', 'in', 'pt', 'pc', 'px', 'em');
    protected static $point_in_cm = 0.035277778;
    protected static $inch_in_cm = 2.54;
    protected static $inch_in_pt = 0.089605556;
    protected static $pc_in_cm = 0.423333336;
    protected static $pc_in_pt = 12;
    protected static $twips_per_point = 20;

    // Non static variables, can be changed
    protected $px_per_em = 14;
    protected $twips_per_pixel_x = 16;
    protected $twips_per_pixel_y = 20;

    /**
     * Strips of the leading digits from $value. So left over will be the unit only.
     *
     * @param int $value The length value string, e.g. '1cm'.
     * @return string The unit of $value, e.g. 'cm'
     */
    static public function stripDigits ($value) {
        return ltrim ($value, '-.0123456789');
    }

    /**
     * Gets only the digits from $value without the unit.
     *
     * @param string|int $value The length value string, e.g. '1cm'.
     * @return string The digits of $value, e.g. '1'
     */
    static public function getDigits ($value) {
        $digits = NULL;
        $length = strlen ((string)$value);
        for ($index = 0 ; $index < $length ; $index++ ) {
            if ( is_numeric ($value [$index]) === false &&
                 $value [$index] != '.' && 
                 $value [$index] != '-' ) {
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
    static public function isValidXSLUnit($unit) {
        return in_array($unit, self::$xsl_units);
    }

    /**
     * Checks if length value string $value has a valid XSL unit.
     *
     * @param string|int $value The length value string, e.g. '1cm'.
     * @return boolean true if valid, false otherwise
     */
    static public function hasValidXSLUnit($value) {
        return in_array(self::stripDigits((string)$value), self::$xsl_units);
    }

    /**
     * Sets the pixel per em unit used for px to em conversion.
     *
     * @param int $value The value to be set.
     */
    public function setPixelPerEm ($value) {
        $this->px_per_em = $value;
    }

    /**
     * Query the pixel per em unit.
     *
     * @return int The current value.
     */
    public function getPixelPerEm () {
        return $this->px_per_em;
    }

    /**
     * Sets the twips per pixel (X axis) used for px to pt conversion.
     *
     * @param int $value The value to be set.
     */
    public function setTwipsPerPixelX ($value) {
        $this->twips_per_pixel_x = $value;
    }

    /**
     * Sets the twips per pixel (Y axis) unit used for px to pt conversion.
     *
     * @param int $value The value to be set.
     */
    public function setTwipsPerPixelY ($value) {
        $this->twips_per_pixel_y = $value;
    }

    /**
     * Query the twips per pixel (X axis) setting.
     *
     * @return int The current value.
     */
    public function getTwipsPerPixelX () {
        return $this->twips_per_pixel_x;
    }

    /**
     * Query the twips per pixel (Y axis) setting.
     *
     * @return int The current value.
     */
    public function getTwipsPerPixelY () {
        return $this->twips_per_pixel_y;
    }

    /**
     * Convert pixel (X axis) to points according to the current settings.
     *
     * @param string|int $pixel String with pixel length value, e.g. '20px'
     * @return string The current value.
     */
    public function pixelToPointsX ($pixel) {
        $pixel = self::getDigits ((string)$pixel);
        $value = $pixel * $this->twips_per_pixel_x / self::$twips_per_point; 
        return round ($value, 2).'pt';
    }

    /**
     * Convert pixel (Y axis) to points according to the current settings.
     *
     * @param string|int $pixel String with pixel length value, e.g. '20px'
     * @return string The current value.
     */
    public function pixelToPointsY ($pixel) {
        $pixel = self::getDigits ((string)$pixel);
        $value = $pixel * $this->twips_per_pixel_y / self::$twips_per_point;
        return round ($value, 2).'pt';
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
        $unit = self::stripDigits ($value);
        if ( $unit == 'pt' ) {
            return $value;
        }

        if ( self::isValidXSLUnit ($unit) === false  ) {
            // Not a vlaid/supported unit. Return original value.
            return $value;
        }

        $value = self::getDigits ($value);
        switch ($unit) {
            case 'cm':
                $value = round (($value/self::$point_in_cm), 2).'pt';
            break;
            case 'mm':
                $value = round (($value/(10 * self::$point_in_cm)), 2).'pt';
            break;
            case 'in':
                $value = round (($value * self::$inch_in_pt), 2).'pt';
            break;
            case 'pc':
                $value = round (($value * self::$pc_in_pt), 2).'pt';
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

    /**
     * Convert length value with valid XSL unit to points.
     *
     * @param string $value  String with length value, e.g. '20px', '20pt'...
     * @param string $axis   Is the value to be converted a value on the X or Y axis? Default is 'y'.
     *        Only relevant for conversion from 'px' or 'em'.
     * @return string The current value.
     */
    public function toCentimeters ($value, $axis = 'y') {
        $unit = self::stripDigits ($value);
        if ( $unit == 'cm' ) {
            return $value;
        }

        if ( self::isValidXSLUnit ($unit) === false  ) {
            // Not a vlaid/supported unit. Return original value.
            return $value;
        }

        $value = self::toPoints ($value, $axis);
        $value = substr($value, 0, -2);
        $value = round (($value * self::$point_in_cm), 2).'cm';
        return $value;
    }

    /**
     * Convert length value with valid XSL unit to pixel.
     *
     * @param string $value  String with length value, e.g. '20pt'...
     * @param string $axis   Is the value to be converted a value on the X or Y axis? Default is 'y'.
     *        Only relevant for conversion from 'px' or 'em'.
     * @return string The current value.
     */
    public function toPixel ($value, $axis = 'y') {
        $unit = self::stripDigits ($value);
        if ( $unit == 'px' ) {
            return $value;
        }

        if ( self::isValidXSLUnit ($unit) === false  ) {
            // Not a vlaid/supported unit. Return original value.
            return $value;
        }

        $value = self::toPoints ($value, $axis);
        $value = substr($value, 0, -2);
        if ($axis == 'x') {
            $value = round ((($value*self::$twips_per_point)/$this->twips_per_pixel_x), 2).'px';
        } else {
            $value = round ((($value*self::$twips_per_point)/$this->twips_per_pixel_y), 2).'px';
        }
        return $value;
    }

    public function getAbsoluteValue ($value, $base) {
        $unit = self::stripDigits ($value);

        $value = self::getDigits ($value);
        switch ($unit) {
            case '%':
                $value = ($value * $base)/100;
            break;
            case 'em':
                $value = $value * $base;
            break;
            default:
                // Not an relative value. Just keep it.
            break;
        }
        return $value;
    }
}
