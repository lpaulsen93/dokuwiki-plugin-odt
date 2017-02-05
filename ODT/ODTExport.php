<?php

require_once DOKU_PLUGIN . 'odt/ODT/ODTDocument.php';
require_once DOKU_PLUGIN . 'odt/ODT/ODTsettings.php';

/**
 * ODTExport:
 * Class containing static code for exporting/generating the ODT file.
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 */
class ODTExport
{
    /**
     * Build the document from scratch.
     * (code taken from old function 'document_end_scratch')
     *
     * @param ODTInternalParams $params
     * @param string      $meta
     * @param string      $userfields
     * @return mixed
     */
    protected static function buildFromScratch(ODTInternalParams $params, $meta=null, $userfields=null, $pagestyles=null){
        // add defaults
        $settings = new ODTSettings();
        $params->ZIP->addData('mimetype', 'application/vnd.oasis.opendocument.text', 'mimetype');
        $params->ZIP->addData('meta.xml', $meta);
        $params->ZIP->addData('settings.xml', $settings->getContent());

        $autostyles = $params->styleset->export('office:automatic-styles');
        $commonstyles = $params->styleset->export('office:styles');
        $masterstyles = $params->styleset->export('office:master-styles');

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
        $value .=   $params->content;
        $value .=           '</office:text>';
        $value .=       '</office:body>';
        $value .=   '</office:document-content>';

        $params->ZIP->addData('content.xml', $value);

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
        $params->ZIP->addData('styles.xml', $value);

        // build final manifest
        $params->ZIP->addData('META-INF/manifest.xml', $params->manifest->getContent());
    }

    /**
     * Build the document from the template.
     * (code taken from old function 'document_end_scratch')
     *
     * @param ODTInternalParams $params
     * @param string      $meta
     * @param string      $userfields
     * @return mixed
     */
    protected static function buildFromODTTemplate(ODTInternalParams $params, $meta=null, $userfields=null, $pagestyles=null, $template=NULL, $tempDir=NULL){
        // for the temp dir
        global $ID;

        if ($template == NULL || $tempDir == NULL) {
            return;
        }

        // Temp dir
        if (is_dir($tempDir)) { io_rmdir($tempDir,true); }
        io_mkdir_p($tempDir);

        // Extract template
        try {
            $ZIPextract = new \splitbrain\PHPArchive\Zip();
            $ZIPextract->open($template);
            $ZIPextract->extract($tempDir);
            $ZIPextract->open($template);
            $templateContents = $ZIPextract->contents();
        } catch (\splitbrain\PHPArchive\ArchiveIOException $e) {
            throw new Exception(' Error extracting the zip archive:'.$template.' to '.$tempDir);
        }

        // Evtl. copy page format of first page to different style
        $first_master = $params->styleset->getStyleAtIndex ('office:master-styles', 0);
        if ($first_master != NULL &&
            $first_master->getProperty('style-page-layout-name') != $params->document->getStyleName('first page')) {
            // The master page of the template references a different page layout style
            // then used by us for the first page. Copy the page format settings.
            $source = $params->document->getStyle($params->document->getStyleName('first page'));
            $dest = $params->document->getStyle($first_master->getProperty('style-page-layout-name'));
            
            if ($source != NULL && $dest != NULL) {
                $dest->setProperty('width', $source->getProperty('width'));
                $dest->setProperty('height', $source->getProperty('height'));
                $dest->setProperty('margin-top', $source->getProperty('margin-top'));
                $dest->setProperty('margin-right', $source->getProperty('margin-right'));
                $dest->setProperty('margin-bottom', $source->getProperty('margin-bottom'));
                $dest->setProperty('margin-left', $source->getProperty('margin-left'));
            }
        }

        $autostyles = $params->styleset->export('office:automatic-styles');
        $commonstyles = $params->styleset->export('office:styles');
        $masterstyles = $params->styleset->export('office:master-styles');

        // Prepare content
        $missingfonts = $params->styleset->getMissingFonts($tempDir.'/styles.xml');

        // Insert content
        $old_content = io_readFile($tempDir.'/content.xml');
        if (strpos($old_content, 'DOKUWIKI-ODT-INSERT') !== FALSE) { // Replace the mark
            self::replaceInFile('/<text:p[^>]*>DOKUWIKI-ODT-INSERT<\/text:p>/',
                $params->content, $tempDir.'/content.xml', true);
        } else { // Append to the template
            self::replaceInFile('</office:text>', $params->content.'</office:text>', $tempDir.'/content.xml');
        }

        // Cut off unwanted content
        if (strpos($old_content, 'DOKUWIKI-ODT-CUT-START') !== FALSE
                && strpos($old_content, 'DOKUWIKI-ODT-CUT-STOP') !== FALSE) {
            self::replaceInFile('/DOKUWIKI-ODT-CUT-START.*DOKUWIKI-ODT-CUT-STOP/',
                '', $tempDir.'/content.xml', true);
        }

        // Insert userfields
        if (strpos($old_content, "text:user-field-decls") === FALSE) { // no existing userfields
            self::replaceInFile('/<office:text([^>]*)>/U', '<office:text\1>'.$userfields, $tempDir.'/content.xml', TRUE);
        } else {
            self::replaceInFile('</text:user-field-decls>', substr($userfields,23), $tempDir.'/content.xml');
        }
        
        // Insert styles & fonts
        $value = io_readFile($tempDir.'/content.xml');
        $original = XMLUtil::getElement('office:automatic-styles', $value);
        self::replaceInFile($original, $autostyles, $tempDir.'/content.xml');

        $value = io_readFile($tempDir.'/styles.xml');
        $original = XMLUtil::getElement('office:automatic-styles', $value);
        self::replaceInFile($original, $autostyles, $tempDir.'/styles.xml');

        $value = io_readFile($tempDir.'/styles.xml');
        $original = XMLUtil::getElement('office:styles', $value);
        self::replaceInFile($original, $commonstyles, $tempDir.'/styles.xml');

        self::replaceInFile('</office:font-face-decls>', $missingfonts.'</office:font-face-decls>', $tempDir.'/styles.xml');

        // Insert page styles
        $page = '';
        foreach ($pagestyles as $name => $layout_name) {
            $page .= '<style:master-page style:name="'.$name.'" style:page-layout-name="'.$layout_name.'"/>';
        }
        if ( !empty($page) ) {
            self::replaceInFile('</office:master-styles>', $page.'</office:master-styles>', $tempDir.'/styles.xml');
        }

        // Add manifest data
        self::replaceInFile('</manifest:manifest>', $params->manifest->getExtraContent() . '</manifest:manifest>', $tempDir . '/META-INF/manifest.xml');

        // Build the Zip
        foreach ($templateContents as $fileInfo) {
            if (!$fileInfo->getIsdir()) {
                $params->ZIP->addFile($tempDir.'/'.$fileInfo->getPath(), $fileInfo);
            }
        }
        io_rmdir($tempDir,true);
    }

    /**
     * Build the document from the template.
     * (code taken from old function 'document_end_scratch')
     *
     * @param ODTInternalParams $params
     * @param string      $meta
     * @param string      $userfields
     * @return mixed
     */
    public static function buildZIPFile(ODTInternalParams $params, $meta=null, $userfields=null, $pagestyles=null, $template=NULL, $tempDir=NULL){
        if ($template == NULL ) {
            self::buildFromScratch($params, $meta, $userfields, $pagestyles);
        } else {
            self::buildFromODTTemplate($params, $meta, $userfields, $pagestyles, $template, $tempDir);
        }
        $params->ZIP->close();
    }

    /**
     * @param string $from
     * @param string $to
     * @param string $file
     * @param bool $regexp
     */
    protected static function replaceInFile($from, $to, $file, $regexp=FALSE) {
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
}
