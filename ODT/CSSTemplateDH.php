<?php
/**
 * CSSTemplateDH: docHandler for creating a document based on a DokuWiki CSS template.
 * Basic styles are taken from styles.xml and are overwritten with CSS definitions.
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author LarsDW223
 */
// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

require_once DOKU_INC.'inc/ZipLib.class.php';
require_once DOKU_INC.'lib/plugins/odt/ODT/ODTmanifest.php';
require_once DOKU_INC.'lib/plugins/odt/ODT/docHandler.php';
require_once DOKU_INC.'lib/plugins/odt/ODT/ODTsettings.php';

/**
 * The scratch document handler
 */
class CSSTemplateDH extends docHandler
{
    protected $factory = NULL;
    protected $settings;
    protected $styleset = NULL;
    protected $template = NULL;

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
        $this->settings = new ODTSettings();

        $this->factory = plugin_load('helper', 'odt_stylefactory');

        // Create default styles (from styles.xml).
        $this->styleset = new ODTDefaultStyles();
        $this->styleset->import();
    }

    protected function importOrderedListStyles($import, $media_sel) {
        $name = $this->styleset->getStyleName('numbering');
        $style = $this->styleset->getStyle($name);
        if ($style == NULL ) {
            return;
        }

        for ($level = 1 ; $level < 11 ; $level++) {
            $properties = array();
            $import->getPropertiesForElement($properties, 'ol', 'level'.$level, $media_sel);

            // Adjust values for ODT
            foreach ($properties as $property => $value) {
                $properties [$property] = $this->factory->adjustValueForODT ($property, $value, 14);
            }
            
            if ($properties ['list-style-type'] !== NULL) {
                $prefix = NULL;
                $suffix = '.';
                $numbering = trim($properties ['list-style-type'],'"');
                switch ($numbering) {
                    case 'decimal':
                        $numbering = '1';
                        break;
                    case 'decimal-leading-zero':
                        $numbering = '1';
                        $prefix = '0';
                        break;
                    case 'lower-alpha':
                    case 'lower-latin':
                        $numbering = 'a';
                        break;
                    case 'lower-roman':
                        $numbering = 'i';
                        break;
                    case 'none':
                        $numbering = '';
                        $suffix = '';
                        break;
                    case 'upper-alpha':
                    case 'upper-latin':
                        $numbering = 'A';
                        break;
                    case 'upper-roman':
                        $numbering = 'i';
                        break;
                }
                $style->setPropertyForLevel($level, 'num-format', $numbering);
                if ($prefix != NULL ) {
                    $style->setPropertyForLevel($level, 'num-prefix', $prefix);
                }
                $style->setPropertyForLevel($level, 'num-suffix', $suffix);
            }
        }
    }

    protected function importUnorderedListStyles($import, $media_sel) {
        $name = $this->styleset->getStyleName('list');
        $style = $this->styleset->getStyle($name);
        if ($style == NULL ) {
            return;
        }

        for ($level = 1 ; $level < 11 ; $level++) {
            $properties = array();
            $import->getPropertiesForElement($properties, 'ul', 'level'.$level, $media_sel);

            // Adjust values for ODT
            foreach ($properties as $property => $value) {
                $properties [$property] = $this->factory->adjustValueForODT ($property, $value, 14);
            }
            
            if ($properties ['list-style-type'] !== NULL) {
                switch ($properties ['list-style-type']) {
                    case 'disc':
                    case 'bullet':
                        $sign = '•';
                        break;
                    case 'circle':
                        $sign = '∘';
                        break;
                    case 'square':
                        $sign = '▪';
                        break;
                    case 'none':
                        $sign = ' ';
                        break;
                    case 'blackcircle':
                        $sign = '●';
                        break;
                    case 'heavycheckmark':
                        $sign = '✔';
                        break;
                    case 'ballotx':
                        $sign = '✗';
                        break;
                    case 'heavyrightarrow':
                        $sign = '➔';
                        break;
                    case 'lightedrightarrow':
                        $sign = '➢';
                        break;
                    default:
                        $sign = trim($properties ['list-style-type'],'"');
                        break;
                }
                $style->setPropertyForLevel($level, 'text-bullet-char', $sign);
            }
        }
    }

    protected function importStyle($import, $style_type, $element, $class, $media_sel) {
        $name = $this->styleset->getStyleName($style_type);
        $style = $this->styleset->getStyle($name);
        if ( $style != NULL ) {
            $style->clearLayoutProperties();

            $properties = array();
            $import->getPropertiesForElement($properties, $element, $class, $media_sel);

            // Adjust values for ODT
            foreach ($properties as $property => $value) {
                $properties [$property] = $this->factory->adjustValueForODT ($property, $value, 14);
            }

            // If the style imported is a table adjust some properties
            if ($style->getFamily() == 'table') {
                // Move 'width' to 'rel-width' if it is relative
                $width = $properties ['width'];
                if ($width != NULL) {
                    if ($properties ['align'] == NULL) {
                        // If width is set but align not, changing the width
                        // will not work. So we set it here if not done by the user.
                        $properties ['align'] = 'center';
                    }
                }
                if ($width [strlen($width)-1] == '%') {
                    $properties ['rel-width'] = $width;
                    unset ($properties ['width']);
                }

                // Convert property 'border-model' to ODT
                if ( !empty ($properties ['border-collapse']) ) {
                    $properties ['border-model'] = $properties ['border-collapse'];
                    unset ($properties ['border-collapse']);
                    if ( $properties ['border-model'] == 'collapse' ) {
                        $properties ['border-model'] = 'collapsing';
                    } else {
                        $properties ['border-model'] = 'separating';
                    }
                }
            }

            $style->importProperties($properties, NULL);
        }
    }

    /**
     * Set the DokuWiki CSS template.
     *
     * @param string $template
     */
    public function import($template_path, $media_sel=NULL) {
        $import = plugin_load('helper', 'odt_cssimport');
        if ( $import != NULL ) {
            $import->importFromFile ($template_path);
        }
        
        $this->importStyle($import, 'heading1', 'h1', NULL, $media_sel);
        $this->importStyle($import, 'heading2', 'h2', NULL, $media_sel);
        $this->importStyle($import, 'heading3', 'h3', NULL, $media_sel);
        $this->importStyle($import, 'heading4', 'h4', NULL, $media_sel);
        $this->importStyle($import, 'heading5', 'h5', NULL, $media_sel);

        $this->importStyle($import, 'horizontal line', 'hr',         NULL, $media_sel);

        $this->importStyle($import, 'body', 'body',       NULL, $media_sel);

        $this->importStyle($import, 'emphasis',     'em',     NULL, $media_sel);
        $this->importStyle($import, 'strong',       'strong', NULL, $media_sel);
        $this->importStyle($import, 'underline',    'u',      NULL, $media_sel);
        $this->importStyle($import, 'monospace',    'code',   NULL, $media_sel);
        $this->importStyle($import, 'del',          'del',    NULL, $media_sel);
        $this->importStyle($import, 'preformatted', 'pre',    NULL, $media_sel);

        $this->importStyle($import, 'quotation1', 'quotation1', NULL, $media_sel);
        $this->importStyle($import, 'quotation2', 'quotation2', NULL, $media_sel);
        $this->importStyle($import, 'quotation3', 'quotation3', NULL, $media_sel);
        $this->importStyle($import, 'quotation4', 'quotation4', NULL, $media_sel);
        $this->importStyle($import, 'quotation5', 'quotation5', NULL, $media_sel);

        $this->importStyle($import, 'table',         'table', NULL, $media_sel);
        $this->importStyle($import, 'table header',  'thead', NULL, $media_sel);
        $this->importStyle($import, 'table heading', 'th',    NULL, $media_sel);
        $this->importStyle($import, 'table cell',    'td',    NULL, $media_sel);

        $this->importUnorderedListStyles($import, $media_sel);
        $this->importOrderedListStyles($import, $media_sel);
        $this->importStyle($import, 'list first paragraph', NULL, 'listfirstparagraph', $media_sel);
    }


    /**
     * Build the document from scratch.
     * (code taken from old function 'document_end_scratch')
     *
     * @param string      $doc
     * @param string      $autostyles
     * @param array       $commonstyles
     * @param string      $meta
     * @param string      $userfields
     * @param ODTStyleSet $styleset
     * @return mixed
     */
    public function build($doc=null, $meta=null, $userfields=null, $pagestyles=null){
        // add defaults
        $this->ZIP->add_File('application/vnd.oasis.opendocument.text', 'mimetype', 0);
        $this->ZIP->add_File($meta,'meta.xml');
        $this->ZIP->add_File($this->settings->getContent(),'settings.xml');

        $autostyles = $this->styleset->export('office:automatic-styles');
        $commonstyles = $this->styleset->export('office:styles');

        $value  =   '<' . '?xml version="1.0" encoding="UTF-8"?' . ">\n";
        $value .=   '<office:document-content ';
        $value .=       'xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" ';
        $value .=       'xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" ';
        $value .=       'xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" ';
        $value .=       'xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" ';
        $value .=       'xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0" ';
        $value .=       'xmlns:fo="urn:oasis:names:tc:opendocument:xmlns:xsl-fo-compatible:1.0" ';
        $value .=       'xmlns:xlink="http://www.w3.org/1999/xlink" ';
        $value .=       'xmlns:dc="http://purl.org/dc/elements/1.1/" ';
        $value .=       'xmlns:meta="urn:oasis:names:tc:opendocument:xmlns:meta:1.0" ';
        $value .=       'xmlns:number="urn:oasis:names:tc:opendocument:xmlns:datastyle:1.0" ';
        $value .=       'xmlns:svg="urn:oasis:names:tc:opendocument:xmlns:svg-compatible:1.0" ';
        $value .=       'xmlns:chart="urn:oasis:names:tc:opendocument:xmlns:chart:1.0" ';
        $value .=       'xmlns:dr3d="urn:oasis:names:tc:opendocument:xmlns:dr3d:1.0" ';
        $value .=       'xmlns:math="http://www.w3.org/1998/Math/MathML" ';
        $value .=       'xmlns:form="urn:oasis:names:tc:opendocument:xmlns:form:1.0" ';
        $value .=       'xmlns:script="urn:oasis:names:tc:opendocument:xmlns:script:1.0" ';
        $value .=       'xmlns:dom="http://www.w3.org/2001/xml-events" ';
        $value .=       'xmlns:xforms="http://www.w3.org/2002/xforms" ';
        $value .=       'xmlns:xsd="http://www.w3.org/2001/XMLSchema" ';
        $value .=       'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" ';
        $value .=   'office:version="1.0">';
        $value .=       '<office:scripts/>';
        $value .=       '<office:font-face-decls>';
        $value .=           '<style:font-face style:name="Tahoma1" svg:font-family="Tahoma"/>';
        $value .=           '<style:font-face style:name="Lucida Sans Unicode" svg:font-family="&apos;Lucida Sans Unicode&apos;" style:font-pitch="variable"/>';
        $value .=           '<style:font-face style:name="Tahoma" svg:font-family="Tahoma" style:font-pitch="variable"/>';
        $value .=           '<style:font-face style:name="Times New Roman" svg:font-family="&apos;Times New Roman&apos;" style:font-family-generic="roman" style:font-pitch="variable"/>';
        $value .=       '</office:font-face-decls>';
        $value .=       $autostyles;
        $value .=       '<office:body>';
        $value .=           '<office:text>';
        $value .=               '<office:forms form:automatic-focus="false" form:apply-design-mode="false"/>';
        $value .=               '<text:sequence-decls>';
        $value .=                   '<text:sequence-decl text:display-outline-level="0" text:name="Illustration"/>';
        $value .=                   '<text:sequence-decl text:display-outline-level="0" text:name="Table"/>';
        $value .=                   '<text:sequence-decl text:display-outline-level="0" text:name="Text"/>';
        $value .=                   '<text:sequence-decl text:display-outline-level="0" text:name="Drawing"/>';
        $value .=               '</text:sequence-decls>';
        $value .=               $userfields;
        $value .=   $doc;
        $value .=           '</office:text>';
        $value .=       '</office:body>';
        $value .=   '</office:document-content>';

        $this->ZIP->add_File($value,'content.xml');

        // Edit 'styles.xml'
        $value = io_readFile(DOKU_PLUGIN.'odt/styles.xml');

        // Add page styles
        $page = '';
        foreach ($pagestyles as $name => $layout_name) {
            $page .= '<style:master-page style:name="'.$name.'" style:page-layout-name="'.$layout_name.'"/>';
        }
        if ( !empty($page) ) {
            $value = str_replace('</office:master-styles>', $page.'</office:master-styles>', $value);
        }

        // Add common styles.
        $original = XMLUtil::getElement('office:styles', $value);
        $value = str_replace($original, $commonstyles, $value);

        // Add automatic styles.
        $value = str_replace('<office:automatic-styles/>', $autostyles, $value);
        $this->ZIP->add_File($value,'styles.xml');

        // build final manifest
        $this->ZIP->add_File($this->manifest->getContent(),'META-INF/manifest.xml');
    }

    /**
     * @param null $source
     */
    public function addStyle(ODTStyle $new) {
        return $this->styleset->addStyle($new);
    }

    /**
     * @param null $source
     */
    public function addAutomaticStyle(ODTStyle $new) {
        return $this->styleset->addAutomaticStyle($new);
    }

    /**
     * The function style checks if a style with the given $name already exists.
     * 
     * @param $name Name of the style to check
     * @return boolean
     */
    public function styleExists ($name) {
        return $this->styleset->styleExists($name);
    }

    /**
     * The function returns the style with the given name
     * 
     * @param $name Name of the style
     * @return ODTStyle or NULL
     */
    public function getStyle ($name) {
        return $this->styleset->getStyle($name);
    }

    /**
     * The function returns the style names used for the basic syntax.
     */
    public function getStyleName($style) {
        return $this->styleset->getStyleName($style);
    }

    /**
     * The function returns the style at the given index
     * 
     * @param $element Element of the style e.g. 'office:styles'
     * @return ODTStyle or NULL
     */
    public function getStyleAtIndex($element, $index) {
        return $this->styleset->getStyleAtIndex($element, $index);
    }
}
