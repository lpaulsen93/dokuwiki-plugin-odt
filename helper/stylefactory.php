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
     * This function creates a new style name. All functions of this class which create a new
     * style/style name shall use this function to create the style name. By doing so it is
     * guaranteed that all style names created by this class are unique.
     *
     * The function returns the name of the new style or NULL if all relevant properties are empty.
     *
     * @author LarsDW223
     */
    protected static function getNewStylename ($type = '') {
        self::$style_count++;
        $style_name = self::$style_base_name.$type.'_'.self::$style_count;
        return $style_name;
    }

    /**
     * @param $first_name
     * @param $value
     * @return string
     */
    protected static function writeExtensionNames ($first_name, $value) {
        static $names_ext = array (
              'fo:country'     => array ('style:country-asian', 'style:country-complex'),
              'fo:language'    => array ('style:language-asian', 'style:language-complex'),
              'fo:font-size'   => array ('style:font-size-asian', 'style:font-size-complex'),
              'fo:font-weight' => array ('style:font-weight-asian', 'style:font-weight-complex'),
              'fo:font-style'  => array ('style:font-style-asian', 'style:font-style-complex'),
            );

        $text = '';
        for ($index = 0 ; $index < count($names_ext [$first_name]) ; $index++) {
            $text .= $names_ext [$first_name][$index].'="'.$value.'" ';
        }
        return $text;
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
        // If the property 'vertical-align' has the value 'sub' or 'super'
        // then for ODT it needs to be converted to the corresponding 'text-position' property.
        // Replace sub and super with text-position.
        $valign = $properties ['vertical-align'];
        if (!empty($valign)) {
            if ( $valign == 'sub' ) {
                $properties ['text-position'] = '-33% 100%';
                unset($properties ['vertical-align']);
            } elseif ( $valign == 'super' ) {
                $properties ['text-position'] = '33% 100%';
                unset($properties ['vertical-align']);
            }
        }

        // Separate country from language
        $lang = $properties ['lang'];
        $country = $properties ['country'];
        if ( !empty($lang) ) {
            $parts = preg_split ('/-/', $lang);
            $lang = $parts [0];
            $country = $parts [1];
            $properties ['country'] = trim($country);
            $properties ['lang'] = trim($lang);
        }
        if (!empty($properties ['country'])) {
            if (empty($properties ['country-asian'])) {
                $properties ['country-asian'] = $properties ['country'];
            }
            if (empty($properties ['country-complex'])) {
                $properties ['country-complex'] = $properties ['country'];
            }
        }

        // Create style name (if not given).
        $style_name = $properties ['style-name'];
        if ( empty($style_name) ) {
            $style_name = self::getNewStylename ('Text');
            $properties ['style-name'] = $style_name;
        }

        // Create empty text style.
        $object = new ODTTextStyle();
        if ($object == NULL) {
            return NULL;
        }
        
        // Import our properties
        $object->importProperties($properties, $disabled_props);
        return $object;
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
        // If the property 'vertical-align' has the value 'sub' or 'super'
        // then for ODT it needs to be converted to the corresponding 'text-position' property.
        // Replace sub and super with text-position.
        $valign = $properties ['vertical-align'];
        if (!empty($valign)) {
            if ( $valign == 'sub' ) {
                $properties ['text-position'] = '-33% 100%';
                unset($properties ['vertical-align']);
            } elseif ( $valign == 'super' ) {
                $properties ['text-position'] = '33% 100%';
                unset($properties ['vertical-align']);
            }
        }

        // Separate country from language
        $lang = $properties ['lang'];
        $country = $properties ['country'];
        if ( !empty($lang) ) {
            $parts = preg_split ('/-/', $lang);
            $lang = $parts [0];
            $country = $parts [1];
            $properties ['country'] = trim($country);
            $properties ['lang'] = trim($lang);
        }
        if (!empty($properties ['country'])) {
            if (empty($properties ['country-asian'])) {
                $properties ['country-asian'] = $properties ['country'];
            }
            if (empty($properties ['country-complex'])) {
                $properties ['country-complex'] = $properties ['country'];
            }
        }

        // Always set 'auto-text-indent = false' if 'text-indent' is set.
        if (!empty($properties ['text-indent'])) {
            $properties ['auto-text-indent'] = 'false';
        }


        // Create style name (if not given).
        $style_name = $properties ['style-name'];
        if ( empty($style_name) ) {
            $style_name = self::getNewStylename ('Paragraph');
            $properties ['style-name'] = $style_name;
        }

        // FIXME: fix missing tab stop handling...
        //case 'tab-stop':
        //    $tab .= $params [$property]['name'].'="'.$value.'" ';
        //    $tab .= self::writeExtensionNames ($params [$property]['name'], $value);
        //    break;

        // Create empty paragraph style.
        $object = new ODTParagraphStyle();
        if ($object == NULL) {
            return NULL;
        }
        
        // Import our properties
        $object->importProperties($properties, $disabled_props);
        return $object;
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
        // If we want to change the table width we must set table:align to something else
        // than "margins". Otherwise the width will not be changed.
        // Also we set a fixed default width of 100%. Otherwise setting the width of the columns
        // will have no effect in case the user does not specify any width for the whole table.
        // FIXME: This will always produce at least one attribute.
        //        It would be more elegant to change the style if we find any width attributes
        //        in the headers/columns. Maybe later.
        $properties ['align'] = 'center';
        $table_width = $max_width_cm.'cm';

        if ( !empty ($properties ['width']) ) {
            // If width has a percentage value we need to use the rel-width attribute,
            // otherwise the width attribute
            $width = $properties ['width'];
            if ( $width [strlen($width)-1] != '%' ) {
                $properties ['width'] = $table_width;
            } else {
                // Better calculate absolute width and use it instead of relative width.
                // Some applications might not support relative width.
                $table_width = (($max_width_cm * trim($width, '%'))/100).'cm';
                $properties ['width'] = $table_width;
            }
        }
        
        // Convert property 'border-model' to ODT
        if ( !empty ($properties ['border-model']) ) {
            if ( $properties ['border-model'] == 'collapse' ) {
                $properties ['border-model'] = 'collapsing';
            } else {
                $properties ['border-model'] = 'separating';
            }
        }

        // Create style name (if not given).
        $style_name = $properties ['style-name'];
        if ( empty($style_name) ) {
            $style_name = self::getNewStylename ('Table');
            $properties ['style-name'] = $style_name;
        }

        // Create empty table style.
        $object = new ODTTableStyle();
        if ($object == NULL) {
            return NULL;
        }
        
        // Import our properties
        $object->importProperties($properties, $disabled_props);
        return $object;
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
        // Create style name (if not given).
        $style_name = $properties ['style-name'];
        if ( empty($style_name) ) {
            $style_name = self::getNewStylename ('TableRow');
            $properties ['style-name'] = $style_name;
        }

        // Create empty table row style.
        $object = new ODTTableRowStyle();
        if ($object == NULL) {
            return NULL;
        }
        
        // Import our properties
        $object->importProperties($properties, $disabled_props);
        return $object;
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
        // Create style name (if not given).
        $style_name = $properties ['style-name'];
        if ( empty($style_name) ) {
            $style_name = self::getNewStylename ('TableCell');
            $properties ['style-name'] = $style_name;
        }

        // Create empty table cell style.
        $object = new ODTTableCellStyle();
        if ($object == NULL) {
            return NULL;
        }
        
        // Import our properties
        $object->importProperties($properties, $disabled_props);
        return $object;
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
        // Create style name (if not given).
        $style_name = $properties ['style-name'];
        if ( empty($style_name) ) {
            $style_name = self::getNewStylename ('TableColumn');
            $properties ['style-name'] = $style_name;
        }

        // Convert width to ODT format
        $table_co_width = $properties ['width'];
        if ( !empty ($table_co_width) ) {
            $length = strlen ($table_co_width);
            if ( $table_co_width [$length-1] != '%' ) {
                $properties ['column-width'] = $table_co_width;
            } else {
                // Columns have a specific syntax for relative width in %!
                // Change % to *.
                //$table_co_width [$length-1] = '*';
                $table_co_width = trim ($table_co_width, '%');
                $properties ['rel-column-width'] = $table_co_width;
            }
        }

        // Create empty table column style.
        $object = new ODTTableColumnStyle();
        if ($object == NULL) {
            return NULL;
        }
        
        // Import our properties
        $object->importProperties($properties, $disabled_props);
        return $object;
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
        $attrs = 0;

        $columns = '';
        if ( empty ($disabled_props ['column-count']) ) {
            $columns = $properties ['column-count'];
            $attrs++;
        }

        $rule_width = '';
        if ( empty ($disabled_props ['column-rule-width']) ) {
            $rule_width = $properties ['column-rule-width'];
            $attrs++;
        }

        $rule_style = '';
        if ( empty ($disabled_props ['column-rule-style']) ) {
            $rule_style = $properties ['column-rule-style'];
            $attrs++;
        }

        $rule_color = '';
        if ( empty ($disabled_props ['column-rule-color']) ) {
            $rule_color = $properties ['column-rule-color'];
            $attrs++;
        }

        $gap = '';
        if ( empty ($disabled_props ['column-gap']) ) {
            $gap = $properties ['column-gap'];
            $attrs++;
        }

        // If all relevant properties are empty or disabled, then there
        // are no attributes for our style. Return NULL to indicate 'no style required'.
        if ( $attrs == 0 ) {
            return NULL;
        }

        // Create style name (if not given).
        $style_name = $properties ['style-name'];
        if ( empty($style_name) ) {
            $style_name = self::getNewStylename ('Frame');
            $properties ['style-name'] = $style_name;
        }

        $width = '1000*';

        $style = '<style:style style:name="'.$style_name.'" style:family="graphic" style:parent-style-name="Frame">
                    <style:graphic-properties fo:border="none" style:vertical-pos="top" style:vertical-rel="paragraph-content" style:horizontal-pos="center" style:horizontal-rel="paragraph">
<style:columns fo:column-count="'.$columns.'" fo:column-gap="'.$gap.'">
<style:column-sep style:style="'.$rule_style.'" style:color="'.$rule_color.'" style:width="'.$rule_width.'"/>
<style:column style:rel-width="'.$width.'" fo:start-indent="0cm" fo:end-indent="0cm"/>
<style:column style:rel-width="'.$width.'" fo:start-indent="0cm" fo:end-indent="0cm"/>
<style:column style:rel-width="'.$width.'" fo:start-indent="0cm" fo:end-indent="0cm"/>
</style:columns>
</style:graphic-properties></style:style>';

        // Create empty frame style.
        // Not supported yet, so we create an "unknown" style
        $object = new ODTUnknownStyle();
        if ($object == NULL) {
            return NULL;
        }
        $object->setStyleContent($style);

        return $object;
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
        // Create style name (if not given).
        $style_name = $properties ['style-name'];
        if ( empty($style_name) ) {
            $style_name = self::getNewStylename ('Page');
            $properties ['style-name'] = $style_name;
        }
        $style = '<style:page-layout style:name="'.$style_name.'">
                <style:page-layout-properties fo:page-width="'.$properties ['width'].'cm" fo:page-height="'.$properties ['height'].'cm" style:num-format="1" style:print-orientation="landscape" fo:margin-top="'.$properties ['margin-top'].'cm" fo:margin-bottom="'.$properties ['margin-bottom'].'cm" fo:margin-left="'.$properties ['margin-left'].'cm" fo:margin-right="'.$properties ['margin-right'].'cm" style:writing-mode="lr-tb" style:footnote-max-height="0cm">
                    <style:footnote-sep style:width="0.018cm" style:distance-before-sep="0.1cm" style:distance-after-sep="0.1cm" style:adjustment="left" style:rel-width="25%" style:color="#000000"/>
                </style:page-layout-properties>
                <style:header-style/>
                <style:footer-style/>
            </style:page-layout>';

        // Create empty page style.
        // Not supported yet, so we create an "unknown" style
        $object = new ODTUnknownStyle();
        if ($object == NULL) {
            return NULL;
        }
        $object->setStyleContent($style);

        return $object;
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
        $properties = array();
        $properties ['style-name'] = $name;
        $properties ['style-display-name'] = $name;
        $properties ['font-size'] = $size;
        $properties ['font-size-asian'] = $size;
        $properties ['font-size-complex'] = $size;
        return self::createTextStyle($properties);
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
        $properties = array();
        $properties ['style-name'] = $style_name;
        if ( !empty($parent) ) {
            $properties ['style-parent'] = $parent;
        }
        if ($before == true ) {
            $properties ['break-before'] = 'page';
        } else {
            $properties ['break-after'] = 'page';
        }
        return self::createParagraphStyle($properties);
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
        $values = preg_split ('/\s+/', $value);
        $value = '';
        foreach ($values as $part) {
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

            $value .= ' '.$part;
        }
        $value = trim($value);
        $value = trim($value, '"');

        return $value;
    }
}
