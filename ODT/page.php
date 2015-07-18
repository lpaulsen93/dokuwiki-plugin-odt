<?php
/**
 * pageFormat: class for handling page formats.
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author LarsDW223
 */
// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

/**
 * The pageFormat class
 */
class pageFormat
{
    var $format = 'A4';
    var $orientation = 'portrait';

    // Page parameters.
    // All values are assumed to be in 'cm' units.
    var $width = 21;
    var $height = 29.7;
    var $margin_top = 2;
    var $margin_bottom = 2;
    var $margin_left = 2;
    var $margin_right = 2;

    /**
     * Return given page format parameters as a single string.
     *
     * @param string $format
     * @param string $orientation
     * @param string $margin_top
     * @param string $margin_right
     * @param string $margin_bottom
     * @param string $margin_left
     * @return string
     */
    public static function formatToString ($format, $orientation, $margin_top=2, $margin_right=2, $margin_bottom=2, $margin_left=2) {
        $margins = $margin_top.'-'.$margin_right.'-'.$margin_bottom.'-'.$margin_left;
        $margins = str_replace (',', '', $margins);
        $margins = str_replace ('.', '', $margins);
        return $format.'-'.$orientation.'-'.$margins;
    }

    /**
     * Return currently set format parameters as a single string.
     *
     * @return string
     */
    public function toString () {
        $margins = $this->margin_top.'-'.$this->margin_right.'-'.$this->margin_bottom.'-'.$this->margin_left;
        $margins = str_replace (',', '', $margins);
        $margins = str_replace ('.', '', $margins);
        return $this->format.'-'.$this->orientation.'-'.$margins;
    }

    /**
     * Query format data. Returns data in assoziative array $dest.
     * Returned fields are 'format', 'orientation', 'width', 'height',
     * 'margin-top', 'margin-bottom', 'margin-left' and 'margin-right'.
     * If $format is unknown, then format 'A4' will be assumed.
     *
     * @param string $format
     * @param string $orientation
     */
    public static function queryFormat (&$dest, $format, $orientation='portrait', $margin_top=2, $margin_right=2, $margin_bottom=2, $margin_left=2) {
        switch ($format) {
            case 'A6':
                $width = 10.5;
                $height = 14.8;
            break;

            case 'A5':
                $width = 14.8;
                $height = 21;
            break;

            case 'A3':
                $width = 29.7;
                $height = 42;
            break;

            case 'B6 (ISO)':
                $width = 12.5;
                $height = 17.6;
            break;

            case 'B5 (ISO)':
                $width = 17.6;
                $height = 25;
            break;

            case 'B4 (ISO)':
                $width = 25;
                $height = 35.3;
            break;

            case 'Letter':
                $width = 21.59;
                $height = 27.94;
            break;

            case 'Legal':
                $width = 21.59;
                $height = 35.56;
            break;

            case 'Long Bond':
                $width = 21.59;
                $height = 33.02;
            break;

            case 'Tabloid':
                $width = 27.94;
                $height = 43.18;
            break;

            case 'B6 (JIS)':
                $width = 12.8;
                $height = 18.2;
            break;

            case 'B5 (JIS)':
                $width = 18.2;
                $height = 25.7;
            break;

            case 'B4 (JIS)':
                $width = 25.7;
                $height = 36.4;
            break;

            case '16 Kai':
                $width = 18.4;
                $height = 26;
            break;

            case '32 Kai':
                $width = 13;
                $height = 18.4;
            break;

            case 'Big 32 Kai':
                $width = 14;
                $height = 20.3;
            break;

            case 'DL Envelope':
                $width = 11;
                $height = 22;
            break;

            case 'C6 Envelope':
                $width = 11.4;
                $height = 16.2;
            break;

            case 'C6/5 Envelope':
                $width = 11.4;
                $height = 22.9;
            break;

            case 'C5 Envelope':
                $width = 16.2;
                $height = 22.9;
            break;

            case 'C4 Envelope':
                $width = 22.9;
                $height = 32.4;
            break;

            case '#6 3/4 Envelope':
                $width = 9.21;
                $height = 16.51;
            break;

            case '#7 3/4 (Monarch) Envelope':
                $width = 9.84;
                $height = 19.05;
            break;

            case '#9 Envelope':
                $width = 9.84;
                $height = 22.54;
            break;

            case '#10 Envelope':
                $width = 10.48;
                $height = 24.13;
            break;

            case '#11 Envelope':
                $width = 11.43;
                $height = 26.35;
            break;

            case '#12 Envelope':
                $width = 12.07;
                $height = 27.94;
            break;

            case 'Japanese Postcard':
                $width = 10;
                $height = 14.8;
            break;

            case 'A4':
            default:
                $format = 'A4';
                $width = 21;
                $height = 29.7;
            break;
        }

        if ( $orientation != 'portrait' ) {
            $orientation = 'landscape';
            $help = $width;
            $width = $height;
            $height = $help;
        }

        // Return format data.
        $dest ['format'] = $format;
        $dest ['orientation'] = $orientation;
        $dest ['width'] = $width;
        $dest ['height'] = $height;

        // Margins are currently accepted 'as is'
        // but could be subject to further checks/adjustments in the future.
        $dest ['margin-top'] = $margin_top;
        $dest ['margin-bottom'] = $margin_bottom;
        $dest ['margin-left'] = $margin_left;
        $dest ['margin-right'] = $margin_right;
    }

    /**
     * Set format. Sets all values according to $format.
     *
     * @param string $format
     * @param string $orientation
     */
    public function setFormat($format, $orientation='portrait', $margin_top=2, $margin_right=2, $margin_bottom=2, $margin_left=2) {
        $data = array();

        // Query format data
        $this->queryFormat ($data, $format, $orientation, $margin_top, $margin_right, $margin_bottom, $margin_left);

        // Save as page settings
        $this->format = $data ['format'];
        $this->orientation = $data ['orientation'];
        $this->width = $data ['width'];
        $this->height = $data ['height'];
        $this->margin_top = $data ['margin-top'];
        $this->margin_bottom = $data ['margin-bottom'];
        $this->margin_left = $data ['margin-left'];
        $this->margin_right = $data ['margin-right'];
    }

    /**
     * @return string
     */
    public function getFormat() {
        return $this->format;
    }

    /**
     * @return string
     */
    public function getOrientation() {
        return $this->orientation;
    }

    /**
     * @return float
     */
    public function getWidth() {
        return $this->width;
    }

    /**
     * @return float
     */
    public function getHeight() {
        return $this->height;
    }

    /**
     * @return int
     */
    public function getMarginTop() {
        return $this->margin_top;
    }

    /**
     * @return int
     */
    public function getMarginBottom() {
        return $this->margin_bottom;
    }

    /**
     * @return int
     */
    public function getMarginLeft() {
        return $this->margin_left;
    }

    /**
     * @return int
     */
    public function getMarginRight() {
        return $this->margin_right;
    }

    /**
     * Return width percentage value if margins are taken into account.
     * Usually "100%" means 21cm in case of A4 format.
     * But usually you like to take care of margins. This function
     * adjusts the percentage to the value which should be used for margins.
     * So 100% == 21cm e.g. becomes 80.9% == 17cm (assuming a margin of 2 cm on both sides).
     *
     * @param string $percentage
     * @return int|string
     */
    function getRelWidthMindMargins ($percentage = '100'){
        $percentage *= $this->width - $this->margin_left - $this->margin_right;
        $percentage /= $this->width;
        return $percentage;
    }

    /**
     * Like getRelWidthMindMargins but returns the absulute width
     * in centimeters.
     *
     * @param string $percentage
     * @return float
     */
    function getAbsWidthMindMargins ($percentage = '100'){
        $percentage *= $this->width - $this->margin_left - $this->margin_right;
        return ($percentage/100);
    }

    /**
     * Return height percentage value if margins are taken into account.
     * Usually "100%" means 29.7cm in case of A4 format.
     * But usually you like to take care of margins. This function
     * adjusts the percentage to the value which should be used for margins.
     * So 100% == 29.7cm e.g. becomes 86.5% == 25.7cm (assuming a margin of 2 cm on top and bottom).
     *
     * @param string $percentage
     * @return float|string
     */
    function getRelHeightMindMargins ($percentage = '100'){
        $percentage *= $this->height - $this->margin_top - $this->margin_bottom;
        $percentage /= $this->height;
        return $percentage;
    }

    /**
     * Like getRelHeightMindMargins but returns the absulute width
     * in centimeters.
     *
     * @param string $percentage
     * @return float
     */
    function getAbsHeightMindMargins ($percentage = '100'){
        $percentage *= $this->height - $this->margin_left - $this->margin_right;
        return ($percentage/100);
    }
}

