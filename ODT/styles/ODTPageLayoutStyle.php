<?php
/**
 * ODTPageLayoutStyle: class for ODT page layout styles.
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author LarsDW223
 */

require_once DOKU_INC.'lib/plugins/odt/ODT/XMLUtil.php';
require_once DOKU_INC.'lib/plugins/odt/ODT/styles/ODTStyle.php';

/**
 * The ODTPageLayoutStyle class
 */
class ODTPageLayoutStyle extends ODTStyle
{
    static $page_layout_fields = array(
        // Fields belonging to "style:master-page"
        'style-name'                       => array ('style:name',                       'style', false),
        'style-page-usage'                 => array ('style:page-usage',                 'style', false),
    );

    static $layout_props_fields = array(
        // Fields belonging to "style:page-layout-properties"
        'width'                            => array ('fo:page-width',                       'props', true),
        'height'                           => array ('fo:page-height',                      'props', true),
        'num-format'                       => array ('style:num-format',                    'props', true),
        'num-letter-sync'                  => array ('style:num-letter-sync',               'props', true),
        'num-prefix'                       => array ('style:num-prefix',                    'props', true),
        'num-suffix'                       => array ('style:num-suffix',                    'props', true),
        'paper-tray-name'                  => array ('style:paper-tray-name',               'props', true),
        'print-orientation'                => array ('style:print-orientation',             'props', true),
        'margin-left'                      => array ('fo:margin-left',                      'props', true),
        'margin-right'                     => array ('fo:margin-right',                     'props', true),
        'margin-top'                       => array ('fo:margin-top',                       'props', true),
        'margin-bottom'                    => array ('fo:margin-bottom',                    'props', true),
        'margin'                           => array ('fo:margin',                           'props', true),
        'border'                           => array ('fo:border',                           'props', true),
        'border-top'                       => array ('fo:border-top',                       'props', true),
        'border-right'                     => array ('fo:border-right',                     'props', true),
        'border-bottom'                    => array ('fo:border-bottom',                    'props', true),
        'border-left'                      => array ('fo:border-left',                      'props', true),
        'border-line-width'                => array ('style:border-line-width',             'props', true),
        'border-line-width-top'            => array ('style:border-line-width-top',         'props', true),
        'border-line-width-bottom'         => array ('style:border-line-width-bottom',      'props', true),
        'border-line-width-left'           => array ('style:border-line-width-left',        'props', true),
        'border-line-width-right'          => array ('style:border-line-width-right',       'props', true),
        'padding'                          => array ('fo:padding',                          'props', true),
        'padding-top'                      => array ('fo:padding-top',                      'props', true),
        'padding-bottom'                   => array ('fo:padding-bottom',                   'props', true),
        'padding-left'                     => array ('fo:padding-left',                     'props', true),
        'padding-right'                    => array ('fo:padding-right',                    'props', true),
        'shadow'                           => array ('style:shadow',                        'props', true),
        'background-color'                 => array ('fo:background-color',                 'props', true),
        'register-truth-ref-style-name'    => array ('style:register-truth-ref-style-name', 'props', true),
        'print'                            => array ('style:print',                         'props', true),
        'print-page-order'                 => array ('style:print-page-order',              'props', true),
        'first-page-number'                => array ('style:first-page-number',             'props', true),
        'scale-to'                         => array ('style:scale-to',                      'props', true),
        'scale-to-pages'                   => array ('style:scale-to-pages',                'props', true),
        'table-centering'                  => array ('style:table-centering',               'props', true),
        'footnote-max-height'              => array ('style:footnote-max-height',           'props', true),
        'writing-mode'                     => array ('style:writing-mode',                  'props', true),
        'layout-grid-mode'                 => array ('style:layout-grid-mode',              'props', true),
        'layout-grid-standard-mode'        => array ('style:layout-grid-standard-mode',     'props', true),
        'layout-grid-base-height'          => array ('style:layout-grid-base-height',       'props', true),
        'layout-grid-ruby-height'          => array ('style:layout-grid-ruby-height',       'props', true),
        'layout-grid-lines'                => array ('style:layout-grid-lines',             'props', true),
        'layout-grid-base-width'           => array ('style:layout-grid-base-width',        'props', true),
        'layout-grid-color'                => array ('style:layout-grid-color',             'props', true),
        'layout-grid-ruby-below'           => array ('style:layout-grid-ruby-below',        'props', true),
        'layout-grid-print'                => array ('style:layout-grid-print',             'props', true),
        'layout-grid-display'              => array ('style:layout-grid-display',           'props', true),
        'layout-grid-snap-to'              => array ('style:layout-grid-snap-to',           'props', true),
    );

    static $bgi_fields = array(
        // Fields belonging to "style:background-image"
        // The content of element "style:background-image" will be saved as is
        'repeat'                   => array ('style:repeat',                   'bgi', true),
        'position'                 => array ('style:position',                 'bgi', true),
        'filter-name'              => array ('style:filter-name',              'bgi', true),
        'opacity'                  => array ('draw:opacity',                   'bgi', true),
        'xlink-type'               => array ('xlink:type',                     'bgi', true),
        'xlink-href'               => array ('xlink:href',                     'bgi', true),
        'xlink-show'               => array ('xlink:show',                     'bgi', true),
        'xlink-actuate'            => array ('xlink:actuate',                  'bgi', true),
    );

    static $columns_fields = array(
        // Fields belonging to "style:columns"
        // The content of element "style:columns" will be saved as is
        'column-count'                   => array ('fo:column-count',          'columns', true),
        'column-gap'                     => array ('fo:column-gap',            'columns', true),
        'column-gap'                     => array ('fo:column-gap',            'columns', true),
    );

    static $footnote_fields = array(
        // Fields belonging to "style:footnote-sep"
        'ftsep-width'                    => array ('style:width',               'ftsep', true),
        'ftsep-rel-width'                => array ('style:rel-width',           'ftsep', true),
        'ftsep-color'                    => array ('style:color',               'ftsep', true),
        'ftsep-line-style'               => array ('style:line-style',          'ftsep', true),
        'ftsep-adjustment'               => array ('style:adjustment',          'ftsep', true),
        'ftsep-distance-before-sep'      => array ('style:distance-before-sep', 'ftsep', true),
        'ftsep-distance-after-sep'       => array ('style:distance-after-sep',  'ftsep', true),
    );

    protected $page_layout_style = array();
    protected $layout_props = array();
    protected $bgi_props = array();
    protected $columns_props = array();
    protected $footnote_props = array();

    protected $content_bgi = NULL;
    protected $content_columns = NULL;
    protected $content_header = NULL;
    protected $content_footer = NULL;

    /**
     * Get the element name for the ODT XML encoding of the style.
     */
    public function getElementName() {
        return 'style:page-layout';
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
        $this->importPropertiesInternal(self::$page_layout_fields, $properties, $disabled, $this->page_layout_style);
        $this->importPropertiesInternal(self::$layout_props_fields, $properties, $disabled, $this->layout_props);
        $this->importPropertiesInternal(self::$bgi_fields, $properties, $disabled, $this->bgi_props);
        $this->importPropertiesInternal(self::$columns_fields, $properties, $disabled, $this->columns_props);
        $this->importPropertiesInternal(self::$footnote_props, $properties, $disabled, $this->footnote_fields);
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
        if (array_key_exists ($property, self::$page_layout_fields)) {
            $this->setPropertyInternal
                ($property, self::$page_layout_fields [$property][0], $value, self::$page_layout_fields [$property][1], $this->page_layout_style);
            return;
        }
        if (array_key_exists ($property, self::$layout_props_fields)) {
            $this->setPropertyInternal
                ($property, self::$layout_props_fields [$property][0], $value, self::$layout_props_fields [$property][1], $this->layout_props);
            return;
        }
        if (array_key_exists ($property, self::$bgi_fields)) {
            $this->setPropertyInternal
                ($property, self::$bgi_fields [$property][0], $value, self::$bgi_fields [$property][1], $this->bgi_props);
            return;
        }
        if (array_key_exists ($property, self::$columns_fields)) {
            $this->setPropertyInternal
                ($property, self::$columns_fields [$property][0], $value, self::$columns_fields [$property][1], $this->columns_props);
            return;
        }
        if (array_key_exists ($property, self::$footnote_fields)) {
            $this->setPropertyInternal
                ($property, self::$footnote_fields [$property][0], $value, self::$footnote_fields [$property][1], $this->footnote_props);
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
        if (array_key_exists ($property, self::$page_layout_fields)) {
            return $this->page_layout_style [$property]['value'];
        }
        if (array_key_exists ($property, self::$layout_props_fields)) {
            return $this->layout_props [$property]['value'];
        }
        if (array_key_exists ($property, self::$bgi_fields)) {
            return $this->bgi_props [$property]['value'];
        }
        if (array_key_exists ($property, self::$columns_fields)) {
            return $this->columns_props [$property]['value'];
        }
        if (array_key_exists ($property, self::$footnote_fields)) {
            return $this->footnote_props [$property]['value'];
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
        $style = new ODTPageLayoutStyle();
        
        // Get attributes for element 'style:master-page'
        $open = XMLUtil::getElementOpenTag('style:page-layout', $xmlCode);
        if (!empty($open)) {
            $style->importODTStyleInternal(self::$page_layout_fields, $open, $style->page_layout_style);

            $childs = XMLUtil::getElementContent ('style:page-layout', $xmlCode);
            if (!empty($childs)) {
                // Get attributes for element 'style:page-layout-properties'
                $open = XMLUtil::getElementOpenTag('style:page-layout-properties', $childs);
                $style->content_header = XMLUtil::getElement ('style:header-style', $childs);
                $style->content_footer = XMLUtil::getElement ('style:footer-style', $childs);
                if (!empty($open)) {
                    $style->importODTStyleInternal(self::$layout_props_fields, $open, $style->layout_props);

                    $childs = XMLUtil::getElementContent ('style:page-layout-properties', $xmlCode);
                    if (!empty($childs)) {
                        // Get 'style:background-image'
                        $open = XMLUtil::getElementOpenTag('style:background-image', $childs);
                        if (!empty($open)) {
                            $style->importODTStyleInternal(self::$bgi_fields, $open, $style->bgi_props);
                            $style->content_bgi = XMLUtil::getElementContent ('style:background-image', $childs);
                        }

                        // Get 'style:columns'
                        $open = XMLUtil::getElementOpenTag('style:columns', $childs);
                        if (!empty($open)) {
                            $style->importODTStyleInternal(self::$columns_fields, $open, $style->columns_props);
                            $style->content_columns = XMLUtil::getElementContent ('style:columns', $childs);
                        }
                        
                        // Get 'style:footnote-sep'
                        $open = XMLUtil::getElementOpenTag('style:footnote-sep', $childs);
                        if (!empty($open)) {
                            $style->importODTStyleInternal(self::$footnote_fields, $open, $style->footnote_props);
                        }
                    }
                }
            }
        }

        return $style;
    }

    /**
     * Encode current style values in a string and return it.
     *
     * @return string ODT XML encoded style
     */
    public function toString() {
        $layout_style = '';
        $layout = '';
        $bgi = '';
        $columns = '';
        $footnote = '';

        // Get page layout style ODT properties
        foreach ($this->page_layout_style as $property => $items) {
            $layout_style .= $items ['odt_property'].'="'.$items ['value'].'" ';
        }

        // Get page layout properties ODT properties
        foreach ($this->layout_props as $property => $items) {
            $layout .= $items ['odt_property'].'="'.$items ['value'].'" ';
        }

        // Get background-image ODT properties
        foreach ($this->bgi_props as $property => $items) {
            $bgi .= $items ['odt_property'].'="'.$items ['value'].'" ';
        }

        // Get columns ODT properties
        foreach ($this->columns_props as $property => $items) {
            $columns .= $items ['odt_property'].'="'.$items ['value'].'" ';
        }

        // Get footnote-sep ODT properties
        foreach ($this->footnote_props as $property => $items) {
            $footnote .= $items ['odt_property'].'="'.$items ['value'].'" ';
        }

        // Build style.
        $style  = '<style:page-layout '.$layout_style.">\n";
        if ( !empty($layout) || !empty($bgi) || !empty($columns) || !empty($footnote) ||
             !empty($this->content_bgi) || !empty($this->content_columns) ) {
            $style .= '<style:page-layout-properties '.$layout.">\n";

            if ( !empty($bgi) || !empty($this->content_bgi) ) {
                $style .= '<style:background-image '.$bgi.">\n";
                $style .= $this->content_bgi;
                $style .= '</style:background-image>'."\n";
            }
            if ( !empty($columns) || !empty($content_columns) ) {
                $style .= '<style:columns '.$columns.">\n";
                $style .= $this->content_columns;
                $style .= '</style:columns>'."\n";
            }
            if ( !empty($footnote) ) {
                $style .= '<style:footnote-sep '.$footnote."/>\n";
            }

            $style .= '</style:page-layout-properties>'."\n";
        }

        $style .= $this->content_header;
        $style .= $this->content_footer;

        $style .= '</style:page-layout'.">\n";
        return $style;
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
     * @param $properties
     * @param null $disabled_props
     * @return ODTUnknownStyle or NULL
     */
    public static function createPageLayoutStyle(array $properties, array $disabled_props = NULL) {
        // Create style name (if not given).
        $style_name = $properties ['style-name'];
        if ( empty($style_name) ) {
            $style_name = self::getNewStylename ('Page');
            $properties ['style-name'] = $style_name;
        }
        $style = '<style:page-layout style:name="'.$style_name.'">
                <style:page-layout-properties fo:page-width="'.$properties ['width'].'cm" fo:page-height="'.$properties ['height'].'cm" style:num-format="1" style:print-orientation="landscape" fo:margin-top="'.$properties ['margin-top'].'cm" fo:margin-bottom="'.$properties ['margin-bottom'].'cm" fo:margin-left="'.$properties ['margin-left'].'cm" fo:margin-right="'.$properties ['margin-right'].'cm" style:writing-mode="lr-tb" style:footnote-max-height="0cm">
                    <style:footnote-sep style:width="0.018cm" style:distance-before-sep="0.1cm" style:distance-after-sep="0.1cm" style:adjustment="left" style:rel-width="25%" style:color="#000000"/>
                </style:page-layout-properties>
                <style:header-style/>
                <style:footer-style/>
            </style:page-layout>';
        return self::importODTStyle($style);
    }
}

