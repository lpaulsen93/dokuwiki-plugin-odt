<?php
/**
 * ODTTextStyle: class for ODT text styles.
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author LarsDW223
 */

require_once DOKU_PLUGIN . 'odt/ODT/XMLUtil.php';
require_once 'ODTStyle.php';

ODTStyleStyle::register('ODTTextStyle');

/**
 * The ODTTextStyle class
 */
class ODTTextStyle extends ODTStyleStyle
{
    static $text_fields = array(
        'padding'                          => array ('fo:padding',                         'text',  true),
        'padding-top'                      => array ('fo:padding-top',                     'text',  true),
        'padding-right'                    => array ('fo:padding-right',                   'text',  true),
        'padding-bottom'                   => array ('fo:padding-bottom',                  'text',  true),
        'padding-left'                     => array ('fo:padding-left',                    'text',  true),
        'border'                           => array ('fo:border',                          'text',  true),
        'border-top'                       => array ('fo:border-top',                      'text',  true),
        'border-right'                     => array ('fo:border-right',                    'text',  true),
        'border-bottom'                    => array ('fo:border-bottom',                   'text',  true),
        'border-left'                      => array ('fo:border-left',                     'text',  true),
        'color'                            => array ('fo:color',                           'text',  true),
        'background-color'                 => array ('fo:background-color',                'text',  true),
        'background-image'                 => array ('fo:background-image',                'text',  true),
        'font-style'                       => array ('fo:font-style',                      'text',  true),
        'font-style-asian'                 => array ('style:font-style-asian',             'text',  true),
        'font-style-complex'               => array ('style:font-style-complex',           'text',  true),
        'font-weight'                      => array ('fo:font-weight',                     'text',  true),
        'font-weight-asian'                => array ('style:font-weight-asian',            'text',  true),
        'font-weight-complex'              => array ('style:font-weight-complex',          'text',  true),
        'font-size'                        => array ('fo:font-size',                       'text',  true),
        'font-size-asian'                  => array ('style:font-size-asian',              'text',  true),
        'font-size-complex'                => array ('style:font-size-complex',            'text',  true),
        'font-family'                      => array ('fo:font-family',                     'text',  true),
        'font-family-asian'                => array ('style:font-family-asian',            'text',  true),
        'font-family-complex'              => array ('style:font-family-complex',          'text',  true),
        'font-variant'                     => array ('fo:font-variant',                    'text',  true),
        'letter-spacing'                   => array ('fo:letter-spacing',                  'text',  true),
        'vertical-align'                   => array ('style:vertical-align',               'text',  true),
        'display'                          => array ('text:display',                       'text',  true),
        'lang'                             => array ('fo:language',                        'text',  true),
        'lang-asian'                       => array ('style:language-asian',               'text',  true),
        'lang-complex'                     => array ('style:language-complex',             'text',  true),
        'country'                          => array ('fo:country',                         'text',  true),
        'country-asian'                    => array ('style:country-asian',                'text',  true),
        'country-complex'                  => array ('style:country-complex',              'text',  true),
        'text-transform'                   => array ('fo:text-transform',                  'text',  true),
        'use-window-font-color'            => array ('style:use-window-font-color',        'text',  true),
        'text-outline'                     => array ('style:text-outline',                 'text',  true),
        'text-line-through-type'           => array ('style:text-line-through-type',       'text',  true),
        'text-line-through-style'          => array ('style:text-line-through-style',      'text',  true),
        'text-line-through-width'          => array ('style:text-line-through-width',      'text',  true),
        'text-line-through-color'          => array ('style:text-line-through-color',      'text',  true),
        'text-line-through-text'           => array ('style:text-line-through-text',       'text',  true),
        'text-line-through-text-style'     => array ('style:text-line-through-text-style', 'text',  true),
        'text-position'                    => array ('style:text-position',                'text',  true),
        'font-name'                        => array ('style:font-name',                    'text',  true),
        'font-name-asian'                  => array ('style:font-name-asian',              'text',  true),
        'font-name-complex'                => array ('style:font-name-complex',            'text',  true),
        'font-family-generic'              => array ('style:font-family-generic',          'text',  true),
        'font-family-generic-asian'        => array ('style:font-family-generic-asian',    'text',  true),
        'font-family-generic-complex'      => array ('style:font-family-generic-complex',  'text',  true),
        'font-style-name'                  => array ('style:font-style-name',              'text',  true),
        'font-style-name-asian'            => array ('style:font-style-name-asian',        'text',  true),
        'font-style-name-complex'          => array ('style:font-style-name-complex',      'text',  true),
        'font-pitch'                       => array ('style:font-pitch',                   'text',  true),
        'font-pitch-asian'                 => array ('style:font-pitch-asian',             'text',  true),
        'font-pitch-complex'               => array ('style:font-pitch-complex',           'text',  true),
        'font-charset'                     => array ('style:font-charset',                 'text',  true),
        'font-charset-asian'               => array ('style:font-charset-asian',           'text',  true),
        'font-charset-complex'             => array ('style:font-charset-complex',         'text',  true),
        'font-size-rel'                    => array ('style:font-size-rel',                'text',  true),
        'font-size-rel-asian'              => array ('style:font-size-rel-asian',          'text',  true),
        'font-size-rel-complex'            => array ('style:font-size-rel-complex',        'text',  true),
        'script-type'                      => array ('style:script-type',                  'text',  true),
        'script'                           => array ('fo:script',                          'text',  true),
        'script-asian'                     => array ('style:script-asian',                 'text',  true),
        'script-complex'                   => array ('style:script-complex',               'text',  true),
        'rfc-language-tag'                 => array ('style:rfc-language-tag',             'text',  true),
        'rfc-language-tag-asian'           => array ('style:rfc-language-tag-asian',       'text',  true),
        'rfc-language-tag-complex'         => array ('style:rfc-language-tag-complex',     'text',  true),
        'rfc-language-tag-complex'         => array ('style:rfc-language-tag-complex',     'text',  true),
        'font-relief'                      => array ('style:font-relief',                  'text',  true),
        'text-shadow'                      => array ('fo:text-shadow',                     'text',  true),
        'text-underline-type'              => array ('style:text-underline-type',          'text',  true),
        'text-underline-style'             => array ('style:text-underline-style',         'text',  true),
        'text-underline-width'             => array ('style:text-underline-width',         'text',  true),
        'text-underline-color'             => array ('style:text-underline-color',         'text',  true),
        'text-overline-type'               => array ('style:text-overline-type',           'text',  true),
        'text-overline-style'              => array ('style:text-overline-style',          'text',  true),
        'text-overline-width'              => array ('style:text-overline-width',          'text',  true),
        'text-overline-color'              => array ('style:text-overline-color',          'text',  true),
        'text-overline-mode'               => array ('style:text-overline-mode',           'text',  true),
        'text-underline-mode'              => array ('style:text-underline-mode',          'text',  true),
        'text-line-through-mode'           => array ('style:text-line-through-mode',       'text',  true),
        'letter-kerning'                   => array ('style:letter-kerning',               'text',  true),
        'text-blinking'                    => array ('style:text-blinking',                'text',  true),
        'text-combine'                     => array ('style:text-combine',                 'text',  true),
        'text-combine-start-char'          => array ('style:text-combine-start-char',      'text',  true),
        'text-combine-end-char'            => array ('style:text-combine-end-char',        'text',  true),
        'text-emphasize'                   => array ('style:text-emphasize',               'text',  true),
        'text-scale'                       => array ('style:text-scale',                   'text',  true),
        'text-rotation-angle'              => array ('style:text-rotation-angle',          'text',  true),
        'text-rotation-scale'              => array ('style:text-rotation-scale',          'text',  true),
        'hyphenate'                        => array ('fo:hyphenate',                       'text',  true),
        'hyphenation-remain-char-count'    => array ('fo:hyphenation-remain-char-count',   'text',  true),
        'hyphenation-push-char-count'      => array ('fo:hyphenation-push-char-count',     'text',  true),
        'condition'                        => array ('text:condition',                     'text',  true),
    );

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Set style properties by importing values from a properties array.
     * Properties might be disabled by setting them in $disabled.
     * The style must have been previously created.
     *
     * @param  $properties Properties to be imported
     * @param  $disabled Properties to be ignored
     */
    public function importProperties($properties, $disabled) {
        $this->importPropertiesInternal(ODTStyleStyle::getStyleProperties (), $properties, $disabled);
        $this->importPropertiesInternal(self::$text_fields, $properties, $disabled);
        $this->setProperty('style-family', $this->getFamily());
    }

    /**
     * Check if a style is a common style.
     *
     * @return bool Is common style
     */
    public function mustBeCommonStyle() {
        return false;
    }

    /**
     * Get the style family of a style.
     *
     * @return string Style family
     */
    static public function getFamily() {
        return 'text';
    }

    /**
     * Set a property.
     * 
     * @param $property The name of the property to set
     * @param $value    New value to set
     */
    public function setProperty($property, $value) {
        $style_fields = ODTStyleStyle::getStyleProperties ();
        if (array_key_exists ($property, $style_fields)) {
            $this->setPropertyInternal
                ($property, $style_fields [$property][0], $value, $style_fields [$property][1]);
            return;
        }
        if (array_key_exists ($property, self::$text_fields)) {
            $this->setPropertyInternal
                ($property, self::$text_fields [$property][0], $value, self::$text_fields [$property][1]);
            return;
        }
    }

    /**
     * Create new style by importing ODT style definition.
     *
     * @param  $xmlCode Style definition in ODT XML format
     * @return ODTStyle New specific style
     */
    static public function importODTStyle($xmlCode) {
        $style = new ODTTextStyle();
        $attrs = 0;

        $open = XMLUtil::getElementOpenTag('style:style', $xmlCode);
        if (!empty($open)) {
            $attrs += $style->importODTStyleInternal(ODTStyleStyle::getStyleProperties (), $open);
        } else {
            $open = XMLUtil::getElementOpenTag('style:default-style', $xmlCode);
            if (!empty($open)) {
                $style->setDefault(true);
                $attrs += $style->importODTStyleInternal(ODTStyleStyle::getStyleProperties (), $open);
            }
        }

        $open = XMLUtil::getElementOpenTag('style:text-properties', $xmlCode);
        if (!empty($open)) {
            $attrs += $style->importODTStyleInternal(self::$text_fields, $open);
        }

        // If style has no meaningfull content then throw it away
        if ( $attrs == 0 ) {
            return NULL;
        }

        return $style;
    }

    static public function getTextProperties () {
        return self::$text_fields;
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
    public static function createTextStyle(array $properties, array $disabled_props = NULL, ODTDocument $doc=NULL){
        // Convert 'text-decoration'.
        if ( $properties ['text-decoration'] == 'line-through' ) {
            $properties ['text-line-through-style'] = 'solid';
        }
        if ( $properties ['text-decoration'] == 'underline' ) {
            $properties ['text-underline-style'] = 'solid';
        }
        if ( $properties ['text-decoration'] == 'overline' ) {
            $properties ['text-overline-style'] = 'solid';
        }

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

        // Extra handling for font-size in '%'
        $save = $disabled_props ['font-size'];
        $odt_fo_size = '';
        if ( empty ($disabled_props ['font-size']) ) {
            $odt_fo_size = $properties ['font-size'];
        }
        $length = strlen ($odt_fo_size);
        if ( $length > 0 && $odt_fo_size [$length-1] == '%' && $doc != NULL) {
            // A font-size in percent is only supported in common style definitions, not in automatic
            // styles. Create a common style and set it as parent for this automatic style.
            $name = 'Size'.trim ($odt_fo_size, '%').'pc';
            $style_obj = self::createSizeOnlyTextStyle ($name, $odt_fo_size);
            $doc->addStyle($style_obj);
            $parent = $style_obj->getProperty('style-name');
            if (!empty($parent)) {
                $properties ['style-parent'] = $parent;
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

        // Restore $disabled_props
        $disabled_props ['font-size'] = $save;
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
}

