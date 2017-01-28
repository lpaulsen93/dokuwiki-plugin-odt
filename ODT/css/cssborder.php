<?php
/**
 * Utility class for handling border properties.
 * Only works with properties stored in an array as delivered from 
 * class cssimportnew, e.g. from method getPropertiesForElement().
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     LarsDW223
 */

/**
 * Class cssborder.
 * 
 * @package CSS\CSSAttributeSelector
 */
class cssborder {
    static public function getWidthShorthandValues ($value, &$top, &$right, &$bottom, &$left) {
        $top    = NULL;
        $right  = NULL;
        $bottom = NULL;
        $left   = NULL;

        $values = preg_split ('/\s+/', $value);
        switch (count($values)) {
            case 1:
                $top    = $values [0];
                $bottom = $values [0];
                $right  = $values [0];
                $left   = $values [0];
                break;
            case 2:
                $top    = $values [0];
                $bottom = $values [0];
                $right  = $values [1];
                $left   = $values [1];
                break;
            case 3:
                $top    = $values [0];
                $right  = $values [1];
                $left   = $values [1];
                $bottom = $values [2];
                break;
            case 4:
            default:
                $top    = $values [0];
                $right  = $values [1];
                $bottom = $values [2];
                $left   = $values [3];
                break;
        }
    }

    static public function getShorthandValues ($value, &$width, &$style, &$color) {
        $width = NULL;
        $style = NULL;
        $color = NULL;
        if (empty($value)) {
            return;
        }
        if ($value == 'initial' || $value == 'inherit') {
            $width = $value;
            $style = $value;
            $color = $value;
            return;
        }
        $values = preg_split ('/\s+/', $value);
        $index = 0;
        $border_width_set = false;
        $border_style_set = false;
        $border_color_set = false;
        while ( $index < 3 ) {
            if ( $border_width_set === false ) {
                switch ($values [$index]) {
                    case 'thin':
                    case 'medium':
                    case 'thick':
                        $width = $values [$index];
                        $index++;
                    break;
                    default:
                        $unit = substr ($values [$index], -2);
                        if ( ctype_digit($values [$index]) ||
                             $unit == 'px' ||
                             $unit == 'pt' ||
                             $unit == 'cm' ) {
                            $width = $values [$index];
                            $index++;
                        } else {
                            // There is no default value? So leave it unset.
                        }
                    break;
                }
                $border_width_set = true;
                continue;
            }
            if ( $border_style_set === false ) {
                switch ($values [$index]) {
                    case 'none':
                    case 'hidden':
                    case 'dotted':
                    case 'dashed':
                    case 'solid':
                    case 'double':
                    case 'groove':
                    case 'ridge':
                    case 'inset':
                    case 'outset':
                    case 'initial':
                    case 'inherit':
                        $style = $values [$index];
                        $index++;
                    break;
                }
                $border_style_set = true;
                continue;
            }
            if ( $border_color_set === false ) {
                if (!empty($values [$index])) {
                    $color = $values [$index];
                }

                // This is the last value.
                break;
            }
        }
    }

    /**
     * The function checks if this atrribute selector matches the
     * attributes given in $attributes as key - value pairs.
     * 
     * @param    string $attributes String containing the selector
     * @return   boolean
     */
    static public function normalize (array &$properties) {
        $border_sides = array ('border-left', 'border-right', 'border-top', 'border-bottom');

        $bl_width = '0px';
        $br_width = '0px';
        $bb_width = '0px';
        $bt_width = '0px';
        $bl_style = 'none';
        $br_style = 'none';
        $bb_style = 'none';
        $bt_style = 'none';
        $bl_color = NULL;
        $br_color = NULL;
        $bb_color = NULL;
        $bt_color = NULL;

        if (!empty($properties ['border'])) {
            $width = NULL;
            $style = NULL;
            $color = NULL;
            self::getShorthandValues ($properties ['border'], $width, $style, $color);
            if (!empty($width)) {
                $bl_width = $width;
                $br_width = $width;
                $bb_width = $width;
                $bt_width = $width;
            }
            if (!empty($style)) {
                $bl_style = $style;
                $br_style = $style;
                $bb_style = $style;
                $bt_style = $style;
            }
            if (!empty($color)) {
                $bl_color = $color;
                $br_color = $color;
                $bb_color = $color;
                $bt_color = $color;
            }
            unset ($properties ['border']);
        }

        if (!empty($properties ['border-left'])) {
            $width = NULL;
            $style = NULL;
            $color = NULL;
            self::getShorthandValues ($properties ['border-left'], $width, $style, $color);
            if (!empty($width)) {
                $bl_width = $width;
            }
            if (!empty($style)) {
                $bl_style = $style;
            }
            if (!empty($color)) {
                $bl_color = $color;
            }
            unset ($properties ['border-left']);
        }

        if (!empty($properties ['border-right'])) {
            $width = NULL;
            $style = NULL;
            $color = NULL;
            self::getShorthandValues ($properties ['border-right'], $width, $style, $color);
            if (!empty($width)) {
                $br_width = $width;
            }
            if (!empty($style)) {
                $br_style = $style;
            }
            if (!empty($color)) {
                $br_color = $color;
            }
            unset ($properties ['border-right']);
        }

        if (!empty($properties ['border-top'])) {
            $width = NULL;
            $style = NULL;
            $color = NULL;
            self::getShorthandValues ($properties ['border-top'], $width, $style, $color);
            if (!empty($width)) {
                $bt_width = $width;
            }
            if (!empty($style)) {
                $bt_style = $style;
            }
            if (!empty($color)) {
                $bt_color = $color;
            }
            unset ($properties ['border-top']);
        }

        if (!empty($properties ['border-bottom'])) {
            $width = NULL;
            $style = NULL;
            $color = NULL;
            self::getShorthandValues ($properties ['border-bottom'], $width, $style, $color);
            if (!empty($width)) {
                $bb_width = $width;
            }
            if (!empty($style)) {
                $bb_style = $style;
            }
            if (!empty($color)) {
                $bb_color = $color;
            }
            unset ($properties ['border-bottom']);
        }

        if (!empty($properties ['border-width'])) {
            $top    = NULL;
            $right  = NULL;
            $bottom = NULL;
            $left   = NULL;
            self::getWidthShorthandValues ($properties ['border-width'], $top, $right, $bottom, $left);
            if (!empty($top)) {
                $bt_width = $top;
            }
            if (!empty($right)) {
                $br_width = $right;
            }
            if (!empty($bottom)) {
                $bb_width = $bottom;
            }
            if (!empty($left)) {
                $bl_width = $left;
            }
            unset ($properties ['border-width']);
        }

        // Now normalize and minimize the collected properties values

        // Re-assemble border properties to per side shorthand.
        if (!empty($bt_width) || !empty($bt_style) || !empty($bt_color)) {
            $properties ['border-top'] = $bt_width.' '.$bt_style.' '.$bt_color;
        }
        if (!empty($br_width) || !empty($br_style) || !empty($br_color)) {
            $properties ['border-right'] = $br_width.' '.$br_style.' '.$br_color;
        }
        if (!empty($bb_width) || !empty($bb_style) || !empty($bb_color)) {
            $properties ['border-bottom'] = $bb_width.' '.$bb_style.' '.$bb_color;
        }
        if (!empty($bl_width) || !empty($bl_style) || !empty($bl_color)) {
            $properties ['border-left'] = $bl_width.' '.$bl_style.' '.$bl_color;
        }

        // If all sides are the same we can put them all together as a single border shorthand
        if ($properties ['border-top'] == $properties ['border-right'] &&
            $properties ['border-top'] == $properties ['border-bottom'] &&
            $properties ['border-top'] == $properties ['border-left']) {
            $properties ['border'] = $properties ['border-top'];
            unset ($properties ['border-top']);
            unset ($properties ['border-right']);
            unset ($properties ['border-bottom']);
            unset ($properties ['border-left']);
        }
    }
}
