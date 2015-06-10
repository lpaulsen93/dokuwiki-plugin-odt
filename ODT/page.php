<?php
/**
 * pageFormat: class for handling page fromats.
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
    // Page parameters.
    // All values are assumed to be in 'cm' units.
    var $width = 21;
    var $height = 29.7;
    var $margin_top = 2;
    var $margin_bottom = 2;
    var $margin_left = 2;
    var $margin_right = 2;

    /**
     * Set format. Sets all vlaues according to $format.
     */
    public function setFormat($format, $orientation='portrait') {
        switch ($format) {
            case 'A6':
                $this->width = 10.5;
                $this->height = 14.8;
            break;

            case 'A5':
                $this->width = 14.8;
                $this->height = 21;
            break;

            case 'A3':
                $this->width = 29.7;
                $this->height = 42;
            break;

            case 'B6 (ISO)':
                $this->width = 12.5;
                $this->height = 17.6;
            break;

            case 'B5 (ISO)':
                $this->width = 17.6;
                $this->height = 25;
            break;

            case 'B4 (ISO)':
                $this->width = 25;
                $this->height = 35.3;
            break;

            case 'Letter':
                $this->width = 21.59;
                $this->height = 27.94;
            break;

            case 'Legal':
                $this->width = 21.59;
                $this->height = 35.56;
            break;

            case 'Long Bond':
                $this->width = 21.59;
                $this->height = 33.02;
            break;

            case 'Tabloid':
                $this->width = 27.94;
                $this->height = 43.18;
            break;

            case 'B6 (JIS)':
                $this->width = 12.8;
                $this->height = 18.2;
            break;

            case 'B5 (JIS)':
                $this->width = 18.2;
                $this->height = 25.7;
            break;

            case 'B4 (JIS)':
                $this->width = 25.7;
                $this->height = 36.4;
            break;

            case '16 Kai':
                $this->width = 18.4;
                $this->height = 26;
            break;

            case '32 Kai':
                $this->width = 13;
                $this->height = 18.4;
            break;

            case 'Big 32 Kai':
                $this->width = 14;
                $this->height = 20.3;
            break;

            case 'DL Envelope':
                $this->width = 11;
                $this->height = 22;
            break;

            case 'C6 Envelope':
                $this->width = 11.4;
                $this->height = 16.2;
            break;

            case 'C6/5 Envelope':
                $this->width = 11.4;
                $this->height = 22.9;
            break;

            case 'C5 Envelope':
                $this->width = 16.2;
                $this->height = 22.9;
            break;

            case 'C4 Envelope':
                $this->width = 22.9;
                $this->height = 32.4;
            break;

            case '#6 3/4 Envelope':
                $this->width = 9.21;
                $this->height = 16.51;
            break;

            case '#7 3/4 (Monarch) Envelope':
                $this->width = 9.84;
                $this->height = 19.05;
            break;

            case '#9 Envelope':
                $this->width = 9.84;
                $this->height = 22.54;
            break;

            case '#10 Envelope':
                $this->width = 10.48;
                $this->height = 24.13;
            break;

            case '#11 Envelope':
                $this->width = 11.43;
                $this->height = 26.35;
            break;

            case '#12 Envelope':
                $this->width = 12.07;
                $this->height = 27.94;
            break;

            case 'Japanese Postcard':
                $this->width = 10;
                $this->height = 14.8;
            break;

            case 'A4':
            default:
                $this->width = 21;
                $this->height = 29.7;
            break;
        }

        if ( $orientation != 'portrait' ) {
            $help = $this->width;
            $this->width = $this->height;
            $this->height = $help;
        }

        // Margins are always the same
        $this->margin_top = 2;
        $this->margin_bottom = 2;
        $this->margin_left = 2;
        $this->margin_right = 2;
    }

    public function getWidth() {
        return $this->width;
    }

    public function getHeight() {
        return $this->height;
    }

    public function getMarginTop() {
        return $this->margin_top;
    }

    public function getMarginBottom() {
        return $this->margin_bottom;
    }

    public function getMarginLeft() {
        return $this->margin_left;
    }

    public function getMarginRight() {
        return $this->margin_right;
    }

    /**
     * Return width percentage value if margins are taken into account.
     * Usually "100%" means 21cm in case of A4 format.
     * But usually you like to take care of margins. This function
     * adjusts the percentage to the value which should be used for margins.
     * So 100% == 21cm e.g. becomes 80.9% == 17cm (assuming a margin of 2 cm on both sides).
     */
    function getRelWidthMindMargins ($percentage = '100'){
        $percentage *= $this->width - $this->margin_left - $this->margin_right;
        $percentage /= $this->width;
        return $percentage;
    }

    /**
     * Like getRelWidthMindMargins but returns the absulute width
     * in centimeters.
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
     */
    function getRelHeightMindMargins ($percentage = '100'){
        $percentage *= $this->height - $this->margin_top - $this->margin_bottom;
        $percentage /= $this->height;
        return $percentage;
    }

    /**
     * Like getRelHeightMindMargins but returns the absulute width
     * in centimeters.
     */
    function getAbsHeightMindMargins ($percentage = '100'){
        $percentage *= $this->height - $this->margin_left - $this->margin_right;
        return ($percentage/100);
    }
}

