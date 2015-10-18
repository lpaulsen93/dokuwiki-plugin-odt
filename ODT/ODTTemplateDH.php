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
    protected $config = null;
    var $template = null;
    var $directory = null;
    protected $styleset = NULL;

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();

        // Load config
        $this->config = plugin_load('helper', 'odt_config');
        $this->config->load($warning);

        // Create styles.
        $this->styleset = new ODTDefaultStyles();
        $this->styleset->import();
    }

    /**
     * Set the template.
     *
     * @param string $template
     */
    public function setTemplate($template) {
        $this->template = $template;
    }

    /**
     * Set the template directory.
     *
     * @param string $directory
     */
    public function setDirectory($directory) {
        $this->directory = $directory;
    }

    /**
     * Build the document from the template.
     * (code taken from old function 'document_end_scratch')
     *
     * @param string      $doc
     * @param string      $autostyles
     * @param array       $commonstyles
     * @param string      $meta
     * @param string      $userfields
     * @param ODTDefaultStyles $styleset
     * @return mixed
     */
    public function build($doc=null, $meta=null, $userfields=null, $pagestyles=null){
        // for the temp dir
        global $ID;

        // Temp dir
        if (is_dir($this->config->getParam('tmpdir'))) {
            // version > 20070626
            $temp_dir = $this->config->getParam('tmpdir');
        } else {
            // version <= 20070626
            $temp_dir = $this->config->getParam('savedir').'/cache/tmp';
        }
        $temp_dir = $temp_dir."/odt/".str_replace(':','-',$ID);
        if (is_dir($temp_dir)) { io_rmdir($temp_dir,true); }
        io_mkdir_p($temp_dir);

        // Extract template
        $template_path = $this->config->getParam('mediadir').'/'.$this->directory."/".$this->template;
        $ok = $this->ZIP->Extract($template_path, $temp_dir);
        if($ok == -1){
            throw new Exception(' Error extracting the zip archive:'.$template_path.' to '.$temp_dir);
        }

        // Import styles from ODT template        
        $this->styleset->importFromODTFile($temp_dir.'/content.xml', 'office:automatic-styles', true);
        $this->styleset->importFromODTFile($temp_dir.'/styles.xml', 'office:automatic-styles', true);
        $this->styleset->importFromODTFile($temp_dir.'/styles.xml', 'office:styles', true);
        $test = $this->styleset->importFromODTFile($temp_dir.'/styles.xml', 'office:master-styles', true);

        // Evtl. copy page format of first page to different style
        $first_master = $this->styleset->getStyleAtIndex ('office:master-styles', 0);
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

        $autostyles = $this->styleset->export('office:automatic-styles');
        $commonstyles = $this->styleset->export('office:styles');
        $masterstyles = $this->styleset->export('office:master-styles');

        // Prepare content
        $missingfonts = $this->styleset->getMissingFonts($temp_dir.'/styles.xml');

        // Insert content
        $old_content = io_readFile($temp_dir.'/content.xml');
        if (strpos($old_content, 'DOKUWIKI-ODT-INSERT') !== FALSE) { // Replace the mark
            $this->_odtReplaceInFile('/<text:p[^>]*>DOKUWIKI-ODT-INSERT<\/text:p>/',
                $doc, $temp_dir.'/content.xml', true);
        } else { // Append to the template
            $this->_odtReplaceInFile('</office:text>', $doc.'</office:text>', $temp_dir.'/content.xml');
        }

        // Cut off unwanted content
        if (strpos($old_content, 'DOKUWIKI-ODT-CUT-START') !== FALSE
                && strpos($old_content, 'DOKUWIKI-ODT-CUT-STOP') !== FALSE) {
            $this->_odtReplaceInFile('/DOKUWIKI-ODT-CUT-START.*DOKUWIKI-ODT-CUT-STOP/',
                '', $temp_dir.'/content.xml', true);
        }

        // Insert userfields
        if (strpos($old_content, "text:user-field-decls") === FALSE) { // no existing userfields
            $this->_odtReplaceInFile('/<office:text([^>]*)>/U', '<office:text\1>'.$userfields, $temp_dir.'/content.xml', TRUE);
        } else {
            $this->_odtReplaceInFile('</text:user-field-decls>', substr($userfields,23), $temp_dir.'/content.xml');
        }
        
        // Insert styles & fonts
        $value = io_readFile($temp_dir.'/content.xml');
        $original = XMLUtil::getElement('office:automatic-styles', $value);
        $this->_odtReplaceInFile($original, $autostyles, $temp_dir.'/content.xml');

        $value = io_readFile($temp_dir.'/styles.xml');
        $original = XMLUtil::getElement('office:automatic-styles', $value);
        $this->_odtReplaceInFile($original, $autostyles, $temp_dir.'/styles.xml');

        $value = io_readFile($temp_dir.'/styles.xml');
        $original = XMLUtil::getElement('office:styles', $value);
        $this->_odtReplaceInFile($original, $commonstyles, $temp_dir.'/styles.xml');

        $this->_odtReplaceInFile('</office:font-face-decls>', $missingfonts.'</office:font-face-decls>', $temp_dir.'/styles.xml');

        // Insert page styles
        $page = '';
        foreach ($pagestyles as $name => $layout_name) {
            $page .= '<style:master-page style:name="'.$name.'" style:page-layout-name="'.$layout_name.'"/>';
        }
        if ( !empty($page) ) {
            $this->_odtReplaceInFile('</office:master-styles>', $page.'</office:master-styles>', $temp_dir.'/styles.xml');
        }

        // Add manifest data
        $this->_odtReplaceInFile('</manifest:manifest>', $this->manifest->getExtraContent() . '</manifest:manifest>', $temp_dir . '/META-INF/manifest.xml');

        // Build the Zip
        $this->ZIP->Compress(null, $temp_dir, null);
        io_rmdir($temp_dir,true);
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
