<?php
/**
 * Helper class for creating ODT styles.
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     LarsDW223
 */

// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

require_once DOKU_INC.'lib/plugins/odt/ODT/styles/ODTTextStyle.php';
require_once DOKU_INC.'lib/plugins/odt/ODT/styles/ODTParagraphStyle.php';
require_once DOKU_INC.'lib/plugins/odt/ODT/styles/ODTTableStyle.php';
require_once DOKU_INC.'lib/plugins/odt/ODT/styles/ODTTableRowStyle.php';
require_once DOKU_INC.'lib/plugins/odt/ODT/styles/ODTTableColumnStyle.php';
require_once DOKU_INC.'lib/plugins/odt/ODT/styles/ODTTableCellStyle.php';

/**
 * Class helper_plugin_odt_stylefactory
 * 
 * @package helper\stylefactory
 */
class helper_plugin_odt_stylefactory extends DokuWiki_Plugin {
    protected static $style_base_name = 'PluginODTAutoStyle_';
    protected static $style_count = 0;

    /**
     * @return array
     */
    function getMethods() {
        $result = array();
        $result[] = array(
                'name'   => 'createTextStyle',
                'desc'   => 'Returns ODT text style definition in $style with the wanted properties. Returns NULL, if no relevant properties were found, otherwise the new style name.',
                'params' => array('$style' => 'string',
                                  '$properties' => 'array',
                                  '$disabled_props' => 'array',
                                  '$parent' => 'string'),
                'return' => array('ODT text style name' => 'string'),
                );
        $result[] = array(
                'name'   => 'createParagraphStyle',
                'desc'   => 'Returns ODT paragrap style definition in $style with the wanted properties. Returns NULL, if no relevant properties were found, otherwise the new style name.',
                'params' => array('$style' => 'string',
                                  '$properties' => 'array',
                                  '$disabled_props' => 'array',
                                  '$parent' => 'string'),
                'return' => array('ODT paragraph style name' => 'string'),
                );
        $result[] = array(
                'name'   => 'createTableTableStyle',
                'desc'   => 'Returns ODT table style definition in $style with the wanted properties. Returns NULL, if no relevant properties were found, otherwise the new style name.',
                'params' => array('$style' => 'string',
                                  '$properties' => 'array',
                                  '$disabled_props' => 'array',
                                  '$max_width_cm' => 'integer'),
                'return' => array('ODT table style name' => 'string'),
                );
        $result[] = array(
                'name'   => 'createTableRowStyle',
                'desc'   => 'Returns ODT table row style definition in $style with the wanted properties. Returns NULL, if no relevant properties were found, otherwise the new style name.',
                'params' => array('$style' => 'string',
                                  '$properties' => 'array',
                                  '$disabled_props' => 'array'),
                'return' => array('ODT table row style name' => 'string'),
                );
        $result[] = array(
                'name'   => 'createTableCellStyle',
                'desc'   => 'Returns ODT table cell style definition in $style with the wanted properties. Returns NULL, if no relevant properties were found, otherwise the new style name.',
                'params' => array('$style' => 'string',
                                  '$properties' => 'array',
                                  '$disabled_props' => 'array'),
                'return' => array('ODT table cell style name' => 'string'),
                );
        $result[] = array(
                'name'   => 'createTableColumnStyle',
                'desc'   => 'Returns ODT table column style definition in $style with the wanted properties. Returns NULL, if no relevant properties were found, otherwise the new style name.',
                'params' => array('$style' => 'string',
                                  '$properties' => 'array',
                                  '$disabled_props' => 'array'),
                'return' => array('ODT table column style name' => 'string'),
                );
        return $result;
    }

    /**
     * This function creates a text style using the style as set in the assoziative array $properties.
     * The parameters in the array should be named as the CSS property names e.g. 'color' or 'background-color'.
     * Properties which shall not be used in the style can be disabled by setting the value in disabled_props
     * to 1 e.g. $disabled_props ['color'] = 1 would block the usage of the color property.
     *
     * The currently supported properties are:
     * background-color, color, font-style, font-weight, font-size, border, font-family, font-variant, letter-spacing,
     * vertical-align, background-image
     *
     * The function returns the name of the new style or NULL if all relevant properties are empty.
     *
     * @author LarsDW223
     * @param $properties
     * @param null $disabled_props
     * @return ODTTextStyle or NULL
     */
    public static function createTextStyle(array $properties, array $disabled_props = NULL){
        return ODTTextStyle::createTextStyle($properties, $disabled_props);
    }

    /**
     * This function creates a paragraph style using the style as set in the assoziative array $properties.
     * The parameters in the array should be named as the CSS property names e.g. 'color' or 'background-color'.
     * Properties which shall not be used in the style can be disabled by setting the value in disabled_props
     * to 1 e.g. $disabled_props ['color'] = 1 would block the usage of the color property.
     *
     * The currently supported properties are:
     * background-color, color, font-style, font-weight, font-size, border, font-family, font-variant, letter-spacing,
     * vertical-align, line-height, background-image
     *
     * The function returns the name of the new style or NULL if all relevant properties are empty.
     *
     * @author LarsDW223
     * @param $properties
     * @param null $disabled_props
     * @return ODTParagraphStyle or NULL
     */
    public static function createParagraphStyle(array $properties, array $disabled_props = NULL){
        return ODTParagraphStyle::createParagraphStyle($properties, $disabled_props);
    }

    /**
     * This function creates a table table style using the style as set in the assoziative array $properties.
     * The parameters in the array should be named as the CSS property names e.g. 'color' or 'background-color'.
     * Properties which shall not be used in the style can be disabled by setting the value in disabled_props
     * to 1 e.g. $disabled_props ['color'] = 1 would block the usage of the color property.
     *
     * The currently supported properties are:
     * width, border-collapse, background-color
     *
     * The function returns the name of the new style or NULL if all relevant properties are empty.
     *
     * @author LarsDW223
     * @param $properties
     * @param null $disabled_props
     * @param int $max_width_cm
     * @return ODTTableStyle or NULL
     */
    public static function createTableTableStyle(array $properties, array $disabled_props = NULL, $max_width_cm = 17){
        return ODTTableStyle::createTableTableStyle($properties, $disabled_props, $max_width_cm);
    }

    /**
     * This function creates a table row style using the style as set in the assoziative array $properties.
     * The parameters in the array should be named as the CSS property names e.g. 'color' or 'background-color'.
     * Properties which shall not be used in the style can be disabled by setting the value in disabled_props
     * to 1 e.g. $disabled_props ['color'] = 1 would block the usage of the color property.
     *
     * The currently supported properties are:
     * height, background-color
     *
     * The function returns the name of the new style or NULL if all relevant properties are empty.
     *
     * @author LarsDW223
     * @param $properties
     * @param null $disabled_props
     * @return ODTTableRowStyle
     */
    public static function createTableRowStyle(array $properties, array $disabled_props = NULL){
        return ODTTableRowStyle::createTableRowStyle($properties, $disabled_props);
    }

    /**
     * This function creates a table cell style using the style as set in the assoziative array $properties.
     * The parameters in the array should be named as the CSS property names e.g. 'color' or 'background-color'.
     * Properties which shall not be used in the style can be disabled by setting the value in disabled_props
     * to 1 e.g. $disabled_props ['color'] = 1 would block the usage of the color property.
     *
     * The currently supported properties are:
     * background-color, vertical-align
     *
     * The function returns the name of the new style or NULL if all relevant properties are empty.
     *
     * @author LarsDW223
     * @param $properties
     * @param null $disabled_props
     * @return ODTTableCellStyle or NULL
     */
    public static function createTableCellStyle(array $properties, array $disabled_props = NULL){
        return ODTTableCellStyle::createTableCellStyle($properties, $disabled_props);
    }

    /**
     * This function creates a table column style using the style as set in the assoziative array $properties.
     * The parameters in the array should be named as the CSS property names e.g. 'color' or 'background-color'.
     * Properties which shall not be used in the style can be disabled by setting the value in disabled_props
     * to 1 e.g. $disabled_props ['color'] = 1 would block the usage of the color property.
     *
     * The currently supported properties are:
     * width
     *
     * The function returns the name of the new style or NULL if all relevant properties are empty.
     *
     * @author LarsDW223
     *
     * @param $style
     * @param $properties
     * @param null $disabled_props
     * @param null $style_name
     * @return ODTUnknownStyle or NULL
     */
    public static function createTableColumnStyle(array $properties, array $disabled_props = NULL){
        return ODTTableColumnStyle::createTableColumnStyle($properties, $disabled_props);
    }

    /**
     * This function creates a frame style for multiple columns, using the style as set in the assoziative array $properties.
     * The parameters in the array should be named as the CSS property names e.g. 'color' or 'background-color'.
     * Properties which shall not be used in the style can be disabled by setting the value in disabled_props
     * to 1 e.g. $disabled_props ['color'] = 1 would block the usage of the color property.
     *
     * The currently supported properties are:
     * column-count, column-rule, column-gap
     *
     * The function returns the name of the new style or NULL if all relevant properties are empty.
     *
     * @author LarsDW223
     *
     * @param $style
     * @param $properties
     * @param null $disabled_props
     * @return ODTUnknownStyle or NULL
     */
    public static function createMultiColumnFrameStyle(array $properties, array $disabled_props = NULL) {
        return ODTUnknownStyle::createMultiColumnFrameStyle($properties, $disabled_props);
    }

    /**
     * This function creates a page layout style with the parameters given in $properies.
     *
     * The currently supported properties are:
     * style-name, width, height, margin-top, margin-bottom, margin-right and margin-left.
     * All properties except the style-name are expected to be numeric values.
     * The function will add 'cm' itself, so do not add any units.
     *
     * The function returns the name of the new style or NULL if all relevant properties are empty.
     *
     * @author LarsDW223
     *
     * @param $properties
     * @param null $disabled_props
     * @return ODTUnknownStyle or NULL
     */
    public static function createPageLayoutStyle(array $properties, array $disabled_props = NULL) {
        return ODTUnknownStyle::createPageLayoutStyle($properties, $disabled_props);
    }

    /**
     * Simple helper function for creating a text style $name setting the specfied font size $size.
     *
     * @author LarsDW223
     *
     * @param string $name
     * @param string $size
     * @return ODTTextStyle
     */
    public static function createSizeOnlyTextStyle ($name, $size) {
        return ODTTextStyle::createSizeOnlyTextStyle ($name, $size);
    }

    /**
     * Simple helper function for creating a paragrapg style for a pagebreak.
     *
     * @author LarsDW223
     *
     * @param string $parent Name of the parent style to set
     * @param string $before Pagebreak before or after?
     * @return ODTParagraphStyle
     */
    public static function createPagebreakStyle($style_name, $parent=NULL,$before=true) {
        return ODTParagraphStyle::createPagebreakStyle($style_name, $parent,$before);
    }

    /**
     * The function adjusts the property value for ODT:
     * - 'em' units are converted to 'pt' units
     * - CSS color names are converted to its RGB value
     * - short color values like #fff are converted to the long format, e.g #ffffff
     *
     * @author LarsDW223
     *
     * @param  string  $property The property name
     * @param  string  $value    The value
     * @param  integer $emValue  Factor for conversion from 'em' to 'pt'
     * @return string  Converted value
     */
    public function adjustValueForODT ($property, $value, $emValue = 0) {
        return ODTUtility::adjustValueForODT ($property, $value, $emValue);
    }
}
