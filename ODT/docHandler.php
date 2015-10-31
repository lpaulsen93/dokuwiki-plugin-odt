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

    /**
     * Check if file exists.
     *
     * @param string $name
     * @return bool
     */
    public function fileExists($name) {
        return $this->manifest->exists($name);
    }

    /**
     * Add a file to the document
     *
     * @param string $name
     * @param string $mime
     * @param string $content
     * @return bool
     */
    public function addFile($name, $mime, $content) {
        if(!$this->manifest->exists($name)){
            $this->manifest->add($name, $mime);
            $this->ZIP->add_File($content, $name, 0);
            return true;
        }

        // File with that name already exists!
        return false;
    }

    /**
     * Adds the file $src as a picture file without adding it to the content.
     * Returns name of the file in the document for reference.
     *
     * @param string $src
     * @return string
     */
    function addFileAsPicture($src){
        $name = '';
        if (file_exists($src)) {
            list($ext,$mime) = mimetype($src);
            $name = 'Pictures/'.md5($src).'.'.$ext;
            $this->addFile($name, $mime, io_readfile($src,false));
        }
        return $name;
    }

    /**
     * Build ODT document.
     *
     * @param string      $doc
     * @param string      $autostyles
     * @param array       $commonstyles
     * @param string      $meta
     * @param string      $userfields
     * @param ODTStyleSet $styleset
     * @return mixed
     */
    abstract public function build($doc=null, $meta=null, $userfields=null, $pagestyles=null);

    /**
     * Get ODT document file.
     *
     * @return string
     */
    public function get() {
        return $this->ZIP->get_file();
    }

    /**
     * Each docHandler needs to provide a way to add a style to the document.
     *
     * @param $new The ODTStyle to add.
     */
    abstract public function addStyle(ODTStyle $new);

    /**
     * Each docHandler needs to provide a way to add an automatic style to the document.
     *
     * @param $new The ODTStyle to add.
     */
    abstract public function addAutomaticStyle(ODTStyle $new);

    /**
     * Each docHandler needs to provide a way to check if a style definition
     * already exists in the document.
     *
     * @param $name Name of the style to check
     * @return boolean
     */
    abstract public function styleExists ($name);

    /**
     * Each docHandler needs to provide a way to get a style definition's
     * object (if it exists).
     *
     * @param $name Name of the style
     * @return ODTStyle
     */
    abstract public function getStyle ($name);

    /**
     * The function returns the style names used for the basic syntax.
     */
    abstract public function getStyleName($style);
}
