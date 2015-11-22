<?php
/**
 * ODTTextListStyle: class for ODT text list styles.
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author LarsDW223
 */

require_once DOKU_INC.'lib/plugins/odt/ODT/XMLUtil.php';
require_once DOKU_INC.'lib/plugins/odt/ODT/styles/ODTStyle.php';
require_once DOKU_INC.'lib/plugins/odt/ODT/styles/ODTTextStyle.php';

/**
 * The ODTTextListStyle class
 */
class ODTTextListStyle extends ODTStyle
{
    static $list_fields = array(
        // Fields belonging to "text:list-style"
        'style-name'                         => array ('style:name',                              'style', false),
        'style-display-name'                 => array ('style:display-name',                      'style', false),
        'consecutive-numbering'              => array ('text:consecutive-numbering',              'style', true),
    );

    static $style_number_fields = array(
        // Fields belonging to "text:list-level-style-number"
        'level'                              => array ('text:level',                              'style-attr', true),
        'text-style-name'                    => array ('text:style-name',                         'style-attr', true),
        'num-format'                         => array ('style:num-format',                        'style-attr', true),
        'num-letter-sync'                    => array ('style:num-letter-sync',                   'style-attr', true),
        'num-prefix'                         => array ('style:num-prefix',                        'style-attr', true),
        'num-suffix'                         => array ('style:num-suffix',                        'style-attr', true),
        'display-levels'                     => array ('text:display-levels',                     'style-attr', true),
        'start-value'                        => array ('text:start-value',                        'style-attr', true),
    );
    
    static $style_bullet_fields = array(
        // Fields belonging to "text:list-level-style-bullet"
        'level'                              => array ('text:level',                              'style-attr', true),
        'text-style-name'                    => array ('text:style-name',                         'style-attr', true),
        'text-bullet-char'                   => array ('text:bullet-char',                        'style-attr', true),
        'num-prefix'                         => array ('style:num-prefix',                        'style-attr', true),
        'num-suffix'                         => array ('style:num-suffix',                        'style-attr', true),
        'text-bullet-relative-size'          => array ('text:bullet-relative-size',               'style-attr', true),
    );

    static $style_image_fields = array(
        // Fields belonging to "text:list-level-style-image"
        'level'                              => array ('text:level',                              'style-attr',  true),
        'type'                               => array ('xlink:type',                              'style-attr',  true),
        'href'                               => array ('xlink:href',                              'style-attr',  true),
        'show'                               => array ('xlink:show',                              'style-attr',  true),
        'actuate'                            => array ('xlink:actuate',                           'style-attr',  true),
        'binary-data'                        => array ('office:binary-data',                      'style-attr',  true),
        'base64Binary'                       => array ('base64Binary',                            'style-attr',  true),
    );

    static $list_level_props_fields = array(
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
    );

    static $label_align_fields = array(
        // Fields belonging to "style:list-level-label-alignment"
        'label-followed-by'                  => array ('text:label-followed-by',                  'level-label', true),
        'list-tab-stop-position'             => array ('text:list-tab-stop-position',             'level-label', true),
        'text-indent'                        => array ('fo:text-indent',                          'level-label', true),
        'margin-left'                        => array ('fo:margin-left',                          'level-label', true),
    );
    protected $list_level_styles = array();

    /**
     * Get the element name for the ODT XML encoding of the style.
     */
    public function getElementName() {
        return 'text:list-style';
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
        $this->importPropertiesInternal(self::$list_fields, $properties, $disabled);
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
     * For a TextListStyle we can only set the style main properties here.
     * All properties specific for a level need to be set using setPropertyForLevel().
     * 
     * @param $property The name of the property to set
     * @param $value    New value to set
     */
    public function setProperty($property, $value) {
        if (array_key_exists ($property, self::$list_fields)) {
            $this->setPropertyInternal
                ($property, self::$list_fields [$property][0], $value, self::$list_fields [$property][1]);
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
        $style = new ODTTextListStyle();
        $attrs = 0;
        
        $open = XMLUtil::getElementOpenTag('text:list-style', $xmlCode);
        if (!empty($open)) {
            // This properties are stored in the properties of ODTStyle
            $attrs += $style->importODTStyleInternal(self::$list_fields, $open);
            $content = XMLUtil::getElementContent('text:list-style', $xmlCode);
        }

        $pos = 0;
        $end = 0;
        $max = strlen ($content);
        $text_fields = ODTTextStyle::getTextProperties ();
        while ($pos < $max)
        {
            // Get XML code for next level.
            $level = XMLUtil::getNextElement($element, substr($content, $pos), $end);
            $level_content = XMLUtil::getNextElementContent($element, $level, $ignore);
            if (!empty($level)) {
                $list_style_properties = array();
                $list_level_properties = array();
                $label_properties = array();
                $text_properties = array();
                $properties = array();
                switch ($element) {
                    case 'text:list-level-style-number':
                        $attrs += $style->importODTStyleInternal(self::$style_number_fields, $level, $list_style_properties);
                        $list_level_style = 'number';
                    break;
                    case 'text:list-level-style-bullet':
                        $attrs += $style->importODTStyleInternal(self::$style_bullet_fields, $level, $list_style_properties);
                        $list_level_style = 'bullet';
                    break;
                    case 'text:list-level-style-image':
                        $attrs += $style->importODTStyleInternal(self::$style_image_fields, $level, $list_style_properties);
                        $list_level_style = 'image';
                    break;
                }

                $temp_content = XMLUtil::getElement('style:text-properties', $level_content);
                $attrs += $style->importODTStyleInternal($text_fields, $temp_content, $text_properties);
                $temp_content = XMLUtil::getElementOpenTag('style:list-level-properties', $level_content);
                $attrs += $style->importODTStyleInternal(self::$list_level_props_fields, $temp_content, $list_level_properties);
                $temp_content = XMLUtil::getElement('style:list-level-label-alignment', $level_content);
                $attrs += $style->importODTStyleInternal(self::$label_align_fields, $temp_content, $label_properties);

                // Assign properties array to our level array
                $level_number = $style->getPropertyInternal('level', $list_style_properties);
                $properties ['list-style'] = $list_style_properties;
                $properties ['list-level'] = $list_level_properties;
                $properties ['label'] = $label_properties;
                $properties ['text'] = $text_properties;
                $style->list_level_styles [$level_number] = $properties;

                // Set special property 'list-level-style' to remember element to encode
                // on call to toString()!
                $style->setPropertyForLevel($level_number, 'list-level-style', $list_level_style);
            }

            $pos += $end;
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
        $level_number = 0;
        foreach ($this->list_level_styles as $key => $properties) {
            $level_number++;
            $element = $this->getPropertyFromLevel($level_number, 'list-level-style');
            switch ($element) {
                case 'number':
                    $fields = self::$style_number_fields;
                break;
                case 'bullet':
                    $fields = self::$style_bullet_fields;
                break;
                case 'image':
                    $fields = self::$style_image_fields;
                break;
            }
            $element = 'text:list-level-style-'.$element;

            $style_attr = '';
            foreach ($this->list_level_styles [$level_number]['list-style'] as $property => $items) {
                // Only add fields/properties which are allowed for the specific list-level-style
                if ($property != 'list-level-style' && array_key_exists ($property, $fields)) {
                    $style_attr .= $items ['odt_property'].'="'.$items ['value'].'" ';
                }
            }
            $level_list = '';
            foreach ($this->list_level_styles [$level_number]['list-level'] as $property => $items) {
                $level_list .= $items ['odt_property'].'="'.$items ['value'].'" ';
            }
            $level_label = '';
            foreach ($this->list_level_styles [$level_number]['label'] as $property => $items) {
                $level_label .= $items ['odt_property'].'="'.$items ['value'].'" ';
            }
            $text = '';
            foreach ($this->list_level_styles [$level_number]['text'] as $property => $items) {
                $text .= $items ['odt_property'].'="'.$items ['value'].'" ';
            }
            
            $levels .= '    <'.$element.' '.$style_attr.">\n";
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
                $levels .= '        <style:text-properties '.$text.'/>'."\n";
            }
            $levels .= "    </".$element.">\n";
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
        if ($property == 'list-level-style') {
            // Property 'list-level-style' is a special property just to remember
            // which element needs to be encoded on a call to toString().
            // It may not be included in the output of toString()!!!
            return $this->getPropertyInternal('list-level-style', $this->list_level_styles [$level]['list-style']);
        }
        $text_fields = ODTTextStyle::getTextProperties ();
        if (array_key_exists ($property, $text_fields)) {
            return $this->getPropertyInternal($property, $this->list_level_styles [$level]['text']);
        }
        $element = $this->getPropertyInternal('list-level-style', $this->list_level_styles [$level]['list-style']);
        switch ($element) {
            case 'number':
                $fields = self::$style_number_fields;
            break;
            case 'bullet':
                $fields = self::$style_bullet_fields;
            break;
            case 'image':
                $fields = self::$style_image_fields;
            break;
        }
        if (array_key_exists ($property, $fields)) {
            return $this->getPropertyInternal($property, $this->list_level_styles [$level]['list-style']);
        }
        if (array_key_exists ($property, self::$list_level_props_fields)) {
            return $this->getPropertyInternal($property, $this->list_level_styles [$level]['list-level']);
        }
        if (array_key_exists ($property, self::$label_align_fields)) {
            return $this->getPropertyInternal($property, $this->list_level_styles [$level]['label']);
        }
    }

    /**
     * Set a property for a specific level.
     * 
     * @param $level    The level for which to set the property (1 to 10)
     * @param $property The name of the property to set
     * @param $value    New value to set
     */
    public function setPropertyForLevel($level, $property, $value) {
        if ($property == 'list-level-style') {
            // Property 'list-level-style' is a special property just to remember
            // which element needs to be encoded on a call to toString().
            // It may not be included in the output of toString()!!!
            $this->setPropertyInternal
                ($property, 'list-level-style', $value, 'list-level-style', $this->list_level_styles [$level]['list-style']);
        } else {
            // First check fields/properties common to each list-level-style
            $text_fields = ODTTextStyle::getTextProperties ();
            if (array_key_exists ($property, $text_fields)) {
                $this->setPropertyInternal
                    ($property, $text_fields [$property][0], $value, $text_fields [$property][1], $this->list_level_styles [$level]['text']);
                return;
            }
            if (array_key_exists ($property, self::$list_level_props_fields)) {
                $this->setPropertyInternal
                    ($property, self::$list_level_props_fields [$property][0], $value, self::$list_level_props_fields [$property][1], $this->list_level_styles [$level]['list-level']);
                return;
            }
            if (array_key_exists ($property, self::$label_align_fields)) {
                $this->setPropertyInternal
                    ($property, self::$label_align_fields [$property][0], $value, self::$label_align_fields [$property][1], $this->list_level_styles [$level]['label']);
                return;
            }

            // Now check fields specific to the list-level-style.
            $element = $this->getPropertyFromLevel ($level, 'list-level-style');
            switch ($element) {
                case 'number':
                    $fields = self::$style_number_fields;
                break;
                case 'bullet':
                    $fields = self::$style_bullet_fields;
                break;
                case 'image':
                    $fields = self::$style_image_fields;
                break;
            }
            if (array_key_exists ($property, $fields)) {
                $this->setPropertyInternal
                    ($property, $fields [$property][0], $value, $fields [$property][1], $this->list_level_styles [$level]['list-style']);
            }
        }
    }
}

