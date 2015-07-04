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
 * The Renderer
 */
class renderer_plugin_odt_book extends renderer_plugin_odt_page {

    /** @var int number of wikipages exported with the ODT renderer */
    protected $wikipages_count = 0;
    /** @var string document title*/
    protected $title = '';

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
        // number of wiki pages included in ODT file
        $this->wikipages_count ++;


        if($this->isBookStart()) {
            parent::document_start();
        } else {
            $this->pagebreak();
        }
    }

    /**
     * Closes the document
     */
    public function document_end() {
         //ODT file creation is performed by finalize_ODTfile()
    }

    /**
     * Completes the ODT file
     */
    public function finalize_ODTfile() {
        // FIXME remove last pagebreak
        // <text:p text:style-name="pagebreak"/>

        $this->meta->setTitle($this->title);

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
}
