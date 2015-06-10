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

require_once DOKU_INC.'inc/ZipLib.class.php';
require_once DOKU_INC.'lib/plugins/odt/ODT/ODTmanifest.php';
require_once DOKU_INC.'lib/plugins/odt/ODT/docHandler.php';

/**
 * The ODT template document handler
 */
class ODTTemplateDH extends docHandler
{
    var $template = null;

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Set the template.
     */
    public function setTemplate($template) {
        $this->template = $template;
    }

    /**
     * Build the document from the template.
     * (code taken from old function 'document_end_scratch')
     */
    public function build($doc=null, $autostyles=null, $commonstyles=null, $meta=null, $userfields=null, $styleset=null){
        // for the temp dir
        global $conf, $ID;

        // Temp dir
        if (is_dir($conf['tmpdir'])) {
            // version > 20070626
            $temp_dir = $conf['tmpdir'];
        } else {
            // version <= 20070626
            $temp_dir = $conf['savedir'].'/cache/tmp';
        }
        $temp_dir = $temp_dir."/odt/".str_replace(':','-',$ID);
        if (is_dir($temp_dir)) { $this->io_rm_rf($temp_dir); }
        io_mkdir_p($temp_dir);

        // Extract template
        $template_path = $conf['mediadir'].'/'.$this->getConf("tpl_dir")."/".$this->template;
        $this->ZIP->Extract($template_path, $temp_dir);

        // Prepare content
        $missingstyles = $styleset->getMissingStyles($temp_dir.'/styles.xml');
        $missingfonts = $styleset->getMissingFonts($temp_dir.'/styles.xml');

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
        $this->_odtReplaceInFile('</office:automatic-styles>', substr($autostyles, 25), $temp_dir.'/content.xml');
        $this->_odtReplaceInFile('</office:automatic-styles>', substr($autostyles, 25), $temp_dir.'/styles.xml');
        $this->_odtReplaceInFile('</office:styles>', $missingstyles.'</office:styles>', $temp_dir.'/styles.xml');
        $this->_odtReplaceInFile('</office:font-face-decls>', $missingfonts.'</office:font-face-decls>', $temp_dir.'/styles.xml');

        // Add manifest data
        $this->_odtReplaceInFile('</manifest:manifest>', $this->manifest->getExtraContent() . '</manifest:manifest>', $temp_dir . '/META-INF/manifest.xml');

        // Build the Zip
        $this->ZIP->Compress(null, $temp_dir, null);
        $this->io_rm_rf($temp_dir);
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
     * Recursively deletes a directory (equivalent to the "rm -rf" command)
     * Found in comments on http://www.php.net/rmdir
     */
    protected function io_rm_rf($f) {
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
}

