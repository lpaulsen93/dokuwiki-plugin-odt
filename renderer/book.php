<?php
/**
 * ODT Plugin: Exports book consisting of more wikipages to ODT file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author Gerrit Uitslag <klapinklapin@gmail.com>
 */
// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

/**
 * The Book Renderer.
 * 
 * @package DokuWiki\Renderer\Book
 */
class renderer_plugin_odt_book extends renderer_plugin_odt_page {

    /** @var int number of wikipages exported with the ODT renderer */
    protected $wikipages_count = 0;
    /** @var string document title*/
    protected $title = '';
    /**
     * Stores action instance
     *
     * @var action_plugin_dw2pdf
     */
    private $actioninstance = null;

    /**
     * load action plugin instance
     */
    public function __construct() {
        parent::__construct();
        $this->actioninstance = plugin_load('action', 'odt_export');
    }

    /**
     * clean out any per-use values
     *
     * This is called before each use of the renderer object and should normally be used to
     * completely reset the state of the renderer to be reused for a new document.
     * For ODT book it resets only some properties.
     */
    public function reset() {
        $this->doc = '';
    }
    /**
     * Initialize the document
     */
    public function document_start() {
        global $ID;

        // number of wiki pages included in ODT file
        $this->wikipages_count ++;


        if($this->isBookStart()) {
            parent::document_start();
        } else {
            $this->pagebreak();
            $this->set_page_bookmark($ID);
        }
    }

    /**
     * Closes the document
     */
    public function document_end() {
        //ODT file creation is performed by finalize_ODTfile()

        // Refresh certain config parameters e.g. 'disable_links'
        // to switch links back to the configured value if they were enabled/disabled at some point
        $this->config->refresh();
    }

    /**
     * Completes the ODT file
     */
    public function finalize_ODTfile() {
        // FIXME remove last pagebreak
        // <text:p text:style-name="pagebreak"/>

        $this->document->setTitle($this->title);

        parent::finalize_ODTfile();
    }

    /**
     * Book start at the first page
     *
     * @return bool
     */
    public function isBookStart() {
        if($this->wikipages_count == 1) {
            return true;
        }
        return false;
    }

    /**
     * Set title for ODT document
     *
     * @param string $title
     */
    public function setTitle($title) {
        $this->title = $title;
    }

    /**
     * Render a wiki internal link.
     * In book export mode a local link with a name/test will be inserted if the
     * referenced page is included in the exported pages. Otherwise an external
     * link will be created.
     *
     * @param string       $id   page ID to link to. eg. 'wiki:syntax'
     * @param string|array $name name for the link, array for media file
     * @param bool         $returnonly whether to return odt or write to doc attribute
     *
     * @author Andreas Gohr <andi@splitbrain.org>, LarsDW223
     */
    function internallink($id, $name = NULL, $returnonly = false) {
        global $ID;
        // default name is based on $id as given
        $default = $this->_simpleTitle($id);
        // now first resolve and clean up the $id
        resolve_pageid(getNS($ID),$id,$exists);
        $name = $this->_getLinkTitle($name, $default, $isImage, $id);

        // build the absolute URL (keeping a hash if any)
        list($id,$hash) = explode('#',$id,2);

        // Is the link a link to a page included in the book?
        $pages = $this->actioninstance->getExportedPages();
        if ( in_array($id, $pages) ) {
            // Yes, create a local link with a name
            if($returnonly) {
                return $this->locallink_with_text($hash, $id, $name, $returnonly);
            } else {
                $this->locallink_with_text($hash, $id, $name, $returnonly);
            }
            return;
        }

        // No, create an external link
        $url = wl($id,'',true);
        if($hash) $url .='#'.$hash;

        if ($ID == $id) {
            if($returnonly) {
                return $this->locallink_with_text($hash, $id, $name, $returnonly);
            } else {
                $this->locallink_with_text($hash, $id, $name, $returnonly);
            }
        } else {
            if($returnonly) {
                return $this->_doLink($url, $name, $returnonly);
            } else {
                $this->_doLink($url, $name, $returnonly);
            }
        }
    }
}
