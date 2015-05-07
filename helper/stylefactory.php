<?php
/**
 * Helper class for creating ODT styles.
 * 
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     LarsDW223
 */

// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

if (!defined('DOKU_LF')) define('DOKU_LF', "\n");
if (!defined('DOKU_TAB')) define('DOKU_TAB', "\t");
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC.'lib/plugins/');

class helper_plugin_odt_stylefactory extends DokuWiki_Plugin {
    protected static $style_base_name = 'PluginODTAutoStyle_';
    protected static $style_count = 0;

    function getMethods() {
        $result = array();
        $result[] = array(
                'name'   => 'createTextStyle',
                'desc'   => 'Returns ODT text style definition in $style with the wanted properties. Returns NULL, if no relevant properties were found, otherwise the new style name.',
                'params' => array('$style' => 'string'),
                'params' => array('$properties' => 'array'),
                'params' => array('$disabled_props' => 'array'),
                'params' => array('$parent' => 'string'),
                'return' => array('ODT text style name' => 'string'),
                );
        $result[] = array(
                'name'   => 'createParagraphStyle',
                'desc'   => 'Returns ODT paragrap style definition in $style with the wanted properties. Returns NULL, if no relevant properties were found, otherwise the new style name.',
                'params' => array('$style' => 'string'),
                'params' => array('$properties' => 'array'),
                'params' => array('$disabled_props' => 'array'),
                'params' => array('$parent' => 'string'),
                'return' => array('ODT paragraph style name' => 'string'),
                );
        $result[] = array(
                'name'   => 'createTableTableStyle',
                'desc'   => 'Returns ODT table style definition in $style with the wanted properties. Returns NULL, if no relevant properties were found, otherwise the new style name.',
                'params' => array('$style' => 'string'),
                'params' => array('$properties' => 'array'),
                'params' => array('$disabled_props' => 'array'),
                'params' => array('$max_width_cm' => 'integer'),
                'return' => array('ODT table style name' => 'string'),            
                );
        $result[] = array(
                'name'   => 'createTableRowStyle',
                'desc'   => 'Returns ODT table row style definition in $style with the wanted properties. Returns NULL, if no relevant properties were found, otherwise the new style name.',
                'params' => array('$style' => 'string'),
                'params' => array('$properties' => 'array'),
                'params' => array('$disabled_props' => 'array'),
                'return' => array('ODT table row style name' => 'string'),
                );
        $result[] = array(
                'name'   => 'createTableCellStyle',
                'desc'   => 'Returns ODT table cell style definition in $style with the wanted properties. Returns NULL, if no relevant properties were found, otherwise the new style name.',
                'params' => array('$style' => 'string'),
                'params' => array('$properties' => 'array'),
                'params' => array('$disabled_props' => 'array'),
                'return' => array('ODT table cell style name' => 'string'),
                );
        $result[] = array(
                'name'   => 'createTableColumnStyle',
                'desc'   => 'Returns ODT table column style definition in $style with the wanted properties. Returns NULL, if no relevant properties were found, otherwise the new style name.',
                'params' => array('$style' => 'string'),
                'params' => array('$properties' => 'array'),
                'params' => array('$disabled_props' => 'array'),
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
     */
    public static function createTextStyle(&$style, $properties, $disabled_props = NULL, $parent = NULL){
        $attrs = 0;

        if ( empty ($disabled_props ['background-color']) === true ) {
            $odt_bg = $properties ['background-color'];
            $attrs++;
        }
        if ( empty ($disabled_props ['color']) === true ) {
            $odt_fo = $properties ['color'];
            $attrs++;
        }
        if ( empty ($disabled_props ['text-align']) === true ) {
            $odt_fo_text_align = $properties ['text-align'];
            $attrs++;
        }
        if ( empty ($disabled_props ['font-style']) === true ) {
            $odt_fo_style = $properties ['font-style'];
            $attrs++;
        }
        if ( empty ($disabled_props ['font-weight']) === true ) {
            $odt_fo_weight = $properties ['font-weight'];
            $attrs++;
        }
        if ( empty ($disabled_props ['font-size']) === true ) {
            $odt_fo_size = $properties ['font-size'];
            $attrs++;
        }
        if ( empty ($disabled_props ['border']) === true ) {
            $odt_fo_border = $properties ['border'];
            $attrs++;
        }
        if ( empty ($disabled_props ['font-family']) === true ) {
            $odt_fo_family = $properties ['font-family'];
            $attrs++;
        }
        if ( empty ($disabled_props ['font-variant']) === true ) {
            $odt_fo_variant = $properties ['font-variant'];
            $attrs++;
        }
        if ( empty ($disabled_props ['letter-spacing']) === true ) {
            $odt_fo_lspacing = $properties ['letter-spacing'];
            $attrs++;
        }
        if ( empty ($disabled_props ['vertical-align']) === true ) {
            $odt_valign = $properties ['vertical-align'];
            $attrs++;
        }
        if ( empty ($disabled_props ['background-image']) === true ) {
            $picture = $properties ['background-image'];
            $attrs++;
        }
        if ( empty ($parent) === false ) {
            $attrs++;
        }

        // If all relevant properties are empty or disabled, then there
        // are no attributes for our style. Return NULL to indicate 'no style required'.
        if ( $attrs == 0 ) {
            return NULL;
        }

        // Replace sub and super with text-position.
        unset($odt_text_pos);
        if ( $odt_valign == 'sub' ) {
            $odt_text_pos = '-33% 100%';
            unset($odt_valign);
        }
        if ( $odt_valign == 'super' ) {
            $odt_text_pos = '33% 100%';
            unset($odt_valign);
        }

        // Create style name.
        $style_name = self::getNewStylename ('Text');

        $style  = '<style:style style:name="'.$style_name.'" style:family="text"';
        if ( empty ($parent) === false ) {
            $style .= ' style:parent-style-name="'.$parent.'" ';
        }
        $style .= '>';
        $style .= '<style:text-properties ';
        if ( empty ($odt_fo) === false ) {
            $style .= 'fo:color="'.$odt_fo.'" ';
        }
        if ( empty ($odt_bg) === false ) {
            $style .= 'fo:background-color="'.$odt_bg.'" ';
        }
        if ( empty ($odt_fo_style) === false ) {
            $style .= 'fo:font-style="'.$odt_fo_style.'" ';
            $style .= 'style:font-style-asian="'.$odt_fo_style.'" ';
            $style .= 'style:font-style-complex="'.$odt_fo_style.'" ';
        }
        if ( empty ($odt_fo_weight) === false ) {
            $style .= 'fo:font-weight="'.$odt_fo_weight.'" ';
            $style .= 'style:font-weight-asian="'.$odt_fo_weight.'" ';
            $style .= 'style:font-weight-complex="'.$odt_fo_weight.'" ';
        }
        if ( empty ($odt_fo_size) === false ) {
            $style .= 'fo:font-size="'.$odt_fo_size.'" ';
            $style .= 'style:font-size-asian="'.$odt_fo_size.'" ';
            $style .= 'style:font-size-complex="'.$odt_fo_size.'" ';
        }
        if ( empty ($odt_fo_border) === false ) {
            $style .= 'fo:border="'.$odt_fo_border.'" ';
        }
        if ( empty ($odt_fo_family) === false ) {
            $style .= 'fo:font-family="'.$odt_fo_family.'" ';
        }
        if ( empty ($odt_fo_variant) === false ) {
            $style .= 'fo:font-variant="'.$odt_fo_variant.'" ';
        }
        if ( empty ($odt_fo_lspacing) === false ) {
            $style .= 'fo:letter-spacing="'.$odt_fo_lspacing.'" ';
        }
        if ( empty ($odt_valign) === false ) {
            $style .= 'style:vertical-align="'.$odt_valign.'" ';
        }
        if ( empty ($odt_text_pos) === false ) {
            $style .= 'style:text-position="'.$odt_text_pos.'" ';
        }
        $style .= '/>';
        $style .= '</style:style>';

        return $style_name;
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
     */
    public static function createParagraphStyle(&$style, $properties, $disabled_props = NULL, $parent = NULL){
        $attrs = 0;

        if ( empty ($disabled_props ['background-color']) === true ) {
            $odt_bg = $properties ['background-color'];
            $attrs++;
        }
        if ( empty ($disabled_props ['color']) === true ) {
            $odt_fo = $properties ['color'];
            $attrs++;
        }
        if ( empty ($disabled_props ['text-align']) === true ) {
            $odt_fo_text_align = $properties ['text-align'];
            $attrs++;
        }
        if ( empty ($disabled_props ['font-style']) === true ) {
            $odt_fo_style = $properties ['font-style'];
            $attrs++;
        }
        if ( empty ($disabled_props ['font-weight']) === true ) {
            $odt_fo_weight = $properties ['font-weight'];
            $attrs++;
        }
        if ( empty ($disabled_props ['font-size']) === true ) {
            $odt_fo_size = $properties ['font-size'];
            $attrs++;
        }
        if ( empty ($disabled_props ['border']) === true ) {
            $odt_fo_border = $properties ['border'];
            $attrs++;
        }
        if ( empty ($disabled_props ['font-family']) === true ) {
            $odt_fo_family = $properties ['font-family'];
            $attrs++;
        }
        if ( empty ($disabled_props ['font-variant']) === true ) {
            $odt_fo_variant = $properties ['font-variant'];
            $attrs++;
        }
        if ( empty ($disabled_props ['letter-spacing']) === true ) {
            $odt_fo_lspacing = $properties ['letter-spacing'];
            $attrs++;
        }
        if ( empty ($disabled_props ['vertical-align']) === true ) {
            $odt_valign = $properties ['vertical-align'];
            $attrs++;
        }
        if ( empty ($disabled_props ['line-height']) === true ) {
            $odt_fo_line_height = $properties ['line-height'];
            $attrs++;
        }
        if ( empty ($disabled_props ['background-image']) === true ) {
            $picture = $properties ['background-image'];
            $attrs++;
        }
        if ( empty ($parent) === false ) {
            $attrs++;
        }

        // If all relevant properties are empty or disabled, then there
        // are no attributes for our style. Return NULL to indicate 'no style required'.
        if ( $attrs == 0 ) {
            return NULL;
        }

        // Replace sub and super with text-position.
        unset($odt_text_pos);
        if ( $odt_valign == 'sub' ) {
            $odt_text_pos = '-33% 100%';
            unset($odt_valign);
        }
        if ( $odt_valign == 'super' ) {
            $odt_text_pos = '33% 100%';
            unset($odt_valign);
        }

        // Create style name.
        $style_name = self::getNewStylename ('Paragraph');

        $style  = '<style:style style:name="'.$style_name.'" style:family="paragraph"';
        if ( empty ($parent) === false ) {
            $style .= ' style:parent-style-name="'.$parent.'" ';
        }
        $style .= '>';
        $style .= '<style:paragraph-properties ';
        if ( empty ($odt_fo_text_align) === false ) {
            $style .= 'fo:text-align="'.$odt_fo_text_align.'" ';
        }
        $style .= '/>';
        $style .= '<style:text-properties ';
        if ( empty ($odt_fo) === false ) {
            $style .= 'fo:color="'.$odt_fo.'" ';
        }
        if ( empty ($odt_bg) === false ) {
            $style .= 'fo:background-color="'.$odt_bg.'" ';
        }
        if ( empty ($odt_fo_style) === false ) {
            $style .= 'fo:font-style="'.$odt_fo_style.'" ';
            $style .= 'style:font-style-asian="'.$odt_fo_style.'" ';
            $style .= 'style:font-style-complex="'.$odt_fo_style.'" ';
        }
        if ( empty ($odt_fo_weight) === false ) {
            $style .= 'fo:font-weight="'.$odt_fo_weight.'" ';
            $style .= 'style:font-weight-asian="'.$odt_fo_weight.'" ';
            $style .= 'style:font-weight-complex="'.$odt_fo_weight.'" ';
        }
        if ( empty ($odt_fo_size) === false ) {
            $style .= 'fo:font-size="'.$odt_fo_size.'" ';
            $style .= 'style:font-size-asian="'.$odt_fo_size.'" ';
            $style .= 'style:font-size-complex="'.$odt_fo_size.'" ';
        }
        if ( empty ($odt_fo_border) === false ) {
            $style .= 'fo:border="'.$odt_fo_border.'" ';
        }
        if ( empty ($odt_fo_family) === false ) {
            $style .= 'fo:font-family="'.$odt_fo_family.'" ';
        }
        if ( empty ($odt_fo_variant) === false ) {
            $style .= 'fo:font-variant="'.$odt_fo_variant.'" ';
        }
        if ( empty ($odt_fo_lspacing) === false ) {
            $style .= 'fo:letter-spacing="'.$odt_fo_lspacing.'" ';
        }
        if ( empty ($odt_fo_line_height) === false ) {
            $style .= 'fo:line-height="'.$odt_fo_line_height.'" ';
        }
        if ( empty ($odt_valign) === false ) {
            $style .= 'style:vertical-align="'.$odt_valign.'" ';
        }
        if ( empty ($odt_text_pos) === false ) {
            $style .= 'style:text-position="'.$odt_text_pos.'" ';
        }
        $style .= '/>';
        $style .= '</style:style>';

        return $style_name;
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
     */
    public static function createTableTableStyle(&$style, $properties, $disabled_props = NULL, $max_width_cm = 17){
        $attrs = 0;

        if ( empty ($disabled_props ['width']) === true ) {
            $width = $properties ['width'];
            $attrs++;
        }
        if ( empty ($disabled_props ['border-collapse']) === true ) {
            $table_border_model = $properties ['border-collapse'];
            $attrs++;
        }
        if ( empty ($disabled_props ['background-color']) === true ) {
            $table_bg_color = $properties ['background-color'];
            $attrs++;
        }

        // If we want to change the table width we must set table:align to something else
        // than "margins". Otherwise the width will not be changed.
        // Also we set a fixed default width of 100%. Otherwise setting the width of the columns
        // will have no effect in case the user does not specify any width for the whole table.
        // FIXME: This will always produce at least one attribute.
        //        It would be more elegant to change the style if we find any width attributes
        //        in the headers/columns. Maybe later.
        $table_align = 'center';
        $attrs++;
        $table_width = $max_width_cm.'cm';
        $attrs++;

        // If all relevant properties are empty or disabled, then there
        // are no attributes for our style. Return NULL to indicate 'no style required'.
        if ( $attrs == 0 ) {
            return NULL;
        }

        if ( empty ($width) === false ) {
            // If width has a percentage value we need to use the rel-width attribute,
            // otherwise the width attribute
            if ( $width [strlen($width)-1] != '%' ) {
                $table_width = $width;
                unset ($table_rel_width);
            } else {
                //unset ($table_width);
                //$table_rel_width = $width;

                // Better calculate absolute width and use it instead of relative width.
                // Some applications might not support relative width.
                unset ($table_rel_width);
                $table_width = (($max_width_cm * trim($width, '%'))/100).'cm';
            }
        }
        if ( empty ($table_border_model) === false ) {
            if ( $table_border_model == 'collapse' ) {
                $table_border_model = 'collapsing';
            } else {
                $table_border_model = 'separating';
            }
        }

        // Create style name.
        $style_name = self::getNewStylename ('Table');

        $style  = '<style:style style:name="'.$style_name.'" style:family="table">';
        $style .= '<style:table-properties ';
        if ( empty ($table_width) === false ) {
            $style .= 'style:width="'.$table_width.'" ';
        }
        if ( empty ($table_rel_width) === false ) {
            $style .= 'style:rel-width="'.$table_rel_width.'" ';
        }
        if ( empty ($table_align) === false ) {
            $style .= ' table:align="'.$table_align.'"';
        }
        if ( empty ($table_border_model) === false ) {
            $style .= ' table:border-model="'.$table_border_model.'"';
        }
        if ( empty ($table_bg_color) === false ) {
            $style .= ' fo:background-color="'.$table_bg_color.'"';
        }
        $style .= '/>';
        $style .= '</style:style>';

        return $style_name;
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
     */
    public static function createTableRowStyle(&$style, $properties, $disabled_props = NULL){
        $attrs = 0;

        if ( empty ($disabled_props ['height']) === true ) {
            $height = $properties ['height'];
            $attrs++;
        }
        if ( empty ($disabled_props ['background-color']) === true ) {
            $table_bg_color = $properties ['background-color'];
            $attrs++;
        }

        // If all relevant properties are empty or disabled, then there
        // are no attributes for our style. Return NULL to indicate 'no style required'.
        if ( $attrs == 0 ) {
            return NULL;
        }

        if ( empty ($height) === false ) {
            // If height has a percentage value we need to use the rel-height attribute,
            // otherwise the height attribute
            if ( $height [strlen($height)-1] != '%' ) {
                $table_height = $height;
                unset ($table_rel_height);
            } else {
                unset ($table_height);
                $table_rel_height = $height;
            }
        }

        // Create style name.
        $style_name = self::getNewStylename ('TableRow');

        $style  = '<style:style style:name="'.$style_name.'" style:family="table-row">';
        $style .= '<style:table-properties ';
        if ( empty ($table_height) === false ) {
            $style .= 'style:height="'.$table_height.'" ';
        }
        if ( empty ($table_rel_height) === false ) {
            $style .= 'style:rel_height="'.$table_rel_height.'" ';
        }
        if ( empty ($table_bg_color) === false ) {
            $style .= ' fo:background-color="'.$table_bg_color.'"';
        }
        $style .= '/>';
        $style .= '</style:style>';

        return $style_name;
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
     */
    public static function createTableCellStyle(&$style, $properties, $disabled_props = NULL){
        $attrs = 0;

        if ( empty ($disabled_props ['background-color']) === true ) {
            $table_bg_color = $properties ['background-color'];
            $attrs++;
        }
        if ( empty ($disabled_props ['vertical-align']) === true ) {
            $table_valign = $properties ['vertical-align'];
            $attrs++;
        }
        if ( empty ($disabled_props ['border']) === true ) {
            $table_border = $properties ['border'];
            $attrs++;
        }

        // If all relevant properties are empty or disabled, then there
        // are no attributes for our style. Return NULL to indicate 'no style required'.
        if ( $attrs == 0 ) {
            return NULL;
        }

        // Create style name.
        $style_name = self::getNewStylename ('TableCell');

        $style  = '<style:style style:name="'.$style_name.'" style:family="table-cell">';
        $style .= '<style:table-cell-properties ';
        if ( empty ($table_valign) === false ) {
            $style .= 'style:vertical-align="'.$table_valign.'" ';
        }
        if ( empty ($table_bg_color) === false ) {
            $style .= 'fo:background-color="'.$table_bg_color.'" ';
        }
        if ( empty ($table_border) === false ) {
            $style .= 'fo:border="'.$table_border.'" ';
        }
        $style .= '/>';
        $style .= '</style:style>';

        return $style_name;
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
     */
    public static function createTableColumnStyle(&$style, $properties, $disabled_props = NULL, $style_name = NULL){
        $attrs = 0;

        if ( empty ($disabled_props ['width']) === true ) {
            $table_co_width = $properties ['width'];
            $attrs++;
        }

        // If all relevant properties are empty or disabled, then there
        // are no attributes for our style. Return NULL to indicate 'no style required'.
        if ( $attrs == 0 ) {
            return NULL;
        }

        // Create style name.
        if ( empty ($style_name) === true ) {
            $style_name = self::getNewStylename ('TableColumn');
        }

        $style  = '<style:style style:name="'.$style_name.'" style:family="table-column">';
        $style .= '<style:table-column-properties ';
        if ( empty ($table_co_width) === false ) {
            $style .= 'style:column-width="'.$table_co_width.'" ';
        }
        $style .= '/>';
        $style .= '</style:style>';

        return $style_name;
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
     */
    public static function createMultiColumnFrameStyle(&$style, $properties, $disabled_props = NULL) {
        $attrs = 0;

        if ( empty ($disabled_props ['column-count']) === true ) {
            $columns = $properties ['column-count'];
            $attrs++;
        }

        if ( empty ($disabled_props ['column-rule']) === true ) {
            $rule_parts = explode (' ', $properties ['column-rule']);
            $attrs++;
        }

        if ( empty ($disabled_props ['column-gap']) === true ) {
            $gap = $properties ['column-gap'];
            $attrs++;
        }

        // If all relevant properties are empty or disabled, then there
        // are no attributes for our style. Return NULL to indicate 'no style required'.
        if ( $attrs == 0 ) {
            return NULL;
        }

        // Create style name.
        $style_name = self::getNewStylename ('Frame');

        $width = '1000*';

        $style = '<style:style style:name="'.$style_name.'" style:family="graphic" style:parent-style-name="Frame">
                    <style:graphic-properties fo:border="none" style:vertical-pos="top" style:vertical-rel="paragraph-content" style:horizontal-pos="center" style:horizontal-rel="paragraph">
<style:columns fo:column-count="'.$columns.'" fo:column-gap="'.$gap.'">
<style:column-sep style:style="'.$rule_parts [1].'" style:color="'.$rule_parts [2].'" style:width="'.$rule_parts [0].'"/>
<style:column style:rel-width="'.$width.'" fo:start-indent="0cm" fo:end-indent="0cm"/>
<style:column style:rel-width="'.$width.'" fo:start-indent="0cm" fo:end-indent="0cm"/>
<style:column style:rel-width="'.$width.'" fo:start-indent="0cm" fo:end-indent="0cm"/>
</style:columns>
</style:graphic-properties></style:style>';

        return $style_name;
    }
}
?>
