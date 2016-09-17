<?php
/**
 * Helper class to read in a CSS style
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     LarsDW223
 */

// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

require_once DOKU_PLUGIN . 'odt/helper/csscolors.php';

/**
 * Abstract class to define kind of enum for the CSS value types.
 * Actually only used by adjustLengthValues().
 */
abstract class CSSValueType
{
    const Other               = 0;
    const LengthValueXAxis    = 1;
    const LengthValueYAxis    = 2;
    const StrokeOrBorderWidth = 3;
    // etc.
}

/**
 * Class css_declaration
 * 
 * @package CSS\css_declaration
 */
class css_declaration {
    protected static $css_units = array ('em', 'ex', '%', 'px', 'cm', 'mm', 'in', 'pt',
                                         'pc', 'ch', 'rem', 'vh', 'vw', 'vmin', 'vmax');
    protected $property;
    protected $value;

    /**
     * Create a new declaration (property:value).
     * 
     * @param string $property The property name of the declaration
     * @param string $value    The assigned value
     */
    public function __construct($property, $value) {
        $this->property = $property;
        $this->value = trim($value, ';');
    }

    /**
     * Get the property name of this declaration.
     * 
     * @return string
     */
    public function getProperty () {
        return $this->property;
    }

    /**
     * Get the value assigned to the property of this declaration.
     * 
     * @return string
     */
    public function getValue () {
        return $this->value;
    }

    /**
     * @param css_declaration[] $decls
     */
    public function explode (&$decls) {
        if ( empty ($this->property) ) {
            return;
        }

        switch ($this->property) {
            case 'background':
                $this->explodeBackgroundShorthand ($decls);
            break;
            case 'font':
                $this->explodeFontShorthand ($decls);
            break;
            case 'padding':
                $this->explodePaddingShorthand ($decls);
            break;
            case 'margin':
                $this->explodeMarginShorthand ($decls);
            break;
            case 'border':
                $this->explodeBorderShorthand ($decls);
            break;
            case 'list-style':
                $this->explodeListStyleShorthand ($decls);
            break;
            case 'flex':
                $this->explodeFlexShorthand ($decls);
            break;
            case 'transition':
                $this->explodeTransitionShorthand ($decls);
            break;
            case 'outline':
                $this->explodeOutlineShorthand ($decls);
            break;
            case 'animation':
                $this->explodeAnimationShorthand ($decls);
            break;
            case 'border-bottom':
                $this->explodeBorderBottomShorthand ($decls);
            break;
            case 'columns':
                $this->explodeColumnsShorthand ($decls);
            break;
            case 'column-rule':
                $this->explodeColumnRuleShorthand ($decls);
            break;

            //FIXME: Implement all the shorthands missing
            //case ...
        }
    }

    /**
     * @return bool
     */
    public function isShorthand () {
        switch ($this->property) {
            case 'background':
            case 'font':
            case 'padding':
            case 'margin':
            case 'border':
            case 'list-style':
            case 'flex':
            case 'transition':
            case 'outline':
            case 'animation':
            case 'border-bottom':
            case 'columns':
            case 'column-rule':
                return true;
            break;

            //FIXME: Implement all the shorthands missing
            //case ...
        }
        return false;
    }

    /**
     * @param css_declaration[] $decls
     */
    protected function explodeBackgroundShorthand (&$decls) {
        if ( $this->property == 'background' ) {
            $values = preg_split ('/\s+/', $this->value);
            $index = 0;
            if ($index < count($values)) {
                $color_done = true;
                $value = $values [$index];
                if ($value [0] == '#' || csscolors::isKnownColorName($value)) {
                    $decls [] = new css_declaration ('background-color', $value);
                    $index++;
                } else {
                    switch ($value) {
                        case 'transparent':
                        case 'inherit':
                        case 'initial':
                            $decls [] = new css_declaration ('background-color', $value);
                            $index++;
                        break;
                    }
                }
            }
            if ($index < count($values)) {
                $decls [] = new css_declaration ('background-image', $values [$index]);
                $index++;
            }
            if ($index < count($values)) {
                $decls [] = new css_declaration ('background-repeat', $values [$index]);
                $index++;
            }
            if ($index < count($values)) {
                $decls [] = new css_declaration ('background-attachment', $values [$index]);
                $index++;
            }
            if ($index < count($values)) {
                $decls [] = new css_declaration ('background-position', $values [$index]);
                $index++;
            }
        }
    }

    /**
     * @param css_declaration[] $decls
     */
    protected function explodeFontShorthand (&$decls) {
        if ( $this->property == 'font' ) {
            $values = preg_split ('/\s+/', $this->value);

            $font_style_set = false;
            $font_variant_set = false;
            $font_weight_set = false;
            $font_size_set = false;

            $font_family = '';
            foreach ($values as $value) {
                if ( $font_style_set === false ) {
                    $default = false;
                    switch ($value) {
                        case 'normal':
                        case 'italic':
                        case 'oblique':
                        case 'initial':
                        case 'inherit':
                            $decls [] = new css_declaration ('font-style', $value);
                        break;
                        default:
                            $default = true;
                            $decls [] = new css_declaration ('font-style', 'normal');
                        break;
                    }
                    $font_style_set = true;
                    if ( $default === false ) {
                        continue;
                    }
                }
                if ( $font_variant_set === false ) {
                    $default = false;
                    switch ($value) {
                        case 'normal':
                        case 'small-caps':
                        case 'initial':
                        case 'inherit':
                            $decls [] = new css_declaration ('font-variant', $value);
                        break;
                        default:
                            $default = true;
                            $decls [] = new css_declaration ('font-variant', 'normal');
                        break;
                    }
                    $font_variant_set = true;
                    if ( $default === false ) {
                        continue;
                    }
                }
                if ( $font_weight_set === false ) {
                    $default = false;
                    switch ($value) {
                        case 'normal':
                        case 'bold':
                        case 'bolder':
                        case 'lighter':
                        case '100':
                        case '200':
                        case '300':
                        case '400':
                        case '500':
                        case '600':
                        case '700':
                        case '800':
                        case '900':
                        case 'initial':
                        case 'inherit':
                            $decls [] = new css_declaration ('font-weight', $value);
                        break;
                        default:
                            $default = true;
                            $decls [] = new css_declaration ('font-weight', 'normal');
                        break;
                    }
                    $font_weight_set = true;
                    if ( $default === false ) {
                        continue;
                    }
                }
                if ( $font_size_set === false ) {
                    $default = false;
                    $params = explode ('/', $value);
                    switch ($params [0]) {
                        case 'medium':
                        case 'xx-small':
                        case 'x-small':
                        case 'small':
                        case 'large':
                        case 'x-large':
                        case 'xx-large':
                        case 'smaller':
                        case 'larger':
                        case 'initial':
                        case 'inherit':
                            $decls [] = new css_declaration ('font-size', $params [0]);
                        break;
                        default:
                            $found = false;
                            foreach (self::$css_units as $css_unit) {
                                if ( strpos ($value, $css_unit) !== false ) {
                                    $decls [] = new css_declaration ('font-size', $params [0]);
                                    $found = true;
                                    break;
                                }
                            }
                            if ( $found === false ) {
                                $default = true;
                                $decls [] = new css_declaration ('font-size', 'medium');
                            }
                        break;
                    }
                    if ( !empty($params [1]) ) {
                        $decls [] = new css_declaration ('line-height', $params [1]);
                    } else {
                        $decls [] = new css_declaration ('line-height', 'normal');
                    }
                    $font_size_set = true;
                    if ( $default === false ) {
                        continue;
                    }
                }

                // All other properties are found.
                // The rest is assumed to be a font-family.
                if ( empty ($font_family) ) {
                    $font_family .= $value;
                } else {
                    $font_family .= ' '.$value;
                }
            }
            $decls [] = new css_declaration ('font-family', $font_family);
        }
    }

    /**
     * @param css_declaration[] $decls
     */
    protected function explodePaddingShorthand (&$decls) {
        if ( $this->property == 'padding' ) {
            $values = preg_split ('/\s+/', $this->value);
            switch (count($values)) {
                case 4:
                    $decls [] = new css_declaration ('padding-top', $values [0]);
                    $decls [] = new css_declaration ('padding-right', $values [1]);
                    $decls [] = new css_declaration ('padding-bottom', $values [2]);
                    $decls [] = new css_declaration ('padding-left', $values [3]);
                break;
                case 3:
                    $decls [] = new css_declaration ('padding-top', $values [0]);
                    $decls [] = new css_declaration ('padding-right', $values [1]);
                    $decls [] = new css_declaration ('padding-left', $values [1]);
                    $decls [] = new css_declaration ('padding-bottom', $values [2]);
                break;
                case 2:
                    $decls [] = new css_declaration ('padding-top', $values [0]);
                    $decls [] = new css_declaration ('padding-bottom', $values [0]);
                    $decls [] = new css_declaration ('padding-right', $values [1]);
                    $decls [] = new css_declaration ('padding-left', $values [1]);
                break;
                case 1:
                    $decls [] = new css_declaration ('padding-top', $values [0]);
                    $decls [] = new css_declaration ('padding-bottom', $values [0]);
                    $decls [] = new css_declaration ('padding-right', $values [0]);
                    $decls [] = new css_declaration ('padding-left', $values [0]);
                break;
            }
        }
    }

    /**
     * @param css_declaration[] $decls
     */
    protected function explodeMarginShorthand (&$decls) {
        if ( $this->property == 'margin' ) {
            $values = preg_split ('/\s+/', $this->value);
            switch (count($values)) {
                case 4:
                    $decls [] = new css_declaration ('margin-top', $values [0]);
                    $decls [] = new css_declaration ('margin-right', $values [1]);
                    $decls [] = new css_declaration ('margin-bottom', $values [2]);
                    $decls [] = new css_declaration ('margin-left', $values [3]);
                break;
                case 3:
                    $decls [] = new css_declaration ('margin-top', $values [0]);
                    $decls [] = new css_declaration ('margin-right', $values [1]);
                    $decls [] = new css_declaration ('margin-left', $values [1]);
                    $decls [] = new css_declaration ('margin-bottom', $values [2]);
                break;
                case 2:
                    $decls [] = new css_declaration ('margin-top', $values [0]);
                    $decls [] = new css_declaration ('margin-bottom', $values [0]);
                    $decls [] = new css_declaration ('margin-right', $values [1]);
                    $decls [] = new css_declaration ('margin-left', $values [1]);
                break;
                case 1:
                    $decls [] = new css_declaration ('margin-top', $values [0]);
                    $decls [] = new css_declaration ('margin-bottom', $values [0]);
                    $decls [] = new css_declaration ('margin-right', $values [0]);
                    $decls [] = new css_declaration ('margin-left', $values [0]);
                break;
            }
        }
    }

    /**
     * @param css_declaration[] $decls
     */
    protected function explodeBorderShorthand (&$decls) {
        $border_sides = array ('border-left', 'border-right', 'border-top', 'border-bottom');
        if ( $this->property == 'border' ) {
            $values = preg_split ('/\s+/', $this->value);
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
                            $decls [] = new css_declaration ('border-width', $values [$index]);
                            foreach ($border_sides as $border_side) {
                                $decls [] = new css_declaration ($border_side.'-width', $values [$index]);
                            }
                        break;
                        default:
                            if ( strpos ($values [$index], 'px') !== false ) {
                                $decls [] = new css_declaration ('border-width', $values [$index]);
                                foreach ($border_sides as $border_side) {
                                    $decls [] = new css_declaration ($border_side.'-width', $values [$index]);
                                }
                            } else {
                                // There is no default value? So leave it unset.
                            }
                        break;
                    }
                    $border_width_set = true;
                    $index++;
                    continue;
                }
                if ( $border_style_set === false ) {
                    switch ($values [$index]) {
                        case 'none':
                        case 'dotted':
                        case 'dashed':
                        case 'solid':
                        case 'double':
                        case 'groove':
                        case 'ridge':
                        case 'inset':
                        case 'outset':
                            $decls [] = new css_declaration ('border-style', $values [$index]);
                            foreach ($border_sides as $border_side) {
                                $decls [] = new css_declaration ($border_side.'-style', $values [$index]);
                            }
                        break;
                        default:
                            $decls [] = new css_declaration ('border-style', 'none');
                            foreach ($border_sides as $border_side) {
                                $decls [] = new css_declaration ($border_side.'-style', 'none');
                            }
                        break;
                    }
                    $border_style_set = true;
                    $index++;
                    continue;
                }
                if ( $border_color_set === false ) {
                    $decls [] = new css_declaration ('border-color', $values [$index]);
                    foreach ($border_sides as $border_side) {
                        $decls [] = new css_declaration ($border_side.'-color', $values [$index]);
                    }

                    // This is the last value.
                    break;
                }
            }
            foreach ($border_sides as $border_side) {
                $decls [] = new css_declaration ($border_side, $values [0].' '.$values [1].' '.$values [2]);
            }
        }
    }

    /**
     * @param css_declaration[] $decls
     */
    protected function explodeListStyleShorthand (&$decls) {
        if ( $this->property == 'list-style' ) {
            $values = preg_split ('/\s+/', $this->value);

            $list_style_type_set = false;
            $list_style_position_set = false;
            $list_style_image_set = false;
            foreach ($values as $value) {
                if ( $list_style_type_set === false ) {
                    $default = false;
                    switch ($value) {
                        case 'disc':
                        case 'armenian':
                        case 'circle':
                        case 'cjk-ideographic':
                        case 'decimal':
                        case 'decimal-leading-zero':
                        case 'georgian':
                        case 'hebrew':
                        case 'hiragana':
                        case 'hiragana-iroha':
                        case 'katakana':
                        case 'katakana-iroha':
                        case 'lower-alpha':
                        case 'lower-greek':
                        case 'lower-latin':
                        case 'lower-roman':
                        case 'none':
                        case 'square':
                        case 'upper-alpha':
                        case 'upper-latin':
                        case 'upper-roman':
                        case 'initial':
                        case 'inherit':
                            $decls [] = new css_declaration ('list-style-type', $value);
                        break;
                        default:
                            $default = true;
                            $decls [] = new css_declaration ('list-style-type', 'disc');
                        break;
                    }
                    $list_style_type_set = true;
                    if ( $default === false ) {
                        continue;
                    }
                }
                if ( $list_style_position_set === false ) {
                    $default = false;
                    switch ($value) {
                        case 'inside':
                        case 'outside':
                        case 'initial':
                        case 'inherit':
                            $decls [] = new css_declaration ('list-style-position', $value);
                        break;
                        default:
                            $default = true;
                            $decls [] = new css_declaration ('list-style-position', 'outside');
                        break;
                    }
                    $list_style_position_set = true;
                    if ( $default === false ) {
                        continue;
                    }
                }
                if ( $list_style_image_set === false ) {
                    $decls [] = new css_declaration ('list-style-image', $value);
                    $list_style_image_set = true;
                }
            }
            if ( $list_style_image_set === false ) {
                $decls [] = new css_declaration ('list-style-image', 'none');
            }
        }
    }

    /**
     * @param css_declaration[] $decls
     */
    protected function explodeFlexShorthand (&$decls) {
        if ( $this->property == 'flex' ) {
            $values = preg_split ('/\s+/', $this->value);
            if ( count($values) > 0 ) {
                $decls [] = new css_declaration ('flex-grow', $values [0]);
            }
            if ( count($values) > 1 ) {
                $decls [] = new css_declaration ('flex-shrink', $values [1]);
            }
            if ( count($values) > 2 ) {
                $decls [] = new css_declaration ('flex-basis', $values [2]);
            }
        }
    }

    /**
     * @param css_declaration[] $decls
     */
    protected function explodeTransitionShorthand (&$decls) {
        if ( $this->property == 'transition' ) {
            $values = preg_split ('/\s+/', $this->value);
            if ( count($values) > 0 ) {
                $decls [] = new css_declaration ('transition-property', $values [0]);
            }
            if ( count($values) > 1 ) {
                $decls [] = new css_declaration ('transition-duration', $values [1]);
            }
            if ( count($values) > 2 ) {
                $decls [] = new css_declaration ('transition-timing-function', $values [2]);
            }
            if ( count($values) > 3 ) {
                $decls [] = new css_declaration ('transition-delay', $values [3]);
            }
        }
    }

    /**
     * @param css_declaration[] $decls
     */
    protected function explodeOutlineShorthand (&$decls) {
        if ( $this->property == 'outline' ) {
            $values = preg_split ('/\s+/', $this->value);

            $outline_color_set = false;
            $outline_style_set = false;
            $outline_width_set = false;
            foreach ($values as $value) {
                if ( $outline_color_set === false ) {
                    $decls [] = new css_declaration ('outline-color', $value);
                    $outline_color_set = true;
                    continue;
                }
                if ( $outline_style_set === false ) {
                    $default = false;
                    switch ($value) {
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
                            $decls [] = new css_declaration ('outline-style', $value);
                        break;
                        default:
                            $default = true;
                            $decls [] = new css_declaration ('outline-style', 'none');
                        break;
                    }
                    $outline_style_set = true;
                    if ( $default === false ) {
                        continue;
                    }
                }
                if ( $outline_width_set === false ) {
                    $default = false;
                    switch ($value) {
                        case 'medium':
                        case 'thin':
                        case 'thick':
                        case 'initial':
                        case 'inherit':
                            $decls [] = new css_declaration ('outline-width', $value);
                        break;
                        default:
                            $found = false;
                            foreach (self::$css_units as $css_unit) {
                                if ( strpos ($value, $css_unit) !== false ) {
                                    $decls [] = new css_declaration ('outline-width', $value);
                                    $found = true;
                                    break;
                                }
                            }
                            if ( $found === false ) {
                                $default = true;
                                $decls [] = new css_declaration ('outline-width', 'medium');
                            }
                        break;
                    }
                    $outline_width_set = true;
                    if ( $default === false ) {
                        continue;
                    }
                }
            }
        }
    }

    /**
     * @param css_declaration[] $decls
     */
    protected function explodeAnimationShorthand (&$decls) {
        if ( $this->property == 'animation' ) {
            $values = preg_split ('/\s+/', $this->value);
            if ( count($values) > 0 ) {
                $decls [] = new css_declaration ('animation-name', $values [0]);
            }
            if ( count($values) > 1 ) {
                $decls [] = new css_declaration ('animation-duration', $values [1]);
            }
            if ( count($values) > 2 ) {
                $decls [] = new css_declaration ('animation-timing-function', $values [2]);
            }
            if ( count($values) > 3 ) {
                $decls [] = new css_declaration ('animation-delay', $values [3]);
            }
            if ( count($values) > 4 ) {
                $decls [] = new css_declaration ('animation-iteration-count', $values [4]);
            }
            if ( count($values) > 5 ) {
                $decls [] = new css_declaration ('animation-direction', $values [5]);
            }
            if ( count($values) > 6 ) {
                $decls [] = new css_declaration ('animation-fill-mode', $values [6]);
            }
            if ( count($values) > 7 ) {
                $decls [] = new css_declaration ('animation-play-state', $values [7]);
            }
        }
    }

    /**
     * @param css_declaration[] $decls
     */
    protected function explodeBorderBottomShorthand (&$decls) {
        if ( $this->property == 'border-bottom' ) {
            $values = preg_split ('/\s+/', $this->value);

            $border_bottom_width_set = false;
            $border_bottom_style_set = false;
            $border_bottom_color_set = false;
            foreach ($values as $value) {
                if ( $border_bottom_width_set === false ) {
                    $default = false;
                    switch ($value) {
                        case 'medium':
                        case 'thin':
                        case 'thick':
                        case 'initial':
                        case 'inherit':
                            $decls [] = new css_declaration ('border-bottom-width', $value);
                        break;
                        default:
                            $found = false;
                            foreach (self::$css_units as $css_unit) {
                                if ( strpos ($value, $css_unit) !== false ) {
                                    $decls [] = new css_declaration ('border-bottom-width', $value);
                                    $found = true;
                                    break;
                                }
                            }
                            if ( $found === false ) {
                                $default = true;
                                $decls [] = new css_declaration ('border-bottom-width', 'medium');
                            }
                        break;
                    }
                    $border_bottom_width_set = true;
                    if ( $default === false ) {
                        continue;
                    }
                }
                if ( $border_bottom_style_set === false ) {
                    $default = false;
                    switch ($value) {
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
                            $decls [] = new css_declaration ('border-bottom-style', $value);
                        break;
                        default:
                            $default = true;
                            $decls [] = new css_declaration ('border-bottom-style', 'none');
                        break;
                    }
                    $border_bottom_style_set = true;
                    if ( $default === false ) {
                        continue;
                    }
                }
                if ( $border_bottom_color_set === false ) {
                    $decls [] = new css_declaration ('border-bottom-color', $value);
                    $border_bottom_color_set = true;
                    continue;
                }
            }
        }
    }

    /**
     * @param css_declaration[] $decls
     */
    protected function explodeColumnsShorthand (&$decls) {
        if ( $this->property == 'columns' ) {
            $values = preg_split ('/\s+/', $this->value);
            if ( count($values) == 1 && $values [0] == 'auto' ) {
                $decls [] = new css_declaration ('column-width', 'auto');
                $decls [] = new css_declaration ('column-count', 'auto');
                return;
            }
            if ( count($values) > 0 ) {
                $decls [] = new css_declaration ('column-width', $values [0]);
            }
            if ( count($values) > 1 ) {
                $decls [] = new css_declaration ('column-count', $values [1]);
            }
        }
    }

    /**
     * @param css_declaration[] $decls
     */
    protected function explodeColumnRuleShorthand (&$decls) {
        if ( $this->property == 'column-rule' ) {
            $values = preg_split ('/\s+/', $this->value);
            if ( count($values) > 0 ) {
                $decls [] = new css_declaration ('column-rule-width', $values [0]);
            }
            if ( count($values) > 1 ) {
                $decls [] = new css_declaration ('column-rule-style', $values [1]);
            }
            if ( count($values) > 2 ) {
                $decls [] = new css_declaration ('column-rule-color', $values [2]);
            }
        }
    }

    /**
     * @param $callback
     */
    public function adjustLengthValues ($callback, $rule=NULL) {
        switch ($this->property) {
            case 'border-width':
            case 'outline-width':
            case 'border-bottom-width':
            case 'column-rule-width':
                $this->value =
                    call_user_func($callback, $this->property, $this->value, CSSValueType::StrokeOrBorderWidth, $rule);
            break;

            case 'margin-left':
            case 'margin-right':
            case 'padding-left':
            case 'padding-right':
            case 'width':
            case 'column-width':
                $this->value =
                    call_user_func($callback, $this->property, $this->value, CSSValueType::LengthValueXAxis, $rule);
            break;

            case 'margin-top':
            case 'margin-bottom':
            case 'padding-top':
            case 'padding-bottom':
            case 'min-height':
            case 'height':
            case 'line-height':
                $this->value =
                    call_user_func($callback, $this->property, $this->value, CSSValueType::LengthValueYAxis, $rule);
            break;

            case 'border':
            case 'border-left':
            case 'border-right':
            case 'border-top':
            case 'border-bottom':
                $this->adjustLengthValuesBorder ($callback, $rule);
            break;

            // FIXME: Shorthands are currently not processed.
            // Every Shorthand would need an extra function which knows if it has any length values.
            // Just like the explode...Shorthand functions.
        }
    }

    /**
     * @param $callback
     */
    protected function adjustLengthValuesBorder ($callback, $rule=NULL) {
        switch ($this->property) {
            case 'border':
            case 'border-left':
            case 'border-right':
            case 'border-top':
            case 'border-bottom':
                $values = preg_split ('/\s+/', $this->value);
                $width =
                    call_user_func($callback, $this->property, $values [0], CSSValueType::StrokeOrBorderWidth, $rule);
                $this->value = $width . ' ' . $values [1] . ' ' . $values [2];
            break;
        }
    }

    /**
     * @param $callback
     */
    public function replaceURLPrefixes ($callback) {
        if (strncmp($this->value, 'url(', 4) == 0) {
            $url = substr($this->value, 4, -1);
            $this->value = call_user_func($callback, $this->property, $this->value, $url);
        }
    }
}

/**
 * Class css_rule
 * 
 * @package CSS\css_rule
 */
class css_rule {
    protected $media = NULL;
    protected $selectors = array ();
    /** @var css_declaration[]  */
    protected $declarations = array ();

    /**
     * @param $selector
     * @param $decls
     * @param null $media
     */
    public function __construct($selector, $decls, $media = NULL) {

        $this->media = trim ($media);
        //print ("\nNew rule: ".$media."\n"); //Debuging

        $this->selectors = explode (' ', $selector);

        $decls = trim ($decls, '{}');

        // Parse declarations
        $pos = 0;
        $end = strlen ($decls);
        while ( $pos < $end ) {
            $colon = strpos ($decls, ':', $pos);
            if ( $colon === false ) {
                break;
            }
            $semi = strpos ($decls, ';', $colon + 1);
            if ( $semi === false ) {
                break;
            }

            $property = substr ($decls, $pos, $colon - $pos);
            $property = trim($property);

            $value = substr ($decls, $colon + 1, $semi - ($colon + 1));
            $value = trim ($value);
            $values = preg_split ('/\s+/', $value);
            $value = '';
            foreach ($values as $part) {
                if ( $part != '!important' ) {
                    $value .= ' '.$part;
                }
            }
            $value = trim($value);

            // Create new declaration
            $declaration = new css_declaration ($property, $value);
            $this->declarations [] = $declaration;

            // Handle CSS shorthands, e.g. 'border'
            if ( $declaration->isShorthand () === true ) {
                $declaration->explode ($this->declarations);
            }

            $pos = $semi + 1;
        }
    }

    /**
     * @return string
     */
    public function toString () {
        $returnString = '';
        $returnString .= "Media= \"".$this->media."\"\n";
        foreach ($this->selectors as $selector) {
            $returnString .= $selector.' ';
        }
        $returnString .= "{\n";
        foreach ($this->declarations as $declaration) {
            $returnString .= '  '.$declaration->getProperty ().':'.$declaration->getValue ().";\n";
        }
        $returnString .= "}\n";
        return $returnString;
    }

    /**
     * @param $element
     * @param $classString
     * @param null $media
     * @return bool|int
     */
    public function matches ($element, $classString, $media = NULL, $cssId=NULL) {

        $media = trim ($media);
        if ( !empty($this->media) && $media != $this->media ) {
            // Wrong media
            //print ("\nNo-Match ".$this->media."==".$media); //Debuging
            return false;
        }

        $matches = 0;
        $classes = explode (' ', $classString);

        foreach ($this->selectors as $selector) {
            if ( !empty($classString) ) {
                foreach ($classes as $class) {
                    if ( $selector [0] == '.' && $selector == '.'.$class ) {
                        $matches++;
                        break;
                    } else if ( $selector [0] == '#' && $selector == '#'.$cssId ) {
                        $matches++;
                        break;
                    } else if ( $selector == $element || $selector == $element.'.'.$class ) {
                        $matches++;
                        break;
                    }
                }
            } else {
                if ( $selector [0] == '#' && $selector == '#'.$cssId ) {
                    $matches++;
                } else if ( $selector == $element ) {
                    $matches++;
                }
            }
        }

        // We only got a match if all selectors were matched
        if ( $matches == count($this->selectors) ) {
            // Return the number of matched selectors
            // This enables the caller to choose the most specific rule
            return $matches;
        }

        return false;
    }

    /**
     * @param $name
     * @return null
     */
    public function getProperty ($name) {
        foreach ($this->declarations as $declaration) {
            if ( $name == $declaration->getProperty () ) {
                return $declaration->getValue ();
            }
        }
        return NULL;
    }

    /**
     * @param $values
     * @return null
     */
    public function getProperties (&$values) {
        foreach ($this->declarations as $declaration) {
            $property = $declaration->getProperty ();
            $value = $declaration->getValue ();
            $values [$property] = $value;
        }
        return NULL;
    }

    /**
     * @param $callback
     */
    public function adjustLengthValues ($callback) {
        foreach ($this->declarations as $declaration) {
            $declaration->adjustLengthValues ($callback);
        }
    }
}

/**
 * Class helper_plugin_odt_cssimport
 * 
 * @package helper\cssimport
 */
class helper_plugin_odt_cssimport extends DokuWiki_Plugin {
    protected $replacements = array();
    protected $raw;
    /** @var css_rule[]  */
    protected $rules = array ();

    /**
     * Imports CSS from a file.
     * @deprecated since 3015-05-23, use importFromFile
     *
     * @param $filename
     */
    function importFrom($filename) {
        dbg_deprecated('importFromFile');
        $this->importFromFile($filename);
    }

    /**
     * @param $contents
     * @return bool
     */
    function importFromString($contents) {
        $this->deleteComments ($contents);
        return $this->importFromStringInternal ($contents);
    }

    /**
     * Delete comments in $contents. All comments are overwritten with spaces.
     * The '&' is required. DO NOT DELETE!!!
     * @param $contents
     */
    protected function deleteComments (&$contents) {
        // Delete all comments first
        $pos = 0;
        $max = strlen ($contents);
        $in_comment = false;
        while ( $pos < $max ) {
            if ( ($pos+1) < $max &&
                 $contents [$pos] == '/' &&
                 $contents [$pos+1] == '*' ) {
                $in_comment = true;

                $contents [$pos] = ' ';
                $contents [$pos+1] = ' ';
                $pos += 2;
                continue;
            }
            if ( ($pos+1) < $max &&
                 $contents [$pos] == '*' &&
                 $contents [$pos+1] == '/' &&
                 $in_comment === true ) {
                $in_comment = false;

                $contents [$pos] = ' ';
                $contents [$pos+1] = ' ';
                $pos += 2;
                continue;
            }
            if ( $in_comment === true ) {
                $contents [$pos] = ' ';
            }
            $pos++;
        }
    }

    /**
     * @param $contents
     * @param null $media
     * @return bool
     */
    protected function importFromStringInternal($contents, $media = NULL, &$processed = NULL) {
        // Find all CSS rules
        $pos = 0;
        $max = strlen ($contents);
        while ( $pos < $max ) {
            $bracket_open = strpos ($contents, '{', $pos);
            if ( $bracket_open === false ) {
                return false;
            }
            $bracket_close = strpos ($contents, '}', $pos);
            if ( $bracket_close === false ) {
                return false;
            }

            // If this is a nested call we might hit a closing } for the media section
            // which was the reason for this function call. In this case break and return.
            if ( $bracket_close < $bracket_open ) {
                $pos = $bracket_close + 1;
                break;
            }

            // Get the part before the open bracket and the last closing bracket
            // (or the start of the string).
            $before_open_bracket = substr ($contents, $pos, $bracket_open - $pos);

            // Is it a @media rule?
            $before_open_bracket = trim ($before_open_bracket);
            $mediapos = stripos($before_open_bracket, '@media');
            if ( $mediapos !== false ) {

                // Yes, decode content as normal rules with @media ... { ... }
                //$new_media = substr_replace ($before_open_bracket, NULL, $mediapos, strlen ('@media'));
                $new_media = substr ($before_open_bracket, $mediapos + strlen ('@media'));
                $contents_in_media = substr ($contents, $bracket_open + 1);

                $nested_processed = 0;
                $result = $this->importFromStringInternal ($contents_in_media, $new_media, $nested_processed);
                if ( $result !== true ) {
                    // Stop parsing on error.
                    return false;
                }
                unset ($new_media);
                $pos = $bracket_open + 1 + $nested_processed;
            } else {

                // No, decode rule the normal way selector { ... }
                $selectors = explode (',', $before_open_bracket);

                $decls = substr ($contents, $bracket_open + 1, $bracket_close - $bracket_open);

                // Create a own, new rule for every selector
                foreach ( $selectors as $selector ) {
                    $selector = trim ($selector);
                    $this->rules [] = new css_rule ($selector, $decls, $media);
                }

                $pos = $bracket_close + 1;
            }
        }
        if ( $processed !== NULL ) {
            $processed = $pos;
        }
        return true;
    }

    /**
     * @param $filename
     * @return bool|void
     */
    function importFromFile($filename) {
        // Try to read in the file content
        if ( empty($filename) ) {
            return false;
        }

        $handle = fopen($filename, "rb");
        if ( $handle === false ) {
            return false;
        }

        $contents = fread($handle, filesize($filename));
        fclose($handle);
        if ( $contents === false ) {
            return false;
        }

        return $this->importFromString ($contents);
    }

    /**
     * @param $filename
     * @return bool
     */
    function loadReplacements($filename) {
        // Try to read in the file content
        if ( empty($filename) ) {
            return false;
        }

        $handle = fopen($filename, "rb");
        if ( $handle === false ) {
            return false;
        }

        $filesize = filesize($filename);
        $contents = fread($handle, $filesize);
        fclose($handle);
        if ( $contents === false ) {
            return false;
        }

        // Delete all comments first
        $contents = preg_replace ('/;.*/', ' ', $contents);

        // Find the start of the replacements section
        $rep_start = strpos ($contents, '[replacements]');
        if ( $rep_start === false ) {
            return false;
        }
        $rep_start += strlen ('[replacements]');

        // Find the end of the replacements section
        // (The end is either the next section or the end of file)
        $rep_end = strpos ($contents, '[', $rep_start);
        if ( $rep_end === false ) {
            $rep_end = $filesize - 1;
        }

        // Find all replacment definitions
        $defs = substr ($contents, $rep_start, $rep_end - $rep_start);
        $defs_end = strlen ($defs);

        $def_pos = 0;
        while ( $def_pos < $defs_end ) {
            $linestart = strpos ($defs, "\n", $def_pos);
            if ( $linestart === false ) {
                break;
            }
            $linestart += strlen ("\n");

            $lineend = strpos ($defs, "\n", $linestart);
            if ( $lineend === false ) {
                $lineend = $defs_end;
            }

            $equal_sign = strpos ($defs, '=', $linestart);
            if ( $equal_sign === false || $equal_sign > $lineend ) {
                $def_pos = $linestart;
                continue;
            }

            $quote_start = strpos ($defs, '"', $equal_sign + 1);
            if ( $quote_start === false || $quote_start > $lineend ) {
                $def_pos = $linestart;
                continue;
            }

            $quote_end = strpos ($defs, '"', $quote_start + 1);
            if ( $quote_end === false || $quote_start > $lineend) {
                $def_pos = $linestart;
                continue;
            }
            if ( $quote_end - $quote_start < 2 ) {
                $def_pos = $linestart;
                continue;
            }

            $replacement = substr ($defs, $linestart, $equal_sign - $linestart);
            $value = substr ($defs, $quote_start + 1, $quote_end - ($quote_start + 1));
            $replacement = trim($replacement);
            $value = trim($value);

            $this->replacements [$replacement] = $value;

            $def_pos = $lineend;
        }

        return true;
    }

    /**
     * @return mixed
     */
    public function getRaw () {
        return $this->raw;
    }

    /**
     * @param $name
     * @return mixed
     */
    public function getReplacement ($name) {
        return $this->replacements [$name];
    }

    /**
     * @param $element
     * @param $classString
     * @param $name
     * @param null $media
     * @return null
     */
    public function getPropertyForElement ($element, $classString, $name, $media = NULL) {
        if ( empty ($name) ) {
            return NULL;
        }

        $value = NULL;
        foreach ($this->rules as $rule) {
            $matched = $rule->matches ($element, $classString, $media);
            if ( $matched !== false ) {
                $current = $rule->getProperty ($name);
                if ( !empty ($current) ) {
                    $value = $current;
                }
            }
        }

        return $value;
    }

    /**
     * @param $classString
     * @param $name
     * @return null
     */
    public function getProperty ($classString, $name) {
        if ( empty ($classString) || empty ($name) ) {
            return NULL;
        }

        $value = $this->getPropertyForElement (NULL, $classString, $name);
        return $value;
    }

    /**
     * @param $dest
     * @param $element
     * @param $classString
     * @param null $media
     */
    public function getPropertiesForElement (&$dest, $element, $classString, $media = NULL, $cssId=NULL) {
        if ( empty ($element) && empty ($classString) && empty ($cssId) ) {
            return;
        }

        foreach ($this->rules as $rule) {
            $matched = $rule->matches ($element, $classString, $media, $cssId);
            if ( $matched !== false ) {
                $rule->getProperties ($dest);
            }
        }
    }

    /**
     * @param $value
     * @param int $emValue
     * @return string
     */
    public function adjustValueForODT ($value, $emValue = 0) {
        // ODT specific function. Shouldn't be used anymore.
        // Call the ODT renderer's function instead.
        dbg_deprecated('renderer_plugin_odt_page::adjustValueForODT');

        $values = preg_split ('/\s+/', $value);
        $value = '';
        foreach ($values as $part) {
            // Replace it if necessary
            $part = trim($part);
            $rep = $this->getReplacement($part);
            if ( !empty ($rep) ) {
                $part = $rep;
            }
            $length = strlen ($part);

            // If it is a short color value (#xxx) then convert it to long value (#xxxxxx)
            // (ODT does not support the short form)
            if ( $part [0] == '#' && $length == 4 ) {
                $part = '#'.$part [1].$part [1].$part [2].$part [2].$part [3].$part [3];
            } else {
                // If it is a CSS color name, get it's real color value
                /** @var helper_plugin_odt_csscolors $odt_colors */
                $odt_colors = plugin_load('helper', 'odt_csscolors');
                $color = $odt_colors->getColorValue ($part);
                if ( $part == 'black' || $color != '#000000' ) {
                    $part = $color;
                }
            }

            if ( $length > 2 && $part [$length-2] == 'e' && $part [$length-1] == 'm' ) {
                $number = substr ($part, 0, $length-2);
                if ( is_numeric ($number) && !empty ($emValue) ) {
                    $part = ($number * $emValue).'pt';
                }
            }

            // Replace px with pt (px does not seem to be supported by ODT)
            if ( $length > 2 && $part [$length-2] == 'p' && $part [$length-1] == 'x' ) {
                $part [$length-1] = 't';
            }

            $value .= ' '.$part;
        }
        $value = trim($value);

        return $value;
    }

    /**
     * @return string
     */
    public function rulesToString () {
        $returnString = '';
        foreach ($this->rules as $rule) {
            $returnString .= $rule->toString ();
        }
        return $returnString;
    }

    /**
     * @param $URL
     * @param $replacement
     * @return string
     */
    public function replaceURLPrefix ($URL, $replacement) {
        if ( !empty ($URL) && !empty ($replacement) ) {
            // Replace 'url(...)' with $replacement
            $URL = substr ($URL, 3);
            $URL = trim ($URL, '()');
            $URL = $replacement.$URL;
        }
        return $URL;
    }

    /**
     * @param $callback
     */
    public function adjustLengthValues ($callback) {
        foreach ($this->rules as $rule) {
            $rule->adjustLengthValues ($callback);
        }
    }
}

