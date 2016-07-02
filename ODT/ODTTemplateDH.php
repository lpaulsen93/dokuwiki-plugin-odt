<?php
/**
 * ODTTemplateDH: docHandler for creating a document from
 * an ODT template.
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

require_once DOKU_INC.'inc/io.php';
require_once DOKU_INC.'lib/plugins/odt/ODT/ODTmanifest.php';
require_once DOKU_INC.'lib/plugins/odt/ODT/docHandler.php';

/**
 * The ODT template document handler
 */
class ODTTemplateDH extends docHandler
{
    /**
     * Build the document from the template.
     * (code taken from old function 'document_end_scratch')
     *
     * @param ODTInternalParams $params
     * @param string      $meta
     * @param string      $userfields
     * @return mixed
     */
    public function build(ODTInternalParams $params, $meta=null, $userfields=null, $pagestyles=null, $template=NULL, $tempDir=NULL){
        // for the temp dir
        global $ID;

        if ($template == NULL || $tempDir == NULL) {
            return;
        }

        // Temp dir
        if (is_dir($tempDir)) { io_rmdir($tempDir,true); }
        io_mkdir_p($tempDir);

        // Extract template
        $ok = $params->ZIP->Extract($template, $tempDir);
        if($ok == -1){
            throw new Exception(' Error extracting the zip archive:'.$template_path.' to '.$tempDir);
        }

        // Import styles from ODT template        
        $params->styleset->importFromODTFile($tempDir.'/content.xml', 'office:automatic-styles', true);
        $params->styleset->importFromODTFile($tempDir.'/styles.xml', 'office:automatic-styles', true);
        $params->styleset->importFromODTFile($tempDir.'/styles.xml', 'office:styles', true);
        $test = $params->styleset->importFromODTFile($tempDir.'/styles.xml', 'office:master-styles', true);

        // Evtl. copy page format of first page to different style
        $first_master = $params->styleset->getStyleAtIndex ('office:master-styles', 0);
        if ($first_master != NULL &&
            $first_master->getProperty('style-page-layout-name') != $this->getStyleName('first page')) {
            // The master page of the template references a different page layout style
            // then used by us for the first page. Copy the page format settings.
            $source = $this->getStyle($this->getStyleName('first page'));
            $dest = $this->getStyle($first_master->getProperty('style-page-layout-name'));
            
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
            $this->_odtReplaceInFile('/<text:p[^>]*>DOKUWIKI-ODT-INSERT<\/text:p>/',
                $params->content, $tempDir.'/content.xml', true);
        } else { // Append to the template
            $this->_odtReplaceInFile('</office:text>', $params->content.'</office:text>', $tempDir.'/content.xml');
        }

        // Cut off unwanted content
        if (strpos($old_content, 'DOKUWIKI-ODT-CUT-START') !== FALSE
                && strpos($old_content, 'DOKUWIKI-ODT-CUT-STOP') !== FALSE) {
            $this->_odtReplaceInFile('/DOKUWIKI-ODT-CUT-START.*DOKUWIKI-ODT-CUT-STOP/',
                '', $tempDir.'/content.xml', true);
        }

        // Insert userfields
        if (strpos($old_content, "text:user-field-decls") === FALSE) { // no existing userfields
            $this->_odtReplaceInFile('/<office:text([^>]*)>/U', '<office:text\1>'.$userfields, $tempDir.'/content.xml', TRUE);
        } else {
            $this->_odtReplaceInFile('</text:user-field-decls>', substr($userfields,23), $tempDir.'/content.xml');
        }
        
        // Insert styles & fonts
        $value = io_readFile($tempDir.'/content.xml');
        $original = XMLUtil::getElement('office:automatic-styles', $value);
        $this->_odtReplaceInFile($original, $autostyles, $tempDir.'/content.xml');

        $value = io_readFile($tempDir.'/styles.xml');
        $original = XMLUtil::getElement('office:automatic-styles', $value);
        $this->_odtReplaceInFile($original, $autostyles, $tempDir.'/styles.xml');

        $value = io_readFile($tempDir.'/styles.xml');
        $original = XMLUtil::getElement('office:styles', $value);
        $this->_odtReplaceInFile($original, $commonstyles, $tempDir.'/styles.xml');

        $this->_odtReplaceInFile('</office:font-face-decls>', $missingfonts.'</office:font-face-decls>', $tempDir.'/styles.xml');

        // Insert page styles
        $page = '';
        foreach ($pagestyles as $name => $layout_name) {
            $page .= '<style:master-page style:name="'.$name.'" style:page-layout-name="'.$layout_name.'"/>';
        }
        if ( !empty($page) ) {
            $this->_odtReplaceInFile('</office:master-styles>', $page.'</office:master-styles>', $tempDir.'/styles.xml');
        }

        // Add manifest data
        $this->_odtReplaceInFile('</manifest:manifest>', $params->manifest->getExtraContent() . '</manifest:manifest>', $tempDir . '/META-INF/manifest.xml');

        // Build the Zip
        $params->ZIP->Compress(null, $tempDir, null);
        io_rmdir($tempDir,true);
    }

    /**
     * @param string $from
     * @param string $to
     * @param string $file
     * @param bool $regexp
     */
    protected function _odtReplaceInFile($from, $to, $file, $regexp=FALSE) {
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
