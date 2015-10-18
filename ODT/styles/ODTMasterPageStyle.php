<?php
/**
 * ODTMasterPageStyle: class for ODT text list styles.
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author LarsDW223
 */

require_once DOKU_INC.'lib/plugins/odt/ODT/XMLUtil.php';
require_once DOKU_INC.'lib/plugins/odt/ODT/styles/ODTStyle.php';

/**
 * The ODTMasterPageStyle class
 */
class ODTMasterPageStyle extends ODTStyle
{
    static $master_fields = array(
        // Fields belonging to "style:master-page"
        'style-name'                       => array ('style:name',                         'style', false),
        'style-display-name'               => array ('style:display-name',                 'style', false),
        'style-page-layout-name'           => array ('style:page-layout-name',             'style', false),
        'draw-style-name'                  => array ('draw:style-name',                    'style', false),
        'style-next'                       => array ('style:next-style-name',              'style', true),
    );

    static $header_footer_fields = array(
        // Fields belonging to "style:header", "style:footer",
        // "style:header-left" and "style:footer-left"
        // The content/child-elements of "style:header" are saved as is
        'style-display'                    => array ('style:display',                      'header', true),
    );

    protected $master_style = array();
    protected $style_header = array();
    protected $style_footer = array();
    protected $style_header_left = array();
    protected $style_footer_left = array();
    protected $content_header = NULL;
    protected $content_footer = NULL;
    protected $content_header_left = NULL;
    protected $content_footer_left = NULL;

    /**
     * Get the element name for the ODT XML encoding of the style.
     */
    public function getElementName() {
        return 'style:master-page';
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
        $this->importPropertiesInternal(self::$master_fields, $properties, $disabled, $this->master_style);
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
        if (array_key_exists ($property, self::$master_fields)) {
            $this->setPropertyInternal
                ($property, self::$master_fields [$property][0], $value, self::$master_fields [$property][1], $this->master_style);
            return;
        }
    }

    /**
     * Get the value of a property.
     * 
     * @param  $property The property name
     * @return string The current value of the property
     */
    public function getProperty($property) {
        if (array_key_exists ($property, self::$master_fields)) {
            return $this->master_style [$property]['value'];
        }
        return NULL;
    }

    /**
     * Create new style by importing ODT style definition.
     *
     * @param  $xmlCode Style definition in ODT XML format
     * @return ODTStyle New specific style
     */
    static public function importODTStyle($xmlCode) {
        $style = new ODTMasterPageStyle();
        
        // Get attributes for element 'style:master-page'
        $open = XMLUtil::getElementOpenTag('style:master-page', $xmlCode);
        if (!empty($open)) {
            $style->importODTStyleInternal(self::$master_fields, $open, $style->master_style);
        }

        // Get attributes for element 'style:header'
        $open = XMLUtil::getElementOpenTag('style:header', $xmlCode);
        if (!empty($open)) {
            $style->importODTStyleInternal(self::$header_footer_fields, $open, $style->style_header);
            $content_header = XMLUtil::getElementContent ('style:header', $xmlCode);
        }

        // Get attributes for element 'style:footer'
        $open = XMLUtil::getElementOpenTag('style:footer', $xmlCode);
        if (!empty($open)) {
            $style->importODTStyleInternal(self::$header_footer_fields, $open, $style->style_footer);
            $content_footer = XMLUtil::getElementContent ('style:footer', $xmlCode);
        }

        // Get attributes for element 'style:header-left'
        $open = XMLUtil::getElementOpenTag('style:header-left', $xmlCode);
        if (!empty($open)) {
            $style->importODTStyleInternal(self::$header_footer_fields, $open, $style->style_header_left);
            $content_header_left = XMLUtil::getElementContent ('style:header-left', $xmlCode);
        }

        // Get attributes for element 'style:footer-left'
        $open = XMLUtil::getElementOpenTag('style:footer-left', $xmlCode);
        if (!empty($open)) {
            $style->importODTStyleInternal(self::$header_footer_fields, $open, $style->style_footer_left);
            $content_footer_left = XMLUtil::getElementContent ('style:footer-left', $xmlCode);
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
        $master = '';
        $header = '';
        $footer = '';
        $header_left = '';
        $footer_left = '';

        // Get master style ODT properties
        foreach ($this->master_style as $property => $items) {
            $master .= $items ['odt_property'].'="'.$items ['value'].'" ';
        }

        // Get header ODT properties
        foreach ($this->style_header as $property => $items) {
            $header .= $items ['odt_property'].'="'.$items ['value'].'" ';
        }

        // Get footer ODT properties
        foreach ($this->style_footer as $property => $items) {
            $footer .= $items ['odt_property'].'="'.$items ['value'].'" ';
        }

        // Get header-left ODT properties
        foreach ($this->style_header_left as $property => $items) {
            $header_left .= $items ['odt_property'].'="'.$items ['value'].'" ';
        }

        // Get footer-left ODT properties
        foreach ($this->style_footer_left as $property => $items) {
            $footer_left .= $items ['odt_property'].'="'.$items ['value'].'" ';
        }

        // Build style.
        $style  = '<style:master-page '.$master.">\n";
        if ( !empty($header) || !empty($content_header) ) {
            $style .= '<style:header '.$header.">\n";
            $style .= $content_header;
            $style .= '</style:header>'."\n";
        }
        if ( !empty($footer) || !empty($content_footer) ) {
            $style .= '<style:footer '.$footer.">\n";
            $style .= $content_footer;
            $style .= '</style:footer>'."\n";
        }
        if ( !empty($header_left) || !empty($content_header_left) ) {
            $style .= '<style:header-left '.$header_left.">\n";
            $style .= $content_header_left;
            $style .= '</style:header-left>'."\n";
        }
        if ( !empty($footer_left) || !empty($content_footer_left) ) {
            $style .= '<style:footer-left '.$footer_left.">\n";
            $style .= $content_footer_left;
            $style .= '</style:footer-left>'."\n";
        }
        $style .= '</style:master-page'.">\n";
        return $style;
    }
}

