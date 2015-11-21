<?php
/**
 * ODTUnknownStyle: class for unknown/not implemented ODT style families.
 * The goal is to at least read in not supported style faimlies and return
 * the original content on a call to toString().
 * 
 * The following has to be taken into account:
 * - the properties of an ODTUnknownStyle can not be changed.
 * - so setProperty() and importProperties() will do nothing.
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author LarsDW223
 */

require_once DOKU_INC.'lib/plugins/odt/ODT/styles/ODTStyle.php';

/**
 * The ODTUnknownStyle class
 */
class ODTUnknownStyle extends ODTStyle
{
    // At least try to read in a name
    static $unknown_fields = array(
        'style-name'                       => array ('style:name',                         'style', false),
        'style-family'                     => array ('style:family',                       'style', false),
    );
    protected $element_name = NULL;
    protected $style_content = NULL;

    /**
     * Get the element name for the ODT XML encoding of the style.
     *
     * @return string The element name
     */
    public function getElementName() {
        return ($this::element_name);
    }

    /**
     * Set the element name.
     *
     * @param  $element_name The element name to set
     */
    public function setElementName($element_name) {
        $this->element_name = $element_name;
    }

    /**
     * Set style properties by importing values from a properties array.
     * Properties might be disabled by setting them in $disabled.
     * The style must have been previously created.
     *
     * Not supported, just a dummy!
     *
     * @param  $properties Properties to be imported
     * @param  $disabled Properties to be ignored
     */
    public function importProperties($properties, $disabled) {
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
     * Not supported, just a dummy.
     * 
     * @param $property The name of the property to set
     * @param $value    New value to set
     */
    public function setProperty($property, $value) {
    }

    /**
     * Set style content. This will be returned on toString().
     * 
     * @param $style_content The complete ODT XML style definition.
     */
    public function setStyleContent($style_content) {
        $this->importODTStyleInternal(self::$unknown_fields, $style_content);
        $this->style_content = $style_content."\n";
    }

    /**
     * Create new style by importing ODT style definition.
     *
     * @param  $xmlCode Style definition in ODT XML format
     * @return ODTStyle New specific style
     */
    static public function importODTStyle($xmlCode) {
        $style = new ODTUnknownStyle();
        $style->setStyleContent($xmlCode);
        return $style;
    }

    /**
     * Encode current style values in a string and return it.
     *
     * @return string ODT XML encoded style
     */
    public function toString() {
        return $this->style_content;
    }

    /**
     * Is the style a default style?
     *
     * @return boolean Is default.
     */
    public function isDefault() {
        if ($this->element_name == 'style:default-style') {
            return true;
        }
        return false;
    }

    /**
     * Get the style family of a style.
     *
     * @return string|NULL Style family
     */
    public function getFamily() {
        return $this->getProperty('style-family');
    }

    /**
     * The function deletes all properties that do not belong to the styles section,
     * e.g. text properties or paragraph properties.
     * For unknown styles this is just a dummy doing nothing.
     */
    public function clearLayoutProperties() {
    }
}
