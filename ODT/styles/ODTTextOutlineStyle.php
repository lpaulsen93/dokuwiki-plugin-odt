<?php
/**
 * ODTTextOutlineStyle: class for ODT text outline styles.
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author LarsDW223
 */

require_once DOKU_INC.'lib/plugins/odt/ODT/XMLUtil.php';
require_once DOKU_INC.'lib/plugins/odt/ODT/styles/ODTStyle.php';
require_once DOKU_INC.'lib/plugins/odt/ODT/styles/ODTTextStyle.php';

/**
 * The ODTTextOutlineStyle class
 */
class ODTTextOutlineStyle extends ODTStyle
{
    static $outline_fields = array(
        // Fields belonging to "text:outline-style"
        'style-name'                         => array ('style:name',                              'style', false),

        // Fields belonging to "text:outline-level-style"
        'level'                              => array ('text:level',                              'level', true),
        'text-style-name'                    => array ('text:style-name',                         'level', true),
        'num-format'                         => array ('style:num-format',                        'level', true),
        'num-letter-sync'                    => array ('style:num-letter-sync',                   'level', true),
        'num-prefix'                         => array ('style:num-prefix',                        'level', true),
        'num-suffix'                         => array ('style:num-suffix',                        'level', true),
        'display-levels'                     => array ('text:display-levels',                     'level', true),
        'start-value'                        => array ('text:start-value',                        'level', true),

        // Fields belonging to "style-list-level-properties"
        'text-align'                         => array ('fo:text-align',                           'level-list', true),
        'text-space-before'                  => array ('text:space-before',                       'level-list', true),
        'text-min-label-width'               => array ('text:min-label-width',                    'level-list', true),
        'text-min-label-distance'            => array ('text:min-label-distance',                 'level-list', true),
        'font-name'                          => array ('style:font-name',                         'level-list', true),
        'width'                              => array ('fo:width',                                'level-list', true),
        'height'                             => array ('fo:height',                               'level-list', true),
        'vertical-rel'                       => array ('style:vertical-rel',                      'level-list', true),
        'vertical-pos'                       => array ('style:vertical-pos',                      'level-list', true),
        'list-level-position-and-space-mode' => array ('text:list-level-position-and-space-mode', 'level-list', true),

        // Fields belonging to "style:list-level-label-alignment"
        'label-followed-by'                  => array ('text:label-followed-by',                  'level-label', true),
        'list-tab-stop-position'             => array ('text:list-tab-stop-position',             'level-label', true),
        'text-indent'                        => array ('fo:text-indent',                          'level-label', true),
        'margin-left'                        => array ('fo:margin-left',                          'level-label', true),
    );
    protected $outline_level_styles = array();

    /**
     * Get the element name for the ODT XML encoding of the style.
     */
    public function getElementName() {
        return 'text:outline-style';
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
        $this->importPropertiesInternal(ODTTextStyle::getTextProperties (), $properties, $disabled);
        $this->importPropertiesInternal(self::$outline_fields, $properties, $disabled);
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
     * Set a property.
     * 
     * @param $property The name of the property to set
     * @param $value    New value to set
     */
    public function setProperty($property, $value) {
        $text_fields = ODTTextStyle::getTextProperties ();
        if (array_key_exists ($property, $style_fields)) {
            $this->setPropertyInternal
                ($property, $text_fields [$property][0], $value, $text_fields [$property][1]);
            return;
        }
        if (array_key_exists ($property, self::$outline_fields)) {
            $this->setPropertyInternal
                ($property, self::$outline_fields [$property][0], $value, self::$outline_fields [$property][1]);
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
        $style = new ODTTextOutlineStyle();
        $attrs = 0;
        
        $open = XMLUtil::getElementOpenTag('text:outline-style', $xmlCode);
        if (!empty($open)) {
            // This properties are stored in the properties of ODTStyle
            $attrs += $style->importODTStyleInternal(self::$outline_fields, $open);
        }

        $pos = 0;
        $end = 0;
        $max = strlen ($xmlCode);
        $level = XMLUtil::getElement('text:outline-level-style', substr($xmlCode, $pos), $end);
        $pos += $end;
        $text_fields = ODTTextStyle::getTextProperties ();

        $check = 0;
        while ($level != NULL)
        {
            // We can have multiple level definitons with all the same properties.
            // So we store this in our own array. The "text:level" is the array key.
            if (!empty($level)) {
                $properties = array();
                $attrs += $style->importODTStyleInternal($text_fields, $level, $properties);
                $attrs += $style->importODTStyleInternal(self::$outline_fields, $level, $properties);
                
                // Assign properties array to our level array
                $level_number = $style->getPropertyInternal('level', $properties);
                $style->outline_level_styles [$level_number] = $properties;
            }

            // Get XML code for next level.
            $level = XMLUtil::getElement('text:outline-level-style', substr($xmlCode, $pos), $end);
            $pos += $end;
            if ($pos >= $max) {
                break;
            }
        }

        // If style has no meaningfull content then throw it away
        if ( $attrs == 0 ) {
            return NULL;
        }

        return $style;
    }

    /**
     * Encode current style values in a string and return it.
     *
     * @return string ODT XML encoded style
     */
    public function toString() {
        $style = '';
        $levels = '';

        // The style properties are stored in the properties of ODTStyle
        foreach ($this->properties as $property => $items) {
            $style .= $items ['odt_property'].'="'.$items ['value'].'" ';
        }

        // The level properties are stored in our level properties
        foreach ($this->outline_level_styles as $key => $properties) {
            $level = '';
            $level_list = '';
            $level_label = '';
            $text = '';
            foreach ($properties as $property => $items) {
                switch ($items ['section']) {
                    case 'level':
                        $level .= $items ['odt_property'].'="'.$items ['value'].'" ';
                        break;
                    case 'level-list':
                        $level_list .= $items ['odt_property'].'="'.$items ['value'].'" ';
                        break;
                    case 'level-label':
                        $level_label .= $items ['odt_property'].'="'.$items ['value'].'" ';
                        break;
                    case 'text':
                        $text .= $items ['odt_property'].'="'.$items ['value'].'" ';
                        break;
                }
            }
            $levels .= '    <text:outline-level-style '.$level.">\n";
            if (!empty($level_list)) {
                if (empty($level_label)) {
                    $levels .= '        <style:list-level-properties '.$level_list."/>\n";
                } else {
                    $levels .= '        <style:list-level-properties '.$level_list.">\n";
                    $levels .= '            <style:list-level-label-alignment '.$level_label."/>\n";
                    $levels .= "        </style:list-level-properties>\n";
                }
            }
            if (!empty($text)) {
                $levels .= '        <style:text-properties '.$text.'/>';
            }
            $levels .= "    </text:outline-level-style>\n";
        }

        // Build style.
        $element = $this->getElementName();
        $style  = '<'.$element.' '.$style.">\n";
        if ( !empty($levels) ) {
            $style .= $levels;
        }
        $style .= '</'.$element.">\n";
        return $style;
    }

    /**
     * Get the value of a property for text outline level $level.
     * 
     * @param  $level      The text outline level (usually 1 to 10)
     * @param  $property   The property name
     * @return string      The current value of the property
     */
    public function getPropertyFromLevel($level, $property) {
        return $this->getPropertyInternal($property, $this->outline_level_styles [$level]);
    }

    /**
     * Set a property.
     * 
     * @param $property The name of the property to set
     * @param $value    New value to set
     */
    public function setPropertyForLevel($level, $property, $value) {
        if (array_key_exists ($property, self::$outline_fields)) {
            $this->setPropertyInternal
                ($property, self::$outline_fields [$property][0], $value, self::$outline_fields [$property][1], $this->outline_level_styles [$level]);
            return;
        }
    }
}

