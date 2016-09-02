<?php
/**
 * ODTTableCellStyle: class for ODT table cell styles.
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author LarsDW223
 */

require_once DOKU_INC.'lib/plugins/odt/ODT/XMLUtil.php';
require_once 'ODTStyle.php';

ODTStyleStyle::register('ODTTableCellStyle');

/**
 * The ODTTableCellStyle class
 */
class ODTTableCellStyle extends ODTStyleStyle
{
    static $table_cell_fields = array(
        'vertical-align'                 => array ('style:vertical-align',              'table-cell',  true),
        'text-align-source'              => array ('style:text-align-source',           'table-cell',  true),
        'direction'                      => array ('style:direction',                   'table-cell',  true),
        'glyph-orientation-vertical'     => array ('style:glyph-orientation-vertical',  'table-cell',  true),
        'writing-mode'                   => array ('style:writing-mode',                'table-cell',  true),
        'shadow'                         => array ('style:shadow',                      'table-cell',  true),
        'background-color'               => array ('fo:background-color',               'table-cell',  true),
        'border'                         => array ('fo:border',                         'table-cell',  true),
        'border-top'                     => array ('fo:border-top',                     'table-cell',  true),
        'border-right'                   => array ('fo:border-right',                   'table-cell',  true),
        'border-bottom'                  => array ('fo:border-bottom',                  'table-cell',  true),
        'border-left'                    => array ('fo:border-left',                    'table-cell',  true),
        'diagonal-tl-br'                 => array ('style:diagonal-tl-br',              'table-cell',  true),
        'diagonal-tl-br-widths'          => array ('style:diagonal-tl-br-widths',       'table-cell',  true),
        'diagonal-bl-tr'                 => array ('style:diagonal-bl-tr',              'table-cell',  true),
        'diagonal-bl-tr-widths'          => array ('style:diagonal-bl-tr-widths',       'table-cell',  true),
        'border-line-width'              => array ('style:border-line-width',           'table-cell',  true),
        'border-line-width-top'          => array ('style:border-line-width-top',       'table-cell',  true),
        'border-line-width-bottom'       => array ('style:border-line-width-bottom',    'table-cell',  true),
        'border-line-width-left'         => array ('style:border-line-width-left',      'table-cell',  true),
        'border-line-width-right'        => array ('style:border-line-width-right',     'table-cell',  true),
        'padding'                        => array ('fo:padding',                        'table-cell',  true),
        'padding-top'                    => array ('fo:padding-top',                    'table-cell',  true),
        'padding-right'                  => array ('fo:padding-right',                  'table-cell',  true),
        'padding-bottom'                 => array ('fo:padding-bottom',                 'table-cell',  true),
        'padding-left'                   => array ('fo:padding-left',                   'table-cell',  true),
        'wrap-option'                    => array ('fo:wrap-option',                    'table-cell',  true),
        'rotation-angle'                 => array ('style:rotation-angle',              'table-cell',  true),
        'rotation-align'                 => array ('style:rotation-align',              'table-cell',  true),
        'cell-protect'                   => array ('style:cell-protect',                'table-cell',  true),
        'print-content'                  => array ('style:print-content',               'table-cell',  true),
        'decimal-places'                 => array ('style:decimal-places',              'table-cell',  true),
        'repeat-content'                 => array ('style:repeat-content',              'table-cell',  true),
        'shrink-to-fit'                  => array ('style:shrink-to-fit',               'table-cell',  true),

        // Fields for background-image
        // (see '<define name="style-background-image"> in relax-ng schema)'
        'repeat'                     => array ('style:repeat',                     'table-cell-background-image',  true),
        'position'                   => array ('style:position',                   'table-cell-background-image',  true),
        'style:filter-name'          => array ('style:filter-name',                'table-cell-background-image',  true),
        'opacity'                    => array ('draw:opacity',                     'table-cell-background-image',  true),
        'type'                       => array ('xlink:type',                       'table-cell-background-image',  true),
        'href'                       => array ('xlink:href',                       'table-cell-background-image',  true),
        'show'                       => array ('xlink:show',                       'table-cell-background-image',  true),
        'actuate'                    => array ('xlink:actuate',                    'table-cell-background-image',  true),
        'binary-data'                => array ('office:binary-data',               'table-cell-background-image',  true),
        'base64Binary'               => array ('base64Binary',                     'table-cell-background-image',  true),
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
        $this->importPropertiesInternal(ODTTextStyle::getTextProperties (), $properties, $disabled);
        $this->importPropertiesInternal(ODTParagraphStyle::getParagraphProperties (), $properties, $disabled);
        $this->importPropertiesInternal(self::$table_cell_fields, $properties, $disabled);
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
        return 'table-cell';
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
        if (array_key_exists ($property, self::$table_cell_fields)) {
            $this->setPropertyInternal
                ($property, self::$table_cell_fields [$property][0], $value, self::$table_cell_fields [$property][1]);
            return;
        }
        $paragraph_fields = ODTParagraphStyle::getParagraphProperties ();
        if (array_key_exists ($property, $paragraph_fields)) {
            $this->setPropertyInternal
                ($property, $paragraph_fields [$property][0], $value, $paragraph_fields [$property][1]);
            return;
        }
        $text_fields = ODTTextStyle::getTextProperties ();
        if (array_key_exists ($property, $text_fields)) {
            $this->setPropertyInternal
                ($property, $text_fields [$property][0], $value, $text_fields [$property][1]);
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
        $style = new ODTTableCellStyle();
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

        $open = XMLUtil::getElementOpenTag('style:paragraph-properties', $xmlCode);
        if (!empty($open)) {
            $attrs += $style->importODTStyleInternal(ODTParagraphStyle::getParagraphProperties (), $xmlCode);
        }

        $open = XMLUtil::getElementOpenTag('style:text-properties', $xmlCode);
        if (!empty($open)) {
            $attrs += $style->importODTStyleInternal(ODTTextStyle::getTextProperties (), $open);
        }

        $open = XMLUtil::getElementOpenTag('style:table-cell-properties', $xmlCode);
        if (!empty($open)) {
            $attrs += $style->importODTStyleInternal(self::$table_cell_fields, $open);
        }

        // If style has no meaningfull content then throw it away
        if ( $attrs == 0 ) {
            return NULL;
        }

        return $style;
    }

    static public function getTableCellProperties () {
        return self::$table_cell_fields;
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
}
