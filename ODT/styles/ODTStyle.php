<?php
/**
 * ODTStyle: base class for ODT styles.
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author LarsDW223
 */

require_once 'ODTUnknownStyle.php';
require_once 'ODTStyleStyle.php';
require_once 'ODTTextOutlineStyle.php';
require_once 'ODTTextListStyle.php';
require_once 'ODTMasterPageStyle.php';
require_once 'ODTPageLayoutStyle.php';
require_once 'ODTTextStyle.php';
require_once 'ODTParagraphStyle.php';
require_once 'ODTTableStyle.php';
require_once 'ODTTableRowStyle.php';
require_once 'ODTTableColumnStyle.php';
require_once 'ODTTableCellStyle.php';

/**
 * The ODTStyle class
 */
abstract class ODTStyle
{
    protected static $style_base_name = 'PluginODTAutoStyle_';
    protected static $style_count = 0;
    protected $properties = array();

    /**
     * Get the element name for the ODT XML encoding of the style.
     *
     * @param  $properties Properties to be imported
     * @param  $disabled Properties to be ignored
     */
    abstract public function getElementName();

    /**
     * Set style properties by importing values from a properties array.
     * Properties might be disabled by setting them in $disabled.
     * The style must have been previously created.
     *
     * @param  $properties Properties to be imported
     * @param  $disabled Properties to be ignored
     */
    abstract public function importProperties($properties, $disabled);

    /**
     * Check if a style is a common style.
     *
     * @return bool Is common style
     */
    abstract public function mustBeCommonStyle();

    /**
     * Encode current style values in a string and return it.
     *
     * @return string ODT XML encoded style
     */
    abstract public function toString();

    /**
     * Set a property.
     * 
     * @param $property The name of the property to set
     * @param $value New value to set
     */
    abstract public function setProperty($property, $value);

    /**
     * Get the value of a property.
     * 
     * @param  $property The property name
     * @return string The current value of the property
     */
    public function getProperty($property) {
        return $this->properties [$property]['value'];
    }

    /**
     * Get the value of a property.
     * 
     * @param  $property   The property name
     * @param  $properties Properties array to query the value from,
     *                     or NULL for using ours.
     * @return string      The current value of the property
     */
    public function getPropertyInternal($property, $properties=NULL) {
        if ( $properties === NULL ) {
            return $this->properties [$property]['value'];
        } else {
            return $properties [$property]['value'];
        }
    }

    /**
     * Get the value of a property.
     * 
     * @param  $property The property name
     * @return string The current value of the property
     */
    public function getPropertySection($property) {
        return $this->properties [$property]['section'];
    }

    /**
     * Create new style by importing ODT style definition.
     *
     * @param  $xmlCode Style definition in ODT XML format
     * @return ODTStyle New specific style
     */
    static public function importODTStyle($xmlCode) {
        $matches = array();
        $pattern = '/<(\w)+[^\s\/>]+/';
        if (preg_match ($pattern, $xmlCode, $matches) !== 1) {
            return NULL;
        }
        $element = trim ($matches [0], '"<>');

        $style = NULL;
        switch ($element) {
            case 'style:style':
            case 'style:default-style':
                $style = ODTStyleStyle::importODTStyle($xmlCode);
                break;
            case 'text:outline-style':
                $style = ODTTextOutlineStyle::importODTStyle($xmlCode);
                break;
            case 'text:list-style':
                $style = ODTTextListStyle::importODTStyle($xmlCode);
                break;
            case 'style:master-page':
                $style = ODTMasterPageStyle::importODTStyle($xmlCode);
                break;
            case 'style:page-layout':
                $style = ODTPageLayoutStyle::importODTStyle($xmlCode);
                break;
            default:
                break;
        }
        if ($style != NULL ) {
            return $style;
        }

        // Unknown/not implemented style.
        // Create generic style which can not be changed.
        $unknown = ODTUnknownStyle::importODTStyle($xmlCode);
        $unknown->setElementName($element);
        return $unknown;
    }

    /**
     * Set a property.
     * 
     * @param $property The name of the property to set
     * @param $value New value to set
     */
    protected function setPropertyInternal($property, $odt_property, $value, $section, &$dest=NULL) {
        if ($value !== NULL) {
            if ( $dest === NULL ) {
                $this->properties [$property] = array ('odt_property' => $odt_property,
                                                       'value' => $value,
                                                       'section' => $section);
            } else {
                $dest [$property] = array ('odt_property' => $odt_property,
                                           'value' => $value,
                                           'section' => $section);
            }
        } else {
            if ( $dest === NULL ) {
                unset ($this->properties [$property]);
            } else {
                unset ($dest [$property]);
            }
        }
    }

    /**
     * Import ODT style definition according to given fields into given style.
     *
     * @param  $style   ODTStyle object for storing the properties
     * @param  $fields  Properties accepted by the object/class
     * @param  $xmlCode Style definition in ODT XML format
     * @return integer  Number of meaningful properties found
     */
    protected function importODTStyleInternal(array $fields, $xmlCode, &$properties=NULL) {
        $attrs = 0;
        foreach ($fields as $property => $field) {
            // The pattern is specified in that way that it also reads in empty attributes.
            // Sometimes an empty attribute is not the same as an not existing one. E.g.
            // in ODT XML '<text:outline-level-style text:level="3" style:num-format="" >'
            // has NOT the same meaning as '<text:outline-level-style text:level="3" >'!!!
            // So DO NOT change the '*' in the pattern to '+'!
            if (preg_match ('/'.$field[0].'="[^"]*"/', $xmlCode, $matches) === 1) {
                $value = substr ($matches [0], strlen($field[0].'="'));
                $value = trim ($value, '"<>');
                $this->setPropertyInternal($property, $field[0], $value, $field[1], $properties);
                if ( $field[2] == true ) {
                    $attrs++;
                }
            }
        }
        return $attrs;
    }

    /**
     * Set style properties by importing values from a properties array.
     * Properties might be disabled by setting them in $disabled.
     * The style must have been previously created. Only those properties
     * will be accepted that are mentioned in the fields array.
     *
     * @param  $style      ODTStyle object for storing the properties
     * @param  $fields     Properties accepted by the object/class
     * @param  $properties Properties to be imported
     * @param  $disabled   Properties to be ignored
     */
    protected function importPropertiesInternal(array $fields, $properties, $disabled, &$dest=NULL) {
        foreach ($properties as $property => $value) {
            if ($disabled [$property] == 0 && array_key_exists ($property, $fields)) {
                $this->setPropertyInternal($property, $fields[$property][0], $value, $fields[$property][1], $dest);
            }
        }
    }

    /**
     * Is this style a default style?
     * Needs to be overwritten if a style could also be a default style.
     *
     * @return boolean  Always false.
     */
    public function isDefault() {
        return false;
    }

    /**
     * This function creates a new style name. All functions of this class which create a new
     * style/style name shall use this function to create the style name. By doing so it is
     * guaranteed that all style names created by this class are unique.
     *
     * The function returns the name of the new style or NULL if all relevant properties are empty.
     */
    public static function getNewStylename ($type = '') {
        self::$style_count++;
        $style_name = self::$style_base_name.$type.'_'.self::$style_count;
        return $style_name;
    }
}
