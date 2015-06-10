<?php
/**
 * docHandler: Abstract class defining the interface for classes
 * which create the ODT document file/zip archive.
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

/**
 * The docHandler interface
 */
abstract class docHandler
{
    var $manifest;
    var $ZIP;

    /**
     * Constructor.
     */
    public function __construct() {
        // prepare the zipper, manifest
        $this->ZIP = new ZipLib();
        $this->manifest = new ODTManifest();
    }

    // Check if file exists.
    public function fileExists($name, $mime) {
        return $this->manifest->exists($name);
    }

    // Add a file to the document
    public function addFile($name, $mime, $content) {
        if(!$this->manifest->exists($name)){
            $this->manifest->add($name, $mime);
            $this->ZIP->add_File($content, $name, 0);
            return true;
        }

        // File with that name already exists!
        return false;
    }

    // Build ODT document.
    abstract public function build($doc=null, $autostyles=null, $commonstyles=null, $meta=null, $userfields=null, $styleset=null);

    // Get ODT document file.
    public function get() {
        return $this->ZIP->get_file();
    }
}

