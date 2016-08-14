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
}
