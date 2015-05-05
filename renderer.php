<?php
/**
 * ODT Plugin: Exports to ODT
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Aurelien Bompard <aurelien@bompard.org>
 */
// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

require_once DOKU_INC.'inc/parser/renderer.php';
require_once DOKU_INC.'lib/plugins/odt/helper/cssimport.php';

// ZipLib.class.php
$dw_version = preg_replace('/[^\d]/', '', getversion());
if (version_compare($dw_version, "20070626") and 
    version_compare(PHP_VERSION,'5.0.0','>')) {
    // If strictly newer than 2007-06-26 and use PHP5, fixes to ZipLib are
    // included in Dokuwiki's ZipLib
    require_once DOKU_INC.'inc/ZipLib.class.php';
} else { // for DW up to 2007-06-26, we need the patched version
    require_once 'ZipLib.class.php';
}

/**
 * The Renderer
 */
class renderer_plugin_odt extends Doku_Renderer {
    var $factory = null;
    var $import = null;
    var $ZIP = null;
    var $meta;
    var $store = '';
    var $footnotes = array();
    var $manifest  = array();
    var $headers = array();
    var $template = "";
    var $fields = array();
    var $in_list_item = false;
    var $in_paragraph = false;
    var $highlight_style_num = 1;
    var $temp_table_column_styles = array ();
    var $temp_in_header = false;
    var $temp_autocols = false;
    var $temp_maxcols = 0;
    var $temp_column = 0;
    var $temp_content = NULL;
    var $temp_cols = NULL;
    var $quote_depth = 0;
    var $quote_pos = 0;
    var $div_z_index = 0;

    // Page parameters. These are used by function initStyles.
    // All values are assumed to be in 'cm' units.
    var $page_width = 21;
    var $page_heigth = 29.7;
    var $margin_top = 2;
    var $margin_bottom = 2;
    var $margin_left = 2;
    var $margin_right = 2;

    // Automatic styles. Will always be added to content.xml and styles.xml.
    // Initalized by function initStyles.
    var $autostyles;
    // Regular styles. May not be present if in template mode, in which case they will be added to styles.xml
    // Initalized by function initStyles.
    var $styles;

    // Font definitions. May not be present if in template mode, in which case they will be added to styles.xml
    var $fonts = array(
        "StarSymbol"=>'<style:font-face style:name="StarSymbol" svg:font-family="StarSymbol"/>', // for bullets
        "Bitstream Vera Sans Mono"=>'<style:font-face style:name="Bitstream Vera Sans Mono" svg:font-family="\'Bitstream Vera Sans Mono\'" style:font-family-generic="modern" style:font-pitch="fixed"/>', // for source code
    );

    /**
     * Return version info
     */
    function getInfo(){
        return confToHash(dirname(__FILE__).'/plugin.info.txt');
    }

    /**
     * Returns the format produced by this renderer.
     */
    function getFormat(){
        return "odt";
    }

    /**
     * Do not make multiple instances of this class
     */
    function isSingleton(){
        return true;
    }


    /**
     * Initialize the rendering
     */
    function document_start() {
        global $ID;

        if ( empty($this->factory) === true ) {
            $this->factory = plugin_load('helper', 'odt_stylefactory');
        }

        $this->autostyles = $this->_getInitAutoStyles();
        $this->styles = $this->_getInitStyles();

        // If older or equal to 2007-06-26, we need to disable caching
        $dw_version = preg_replace('/[^\d]/', '', getversion());
        if (version_compare($dw_version, "20070626", "<=")) {
            $this->info["cache"] = false;
        }

        // prepare the zipper
        $this->ZIP = new ZipLib();

        // prepare meta data
        $this->meta             = array(
                'meta:generator'            => 'DokuWiki '.getversion(),
                'meta:initial-creator'      => 'Generated',
                'meta:creation-date'        => date('Y-m-d\\TH::i:s', null), //FIXME
                'dc:creator'                => 'Generated',
                'dc:date'                   => date('Y-m-d\\TH::i:s', null),
                'dc:language'               => 'en-US',
                'meta:editing-cycles'       => '1',
                'meta:editing-duration'     => 'PT0S',
            );

        //$headers = array('Content-Type'=>'text/plain'); p_set_metadata($ID,array('format' => array('odt' => $headers) )); return ; // DEBUG
        // send the content type header, new method after 2007-06-26 (handles caching)
        $output_filename = str_replace(':','-',$ID).".odt";
        if (version_compare($dw_version, "20070626")) {
            // store the content type headers in metadata
            $headers = array(
                'Content-Type' => 'application/vnd.oasis.opendocument.text',
                'Content-Disposition' => 'attachment; filename="'.$output_filename.'";',
            );
            p_set_metadata($ID,array('format' => array('odt' => $headers) ));
        } else { // older method
            header('Content-Type: application/vnd.oasis.opendocument.text');
            header('Content-Disposition: attachment; filename="'.$output_filename.'";');
        }
    }

    /**
     * Prepare meta.xml
     */
    function _odtMeta(){
        $value  =   '<' . '?xml version="1.0" encoding="UTF-8"?' . ">\n";
        $value .=   '<office:document-meta ';
        $value .=       'xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" ';
        $value .=       'xmlns:xlink="http://www.w3.org/1999/xlink" ';
        $value .=       'xmlns:dc="http://purl.org/dc/elements/1.1/" ';
        $value .=       'xmlns:meta="urn:oasis:names:tc:opendocument:xmlns:meta:1.0" ';
        $value .=   'office:version="1.0">';
        $value .=       '<office:meta>';
    # FIXME
    #    foreach($meta as $meta_key => $meta_value)
    #        $value .=       '<' . $meta_key . '>' . ODUtils::encode($meta_value) . '</' . $meta_key . '>';
        $value .=       '</office:meta>';
        $value .=   '</office:document-meta>';
        $this->ZIP->add_File($value,'meta.xml');
    }

    /**
     * Prepare manifest.xml
     */
    function _odtManifest(){
        $value  =   '<' . '?xml version="1.0" encoding="UTF-8"?' . ">\n";
        $value .=   '<manifest:manifest xmlns:manifest="urn:oasis:names:tc:opendocument:xmlns:manifest:1.0">';
        $value .=   '<manifest:file-entry manifest:media-type="application/vnd.oasis.opendocument.text" manifest:full-path="/"/>';
        $value .=   '<manifest:file-entry manifest:media-type="text/xml" manifest:full-path="settings.xml"/>';
        $value .=   '<manifest:file-entry manifest:media-type="text/xml" manifest:full-path="meta.xml"/>';
        $value .=   '<manifest:file-entry manifest:media-type="text/xml" manifest:full-path="content.xml"/>';
        $value .=   '<manifest:file-entry manifest:media-type="text/xml" manifest:full-path="styles.xml"/>';
        $value .= $this->_odtGetManifest();
        $value .=   '</manifest:manifest>';
        $this->ZIP->add_File($value,'META-INF/manifest.xml');
    }

    function _odtGetManifest() {
        $value = '';
        foreach($this->manifest as $path => $type){
            $value .= '<manifest:file-entry manifest:media-type="'.$this->_xmlEntities($type).
                      '" manifest:full-path="'.$this->_xmlEntities($path).'"/>';
        }
        return $value;
    }

    /**
     * Prepare settings.xml
     */
    function _odtSettings(){
        $value  =   '<' . '?xml version="1.0" encoding="UTF-8"?' . ">\n";
        $value .=   '<office:document-settings xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:config="urn:oasis:names:tc:opendocument:xmlns:config:1.0" office:version="1.0"><office:settings><config:config-item-set config:name="dummy-settings"><config:config-item config:name="MakeValidatorHappy" config:type="boolean">true</config:config-item></config:config-item-set></office:settings></office:document-settings>';
        $this->ZIP->add_File($value,'settings.xml');
    }




    /**
     * Closes the document
     */
    function document_end(){
        global $conf;
        //$this->doc .= $this->_odtAutoStyles(); return; // DEBUG

        $this->doc = preg_replace('#<text:p[^>]*>\s*</text:p>#', '', $this->doc);

        // Template name provided in the URL
        if (isset($_GET["odt-template"])) {
            $this->template = $_GET["odt-template"];
        }

        // Template provided in the configuration
        if (!$this->template and $this->getConf("tpl_default")) {
            $this->template = $this->getConf("tpl_default");
        }

        if ($this->template) { // template chosen
            if (file_exists($conf['mediadir'].'/'.$this->getConf("tpl_dir")."/".$this->template)) { //template found
                $this->document_end_template();
            } else { // template chosen but not found : warn the user and use the default template
                $this->doc = '<text:p text:style-name="Text_20_body"><text:span text:style-name="Strong_20_Emphasis">'
                             .$this->_xmlEntities( sprintf($this->getLang('tpl_not_found'),$this->template,$this->getConf("tpl_dir")) )
                             .'</text:span></text:p>'.$this->doc;
                $this->document_end_scratch();
            }
        } else {
            $this->document_end_scratch();
        }
        $this->doc = $this->ZIP->get_file();
    }


    /**
     * Closes the document when not using a template
     */
    function document_end_scratch(){
        $autostyles = $this->_odtAutoStyles();
        $userfields = $this->_odtUserFields();

        // add defaults
        $this->ZIP->add_File('application/vnd.oasis.opendocument.text', 'mimetype', 0);

        $this->_odtMeta();
        $this->_odtSettings();

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
        $value .=   $this->doc;
        $value .=           '</office:text>';
        $value .=       '</office:body>';
        $value .=   '</office:document-content>';

        $this->ZIP->add_File($value,'content.xml');

        $value = io_readFile(DOKU_PLUGIN.'odt/styles.xml');

        // Add common styles.
        unset($common);
        foreach ($this->styles as $style) {
            $common .= $style;
        }
        $value = str_replace('</office:styles>', $common.'</office:styles>', $value);

        $value = str_replace('<office:automatic-styles/>', $autostyles, $value);
        $this->ZIP->add_File($value,'styles.xml');

        // build final manifest
        $this->_odtManifest();
    }

    /**
     * Closes the document using a template
     */
    function document_end_template(){
        global $conf, $ID; // for the temp dir

        // Temp dir
        if (is_dir($conf['tmpdir'])) {
            $temp_dir = $conf['tmpdir']; // version > 20070626
        } else {
            $temp_dir = $conf['savedir'].'/cache/tmp'; // version <= 20070626
        }
        $this->temp_dir = $temp_dir."/odt/".str_replace(':','-',$ID);
        if (is_dir($this->temp_dir)) { $this->io_rm_rf($this->temp_dir); }
        io_mkdir_p($this->temp_dir);

        // Extract template
        $template_path = $conf['mediadir'].'/'.$this->getConf("tpl_dir")."/".$this->template;
        $this->ZIP->Extract($template_path, $this->temp_dir);

        // Prepare content
        $autostyles = $this->_odtAutoStyles();
        $missingstyles = $this->_odtStyles();
        $missingfonts = $this->_odtFonts();
        $userfields = $this->_odtUserFields();

        // Insert content
        $old_content = io_readFile($this->temp_dir.'/content.xml');
        if (strpos($old_content, 'DOKUWIKI-ODT-INSERT') !== FALSE) { // Replace the mark
            $this->_odtReplaceInFile('/<text:p[^>]*>DOKUWIKI-ODT-INSERT<\/text:p>/', 
                $this->doc, $this->temp_dir.'/content.xml', true);
        } else { // Append to the template
            $this->_odtReplaceInFile('</office:text>', $this->doc.'</office:text>', $this->temp_dir.'/content.xml');
        }

        // Cut off unwanted content
        if (strpos($old_content, 'DOKUWIKI-ODT-CUT-START') !== FALSE 
                && strpos($old_content, 'DOKUWIKI-ODT-CUT-STOP') !== FALSE) {
            $this->_odtReplaceInFile('/DOKUWIKI-ODT-CUT-START.*DOKUWIKI-ODT-CUT-STOP/', 
                '', $this->temp_dir.'/content.xml', true);
        }

        // Insert userfields
        if (strpos($old_content, "text:user-field-decls") === FALSE) { // no existing userfields
            $this->_odtReplaceInFile('/<office:text([^>]*)>/U', '<office:text\1>'.$userfields, $this->temp_dir.'/content.xml', TRUE);
        } else {
            $this->_odtReplaceInFile('</text:user-field-decls>', substr($userfields,23), $this->temp_dir.'/content.xml');
        }

        // Insert styles & fonts
        $this->_odtReplaceInFile('</office:automatic-styles>', substr($autostyles, 25), $this->temp_dir.'/content.xml');
        $this->_odtReplaceInFile('</office:automatic-styles>', substr($autostyles, 25), $this->temp_dir.'/styles.xml');
        $this->_odtReplaceInFile('</office:styles>', $missingstyles.'</office:styles>', $this->temp_dir.'/styles.xml');
        $this->_odtReplaceInFile('</office:font-face-decls>', $missingfonts.'</office:font-face-decls>', $this->temp_dir.'/styles.xml');

        // Add manifest data
        $this->_odtReplaceInFile('</manifest:manifest>', $this->_odtGetManifest() . '</manifest:manifest>', $this->temp_dir . '/META-INF/manifest.xml');

        // Build the Zip
        $this->ZIP->Compress(null, $this->temp_dir, null);
        $this->io_rm_rf($this->temp_dir);
    }

    function _odtReplaceInFile($from, $to, $file, $regexp=FALSE) {
        $value = io_readFile($file);
        if ($regexp) {
            $value = preg_replace($from, $to, $value);
        } else {
            $value = str_replace($from, $to, $value);
        }
        $file_f = fopen($file, 'w');
        fwrite($file_f, $value);
        fclose($file_f);
    }

    /**
     * Recursively deletes a directory (equivalent to the "rm -rf" command)
     * Found in comments on http://www.php.net/rmdir
     */
    function io_rm_rf($f) {
        if (is_dir($f)) {
            foreach(glob($f.'/*') as $sf) {
                if (is_dir($sf) && !is_link($sf)) {
                    $this->io_rm_rf($sf);
                } else {
                    unlink($sf);
                }
            }
        } else { // avoid nasty consequenses if something wrong is given
            die("Error: not a directory - $f");
        }
        rmdir($f);
    }

    // not supported - use OpenOffice builtin tools instead!
    function render_TOC() { return ''; }

    function toc_additem($id, $text, $level) {}

    /**
     * Return autostyles initial content.
     * Uses page parameters defined in the class.
     *
     * @author LarsDW223
     */
    function _getInitAutoStyles (){
        $autostyles = array(
        "pm1"=>'
            <style:page-layout style:name="pm1">
                <style:page-layout-properties fo:page-width="'.$this->page_width.'cm" fo:page-height="'.$this->page_heigth.'cm" style:num-format="1" style:print-orientation="portrait" fo:margin-top="'.$this->margin_top.'cm" fo:margin-bottom="'.$this->margin_bottom.'cm" fo:margin-left="'.$this->margin_left.'cm" fo:margin-right="'.$this->margin_right.'cm" style:writing-mode="lr-tb" style:footnote-max-height="0cm">
                    <style:footnote-sep style:width="0.018cm" style:distance-before-sep="0.1cm" style:distance-after-sep="0.1cm" style:adjustment="left" style:rel-width="25%" style:color="#000000"/>
                </style:page-layout-properties>
                <style:header-style/>
                <style:footer-style/>
            </style:page-layout>',
        "sub"=>'
            <style:style style:name="sub" style:family="text">
                <style:text-properties style:text-position="-33% 80%"/>
            </style:style>',
        "sup"=>'
            <style:style style:name="sup" style:family="text">
                <style:text-properties style:text-position="33% 80%"/>
            </style:style>',
        "del"=>'
            <style:style style:name="del" style:family="text">
                <style:text-properties style:text-line-through-style="solid"/>
            </style:style>',
        "underline"=>'
            <style:style style:name="underline" style:family="text">
              <style:text-properties style:text-underline-style="solid"
                 style:text-underline-width="auto" style:text-underline-color="font-color"/>
            </style:style>',
        "media"=>'
            <style:style style:name="media" style:family="graphic" style:parent-style-name="Graphics">
                <style:graphic-properties style:run-through="foreground" style:wrap="parallel" style:number-wrapped-paragraphs="no-limit"
                   style:wrap-contour="false" style:vertical-pos="top" style:vertical-rel="baseline" style:horizontal-pos="left"
                   style:horizontal-rel="paragraph"/>
            </style:style>',
        "medialeft"=>'
            <style:style style:name="medialeft" style:family="graphic" style:parent-style-name="Graphics">
              <style:graphic-properties style:run-through="foreground" style:wrap="parallel" style:number-wrapped-paragraphs="no-limit"
                 style:wrap-contour="false" style:horizontal-pos="left" style:horizontal-rel="paragraph"/>
            </style:style>',
        "mediaright"=>'
            <style:style style:name="mediaright" style:family="graphic" style:parent-style-name="Graphics">
              <style:graphic-properties style:run-through="foreground" style:wrap="parallel" style:number-wrapped-paragraphs="no-limit"
                 style:wrap-contour="false" style:horizontal-pos="right" style:horizontal-rel="paragraph"/>
            </style:style>',
        "mediacenter"=>'
            <style:style style:name="mediacenter" style:family="graphic" style:parent-style-name="Graphics">
               <style:graphic-properties style:run-through="foreground" style:wrap="none" style:horizontal-pos="center"
                  style:horizontal-rel="paragraph"/>
            </style:style>',
        "tablealigncenter"=>'
            <style:style style:name="tablealigncenter" style:family="paragraph" style:parent-style-name="Table_20_Contents">
                <style:paragraph-properties fo:text-align="center"/>
            </style:style>',
        "tablealignright"=>'
            <style:style style:name="tablealignright" style:family="paragraph" style:parent-style-name="Table_20_Contents">
                <style:paragraph-properties fo:text-align="end"/>
            </style:style>',
        "tablealignleft"=>'
            <style:style style:name="tablealignleft" style:family="paragraph" style:parent-style-name="Table_20_Contents">
                <style:paragraph-properties fo:text-align="left"/>
            </style:style>',
        "tableheader"=>'
            <style:style style:name="tableheader" style:family="table-cell">
                <style:table-cell-properties fo:padding="0.05cm" fo:border-left="0.002cm solid #000000" fo:border-right="0.002cm solid #000000" fo:border-top="0.002cm solid #000000" fo:border-bottom="0.002cm solid #000000"/>
            </style:style>',
        "tablecell"=>'
            <style:style style:name="tablecell" style:family="table-cell">
                <style:table-cell-properties fo:padding="0.05cm" fo:border-left="0.002cm solid #000000" fo:border-right="0.002cm solid #000000" fo:border-top="0.002cm solid #000000" fo:border-bottom="0.002cm solid #000000"/>
            </style:style>',
        "legendcenter"=>'
            <style:style style:name="legendcenter" style:family="paragraph" style:parent-style-name="Illustration">
                <style:paragraph-properties fo:text-align="center"/>
            </style:style>', );
        return $autostyles;
    }

    /**
     * Return styles initial content.
     *
     * @author LarsDW223
     */
    function _getInitStyles (){
        $styles = array(
        "Source_20_Text"=>'
            <style:style style:name="Source_20_Text" style:display-name="Source Text" style:family="text">
                <style:text-properties style:font-name="Bitstream Vera Sans Mono" style:font-name-asian="Bitstream Vera Sans Mono" style:font-name-complex="Bitstream Vera Sans Mono"/>
            </style:style>',
        "Preformatted_20_Text"=>'
            <style:style style:name="Preformatted_20_Text" style:display-name="Preformatted Text" style:family="paragraph" style:parent-style-name="Standard" style:class="html">
                <style:paragraph-properties fo:margin-top="0cm" fo:margin-bottom="0.2cm"/>
                <style:text-properties style:font-name="Bitstream Vera Sans Mono" style:font-name-asian="Bitstream Vera Sans Mono" style:font-name-complex="Bitstream Vera Sans Mono"/>
            </style:style>',
        "Source_20_Code"=>'
            <style:style style:name="Source_20_Code" style:display-name="Source Code" style:family="paragraph" style:parent-style-name="Preformatted_20_Text">
                <style:paragraph-properties fo:padding="0.05cm" style:shadow="none" fo:border="0.002cm solid #8cacbb" fo:background-color="#f7f9fa"/>
            </style:style>',
        "Source_20_File"=>'
            <style:style style:name="Source_20_File" style:display-name="Source File" style:family="paragraph" style:parent-style-name="Preformatted_20_Text">
                <style:paragraph-properties fo:padding="0.05cm" style:shadow="none" fo:border="0.002cm solid #8cacbb" fo:background-color="#f1f4f5"/>
            </style:style>',
        "Horizontal_20_Line"=>'
            <style:style style:name="Horizontal_20_Line" style:display-name="Horizontal Line" style:family="paragraph" style:parent-style-name="Standard" style:next-style-name="Text_20_body" style:class="html">
                <style:paragraph-properties fo:margin-top="0cm" fo:margin-bottom="0.5cm" style:border-line-width-bottom="0.002cm 0.035cm 0.002cm" fo:padding="0cm" fo:border-left="none" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.04cm double #808080" text:number-lines="false" text:line-number="0" style:join-border="false"/>
                <style:text-properties fo:font-size="6pt" style:font-size-asian="6pt" style:font-size-complex="6pt"/>
            </style:style>',
        "Footnote"=>'
            <style:style style:name="Footnote" style:family="paragraph" style:parent-style-name="Standard" style:class="extra">
                <style:paragraph-properties fo:margin-left="0.5cm" fo:margin-right="0cm" fo:text-indent="-0.5cm" style:auto-text-indent="false" text:number-lines="false" text:line-number="0"/>
                <style:text-properties fo:font-size="10pt" style:font-size-asian="10pt" style:font-size-complex="10pt"/>
            </style:style>',
        "Emphasis"=>'
            <style:style style:name="Emphasis" style:family="text">
                <style:text-properties fo:font-style="italic" style:font-style-asian="italic" style:font-style-complex="italic"/>
            </style:style>',
        "Strong_20_Emphasis"=>'
            <style:style style:name="Strong_20_Emphasis" style:display-name="Strong Emphasis" style:family="text">
                <style:text-properties fo:font-weight="bold" style:font-weight-asian="bold" style:font-weight-complex="bold"/>
            </style:style>',);
        return $styles;
    }

    /**
     * Return total page width in centimeters
     * (margins are included)
     *
     * @author LarsDW223
     */
    function _getPageWidth(){
        return $this->page_width;
    }

    /**
     * Return total page heigth in centimeters
     * (margins are included)
     *
     * @author LarsDW223
     */
    function _getPageHeigth(){
        return $this->page_heigth;
    }

    /**
     * Return left margin in centimeters
     *
     * @author LarsDW223
     */
    function _getLeftMargin(){
        return $this->margin_left;
    }

    /**
     * Return right margin in centimeters
     *
     * @author LarsDW223
     */
    function _getRightMargin(){
        return $this->margin_right;
    }

    /**
     * Return top margin in centimeters
     *
     * @author LarsDW223
     */
    function _getTopMargin(){
        return $this->margin_top;
    }

    /**
     * Return bottom margin in centimeters
     *
     * @author LarsDW223
     */
    function _getBottomMargin(){
        return $this->margin_bottom;
    }

    /**
     * Return width percentage value if margins are taken into account.
     * Usually "100%" means 21cm in case of A4 format.
     * But usually you like to take care of margins. This function
     * adjusts the percentage to the value which should be used for margins.
     * So 100% == 21cm e.g. becomes 80.9% == 17cm (assuming a margin of 2 cm on both sides).
     *
     * @author LarsDW223
     */
    function _getRelWidthMindMargins ($percentage = '100'){
        $percentage *= $this->page_width - $this->margin_left - $this->margin_right;
        $percentage /= $this->page_width;
        return $percentage;
    }

    /**
     * Like _getRelWidthMindMargins but returns the absulute width
     * in centimeters.
     *
     * @author LarsDW223
     */
    function _getAbsWidthMindMargins ($percentage = '100'){
        $percentage *= $this->page_width - $this->margin_left - $this->margin_right;
        return ($percentage/100);
    }

    /**
     * Return heigth percentage value if margins are taken into account.
     * Usually "100%" means 29.7cm in case of A4 format.
     * But usually you like to take care of margins. This function
     * adjusts the percentage to the value which should be used for margins.
     * So 100% == 29.7cm e.g. becomes 86.5% == 25.7cm (assuming a margin of 2 cm on top and bottom).
     *
     * @author LarsDW223
     */
    function _getRelHeigthMindMargins ($percentage = '100'){
        $percentage *= $this->page_heigth - $this->margin_top - $this->margin_bottom;
        $percentage /= $this->page_heigth;
        return $percentage;
    }

    /**
     * Like _getRelHeigthMindMargins but returns the absulute width
     * in centimeters.
     *
     * @author LarsDW223
     */
    function _getAbsHeigthMindMargins ($percentage = '100'){
        $percentage *= $this->page_heigth - $this->margin_left - $this->margin_right;
        return ($percentage/100);
    }

    function _odtAutoStyles() {
        $value = '<office:automatic-styles>';
        foreach ($this->autostyles as $stylename=>$stylexml) {
            $value .= $stylexml;
        }
        $value .= '</office:automatic-styles>';
        return $value;
    }

    function _odtUserFields() {
        $value = '<text:user-field-decls>';
        foreach ($this->fields as $fname=>$fvalue) {
            $value .= '<text:user-field-decl office:value-type="string" text:name="'.$fname.'" office:string-value="'.$fvalue.'"/>';
        }
        $value .= '</text:user-field-decls>';
        return $value;
    }

    /* Add missing styles in the template */
    function _odtStyles() {
        $value = '';
        $existing_styles = io_readFile($this->temp_dir.'/styles.xml');
        foreach ($this->styles as $stylename=>$stylexml) {
            if (strpos($existing_styles, 'style:name="'.$stylename.'"') === FALSE) {
                $value .= $stylexml;
            }
        }
        // Loop on bullet/numerotation styles
        if (strpos($existing_styles, 'style:name="List_20_1"') === FALSE) {
            $value .= '<text:list-style style:name="List_20_1" style:display-name="List 1">';
            for ($i=1;$i<=10;$i++) {
                $value .= '<text:list-level-style-bullet text:level="'.$i.'" text:style-name="Numbering_20_Symbols" text:bullet-char="•">
                               <style:list-level-properties text:space-before="'.(0.4*($i-1)).'cm" text:min-label-width="0.4cm"/>
                               <style:text-properties style:font-name="StarSymbol"/>
                           </text:list-level-style-bullet>';
            }
            $value .= '</text:list-style>';
        }
        if (strpos($existing_styles, 'style:name="Numbering_20_1"') === FALSE) {
            $value .= '<text:list-style style:name="Numbering_20_1" style:display-name="Numbering 1">';
            for ($i=1;$i<=10;$i++) {
                $value .= '<text:list-level-style-number text:level="'.$i.'" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
                               <style:list-level-properties text:space-before="'.(0.5*($i-1)).'cm" text:min-label-width="0.5cm"/>
                           </text:list-level-style-number>';
            }
            $value .= '</text:list-style>';
        }
        return $value;
    }

    /* Add missing fonts in the template */
    function _odtFonts() {
        $value = '';
        $existing_styles = io_readFile($this->temp_dir.'/styles.xml');
        foreach ($this->fonts as $name=>$xml) {
            if (strpos($existing_styles, 'style:name="'.$name.'"') === FALSE) {
                $value .= $xml;
            }
        }
        return $value;
    }

    function cdata($text) {
        $this->doc .= $this->_xmlEntities($text);
    }

    function p_open($style='Text_20_body'){
        if (!$this->in_paragraph) { // opening a paragraph inside another paragraph is illegal
            $this->in_paragraph = true;
            $this->doc .= '<text:p text:style-name="'.$style.'">';
        }
    }

    function p_close(){
        if ($this->in_paragraph) {
            $this->in_paragraph = false;
            $this->doc .= '</text:p>';
        }
    }

    function header($text, $level, $pos){
        $hid = $this->_headerToLink($text,true);
        $this->doc .= '<text:h text:style-name="Heading_20_'.$level.'" text:outline-level="'.$level.'">';
        $this->doc .= '<text:bookmark-start text:name="'.$hid.'"/>';
        $this->doc .= $this->_xmlEntities($text);
        $this->doc .= '<text:bookmark-end text:name="'.$hid.'"/>';
        $this->doc .= '</text:h>';
    }

    function hr() {
        $this->doc .= '<text:p text:style-name="Horizontal_20_Line"/>';
    }

    function linebreak() {
        $this->doc .= '<text:line-break/>';
    }

    function strong_open() {
        $this->doc .= '<text:span text:style-name="Strong_20_Emphasis">';
    }

    function strong_close() {
        $this->doc .= '</text:span>';
    }

    function emphasis_open() {
        $this->doc .= '<text:span text:style-name="Emphasis">';
    }

    function emphasis_close() {
        $this->doc .= '</text:span>';
    }

    function underline_open() {
        $this->doc .= '<text:span text:style-name="underline">';
    }

    function underline_close() {
        $this->doc .= '</text:span>';
    }

    function monospace_open() {
        $this->doc .= '<text:span text:style-name="Source_20_Text">';
    }

    function monospace_close() {
        $this->doc .= '</text:span>';
    }

    function subscript_open() {
        $this->doc .= '<text:span text:style-name="sub">';
    }

    function subscript_close() {
        $this->doc .= '</text:span>';
    }

    function superscript_open() {
        $this->doc .= '<text:span text:style-name="sup">';
    }

    function superscript_close() {
        $this->doc .= '</text:span>';
    }

    function deleted_open() {
        $this->doc .= '<text:span text:style-name="del">';
    }

    function deleted_close() {
        $this->doc .= '</text:span>';
    }

    /*
     * Tables
     */
    function table_open($maxcols = NULL, $numrows = NULL){
        $this->doc .= '<table:table>';
        for($i=0; $i<$maxcols; $i++){
            $this->doc .= '<table:table-column />';
        }
    }

    function table_close(){
        $this->doc .= '</table:table>';
    }

    function tablerow_open(){
        $this->doc .= '<table:table-row>';
    }

    function tablerow_close(){
        $this->doc .= '</table:table-row>';

        // Reset temporary column counter
        $this->temp_column = 0;
    }

    function tableheader_open($colspan = 1, $align = "left", $rowspan = 1){
        $this->doc .= '<table:table-cell office:value-type="string" table:style-name="tableheader" ';
        //$this->doc .= ' table:style-name="tablealign'.$align.'"';
        if ( $colspan > 1 ) {
            $this->doc .= ' table:number-columns-spanned="'.$colspan.'"';
        }
        if ( $rowspan > 1 ) {
            $this->doc .= ' table:number-rows-spanned="'.$rowspan.'"';
        }
        $this->doc .= '>';
        $this->p_open('Table_20_Heading');
    }

    function tableheader_close(){
        $this->p_close();
        $this->doc .= '</table:table-cell>';
    }

    function tablecell_open($colspan = 1, $align = "left", $rowspan = 1){
        $this->doc .= '<table:table-cell office:value-type="string" table:style-name="tablecell" ';
        if ( $colspan > 1 ) {
            $this->doc .= ' table:number-columns-spanned="'.$colspan.'"';
        }
        if ( $rowspan > 1 ) {
            $this->doc .= ' table:number-rows-spanned="'.$rowspan.'"';
        }
        $this->doc .= '>';
        if (!$align) $align = "left";
        $style = "tablealign".$align;
        $this->p_open($style);
    }

    function tablecell_close(){
        $this->p_close();
        $this->doc .= '</table:table-cell>';
    }

    /**
     * Callback for footnote start syntax
     *
     * All following content will go to the footnote instead of
     * the document. To achieve this the previous rendered content
     * is moved to $store and $doc is cleared
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    function footnote_open() {

        // move current content to store and record footnote
        $this->store = $this->doc;
        $this->doc   = '';
    }

    /**
     * Callback for footnote end syntax
     *
     * All rendered content is moved to the $footnotes array and the old
     * content is restored from $store again
     *
     * @author Andreas Gohr
     */
    function footnote_close() {
        // recover footnote into the stack and restore old content
        $footnote = $this->doc;
        $this->doc = $this->store;
        $this->store = '';

        // check to see if this footnote has been seen before
        $i = array_search($footnote, $this->footnotes);

        if ($i === false) {
            $i = count($this->footnotes);
            // its a new footnote, add it to the $footnotes array
            $this->footnotes[$i] = $footnote;

            $this->doc .= '<text:note text:id="ftn'.$i.'" text:note-class="footnote">';
            $this->doc .= '<text:note-citation>'.($i+1).'</text:note-citation>';
            $this->doc .= '<text:note-body>';
            $this->doc .= '<text:p text:style-name="Footnote">';
            $this->doc .= $footnote;
            $this->doc .= '</text:p>';
            $this->doc .= '</text:note-body>';
            $this->doc .= '</text:note>';

        } else {
            // seen this one before - just reference it FIXME: style isn't correct yet
            $this->doc .= '<text:note-ref text:note-class="footnote" text:ref-name="ftn'.$i.'">'.($i+1).'</text:note-ref>';
        }
    }

    function listu_open() {
        $this->p_close();
        $this->doc .= '<text:list text:style-name="List_20_1">';
    }

    function listu_close() {
        $this->doc .= '</text:list>';
    }

    function listo_open() {
        $this->p_close();
        $this->doc .= '<text:list text:style-name="Numbering_20_1">';
    }

    function listo_close() {
        $this->doc .= '</text:list>';
    }

    function listitem_open($level) {
        $this->in_list_item = true;
        $this->doc .= '<text:list-item>';
    }

    function listitem_close() {
        $this->in_list_item = false;
        $this->doc .= '</text:list-item>';
    }

    function listcontent_open() {
        $this->doc .= '<text:p text:style-name="Text_20_body">';
    }

    function listcontent_close() {
        $this->doc .= '</text:p>';
    }

    function unformatted($text) {
        $this->doc .= $this->_xmlEntities($text);
    }

    function acronym($acronym) {
        $this->doc .= $this->_xmlEntities($acronym);
    }

    function smiley($smiley) {
        if ( array_key_exists($smiley, $this->smileys) ) {
            $src = DOKU_INC."lib/images/smileys/".$this->smileys[$smiley];
            $this->_odtAddImage($src);
        } else {
            $this->doc .= $this->_xmlEntities($smiley);
        }
    }

    function entity($entity) {
        # UTF-8 entity decoding is broken in PHP <5
        if (version_compare(phpversion(), "5.0.0") and array_key_exists($entity, $this->entities) ) {
            # decoding may fail for missing Multibyte-Support in entity_decode
            $dec = @html_entity_decode($this->entities[$entity],ENT_NOQUOTES,'UTF-8');
            if($dec){
                $this->doc .= $this->_xmlEntities($dec);
            }else{
                $this->doc .= $this->_xmlEntities($entity);
            }
        } else {
            $this->doc .= $this->_xmlEntities($entity);
        }
    }

    function multiplyentity($x, $y) {
        $this->doc .= $x.'×'.$y;
    }

    function singlequoteopening() {
        global $lang;
        $this->doc .= $lang['singlequoteopening'];
    }

    function singlequoteclosing() {
        global $lang;
        $this->doc .= $lang['singlequoteclosing'];
    }

    function apostrophe() {
        global $lang;
        $this->doc .= $lang['apostrophe'];
    }

    function doublequoteopening() {
        global $lang;
        $this->doc .= $lang['doublequoteopening'];
    }

    function doublequoteclosing() {
        global $lang;
        $this->doc .= $lang['doublequoteclosing'];
    }

    function php($text, $wrapper='dummy') {
        $this->monospace_open();
        $this->doc .= $this->_xmlEntities($text);
        $this->monospace_close();
    }
    function phpblock($text) {
        $this->file($text);
    }

    function html($text, $wrapper='dummy') {
        $this->monospace_open();
        $this->doc .= $this->_xmlEntities($text);
        $this->monospace_close();
    }
    function htmlblock($text) {
        $this->file($text);
    }

    /**
     * static call back to replace spaces
     */
    function _preserveSpace($matches){
        $spaces = $matches[1];
        $len    = strlen($spaces);
        return '<text:s text:c="'.$len.'"/>';
    }

    function preformatted($text) {
        $this->_preformatted($text);
    }

    function file($text, $language=null, $filename=null) {
        $this->_highlight('file', $text, $language);
    }

    function quote_open() {
        // Do not go higher than 5 because only 5 quotation styles are defined.
        if ( $this->quote_depth < 5 ) {
            $this->quote_depth++;
        }
        if ($this->quote_depth == 1) {
            // On quote level 1 open a new paragraph with 'Quotation 1' style
            $this->p_close();
            $this->quote_pos = strlen ($this->doc);
            $this->p_open('Quotation 1');
            $this->quote_pos = strpos ($this->doc, 'Quotation 1', $this->quote_pos);
            $this->quote_pos += strlen('Quotation 1') - 1;
        } else {
            // Quote level is greater than 1. Set new style by just changing the number.
            // This is possible because the styles in style.xml are named 'Quotation 1', 'Quotation 2'...
            $this->doc [$this->quote_pos] = $this->quote_depth;
        }
    }

    function quote_close() {
        if ( $this->quote_depth > 0 ) {
            $this->quote_depth--;
        }
        if ($this->quote_depth == 0 && $this->in_paragraph) { // only close the paragraph if we're actually in one
            $this->p_close();
        }
    }

    function code($text, $language=null, $filename=null) {
        $this->_highlight('code', $text, $language);
    }

    function _preformatted($text, $style="Preformatted_20_Text", $notescaped=true) {
        if ($notescaped) {
            $text = $this->_xmlEntities($text);
        }
        if (strpos($text, "\n") !== FALSE and strpos($text, "\n") == 0) {
            // text starts with a newline, remove it
            $text = substr($text,1);
        }
        $text = str_replace("\n",'<text:line-break/>',$text);
        $text = str_replace("\t",'<text:tab/>',$text);
        $text = preg_replace_callback('/(  +)/',array('renderer_plugin_odt','_preserveSpace'),$text);

        if ($this->in_list_item) { // if we're in a list item, we must close the <text:p> tag
            $this->doc .= '</text:p>';
            $this->doc .= '<text:p text:style-name="'.$style.'">';
            $this->doc .= $text;
            $this->doc .= '</text:p>';
            $this->doc .= '<text:p>';
        } else {
            $this->doc .= '<text:p text:style-name="'.$style.'">';
            $this->doc .= $text;
            $this->doc .= '</text:p>';
        }
    }

    function _highlight($type, $text, $language=null) {
        global $conf;
        $style_name = "Source_20_Code";
        if ($type == "file") $style_name = "Source_20_File";

        if (is_null($language)) {
            $this->_preformatted($text, $style_name);
            return;
        }

        // from inc/parserutils.php:p_xhtml_cached_geshi()
        require_once(DOKU_INC . 'inc/geshi.php');
        $geshi = new GeSHi($text, $language, DOKU_INC . 'inc/geshi');
        $geshi->set_encoding('utf-8');
        // $geshi->enable_classes(); DO NOT WANT !
        $geshi->set_header_type(GESHI_HEADER_PRE);
        $geshi->enable_keyword_links(false);

        // remove GeSHi's wrapper element (we'll replace it with our own later)
        // we need to use a GeSHi wrapper to avoid <BR> throughout the highlighted text
        $highlighted_code = trim(preg_replace('!^<pre[^>]*>|</pre>$!','',$geshi->parse_code()),"\n\r");
        // remove useless leading and trailing whitespace-newlines
        $highlighted_code = preg_replace('/^&nbsp;\n/','',$highlighted_code);
        $highlighted_code = preg_replace('/\n&nbsp;$/','',$highlighted_code);
        // replace styles
        $highlighted_code = str_replace("</span>", "</text:span>", $highlighted_code);
        $highlighted_code = preg_replace_callback('/<span style="([^"]+)">/', array('renderer_plugin_odt','_convert_css_styles'), $highlighted_code);
        // cleanup leftover span tags
        $highlighted_code = preg_replace('/<span[^>]*>/', "<text:span>", $highlighted_code);
        $highlighted_code = str_replace("&nbsp;", "&#xA0;", $highlighted_code);
        $this->_preformatted($highlighted_code, $style_name, false);
    }

    function _convert_css_styles($matches) {
        $all_css_styles = $matches[1];
        // parse the CSS attribute
        $css_styles = array();
        foreach(explode(";", $all_css_styles) as $css_style) {
            $css_style_array = explode(":", $css_style);
            if (!trim($css_style_array[0]) or !trim($css_style_array[1])) {
                continue;
            }
            $css_styles[trim($css_style_array[0])] = trim($css_style_array[1]);
        }
        // create the ODT xml style
        $style_name = "highlight." . $this->highlight_style_num;
        $this->highlight_style_num += 1;
        $style_content = '
            <style:style style:name="'.$style_name.'" style:family="text">
                <style:text-properties ';
        foreach($css_styles as $style_key=>$style_value) {
            // Hats off to those who thought out the OpenDocument spec: styling syntax is similar to CSS !
            $style_content .= 'fo:'.$style_key.'="'.$style_value.'" ';
        }
        $style_content .= '/>
            </style:style>';
        // add the style to the library
        $this->autostyles[$style_name] = $style_content;
        // now make use of the new style
        return '<text:span text:style-name="'.$style_name.'">';
    }

   function internalmedia ($src, $title=NULL, $align=NULL, $width=NULL,
                            $height=NULL, $cache=NULL, $linking=NULL) {
        global $conf;
        global $ID;
        resolve_mediaid(getNS($ID),$src, $exists);
        list($ext,$mime) = mimetype($src);

        if(substr($mime,0,5) == 'image'){
            $file = mediaFN($src);
            $this->_odtAddImage($file, $width, $height, $align, $title);
        }else{
/*
            // FIXME build absolute medialink and call externallink()
            $this->code('FIXME internalmedia: '.$src);
*/
            //FIX by EPO/Intersel - create a link to the dokuwiki internal resource
            if (empty($title)) {$title=explode(':',$src); $title=end($title);}
            $this->externalmedia(str_replace('doku.php?id=','lib/exe/fetch.php?media=',wl($src,'',true)),$title);
            //End of FIX
        }
    }
    function externalmedia ($src, $title=NULL, $align=NULL, $width=NULL,
                            $height=NULL, $cache=NULL, $linking=NULL) {
        global $conf;
        global $ID;
        list($ext,$mime) = mimetype($src);

        if(substr($mime,0,5) == 'image'){
            $tmp_dir = $conf['tmpdir']."/odt";
            $tmp_name = $tmp_dir."/".md5($src).'.'.$ext;
            $final_name = 'Pictures/'.md5($tmp_name).'.'.$ext;
            if(!isset($this->manifest[$final_name])){
                $client = new DokuHTTPClient;
                $img = $client->get($src);
                if ($img === FALSE) {
                    $tmp_name = $src; // fallback to a simple link
                } else {
                    if (!is_dir($tmp_dir)) io_mkdir_p($tmp_dir);
                    $tmp_img = fopen($tmp_name, "w") or die("Can't create temp file $tmp_img");
                    fwrite($tmp_img, $img);
                    fclose($tmp_img);
                }
            }
            $this->_odtAddImage($tmp_name, $width, $height, $align, $title);
            if (file_exists($tmp_name)) unlink($tmp_name);
        }else{
            $this->externallink($src,$title);
        }
    }

    function camelcaselink($link) {
        $this->internallink($link,$link);
    }

    function reference($id, $name = NULL) {
        $this->doc .= '<text:a xlink:type="simple" xlink:href="#'.$id.'"';
        if ($name) {
            $this->doc .= '>'.$this->_xmlEntities($name).'</text:a>';
        } else {
            $this->doc .= '/>';
        }
    }

    /**
     * Render an internal Wiki Link
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    function internallink($id, $name = NULL) {
        global $conf;
        global $ID;
        // default name is based on $id as given
        $default = $this->_simpleTitle($id);
        // now first resolve and clean up the $id
        resolve_pageid(getNS($ID),$id,$exists);
        $name = $this->_getLinkTitle($name, $default, $isImage, $id);

        // build the absolute URL (keeping a hash if any)
        list($id,$hash) = explode('#',$id,2);
        $url = wl($id,'',true);
        if($hash) $url .='#'.$hash;

        if ($ID == $id) {
            $this->reference($hash, $name);
        } else {
            $this->_doLink($url,$name);
        }
    }

    /**
     * Add external link
     */
    function externallink($url, $name = NULL) {
        global $conf;

        $name = $this->_getLinkTitle($name, $url, $isImage);

        $this->_doLink($url,$name);
    }

    /**
     * Just print local links
     *
     * @fixme add image handling
     */
    function locallink($hash, $name = NULL){
        $name  = $this->_getLinkTitle($name, $hash, $isImage);
        $this->doc .= $name;
    }

    /**
     * InterWiki links
     */
    function interwikilink($match, $name = NULL, $wikiName, $wikiUri) {
        $name  = $this->_getLinkTitle($name, $wikiUri, $isImage);
        $url = $this-> _resolveInterWiki($wikiName,$wikiUri);
        $this->_doLink($url,$name);
    }

    /**
     * Just print WindowsShare links
     *
     * @fixme add image handling
     */
    function windowssharelink($url, $name = NULL) {
        $name  = $this->_getLinkTitle($name, $url, $isImage);
        $this->doc .= $name;
    }

    /**
     * Just print email links
     *
     * @fixme add image handling
     */
    function emaillink($address, $name = NULL) {
        $name  = $this->_getLinkTitle($name, $address, $isImage);
        $this->_doLink("mailto:".$address,$name);
    }

    /**
     * Add a hyperlink, handling Images correctly
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    function _doLink($url,$name){
        $url = $this->_xmlEntities($url);
        if(is_array($name)){
            // Images
            if($url) $this->doc .= '<draw:a xlink:type="simple" xlink:href="'.$url.'">';

            if($name['type'] == 'internalmedia'){
                $this->internalmedia($name['src'],
                                     $name['title'],
                                     $name['align'],
                                     $name['width'],
                                     $name['height'],
                                     $name['cache'],
                                     $name['linking']);
            }

            if($url) $this->doc .= '</draw:a>';
        }else{
            // Text
            if($url) $this->doc .= '<text:a xlink:type="simple" xlink:href="'.$url.'">';
            $this->doc .= $name; // we get the name already XML encoded
            if($url) $this->doc .= '</text:a>';
        }
    }

    /**
     * Construct a title and handle images in titles
     *
     * @author Harry Fuecks <hfuecks@gmail.com>
     */
    function _getLinkTitle($title, $default, & $isImage, $id=null) {
        global $conf;

        $isImage = false;
        if ( is_null($title) ) {
            if ($conf['useheading'] && $id) {
                $heading = p_get_first_heading($id);
                if ($heading) {
                    return $this->_xmlEntities($heading);
                }
            }
            return $this->_xmlEntities($default);
        } else if ( is_string($title) ) {
            return $this->_xmlEntities($title);
        } else if ( is_array($title) ) {
            $isImage = true;
            return $title;
        }
    }

    /**
     * Creates a linkid from a headline
     *
     * @param string  $title   The headline title
     * @param boolean $create  Create a new unique ID?
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    function _headerToLink($title,$create=false) {
        $title = str_replace(':','',cleanID($title));
        $title = ltrim($title,'0123456789._-');
        if(empty($title)) $title='section';

        if($create){
            // make sure tiles are unique
            $num = '';
            while(in_array($title.$num,$this->headers)){
                ($num) ? $num++ : $num = 1;
            }
            $title = $title.$num;
            $this->headers[] = $title;
        }

        return $title;
    }


    function _xmlEntities($value) {
        return str_replace( array('&','"',"'",'<','>'), array('&#38;','&#34;','&#39;','&#60;','&#62;'), $value);
    }

    function rss ($url,$params){
        global $lang;
        global $conf;

        require_once(DOKU_INC.'inc/FeedParser.php');
        $feed = new FeedParser();
        $feed->feed_url($url);

        //disable warning while fetching
        if (!defined('DOKU_E_LEVEL')) { $elvl = error_reporting(E_ERROR); }
        $rc = $feed->init();
        if (!defined('DOKU_E_LEVEL')) { error_reporting($elvl); }

        //decide on start and end
        if($params['reverse']){
            $mod = -1;
            $start = $feed->get_item_quantity()-1;
            $end   = $start - ($params['max']);
            $end   = ($end < -1) ? -1 : $end;
        }else{
            $mod   = 1;
            $start = 0;
            $end   = $feed->get_item_quantity();
            $end   = ($end > $params['max']) ? $params['max'] : $end;;
        }

        $this->listu_open();
        if($rc){
            for ($x = $start; $x != $end; $x += $mod) {
                $item = $feed->get_item($x);
                $this->listitem_open(0);
                $this->listcontent_open();
                $this->externallink($item->get_permalink(),
                                    $item->get_title());
                if($params['author']){
                    $author = $item->get_author(0);
                    if($author){
                        $name = $author->get_name();
                        if(!$name) $name = $author->get_email();
                        if($name) $this->cdata(' '.$lang['by'].' '.$name);
                    }
                }
                if($params['date']){
                    $this->cdata(' ('.$item->get_date($conf['dformat']).')');
                }
                if($params['details']){
                    $this->cdata(strip_tags($item->get_description()));
                }
                $this->listcontent_close();
                $this->listitem_close();
            }
        }else{
            $this->listitem_open(0);
            $this->listcontent_open();
            $this->emphasis_open();
            $this->cdata($lang['rssfailed']);
            $this->emphasis_close();
            $this->externallink($url);
            $this->listcontent_close();
            $this->listitem_close();
        }
        $this->listu_close();
    }

    /**
     * Adds the content of $string as a SVG picture to the document.
     * The other parameters behave in the same way as in _odtAddImage.
     *
     * @author LarsDW223
     */
    function _addStringAsSVGImage($string, $width = NULL, $height = NULL, $align = NULL, $title = NULL, $style = NULL) {

        if ( empty($string) ) { return; }

        $ext  = '.svg';
        $mime = '.image/svg+xml';
        $name = 'Pictures/'.md5($string).'.'.$ext;
        if(!$this->manifest[$name]){
            $this->manifest[$name] = $mime;
            $this->ZIP->add_File($string, $name, 0);
        }

        // make sure width and height are available
        if (!$width || !$height) {
            list($width, $height) = $this->_odtGetImageSize($src, $width, $height);
        }

        if($align){
            $anchor = 'paragraph';
        }else{
            $anchor = 'as-char';
        }

        if (!$style or !array_key_exists($style, $this->autostyles)) {
            $style = 'media'.$align;
        }

        if ($title) {
            $this->doc .= '<draw:frame draw:style-name="'.$style.'" draw:name="'.$this->_xmlEntities($title).' Legend"
                            text:anchor-type="'.$anchor.'" draw:z-index="0" svg:width="'.$width.'">';
            $this->doc .= '<draw:text-box>';
            $this->doc .= '<text:p text:style-name="legendcenter">';
        }
        $this->doc .= '<draw:frame draw:style-name="'.$style.'" draw:name="'.$this->_xmlEntities($title).'"
                        text:anchor-type="'.$anchor.'" draw:z-index="0"
                        svg:width="'.$width.'" svg:height="'.$height.'" >';
        $this->doc .= '<draw:image xlink:href="'.$this->_xmlEntities($name).'"
                        xlink:type="simple" xlink:show="embed" xlink:actuate="onLoad"/>';
        $this->doc .= '</draw:frame>';
        if ($title) {
            $this->doc .= $this->_xmlEntities($title).'</text:p></draw:text-box></draw:frame>';
        }
    }

    /**
     * Adds the image $src as a picture file without adding it to the content
     * of the document. The link name which can be used for the ODT draw:image xlink:href
     * is returned. The caller is responsible for creating the frame and image tag
     * but therefore has full control over it. This means he can also set parameters
     * in the odt frame and image tag which can not be changed using the function _odtAddImage.
     *
     * @author LarsDW223
     */
    function _odtAddImageAsFileOnly($src){
        if (file_exists($src)) {
            list($ext,$mime) = mimetype($src);
            $name = 'Pictures/'.md5($src).'.'.$ext;
            if(!$this->manifest[$name]){
                $this->manifest[$name] = $mime;
                $this->ZIP->add_File(io_readfile($src,false),$name,0);
            }
        }
        return $name;
    }

    function _odtAddImage($src, $width = NULL, $height = NULL, $align = NULL, $title = NULL, $style = NULL){
        if (file_exists($src)) {
            list($ext,$mime) = mimetype($src);
            $name = 'Pictures/'.md5($src).'.'.$ext;
            if(!$this->manifest[$name]){
                $this->manifest[$name] = $mime;
                $this->ZIP->add_File(io_readfile($src,false),$name,0);
            }
        } else {
            $name = $src;
        }
        // make sure width and height are available
        if (!$width || !$height) {
            list($width, $height) = $this->_odtGetImageSize($src, $width, $height);
        }

        if($align){
            $anchor = 'paragraph';
        }else{
            $anchor = 'as-char';
        }

        if (!$style or !array_key_exists($style, $this->autostyles)) {
            $style = 'media'.$align;
        }

        if ($title) {
            $this->doc .= '<draw:frame draw:style-name="'.$style.'" draw:name="'.$this->_xmlEntities($title).' Legend"
                            text:anchor-type="'.$anchor.'" draw:z-index="0" svg:width="'.$width.'">';
            $this->doc .= '<draw:text-box>';
            $this->doc .= '<text:p text:style-name="legendcenter">';
        }
        $this->doc .= '<draw:frame draw:style-name="'.$style.'" draw:name="'.$this->_xmlEntities($title).'"
                        text:anchor-type="'.$anchor.'" draw:z-index="0"
                        svg:width="'.$width.'" svg:height="'.$height.'" >';
        $this->doc .= '<draw:image xlink:href="'.$this->_xmlEntities($name).'"
                        xlink:type="simple" xlink:show="embed" xlink:actuate="onLoad"/>';
        $this->doc .= '</draw:frame>';
        if ($title) {
            $this->doc .= $this->_xmlEntities($title).'</text:p></draw:text-box></draw:frame>';
        }
    }

    function _odtGetImageSize($src, $width = NULL, $height = NULL){
        if (file_exists($src)) {
            $info  = getimagesize($src);
            if(!$width){
                $width  = $info[0];
                $height = $info[1];
            }else{
                $height = round(($width * $info[1]) / $info[0]);
            }
        }

        // convert from pixel to centimeters
        if ($width) $width = (($width/96.0)*2.54);
        if ($height) $height = (($height/96.0)*2.54);

        if ($width && $height) {
            // Don't be wider than the page
            if ($width >= 17){ // FIXME : this assumes A4 page format with 2cm margins
                $width = $width.'cm"  style:rel-width="100%';
                $height = $height.'cm"  style:rel-height="scale';
            } else {
                $width = $width.'cm';
                $height = $height.'cm';
            }
        } else {
            // external image and unable to download, fallback
            if ($width) {
                $width = $width."cm";
            } else {
                $width = '" svg:rel-width="100%';
            }
            if ($height) {
                $height = $height."cm";
            } else {
                $height = '" svg:rel-height="100%';
            }
        }
        return array($width, $height);
    }

    /**
     * This function opens a new span using the style as set in the imported CSS $import.
     * So, the function requires the helper class 'helper_plugin_odt_cssimport'.
     * The CSS style is selected by the element type 'span' and the specified classes in $classes.
     * The property 'background-image' is not supported by an ODT span. This will be emulated
     * by inserting an image manually in the span. If the url from the CSS should be converted to
     * a local path, then the caller can specify a $baseURL. The full path will then be $baseURL/background-image.
     *
     * This function calls _odtSpanOpenUseProperties. See the function description for supported properties.
     *
     * The span should be closed by calling '_odtSpanClose'.
     *
     * @author LarsDW223
     */
    function _odtSpanOpenUseCSS(helper_plugin_odt_cssimport $import, $classes, $baseURL = NULL, $element = NULL){
        $properties = array();
        if ( empty($element) === true ) {
            $element = 'span';
        }
        $this->_processCSSClass ($properties, $import, $classes, $baseURL, $element);
        $this->_odtSpanOpenUseProperties($properties);
    }

    /**
     * This function opens a new span using the style as specified in $style.
     * The property 'background-image' is not supported by an ODT span. This will be emulated
     * by inserting an image manually in the span. If the url from the CSS should be converted to
     * a local path, then the caller can specify a $baseURL. The full path will then be $baseURL/background-image.
     *
     * This function calls _odtSpanOpenUseProperties. See the function description for supported properties.
     *
     * The span should be closed by calling '_odtSpanClose'.
     *
     * @author LarsDW223
     */
    function _odtSpanOpenUseCSSStyle($style, $baseURL = NULL){
        $properties = array();
        $this->_processCSSStyle ($properties, $style, $baseURL);
        $this->_odtSpanOpenUseProperties($properties);
    }

    /**
     * This function opens a new span using the style as set in the assoziative array $properties.
     * The parameters in the array should be named as the CSS property names e.g. 'color' or 'background-color'.
     * The property 'background-image' is not supported by an ODT span. This will be emulated
     * by inserting an image manually in the span.
     *
     * background-color, color, font-style, font-weight, font-size, border, font-family, font-variant, letter-spacing,
     * vertical-align, background-image (emulated)
     *
     * The span should be closed by calling '_odtSpanClose'.
     *
     * @author LarsDW223
     */
    function _odtSpanOpenUseProperties($properties){
        $disabled = array ();

        $odt_bg = $properties ['background-color'];
        $picture = $properties ['background-image'];

        if ( empty ($picture) === false ) {
            $this->style_count++;

            // If a picture/background-image is set, than we insert it manually here.
            // This is a workaround because ODT does not support the background-image attribute in a span.

            // Define graphic style for picture
            $style_name = 'odt_auto_style_span_graphic_'.$this->style_count;
            $image_style = '<style:style style:name="'.$style_name.'" style:family="graphic" style:parent-style-name="Graphics"><style:graphic-properties style:vertical-pos="middle" style:vertical-rel="text" style:horizontal-pos="from-left" style:horizontal-rel="paragraph" fo:background-color="'.$odt_bg.'" style:flow-with-text="true"></style:graphic-properties></style:style>';

            // Add style and image to our document
            $this->autostyles[$style_name] = $image_style;
            $this->_odtAddImage ($picture,NULL,NULL,NULL,NULL,$style_name);
        }

        // Create a text style for our span
        $disabled ['background-image'] = 1;
        $style_name = $this->_createTextStyle ($properties, $disabled);

        // Open span
        $this->doc .= '<text:span text:style-name="'.$style_name.'">';
    }

    /**
     * This function closes a span (previously opened with _odtSpanOpenUseCSS).
     *
     * @author LarsDW223
     */
    function _odtSpanClose(){
        $this->doc .= '</text:span>';
    }

    /**
     * This function opens a new paragraph using the style as set in the imported CSS $import.
     * So, the function requires the helper class 'helper_plugin_odt_cssimport'.
     * The CSS style is selected by the element type 'p' and the specified classes in $classes.
     * The property 'background-image' is emulated by inserting an image manually in the paragraph.
     * If the url from the CSS should be converted to a local path, then the caller can specify a $baseURL.
     * The full path will then be $baseURL/background-image.
     *
     * This function calls _odtParagraphOpenUseProperties. See the function description for supported properties.
     *
     * The span should be closed by calling '_odtParagraphClose'.
     *
     * @author LarsDW223
     */
    function _odtParagraphOpenUseCSS(helper_plugin_odt_cssimport $import, $classes, $baseURL = NULL, $element = NULL){
        $properties = array();
        if ( empty($element) === true ) {
            $element = 'p';
        }
        $this->_processCSSClass ($properties, $import, $classes, $baseURL, $element);
        $this->_odtParagraphOpenUseProperties($properties);
    }

    /**
     * This function opens a new paragraph using the style as specified in $style.
     * The property 'background-image' is emulated by inserting an image manually in the paragraph.
     * If the url from the CSS should be converted to a local path, then the caller can specify a $baseURL.
     * The full path will then be $baseURL/background-image.
     *
     * This function calls _odtParagraphOpenUseProperties. See the function description for supported properties.
     *
     * The paragraph must be closed by calling 'p_close'.
     *
     * @author LarsDW223
     */
    function _odtParagraphOpenUseCSSStyle($style, $baseURL = NULL){
        $properties = array();
        $this->_processCSSStyle ($properties, $style, $baseURL);
        $this->_odtParagraphOpenUseProperties($properties);
    }

    /**
     * This function opens a new paragraph using the style as set in the assoziative array $properties.
     * The parameters in the array should be named as the CSS property names e.g. 'color' or 'background-color'.
     * The property 'background-image' is emulated by inserting an image manually in the paragraph.
     *
     * The currently supported properties are:
     * background-color, color, font-style, font-weight, font-size, border, font-family, font-variant, letter-spacing,
     * vertical-align, line-height, background-image (emulated)
     *
     * The paragraph must be closed by calling 'p_close'.
     *
     * @author LarsDW223
     */
    function _odtParagraphOpenUseProperties($properties){
        $disabled = array ();

        if ($this->in_paragraph) {
            // opening a paragraph inside another paragraph is illegal
            return;
        }
        $this->in_paragraph = true;

        $odt_bg = $properties ['background-color'];
        $picture = $properties ['background-image'];

        if ( empty ($picture) === false ) {
            // If a picture/background-image is set, than we insert it manually here.
            // This is a workaround because ODT background-image works different than in CSS.

            // Define graphic style for picture
            $this->style_count++;
            $style_name = 'odt_auto_style_span_graphic_'.$this->style_count;
            $image_style = '<style:style style:name="'.$style_name.'" style:family="graphic" style:parent-style-name="Graphics"><style:graphic-properties style:vertical-pos="middle" style:vertical-rel="text" style:horizontal-pos="from-left" style:horizontal-rel="paragraph" fo:background-color="'.$odt_bg.'" style:flow-with-text="true"></style:graphic-properties></style:style>';

            // Add style and image to our document
            $this->autostyles[$style_name] = $image_style;
            $this->_odtAddImage ($picture,NULL,NULL,NULL,NULL,$style_name);
        }

        // Create the style for the paragraph.
        $disabled ['background-image'] = 1;
        $style_name = $this->_createParagraphStyle ($properties, $disabled);

        // Open a paragraph
        $this->doc .= '<text:p text:style-name="'.$style_name.'">';
    }

    /**
     * This function opens a div. As divs are not supported by ODT, it will be exported as a frame.
     * To be more precise, to frames will be created. One including a picture nad the other including the text.
     * A picture frame will only be created if a 'background-image' is set in the CSS style.
     *
     * The currently supported CSS properties are:
     * background-color, color, padding, margin, display, border-radius, min-height.
     * The background-image is simulated using a picture frame.
     * FIXME: Find a way to successfuly use the background-image in the graphic style (see comments).
     *
     * The div should be closed by calling '_odtDivCloseAsFrame'.
     *
     * @author LarsDW223
     */
    function _odtDivOpenAsFrameUseCSS (helper_plugin_odt_cssimport $import, $classes, $baseURL = NULL, $element = NULL) {
        $properties = array();

        $this->div_z_index += 5;
        $this->style_count++;

        if ( empty($element) === true ) {
            $element = 'div';
        }

        $import->getPropertiesForElement($properties, $element, $classes);
        foreach ($properties as $property => $value) {
            $properties [$property] = $import->adjustValueForODT ($value, 14);
        }
        $odt_bg = $properties ['background-color'];
        $odt_fo = $properties ['color'];
        $padding_left = $properties ['padding-left'];
        $padding_right = $properties ['padding-right'];
        $padding_top = $properties ['padding-top'];
        $padding_bottom = $properties ['padding-bottom'];
        $margin_left = $properties ['margin-left'];
        $margin_right = $properties ['margin-right'];
        $margin_top = $properties ['margin-top'];
        $margin_bottom = $properties ['margin-bottom'];
        $display = $properties ['display'];
        $fo_border = $properties ['border'];
        $radius = $properties ['border-radius'];
        $picture = $properties ['background-image'];
        $pic_positions = preg_split ('/\s/', $properties ['background-position']);

        $min_height = $properties ['min-height'];

        if ( empty ($picture) === false ) {
            // If a picture/background-image is set in the CSS, than we insert it manually here.
            // This is a workaround because ODT does not support the background-image attribute in a span.

            if ( empty ($baseURL) === false) {
                // Replace 'url(...)' with $baseURL
                $picture = $import->replaceURLPrefix ($picture, $baseURL);
            }
            $pic_link=$this->_odtAddImageAsFileOnly($picture);
            list($pic_width, $pic_height) = $this->_odtGetImageSize($picture);
        }

        $horiz_pos = 'center';
        $width = 100;
        $width_abs = $this->_getAbsWidthMindMargins ($width);

        // Add our styles.
        $style_name = 'odt_auto_style_div_'.$this->style_count;

        $style =
         '<style:style style:name="'.$style_name.'_text_frame" style:family="graphic">
             <style:graphic-properties svg:stroke-color="'.$odt_bg.'"
                 draw:fill="solid" draw:fill-color="'.$odt_bg.'"
                 draw:textarea-horizontal-align="left"
                 draw:textarea-vertical-align="center"
                 style:horizontal-pos="'.$horiz_pos.'" fo:background-color="'.$odt_bg.'" style:background-transparency="100%" ';
        if ( empty($padding_left) === false ) {
            $style .= 'fo:padding-left="'.$padding_left.'" ';
        }
        if ( empty($padding_right) === false ) {
            $style .= 'fo:padding-right="'.$padding_right.'" ';
        }
        if ( empty($padding_top) === false ) {
            $style .= 'fo:padding-top="'.$padding_top.'" ';
        }
        if ( empty($padding_bottom) === false ) {
            $style .= 'fo:padding-bottom="'.$padding_bottom.'" ';
        }
        if ( empty($margin_left) === false ) {
            $style .= 'fo:margin-left="'.$margin_left.'" ';
        }
        if ( empty($margin_right) === false ) {
            $style .= 'fo:margin-right="'.$margin_right.'" ';
        }
        if ( empty($margin_top) === false ) {
            $style .= 'fo:margin-top="'.$margin_top.'" ';
        }
        if ( empty($margin_bottom) === false ) {
            $style .= 'fo:margin-bottom="'.$margin_bottom.'" ';
        }
        if ( empty ($fo_border) === false ) {
            $style .= 'fo:border="'.$fo_border.'" ';
        }
        $style .= 'fo:min-height="'.$min_height.'"
                 style:rel-width="'.$width.'%"
                 style:wrap="none"';
        $style .= '>';

        // FIXME: Delete the part below 'if ( $picture != NULL ) {...}'
        // and use this background-image definition. For some reason the background-image is not displayed.
        // Help is welcome.
        /*$style .= '<style:background-image ';
        $style .= 'xlink:href="'.$pic_link.'" xlink:type="simple" xlink:actuate="onLoad"
                   style:position="center center" style:repeat="no-repeat" draw:opacity="100%"/>';*/
        $style .= '</style:graphic-properties>';
        $style .= '</style:style>';
        $style .= '<style:style style:name="'.$style_name.'_image_frame" style:family="graphic">
             <style:graphic-properties svg:stroke-color="'.$odt_bg.'"
                 draw:fill="none" draw:fill-color="'.$odt_bg.'"
                 draw:textarea-horizontal-align="left"
                 draw:textarea-vertical-align="center"
                 style:wrap="none"/>
         </style:style>
         <style:style style:name="'.$style_name.'_text_box" style:family="paragraph">
             <style:text-properties fo:color="'.$odt_fo.'"/>
             <style:paragraph-properties
              fo:margin-left="'.$padding_left.'pt" fo:margin-right="10pt" fo:text-indent="0cm"/>
         </style:style>';
        $this->autostyles[$style_name] = $style;

        // Group the frame so that they are stacked one on each other.
        $this->p_close();
        $this->doc .= '<text:p>';
        if ( $display == NULL ) {
            $this->doc .= '<draw:g>';
        } else {
            $this->doc .= '<draw:g draw:display="' . $display . '">';
        }

        // Draw a frame with the image in it, if required.
        // FIXME: delete this part if 'background-image' in graphic style is working.
        if ( $picture != NULL )
        {
            $this->doc .= '<draw:frame draw:style-name="'.$style_name.'_image_frame" draw:name="Bild1"
                                text:anchor-type="paragraph"
                                svg:x="'.$pic_positions [0].'" svg:y="'.$pic_positions [0].'"
                                svg:width="'.$pic_width.'" svg:height="'.$pic_height.'"
                                draw:z-index="'.($this->div_z_index + 1).'">
                               <draw:image xlink:href="'.$pic_link.'"
                                xlink:type="simple" xlink:show="embed" xlink:actuate="onLoad"/>
                                </draw:frame>';
        }

        // Draw a frame with a text box in it. the text box will be left opened
        // to grow with the content (requires fo:min-height in $style_name).
        $this->doc .= '<draw:frame draw:style-name="'.$style_name.'_text_frame" draw:name="Bild1"
                            text:anchor-type="paragraph"
                            svg:x="0cm" svg:y="0cm"
                            svg:width="'.$width_abs.'cm" svg:height="10cm" ';
        $this->doc .= 'draw:z-index="'.($this->div_z_index + 0).'">';
        $this->doc .= '<draw:text-box ';

        // If required use round corners.
        if ( empty($radius) === false )
            $this->doc .= 'draw:corner-radius="'.$radius.'" ';

        $this->doc .= '>';
        //$this->p_open($style_name.'_text_box');
    }

    /**
     * This function closes a div/frame (previously opened with _odtDivOpenAsFrameUseCSS).
     *
     * @author LarsDW223
     */
    function _odtDivCloseAsFrame () {
        //$this->p_close();
        $this->doc .= '</draw:text-box></draw:frame>';
        $this->doc .= '</draw:g>';
        $this->doc .= '</text:p>';

        $this->div_z_index -= 5;
    }

    /**
     * This function opens a new table using the style as set in the imported CSS $import.
     * So, the function requires the helper class 'helper_plugin_odt_cssimport'.
     * The CSS style is selected by the element type 'td' and the specified classes in $classes.
     *
     * This function calls _odtTableOpenUseProperties. See the function description for supported properties.
     *
     * The table should be closed by calling 'table_close()'.
     *
     * @author LarsDW223
     */
    function _odtTableOpenUseCSS(helper_plugin_odt_cssimport $import, $classes, $baseURL = NULL, $element = NULL, $maxcols = NULL, $numrows = NULL){
        $properties = array();
        if ( empty($element) === true ) {
            $element = 'table';
        }
        $this->_processCSSClass ($properties, $import, $classes, $baseURL, $element);
        $this->_odtTableOpenUseProperties($properties, $maxcols, $numrows);
    }

    /**
     * This function opens a new table using the style as specified in $style.
     *
     * This function calls _odtTableOpenUseProperties. See the function description for supported properties.
     *
     * The table should be closed by calling 'table_close()'.
     *
     * @author LarsDW223
     */
    function _odtTableOpenUseCSSStyle($style, $baseURL = NULL, $maxcols = NULL, $numrows = NULL){
        $properties = array();
        $this->_processCSSStyle ($properties, $style, $baseURL);
        $this->_odtTableOpenUseProperties($properties, $maxcols, $numrows);
    }

    /**
     * This function opens a new table using the style as set in the assoziative array $properties.
     * The parameters in the array should be named as the CSS property names e.g. 'width'.
     *
     * The currently supported properties are:
     * width, border-collapse, background-color
     *
     * The table must be closed by calling 'table_close'.
     *
     * @author LarsDW223
     */
    function _odtTableOpenUseProperties ($properties, $maxcols = NULL, $numrows = NULL){
        unset ($this->temp_content);

        // We are starting a new table header declaration.
        // The flag will be set to false on opening the first table cell of the body.
        $this->temp_in_header = true;

        // Create style.
        $style_name = $this->factory->createTableTableStyle ($style, $properties);
        $this->autostyles[$style_name] = $style;

        // Open the table referencing our style.
        $this->doc .= '<table:table table:style-name="'.$style_name.'">';

        // Delete any old values in the temporary column styles array.
        for ( $column = 0 ; $column < count($this->temp_table_column_styles) ; $column++ ) {
            unset($this->$temp_table_column_styles [$column]);
        }

        // Create columns with predefined and temporarily remembered style names.
        if ( empty ($maxcols) === true ) {
            // Try to automatically detect the number of columns.
            $this->temp_autocols = true;
            $this->doc .= '<ColumnsPlaceholder>';
            unset ($this->temp_cols);
        } else {
            $this->temp_autocols = false;

            for ( $column = 0 ; $column < $maxcols ; $column++ ) {
                $this->style_count++;
                $style_name = 'odt_auto_style_table_column_'.$this->style_count;
                $this->temp_table_column_styles [$column] = $style_name;

                $this->doc .= '<table:table-column table:style-name="'.$style_name.'"/>';
            }
        }

        // Reset temporary column counter
        $this->temp_column = 0;
        $this->temp_maxcols = 0;
    }

    function _odtTableHeaderOpenUseCSS(helper_plugin_odt_cssimport $import, $classes, $baseURL = NULL, $element = NULL, $colspan = 1, $rowspan = 1){
        $properties = array();
        if ( empty($element) === true ) {
            $element = 'th';
        }
        $this->_processCSSClass ($properties, $import, $classes, $baseURL, $element);
        $this->_odtTableHeaderOpenUseProperties($properties, $colspan, $rowspan);
    }

    function _odtTableHeaderOpenUseCSSStyle($style, $baseURL = NULL, $colspan = 1, $rowspan = 1){
        $properties = array();
        $this->_processCSSStyle ($properties, $style, $baseURL);
        $this->_odtTableHeaderOpenUseProperties($properties, $colspan, $rowspan);
    }

    function _odtTableHeaderOpenUseProperties ($properties = NULL, $colspan = 1, $rowspan = 1){
        // Open cell, second parameter MUST BE true to indicate we are in the header.
        $this->_odtTableCellOpenUsePropertiesInternal ($properties, true, $colspan, $rowspan);

        /*$this->doc .= '<table:table-cell office:value-type="string" table:style-name="tableheader" ';
        if ( $colspan > 1 ) {
            $this->doc .= ' table:number-columns-spanned="'.$colspan.'"';
        }
        if ( $rowspan > 1 ) {
            $this->doc .= ' table:number-rows-spanned="'.$rowspan.'"';
        }
        $this->doc .= '>';
        $this->p_open('Standard');*/

        // Overwrite/Create column style for actual column if $properties has any
        // meaningful params for a column-style (e.g. width).
        $style_name = $this->temp_table_column_styles [$this->temp_column];
        //$style_name = $this->_createTableColumnStyle ($properties, NULL, $style_name);
        $style_name = $this->factory->createTableColumnStyle ($style, $properties, NULL, $style_name);
        $this->autostyles[$style_name] = $style;
        $this->temp_column++;

        // Eventually add a new temp column if in auto mode
        if ( $this->temp_autocols === true ) {
            if ( $this->temp_column > $this->temp_maxcols ) {
                // Add temp column.
                $this->temp_cols .= '<table:table-column table:style-name="'.$style_name.'"/>';
                $this->temp_maxcols++;
            }
        }
    }

    /**
     * This function opens a new table row using the style as set in the imported CSS $import.
     * So, the function requires the helper class 'helper_plugin_odt_cssimport'.
     * The CSS style is selected by the element type 'td' and the specified classes in $classes.
     *
     * This function calls _odtTableRowOpenUseProperties. See the function description for supported properties.
     *
     * The row should be closed by calling 'tablerow_close()'.
     *
     * @author LarsDW223
     */
    function _odtTableRowOpenUseCSS(helper_plugin_odt_cssimport $import, $classes, $baseURL = NULL, $element = NULL){
        $properties = array();
        if ( empty($element) === true ) {
            $element = 'tr';
        }
        $this->_processCSSClass ($properties, $import, $classes, $baseURL, $element);
        $this->_odtTableRowOpenUseProperties($properties);

        // Reset temporary column counter
        $this->temp_column = 0;
    }

    /**
     * This function opens a new table row using the style as specified in $style.
     *
     * This function calls _odtTableRowOpenUseProperties. See the function description for supported properties.
     *
     * The row should be closed by calling 'tablerow_close()'.
     *
     * @author LarsDW223
     */
    function _odtTableRowOpenUseCSSStyle($style, $baseURL = NULL){
        $properties = array();
        $this->_processCSSStyle ($properties, $style, $baseURL);
        $this->_odtTableRowOpenUseProperties($properties);
    }

    function _odtTableRowOpenUseProperties ($properties){
        // Create style.
        $style_name = $this->factory->createTableRowStyle ($style, $properties);
        $this->autostyles[$style_name] = $style;

        // Open table row.
        $this->doc .= '<table:table-row table:style-name="'.$style_name.'">';
    }

    /**
     * This function opens a new table cell using the style as set in the imported CSS $import.
     * So, the function requires the helper class 'helper_plugin_odt_cssimport'.
     * The CSS style is selected by the element type 'td' and the specified classes in $classes.
     *
     * This function calls _odtTableCellOpenUseProperties. See the function description for supported properties.
     *
     * The cell should be closed by calling 'tablecell_close()'.
     *
     * @author LarsDW223
     */
    function _odtTableCellOpenUseCSS(helper_plugin_odt_cssimport $import, $classes, $baseURL = NULL, $element = NULL){
        $properties = array();
        if ( empty($element) === true ) {
            $element = 'td';
        }
        $this->_processCSSClass ($properties, $import, $classes, $baseURL, $element);
        $this->_odtTableCellOpenUseProperties($properties);
    }

    /**
     * This function opens a new table cell using the style as specified in $style.
     *
     * This function calls _odtTableCellOpenUseProperties. See the function description for supported properties.
     *
     * The cell should be closed by calling 'tablecell_close()'.
     *
     * @author LarsDW223
     */
    function _odtTableCellOpenUseCSSStyle($style, $baseURL = NULL){
        $properties = array();
        $this->_processCSSStyle ($properties, $style, $baseURL);
        $this->_odtTableCellOpenUseProperties($properties);
    }

    function _odtTableCellOpenUseProperties ($properties){
        $this->_odtTableCellOpenUsePropertiesInternal ($properties);
    }

    protected function _odtTableCellOpenUsePropertiesInternal ($properties, $inHeader = false, $colspan = 1, $rowspan = 1){
        $disabled = array ();

        if ( $inHeader === false ) {
            // Table header definition is finished.
            $this->temp_in_header = false;
        }

        // Writeback temporary table content if this is the first cell in the table body.
        if ( $inHeader === false && empty($this->temp_cols) === false) {                
            // First replace columns placeholder with created columns, if in auto mode.
            if ( $this->temp_autocols === true ) {
                $this->doc =
                    str_replace ('<ColumnsPlaceholder>', $this->temp_cols, $this->doc);

                $this->doc =
                    str_replace ('<MaxColsPlaceholder>', $this->temp_maxcols, $this->doc);

                unset ($this->temp_content);
                unset ($this->temp_cols);
                $this->temp_autocols = false;
            }
        }

        // Create style name. (Re-enable background-color!)
        $style_name = $this->factory->createTableCellStyle ($style, $properties);
        $this->autostyles[$style_name] = $style;

        // Create a paragraph style for the paragraph within the cell.
        // Disable properties that belong to the table cell style.
        $disabled ['border'] = 1;
        $disabled ['background-color'] = 1;
        $disabled ['background-image'] = 1;
        $disabled ['vertical-align'] = 1;
        $style_name_paragraph = $this->_createParagraphStyle ($properties, $disabled);

        // Open cell.
        //$this->doc .= '<table:table-cell table:style-name="'.$style_name.'">';

        $this->doc .= '<table:table-cell office:value-type="string" table:style-name="'.$style_name.'" ';
        if ( $colspan > 1 ) {
            $this->doc .= ' table:number-columns-spanned="'.$colspan.'"';
        }
        if ( $inHeader === true && $this->temp_autocols === true && $colspan == 0 ) {
            $this->doc .= ' table:number-columns-spanned="<MaxColsPlaceholder>"';
        }
        if ( $rowspan > 1 ) {
            $this->doc .= ' table:number-rows-spanned="'.$rowspan.'"';
        }
        $this->doc .= '>';

        // If a paragraph style was created, means text properties were set,
        // then we open a paragraph with our own style. Otherwise we use the standard style.
        if ( $style_name_paragraph != NULL ) {
            $this->p_open($style_name_paragraph);
        } else {
            $this->p_open();
        }
    }

    /**
     * Simple helper function for creating a style $name setting the specfied font size $size.
     *
     * @author LarsDW223
     */
    protected function _odtBuildSizeStyle ($name, $size) {
        $style = '<style:style style:name="'.$name.'" style:display-name="'.$name.'" style:family="text"><style:text-properties fo:font-size="'.$size.'" style:font-size-asian="'.$size.'" style:font-size-complex="'.$size.'"/></style:style>';
        return $style;
    }

    /**
     * This function creates a text style using the style as set in the assoziative array $properties.
     * The parameters in the array should be named as the CSS property names e.g. 'color' or 'background-color'.
     * Properties which shall not be used in the style can be disabled by setting the value in disabled_props
     * to 1 e.g. $disabled_props ['color'] = 1 would block the usage of the color property.
     *
     * The currently supported properties are:
     * background-color, color, font-style, font-weight, font-size, border, font-family, font-variant, letter-spacing,
     * vertical-align, background-image
     *
     * The function returns the name of the new style or NULL if all relevant properties are empty.
     *
     * @author LarsDW223
     */
    protected function _createTextStyle($properties, $disabled_props = NULL){
        $save = $disabled_props ['font-size'];

        if ( empty ($disabled_props ['font-size']) === true ) {
            $odt_fo_size = $properties ['font-size'];
        }
        unset ($parent);
        $length = strlen ($odt_fo_size);
        if ( $length > 0 && $odt_fo_size [$length-1] == '%' ) {
            // A font-size in percent is only supported in common style definitions, not in automatic
            // styles. Create a common style and set it as parent for this automatic style.
            $name = 'Size'.trim ($odt_fo_size, '%').'pc';
            $this->styles [$name] = $this->_odtBuildSizeStyle ($name, $odt_fo_size);
            $parent = $name;
        }

        $style_name = $this->factory->createTextStyle($style, $properties, $disabled_props, $parent);
        if ( $style_name == NULL ) {
            return NULL;
        }
        $this->autostyles[$style_name] = $style;

        $disabled_props ['font-size'] = $save;

        return $style_name;
    }

    /**
     * This function creates a paragraph style using. It uses the createParagraphStyle function
     * from the stylefactory helper class but takes care of the extra handling required for the
     * font-size attribute.
     *
     * The function returns the name of the new style or NULL if all relevant properties are empty.
     *
     * @author LarsDW223
     */
    protected function _createParagraphStyle($properties, $disabled_props = NULL){
        $save = $disabled_props ['font-size'];

        if ( empty ($disabled_props ['font-size']) === true ) {
            $odt_fo_size = $properties ['font-size'];
        }
        unset ($parent);
        $length = strlen ($odt_fo_size);
        if ( $length > 0 && $odt_fo_size [$length-1] == '%' ) {
            // A font-size in percent is only supported in common style definitions, not in automatic
            // styles. Create a common style and set it as parent for this automatic style.
            $name = 'Size'.trim ($odt_fo_size, '%').'pc';
            $this->styles [$name] = $this->_odtBuildSizeStyle ($name, $odt_fo_size);
            $parent = $name;
        }

        $style_name = $this->factory->createParagraphStyle($style, $properties, $disabled_props, $parent);
        if ( $style_name == NULL ) {
            return NULL;
        }
        $this->autostyles[$style_name] = $style;

        $disabled_props ['font-size'] = $save;

        return $style_name;
    }

    /**
     * This function processes the CSS declarations in $style and saves them in $properties
     * as key - value pairs, e.g. $properties ['color'] = 'red'. It also adjusts the values
     * for the ODT format and changes URLs to local paths if required, using $baseURL).
     *
     * @author LarsDW223
     */
    protected function _processCSSStyle(&$properties, $style, $baseURL = NULL){
        if ( $this->import == NULL ) {
            $this->import = new helper_plugin_odt_cssimport ();
            if ( $this->import == NULL ) {
                // Failed to create helper. Can't proceed.
                return;
            }
        }

        // Create rule with selector '*' (doesn't matter) and declarations as set in $style
        $rule = new css_rule ('*', $style);
        $rule->getProperties ($properties);
        foreach ($properties as $property => $value) {
            $properties [$property] = $this->import->adjustValueForODT ($value, 14);
        }

        if ( empty ($properties ['background-image']) === false ) {
            if ( empty ($baseURL) === false) {
                // Replace 'url(...)' with $baseURL
                $properties ['background-image'] = $this->import->replaceURLPrefix ($properties ['background-image'], $baseURL);
            }
        }
    }

    /**
     * This function examines the CSS properties for $classes and $element based on the data
     * in $import and saves them in $properties as key - value pairs, e.g. $properties ['color'] = 'red'.
     * It also adjusts the values for the ODT format and changes URLs to local paths if required, using $baseURL).
     *
     * @author LarsDW223
     */
    protected function _processCSSClass(&$properties, helper_plugin_odt_cssimport $import, $classes, $baseURL = NULL, $element = NULL){
        $import->getPropertiesForElement($properties, $element, $classes);
        foreach ($properties as $property => $value) {
            $properties [$property] = $import->adjustValueForODT ($value, 14);
        }

        if ( empty ($properties ['background-image']) === false ) {
            if ( empty ($baseURL) === false) {
                // Replace 'url(...)' with $baseURL
                $properties ['background-image'] = $import->replaceURLPrefix ($properties ['background-image'], $baseURL);
            }
        }
    }
}

//Setup VIM: ex: et ts=4 enc=utf-8 :
