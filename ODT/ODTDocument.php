<?php

require_once DOKU_PLUGIN . 'odt/ODT/docHandler.php';
require_once DOKU_PLUGIN . 'odt/ODT/scratchDH.php';
require_once DOKU_PLUGIN . 'odt/ODT/ODTTemplateDH.php';
require_once DOKU_PLUGIN . 'odt/ODT/CSSTemplateDH.php';
require_once DOKU_PLUGIN . 'odt/ODT/ODTState.php';

/**
 * Main class/API for creating an ODTDocument.
 * 
 * Work in progress!!! Goals:
 * 
 * - Move all pure ODT specific code away from the ODT-DokuWiki
 *   renderer class in page.php/book.php
 * 
 * - Make the ODT DokuWiki renderer classes only call functions in this
 *   class directly to have a single class only which is seen/used by
 *   the renderer classes
 * 
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author  LarsDW223
 */
class ODTDocument
{
    // Public for now.
    // Will become protected as soon as all stuff using state
    // has been moved.
    public $state;
    /** @var docHandler */
    protected $docHandler = null;

    /**
     * Constructor:
     * - initializes the state
     * - creates the default docHandler
     */
    public function __construct() {
        // Initialize state
        $this->state = new ODTState();

        // Use standard handler, document from scratch.
        $this->docHandler = new scratchDH ();
    }

    /**
     * Set ODT template file.
     *
     * @param string $style_name The style to use.
     */
    public function setODTTemplate ($file, $directory) {
        // Document based on ODT template.
        $this->docHandler = new ODTTemplateDH ();
        $this->docHandler->setTemplate($file);
        $this->docHandler->setDirectory($directory);

        // Do NOT overwrite outline style of ODT template.
    }

    /**
     * Set CSS template file.
     *
     * @param string $style_name The style to use.
     */
    public function setCSSTemplate ($template_path, $media_sel, $mediadir) {
        // Document based on CSS template.
        $this->docHandler = new CSSTemplateDH ();
        $this->docHandler->import($template_path, $media_sel, $mediadir);
    }

    // Functions generating content for now will have to be passed
    // $renderer->doc. Later this will be removed and an internal doc
    // variable will be maintained. This will break backwards compatibility
    // with plugins writing to $renderer->doc directly (instead of calling cdata).

    /**
     * Open a text span.
     *
     * @param string $style_name The style to use.
     */
    function spanOpen($style_name, &$content){
        $span = new ODTElementSpan ($style_name);
        $this->state->enter($span);
        $content .= $span->getOpeningTag();
    }

    /**
     * Close a text span.
     *
     * @param string $style_name The style to use.
     */    
    function spanClose(&$content) {
        $this->closeCurrentElement($content);
    }

    /**
     * Get the style name for a style alias.
     *
     * @param string $alias The alias for the style.
     * @return string The style name used in the ODT document
     */    
    public function getStyleName($alias) {
        return $this->docHandler->getStyleName($alias);
    }

    /**
     * Get the style object with style name $styleName.
     *
     * @param string $styleName The style name ofthe style style.
     * @return ODTStyle The style object
     */    
    public function getStyle($styleName) {
        return $this->docHandler->getStyle($styleName);
    }

    /**
     * Get the style object by $alias.
     *
     * @param string $alias The alias for the style.
     * @return ODTStyle The style object
     */    
    public function getStyleByAlias($alias) {
        return $this->docHandler->getStyle($this->docHandler->getStyleName($alias));
    }

    /**
     * Add style object to the document as a common style.
     *
     * @param ODTStyle $style_obj Object to add
     */
    public function addStyle(ODTStyle $style_obj) {
        $this->docHandler->addStyle($style_obj);
    }

    /**
     * Add style object to the document as an automatic style.
     *
     * @param ODTStyle $style_obj Object to add
     */
    public function addAutomaticStyle(ODTStyle $style_obj) {
        $this->docHandler->addAutomaticStyle($style_obj);
    }

    /**
     * Check if a style with $styleName already exists.
     *
     * @param string $styleName The style name ofthe style style.
     * @return bool
     */    
    public function styleExists($styleName) {
        return $this->docHandler->styleExists($styleName);
    }

    /**
     * Add a file to the document.
     *
     * @param string $fileName Full file name in the document
     *                         e.g. 'Pictures/myimage.png'
     * @param string $mime Mime type
     * @param string $content The content of the file
     */    
    public function addFile($fileName, $mime, $content) {
        $this->docHandler->addFile($fileName, $mime, $content);
    }

    /**
     * Adds the image $fileName as a picture file without adding it to
     * the content of the document. The link name which can be used for
     * the ODT draw:image xlink:href is returned.
     *
     * @param string $fileName
     * @return string
     */
    function addFileAsPicture($fileName){
        return $this->docHandler->addFileAsPicture($fileName);
    }

    /**
     * Check if a file already exists in the document.
     *
     * @param string $fileName Full file name in the document
     *                         e.g. 'Pictures/myimage.png'
     * @return bool
     */    
    public function fileExists($fileName) {
        return $this->docHandler->fileExists($fileName);
    }

    /**
     * Get ODT file as string (ZIP archive).
     *
     * @param string $content The content
     * @param string $metaContent The content of the meta file
     * @param string $userFieldDecls The user field declarations
     * @param array $pageStyles Array of all page styles
     * @return string String containing ODT ZIP stream
     */
    public function getODTFileAsString($content, $metaContent, $userFieldDecls, array $pageStyles) {
        // Build the document
        $this->docHandler->build($content,
                                 $metaContent,
                                 $userFieldDecls,
                                 $pageStyles);

        // Return document
        return $this->docHandler->get();
    }

    /**
     * Import CSS code for styles from a string.
     *
     * @param string $cssCode The CSS code to import
     * @param string $mediaSel The media selector to use e.g. 'print'
     * @param string $mediaPath Local path to media files
     */
    public function importCSSFromString($cssCode, $mediaSel=NULL, $mediaPath) {
        $this->docHandler->import_css_from_string ($cssCode, $mediaSel, $mediaPath);
    }

    /**
     * General internal function for closing an element.
     * Can always be used to close any open element if no more actions
     * are required apart from generating the closing tag and
     * removing the element from the state stack.
     */
    protected function closeCurrentElement(&$content) {
        $current = $this->state->getCurrent();
        if ($current != NULL) {
            $content .= $current->getClosingTag($content);
            $this->state->leave();
        }
    }
}
