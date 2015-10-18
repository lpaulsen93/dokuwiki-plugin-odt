<?php
/**
 * scratchDH: docHandler for creating a document from scratch.
 * No additional files, styles are taken from styles.xml.
 *
 * Most code was taken from renderer.php.
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Aurelien Bompard <aurelien@bompard.org>
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
class scratchDH extends docHandler
{
    protected $settings;
    protected $styleset = NULL;

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
        $this->settings = new ODTSettings();

        // Create styles.
        $this->styleset = new ODTDefaultStyles();
        $this->styleset->import();
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
        $masterstyles = $this->styleset->export('office:master-styles');

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
        $value .=       'xmlns:ooo="http://openoffice.org/2004/office" ';
        $value .=       'xmlns:ooow="http://openoffice.org/2004/writer" ';
        $value .=       'xmlns:oooc="http://openoffice.org/2004/calc" ';
        $value .=       'xmlns:dom="http://www.w3.org/2001/xml-events" ';
        $value .=       'xmlns:xforms="http://www.w3.org/2002/xforms" ';
        $value .=       'xmlns:xsd="http://www.w3.org/2001/XMLSchema" ';
        $value .=       'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" ';
        $value .=       'xmlns:rpt="http://openoffice.org/2005/report" ';
        $value .=       'xmlns:of="urn:oasis:names:tc:opendocument:xmlns:of:1.2" ';
        $value .=       'xmlns:xhtml="http://www.w3.org/1999/xhtml" ';
        $value .=       'xmlns:grddl="http://www.w3.org/2003/g/data-view#" ';
        $value .=       'xmlns:officeooo="http://openoffice.org/2009/office" ';
        $value .=       'xmlns:tableooo="http://openoffice.org/2009/table" ';
        $value .=       'xmlns:drawooo="http://openoffice.org/2010/draw" ';
        $value .=       'xmlns:calcext="urn:org:documentfoundation:names:experimental:calc:xmlns:calcext:1.0" ';
        $value .=       'xmlns:loext="urn:org:documentfoundation:names:experimental:office:xmlns:loext:1.0" ';
        $value .=       'xmlns:field="urn:openoffice:names:experimental:ooo-ms-interop:xmlns:field:1.0" ';
        $value .=       'xmlns:formx="urn:openoffice:names:experimental:ooxml-odf-interop:xmlns:form:1.0" ';
        $value .=       'xmlns:css3t="http://www.w3.org/TR/css3-text/" ';
        $value .=   'office:version="1.2">';
        $value .=       '<office:scripts/>';
        $value .=       '<office:font-face-decls>';
        $value .=           '<style:font-face style:name="OpenSymbol" svg:font-family="OpenSymbol" style:font-charset="x-symbol"/>';
        $value .=           '<style:font-face style:name="StarSymbol1" svg:font-family="StarSymbol" style:font-charset="x-symbol"/>';
        $value .=           '<style:font-face style:name="StarSymbol" svg:font-family="StarSymbol"/>';
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

        // Insert new master page styles
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
