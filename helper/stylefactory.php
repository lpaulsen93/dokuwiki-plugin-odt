<?php
/**
 * Helper class for creating ODT styles.
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     LarsDW223
 */

// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();


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
     * @param $style
     * @param $properties
     * @param null $disabled_props
     * @param null $parent
     * @return string
     */
    public static function createTextStyle(&$style, $properties, $disabled_props = NULL, $parent = NULL){
        static $params = array (
              'style-name'         => array ('section' => 'header',    'name' => 'style:name',           'is_attr' => false),
              'style-display-name' => array ('section' => 'header',    'name' => 'style:display-name',   'is_attr' => false),
              'padding-top'        => array ('section' => 'text',      'name' => 'fo:padding-top',       'is_attr' => true),
              'padding-bottom'     => array ('section' => 'text',      'name' => 'fo:padding-bottom',    'is_attr' => true),
              'padding-left'       => array ('section' => 'text',      'name' => 'fo:padding-left',      'is_attr' => true),
              'padding-right'      => array ('section' => 'text',      'name' => 'fo:padding-right',     'is_attr' => true),
              'border-left'        => array ('section' => 'text',      'name' => 'fo:border-left',       'is_attr' => true),
              'border-right'       => array ('section' => 'text',      'name' => 'fo:border-right',      'is_attr' => true),
              'border-top'         => array ('section' => 'text',      'name' => 'fo:border-top',        'is_attr' => true),
              'border-bottom'      => array ('section' => 'text',      'name' => 'fo:border-bottom',     'is_attr' => true),
              'color'              => array ('section' => 'text',      'name' => 'fo:color',             'is_attr' => true),
              'background-color'   => array ('section' => 'text',      'name' => 'fo:background-color',  'is_attr' => true),
              'background-image'   => array ('section' => 'text',      'name' => 'fo:background-image',  'is_attr' => true),
              'font-style'         => array ('section' => 'text',      'name' => 'fo:font-style',        'is_attr' => true),
              'font-weight'        => array ('section' => 'text',      'name' => 'fo:font-weight',       'is_attr' => true),
              'font-size'          => array ('section' => 'text',      'name' => 'fo:font-size',         'is_attr' => true),
              'font-family'        => array ('section' => 'text',      'name' => 'fo:font-family',       'is_attr' => true),
              'font-variant'       => array ('section' => 'text',      'name' => 'fo:font-variant',      'is_attr' => true),
              'letter-spacing'     => array ('section' => 'text',      'name' => 'fo:letter-spacing',    'is_attr' => true),
              'vertical-align'     => array ('section' => 'text',      'name' => 'style:vertical-align', 'is_attr' => true),
              'display'            => array ('section' => 'text',      'name' => 'text:display',         'is_attr' => true),
              'lang'               => array ('section' => 'text',      'name' => 'fo:language',          'is_attr' => true),
              );

        $attrs = 0;

        // First, count parameters that are an attribute, not empty and not disabled.
        foreach ($properties as $property => $value) {
            if ( empty ($disabled_props [$property]) &&
                 !empty ($properties [$property]) &&
                 $params [$property]['is_attr'] === true ) {
                $attrs++;
            }
        }

        // If all relevant properties are empty or disabled, then there
        // are no attributes for our style. Return NULL to indicate 'no style required'.
        if ( $attrs == 0 ) {
            return NULL;
        }

        // Replace sub and super with text-position.
        $odt_valign = $properties ['vertical-align'];
        $odt_text_pos = '';
        if ( $odt_valign == 'sub' ) {
            $odt_text_pos = '-33% 100%';
            unset($odt_valign);
        } elseif ( $odt_valign == 'super' ) {
            $odt_text_pos = '33% 100%';
            unset($odt_valign);
        }

        // Separate country from language
        $lang = $properties ['lang'];
        $country = $properties ['country'];
        if ( !empty($lang) ) {
            $parts = preg_split ('/-/', $lang);
            $lang = $parts [0];
            $country = $parts [1];
        }

        // Create style name (if not given).
        $style_name = $properties ['style-name'];
        if ( empty($style_name) ) {
            $style_name = self::getNewStylename ('Text');
            $properties ['style-name'] = $style_name;
        }

        // Build content for the different sections of the style
        // (Except style-name, already inserted above)
        $header = '';
        $text = '';
        foreach ($properties as $property => $value) {
            if ( empty ($disabled_props [$property]) && !empty ($properties [$property]) ) {
                $name = $params [$property]['name'];
                switch ($params [$property]['section']) {

                    case 'header':
                        if ( $property != 'style-name' ) {
                            $value = trim($value, '"');
                        } else {
                            $value = $style_name;
                        }
                        $header .= $params [$property]['name'].'="'.$value.'" ';
                        $header .= self::writeExtensionNames ($name, $value);
                        break;

                    case 'text':
                        if ( $property != 'lang' ) {
                            $value = trim($value, '"');
                        } else {
                            $value = trim($lang, '"');
                        }
                        $text .= $params [$property]['name'].'="'.$value.'" ';
                        $text .= self::writeExtensionNames ($name, $value);
                        break;
                }
            }
        }

        // Some extra handling for text-position and country.
        if ( !empty ($odt_text_pos) ) {
            $text .= 'style:text-position="'.$odt_text_pos.'" ';
            $text .= self::writeExtensionNames ('style:text-position', $odt_text_pos);
        }
        if ( !empty($country) ) {
            $text .= 'fo:country="'.$country.'" ';
            $text .= self::writeExtensionNames ('fo:country', $country);
        }

        // Build style.
        $style  = '<style:style '.$header.' style:family="text"';
        if ( !empty ($parent) ) {
            $style .= ' style:parent-style-name="'.$parent.'" ';
        }
        $style .= '>';
        $style .= '<style:text-properties '.$text.'/>';
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
     * @param $style
     * @param $properties
     * @param null $disabled_props
     * @param null $parent
     * @return string
     */
    public static function createParagraphStyle(&$style, $properties, $disabled_props = NULL, $parent = NULL){
        static $params = array (
              'style-name'         => array ('section' => 'header',    'name' => 'style:name',              'is_attr' => false),
              'style-display-name' => array ('section' => 'header',    'name' => 'style:display-name',      'is_attr' => false),
              'style-parent'       => array ('section' => 'header',    'name' => 'style:parent-style-name', 'is_attr' => true),
              'style-class'        => array ('section' => 'header',    'name' => 'style:class',             'is_attr' => true),
              'master-page-name'   => array ('section' => 'header',    'name' => 'style:master-page-name',  'is_attr' => true),
              'style-position'     => array ('section' => 'tab-stop',  'name' => 'style:position',          'is_attr' => true),
              'style-type'         => array ('section' => 'tab-stop',  'name' => 'style:type',              'is_attr' => true),
              'style-leader-style' => array ('section' => 'tab-stop',  'name' => 'style:leader-style',      'is_attr' => true),
              'style-leader-text'  => array ('section' => 'tab-stop',  'name' => 'style:leader-text',       'is_attr' => true),
              'text-align'         => array ('section' => 'paragraph', 'name' => 'fo:text-align',           'is_attr' => true),
              'text-indent'        => array ('section' => 'paragraph', 'name' => 'fo:text-indent',          'is_attr' => true),
              'margin-top'         => array ('section' => 'paragraph', 'name' => 'fo:margin-top',           'is_attr' => true),
              'margin-bottom'      => array ('section' => 'paragraph', 'name' => 'fo:margin-bottom',        'is_attr' => true),
              'margin-left'        => array ('section' => 'paragraph', 'name' => 'fo:margin-left',          'is_attr' => true),
              'margin-right'       => array ('section' => 'paragraph', 'name' => 'fo:margin-right',         'is_attr' => true),
              'page-number'        => array ('section' => 'paragraph', 'name' => 'style:page-number',       'is_attr' => true),
              'padding-top'        => array ('section' => 'text',      'name' => 'fo:padding-top',          'is_attr' => true),
              'padding-bottom'     => array ('section' => 'text',      'name' => 'fo:padding-bottom',       'is_attr' => true),
              'padding-left'       => array ('section' => 'text',      'name' => 'fo:padding-left',         'is_attr' => true),
              'padding-right'      => array ('section' => 'text',      'name' => 'fo:padding-right',        'is_attr' => true),
              'border-left'        => array ('section' => 'text',      'name' => 'fo:border-left',          'is_attr' => true),
              'border-right'       => array ('section' => 'text',      'name' => 'fo:border-right',         'is_attr' => true),
              'border-top'         => array ('section' => 'text',      'name' => 'fo:border-top',           'is_attr' => true),
              'border-bottom'      => array ('section' => 'text',      'name' => 'fo:border-bottom',        'is_attr' => true),
              'color'              => array ('section' => 'text',      'name' => 'fo:color',                'is_attr' => true),
              'background-color'   => array ('section' => 'text',      'name' => 'fo:background-color',     'is_attr' => true),
              'background-image'   => array ('section' => 'text',      'name' => 'fo:background-image',     'is_attr' => true),
              'font-style'         => array ('section' => 'text',      'name' => 'fo:font-style',           'is_attr' => true),
              'font-weight'        => array ('section' => 'text',      'name' => 'fo:font-weight',          'is_attr' => true),
              'font-size'          => array ('section' => 'text',      'name' => 'fo:font-size',            'is_attr' => true),
              'font-family'        => array ('section' => 'text',      'name' => 'fo:font-family',          'is_attr' => true),
              'font-variant'       => array ('section' => 'text',      'name' => 'fo:font-variant',         'is_attr' => true),
              'letter-spacing'     => array ('section' => 'text',      'name' => 'fo:letter-spacing',       'is_attr' => true),
              'vertical-align'     => array ('section' => 'text',      'name' => 'style:vertical-align',    'is_attr' => true),
              'line-height'        => array ('section' => 'text',      'name' => 'fo:line-height',          'is_attr' => true),
              'display'            => array ('section' => 'text',      'name' => 'text:display',            'is_attr' => true),
              'lang'               => array ('section' => 'text',      'name' => 'fo:language',             'is_attr' => true),
              );

        $attrs = 0;

        // First, count parameters that are an attribute, not empty and not disabled.
        foreach ($properties as $property => $value) {
            if ( empty ($disabled_props [$property]) &&
                 !empty ($properties [$property]) &&
                 $params [$property]['is_attr'] === true ) {
                $attrs++;
            }
        }

        if ( !empty($parent) && empty($properties ['style-parent']) ) {
            $properties ['style-parent'] = $parent;
            $attrs++;
        }

        // If all relevant properties are empty or disabled, then there
        // are no attributes for our style. Return NULL to indicate 'no style required'.
        if ( $attrs == 0 ) {
            return NULL;
        }

        // Replace sub and super with text-position.
        $odt_valign = $properties ['vertical-align'];
        $odt_text_pos = '';
        if ( $odt_valign == 'sub' ) {
            $odt_text_pos = '-33% 100%';
            unset($odt_valign);
        } elseif ( $odt_valign == 'super' ) {
            $odt_text_pos = '33% 100%';
            unset($odt_valign);
        }

        // Separate country from language
        $lang = $properties ['lang'];
        $country = $properties ['country'];
        if ( !empty($lang) ) {
            $parts = preg_split ('/-/', $lang);
            $lang = $parts [0];
            $country = $parts [1];
        }

        // Create style name (if not given).
        $style_name = $properties ['style-name'];
        if ( empty($style_name) ) {
            $style_name = self::getNewStylename ('Paragraph');
            $properties ['style-name'] = $style_name;
        }

        // Build content for the different sections of the style
        // (Except style-name, already inserted above)
        $header = '';
        $text = '';
        $paragraph = '';
        $tab = '';
        foreach ($properties as $property => $value) {
            if ( empty ($disabled_props [$property]) && !empty ($properties [$property]) ) {
                switch ($params [$property]['section']) {

                    case 'header':
                        if ( $property != 'style-name' ) {
                            $value = trim($value, '"');
                        } else {
                            $value = $style_name;
                        }
                        $header .= $params [$property]['name'].'="'.$value.'" ';
                        $header .= self::writeExtensionNames ($params [$property]['name'], $value);
                        break;

                    case 'text':
                        if ( $property != 'lang' ) {
                            $value = trim($value, '"');
                        } else {
                            $value = trim($lang, '"');
                        }
                        $text .= $params [$property]['name'].'="'.$value.'" ';
                        $text .= self::writeExtensionNames ($params [$property]['name'], $value);
                        break;

                    case 'paragraph':
                        $value = trim($value, '"');
                        $paragraph .= $params [$property]['name'].'="'.$value.'" ';
                        $paragraph .= self::writeExtensionNames ($params [$property]['name'], $value);
                        if ( $property == 'text-indent' ) {
                            $paragraph .= ' style:auto-text-indent="false" ';
                        }
                        break;

                    case 'tab-stop':
                        $tab .= $params [$property]['name'].'="'.$value.'" ';
                        $tab .= self::writeExtensionNames ($params [$property]['name'], $value);
                        break;
                }
            }
        }

        // Some extra handling for text-position and country.
        if ( !empty ($odt_text_pos) ) {
            $text .= 'style:text-position="'.$odt_text_pos.'" ';
            $text .= self::writeExtensionNames ('style:text-position', $odt_text_pos);
        }
        if ( !empty($country) ) {
            $text .= 'fo:country="'.$country.'" ';
            $text .= self::writeExtensionNames ('fo:country', $country);
        }

        // Build style.
        $style  = '<style:style '.$header.' style:family="paragraph"';
        $style .= '>';
        $style .= '<style:paragraph-properties '.$paragraph.'>';
        if ( !empty($tab) ) {
            $style .= '<style:tab-stops><style:tab-stop '.$tab.'/></style:tab-stops>';
        }
        $style .= '</style:paragraph-properties>';
        if ( !empty($text) ) {
            $style .= '<style:text-properties '.$text.'/>';
        }
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
     * @param $style
     * @param $properties
     * @param null $disabled_props
     * @param int $max_width_cm
     * @return string
     */
    public static function createTableTableStyle(&$style, $properties, $disabled_props = NULL, $max_width_cm = 17){
        $attrs = 0;

        if ( empty ($disabled_props ['width']) ) {
            $width = $properties ['width'];
            $attrs++;
        }
        if ( empty ($disabled_props ['border-collapse']) ) {
            $table_border_model = $properties ['border-collapse'];
            $attrs++;
        }
        if ( empty ($disabled_props ['background-color']) ) {
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

        if ( !empty ($width) ) {
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
        if ( !empty ($table_border_model) ) {
            if ( $table_border_model == 'collapse' ) {
                $table_border_model = 'collapsing';
            } else {
                $table_border_model = 'separating';
            }
        }

        // Create style name (if not given).
        $style_name = $properties ['style-name'];
        if ( empty($style_name) ) {
            $style_name = self::getNewStylename ('Table');
            $properties ['style-name'] = $style_name;
        }

        $style  = '<style:style style:name="'.$style_name.'" ';
        if ( !empty ($properties ['style-display-name']) ) {
            $style .= 'style:display-name="'.$properties ['style-display-name'].'" ';
        }
        $style .= 'style:family="table">';
        $style .= '<style:table-properties ';
        if ( !empty ($table_width) ) {
            $style .= 'style:width="'.$table_width.'" ';
        }
        if ( !empty ($table_rel_width) ) {
            $style .= 'style:rel-width="'.$table_rel_width.'" ';
        }
        if ( !empty ($table_align) ) {
            $style .= ' table:align="'.$table_align.'"';
        }
        if ( !empty ($table_border_model) ) {
            $style .= ' table:border-model="'.$table_border_model.'"';
        }
        if ( !empty ($table_bg_color) ) {
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
     * @param $style
     * @param $properties
     * @param null $disabled_props
     * @return string
     */
    public static function createTableRowStyle(&$style, $properties, $disabled_props = NULL){
        $attrs = 0;

        if ( empty ($disabled_props ['height']) ) {
            $height = $properties ['height'];
            $attrs++;
        }
        if ( empty ($disabled_props ['background-color']) ) {
            $table_bg_color = $properties ['background-color'];
            $attrs++;
        }

        // If all relevant properties are empty or disabled, then there
        // are no attributes for our style. Return NULL to indicate 'no style required'.
        if ( $attrs == 0 ) {
            return NULL;
        }

        if ( !empty ($height) ) {
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
        if ( !empty ($table_height) ) {
            $style .= 'style:height="'.$table_height.'" ';
        }
        if ( !empty ($table_rel_height) ) {
            $style .= 'style:rel_height="'.$table_rel_height.'" ';
        }
        if ( !empty ($table_bg_color) ) {
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
     * @param $style
     * @param $properties
     * @param null $disabled_props
     * @return string
     */
    public static function createTableCellStyle(&$style, $properties, $disabled_props = NULL){
        $attrs = 0;

        if ( empty ($disabled_props ['background-color']) ) {
            $table_bg_color = $properties ['background-color'];
            $attrs++;
        }
        if ( empty ($disabled_props ['vertical-align']) ) {
            $table_valign = $properties ['vertical-align'];
            $attrs++;
        }
        if ( empty ($disabled_props ['border']) ) {
            $table_border = $properties ['border'];
            $attrs++;
        }
        if ( empty ($disabled_props ['padding-left']) ) {
            $pad_left = $properties ['padding-left'];
            $attrs++;
        }
        if ( empty ($disabled_props ['padding-right']) ) {
            $pad_right = $properties ['padding-right'];
            $attrs++;
        }
        if ( empty ($disabled_props ['padding-top']) ) {
            $pad_top = $properties ['padding-top'];
            $attrs++;
        }
        if ( empty ($disabled_props ['padding-bottom']) ) {
            $pad_bottom = $properties ['padding-bottom'];
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
        if ( !empty ($table_valign) ) {
            $style .= 'style:vertical-align="'.$table_valign.'" ';
        }
        if ( !empty ($table_bg_color) ) {
            $style .= 'fo:background-color="'.$table_bg_color.'" ';
        }
        if ( !empty ($table_border) ) {
            $style .= 'fo:border="'.$table_border.'" ';
        }
        if ( !empty ($pad_left) ) {
            $style .= 'fo:padding-left="'.$pad_left.'" ';
        }
        if ( !empty ($pad_right) ) {
            $style .= 'fo:padding-right="'.$pad_right.'" ';
        }
        if ( !empty ($pad_top) ) {
            $style .= 'fo:padding-top="'.$pad_top.'" ';
        }
        if ( !empty ($pad_bottom) ) {
            $style .= 'fo:padding-bottom="'.$pad_bottom.'" ';
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
     *
     * @param $style
     * @param $properties
     * @param null $disabled_props
     * @param null $style_name
     * @return null|string
     */
    public static function createTableColumnStyle(&$style, $properties, $disabled_props = NULL, $style_name = NULL){
        $attrs = 0;

        if ( empty ($disabled_props ['width']) ) {
            $table_co_width = $properties ['width'];
            $attrs++;
        }

        // If all relevant properties are empty or disabled, then there
        // are no attributes for our style. Return NULL to indicate 'no style required'.
        if ( $attrs == 0 ) {
            return NULL;
        }

        // Create style name.
        if ( empty ($style_name) ) {
            $style_name = self::getNewStylename ('TableColumn');
        }

        $style  = '<style:style style:name="'.$style_name.'" style:family="table-column">';
        $style .= '<style:table-column-properties ';
        if ( !empty ($table_co_width) ) {
            $length = strlen ($table_co_width);
            if ( $table_co_width [$length-1] != '%' ) {
                $style .= 'style:column-width="'.$table_co_width.'" ';
            } else {
                // Columns have a specific syntax for relative width in %!
                // Change % to *.
                //$table_co_width [$length-1] = '*';
                $table_co_width = trim ($table_co_width, '%');
                $style .= 'style:rel-column-width="'.$table_co_width.'*" ';
            }
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
     *
     * @param $style
     * @param $properties
     * @param null $disabled_props
     * @return null|string
     */
    public static function createMultiColumnFrameStyle(&$style, $properties, $disabled_props = NULL) {
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

        // Create style name.
        $style_name = self::getNewStylename ('Frame');

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

        return $style_name;
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
     * @param $style
     * @param $properties
     * @param null $disabled_props
     * @return string
     */
    public static function createPageLayoutStyle(&$style, $properties) {
        $style_name = $properties ['style-name'];
        if ( empty($style_name) ) {
            $style_name = self::getNewStylename ('Page');
        }
        $style = '<style:page-layout style:name="'.$style_name.'">
                <style:page-layout-properties fo:page-width="'.$properties ['width'].'cm" fo:page-height="'.$properties ['height'].'cm" style:num-format="1" style:print-orientation="landscape" fo:margin-top="'.$properties ['margin-top'].'cm" fo:margin-bottom="'.$properties ['margin-bottom'].'cm" fo:margin-left="'.$properties ['margin-left'].'cm" fo:margin-right="'.$properties ['margin-right'].'cm" style:writing-mode="lr-tb" style:footnote-max-height="0cm">
                    <style:footnote-sep style:width="0.018cm" style:distance-before-sep="0.1cm" style:distance-after-sep="0.1cm" style:adjustment="left" style:rel-width="25%" style:color="#000000"/>
                </style:page-layout-properties>
                <style:header-style/>
                <style:footer-style/>
            </style:page-layout>';
        return $style_name;
    }
}

