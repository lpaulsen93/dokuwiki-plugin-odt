<?php

require_once DOKU_PLUGIN . 'odt/ODT/docHandler.php';
require_once DOKU_PLUGIN . 'odt/ODT/scratchDH.php';
require_once DOKU_PLUGIN . 'odt/ODT/ODTTemplateDH.php';
require_once DOKU_PLUGIN . 'odt/ODT/CSSTemplateDH.php';
require_once DOKU_PLUGIN . 'odt/ODT/ODTState.php';
require_once DOKU_PLUGIN . 'odt/ODT/ODTUtility.php';
require_once DOKU_PLUGIN . 'odt/ODT/ODTList.php';
require_once DOKU_PLUGIN . 'odt/ODT/ODTFootnote.php';
require_once DOKU_PLUGIN . 'odt/ODT/ODTHeading.php';
require_once DOKU_PLUGIN . 'odt/ODT/ODTParagraph.php';
require_once DOKU_PLUGIN . 'odt/ODT/ODTTable.php';

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
    /** @var array store the table of contents */
    public $toc = array();
    /** @var Current pageFormat */
    public $page = null;
    /** @var changePageFormat */
    public $changePageFormat = NULL;

    /** @var docHandler */
    protected $docHandler = null;
    /** @var Array of used page styles. Will stay empty if only A4-portrait is used */
    protected $pageStyles = array ();
    /** @var Array of paragraph style names that prevent an empty paragraph from being deleted */
    protected $preventDeletetionStyles = array ();
    /** @var pagebreak */
    protected $pagebreak = false;
    /** @var headers */
    protected $headers = array();
    /** @var refIDCount */
    protected $refIDCount = 0;
    /** @var pageBookmark */
    protected $pageBookmark = NULL;
    /** @var array store the bookmarks */
    protected $bookmarks = array();
    /** @var string temporary storage of xml-content */
    public $store = '';
    /** @var array */
    public $footnotes = array();

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
     * @param string $styleName The style to use.
     */
    function spanOpen($styleName, &$content){
        $span = new ODTElementSpan ($styleName);
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
     * Open a paragraph
     *
     * @param string $styleName The style to use.
     */
    function paragraphOpen($styleName=NULL, &$content){
        ODTParagraph::paragraphOpen($this, $styleName, $content);
    }

    /**
     * Close a paragraph
     */
    function paragraphClose(&$content){
        ODTParagraph::paragraphClose($this, $content);
    }

    /**
     * Insert a horizontal rule
     */
    function horizontalRule(&$content) {
        $this->paragraphClose($content);
        $styleName = $this->getStyleName('horizontal line');
        $this->paragraphOpen($styleName, $content);
        $this->paragraphClose($content);

        // Save paragraph style name in 'Do not delete array'!
        $this->preventDeletetionStyles [] = $styleName;
    }

    /**
     * Add a linebreak
     */
    function linebreak(&$content) {
        $content .= '<text:line-break/>';
    }

    /**
     * Add a pagebreak
     */
    function pagebreak(&$content) {
        // Only set marker to insert a pagebreak on "next occasion".
        // The pagebreak will then be inserted in the next call to p_open() or header().
        // The style will be a "pagebreak" style with the paragraph or header style as the parent.
        // This prevents extra empty lines after the pagebreak.
        $this->paragraphClose($content);
        $this->pagebreak = true;
    }

    /**
     * Check if a pagebreak is pending
     * 
     * @return bool
     */
    function pagebreakIsPending() {
        return $this->pagebreak;
    }

    /**
     * Set pagebreak pending.
     * 
     * @return bool
     */
    function setPagebreakPending($value) {
        return $this->pagebreak = $value;
    }

    /**
     * Check if a header with $title exists.
     * 
     * @return bool
     */
    function headerExists($title) {
        return in_array($title, $this->headers);
    }

    /**
     * Add $title to headers.
     * 
     * @return bool
     */
    function addHeader($title) {
        $this->headers[] = $title;
    }

    /**
     * Creates a reference ID for the TOC
     *
     * @param string $title The headline/item title
     * @return string
     *
     * @author LarsDW223
     */
    public function buildTOCReferenceID($title) {
        // FIXME: not DokuWiki dependant function woud be nicer...
        $title = str_replace(':','',cleanID($title));
        $title = ltrim($title,'0123456789._-');
        if(empty($title)) {
            $title='NoTitle';
        }

        $this->refIDCount++;

        // The reference ID needs to start with '__RefHeading___'.
        // Otherwise LibreOffice will display $ref instead of the heading
        // name when moving the mouse over the link in the TOC.
        $ref = '__RefHeading___'.$title.'_'.$this->refIDCount;
        return $ref;
    }

    /**
     * Add an item to the TOC
     *
     * @param string $refID    the reference ID
     * @param string $hid      the hash link
     * @param string $text     the text to display
     * @param int    $level    the nesting level
     */
    function tocAddItemInternal($refID, $hid, $text, $level) {
        $item = $refID.','.$hid.','.$text.','. $level;
        $this->toc[] = $item;
    }

    /**
     * Set bookmark for the start of the page. This just saves the title temporarily.
     * It is then to be inserted in the first header or paragraph.
     *
     * @param string $id    ID of the bookmark
     */
    function setPageBookmark($id, $content){
        $inParagraph = $this->state->getInParagraph();
        if ($inParagraph) {
            $this->insertBookmarkInternal($id, true, $content);
        } else {
            $this->pageBookmark = $id;
        }
    }

    /**
     * Insert a bookmark.
     *
     * @param string $id    ID of the bookmark
     */
    protected function insertBookmarkInternal($id, $openParagraph=true, &$content){
        if ($openParagraph) {
            $this->paragraphOpen(NULL, $content);
        }
        $content .= '<text:bookmark text:name="'.$id.'"/>';
        $this->bookmarks [] = $id;
    }

    /**
     * Insert a pending page bookmark
     *
     * @param string $text  the text to display
     * @param int    $level header level
     * @param int    $pos   byte position in the original source
     */
    function insertPendingPageBookmark(&$content){
        // Insert page bookmark if requested and not done yet.
        if ( !empty($this->pageBookmark) ) {
            $this->insertBookmarkInternal($this->pageBookmark, false, $content);
            $this->pageBookmark = NULL;
        }
    }

    /**
     * Render a heading
     *
     * @param string $text  the text to display
     * @param int    $level header level
     * @param int    $pos   byte position in the original source
     */
    function heading($text, $level, &$content){
        ODTHeading::heading($this, $text, $level, $content);
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
     * @return string String containing ODT ZIP stream
     */
    public function getODTFileAsString(&$content, $metaContent, $userFieldDecls) {
        // Replace local link placeholders with links to headings or bookmarks
        $styleName = $this->getStyleName('local link');
        $visitedStyleName = $this->getStyleName('visited local link');
        ODTUtility::replaceLocalLinkPlaceholders($content, $this->toc, $this->bookmarks, $styleName, $visitedStyleName);

        // Delete paragraphs which only contain whitespace (but keep pagebreaks!)
        ODTUtility::deleteUselessElements($content, $this->preventDeletetionStyles);
           
        // Build the document
        $this->docHandler->build($content,
                                 $metaContent,
                                 $userFieldDecls,
                                 $this->pageStyles);

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
    public function closeCurrentElement(&$content) {
        $current = $this->state->getCurrent();
        if ($current != NULL) {
            $content .= $current->getClosingTag($content);
            $this->state->leave();
        }
    }

    /**
     * This function creates a style for changing the page format if required.
     * It returns NULL if no page format change is pending or if the current
     * page format is equal to the required page format.
     *
     * @param string  $parent Parent style name.
     * @return string Name of the style to be used for changing page format
     * 
     * FIXME: make protected as soon as function header is moved here also!
     */
    public function doPageFormatChange ($parent = NULL) {
        if ( $this->changePageFormat == NULL ) {
            // Error.
            return NULL;
        }
        $data = $this->changePageFormat;
        $this->changePageFormat = NULL;

        if ( empty($parent) ) {
            $parent = 'Standard';
        }

        // Create page layout style
        $format_string = $this->page->formatToString ($data['format'], $data['orientation'], $data['margin-top'], $data['margin-right'], $data['margin-bottom'], $data['margin-left']);
        $properties ['style-name']    = 'Style-Page-'.$format_string;
        $properties ['width']         = $data ['width'];
        $properties ['height']        = $data ['height'];
        $properties ['margin-top']    = $data ['margin-top'];
        $properties ['margin-bottom'] = $data ['margin-bottom'];
        $properties ['margin-left']   = $data ['margin-left'];
        $properties ['margin-right']  = $data ['margin-right'];
        $style_obj = ODTUnknownStyle::createPageLayoutStyle($properties);
        $style_name = $style_obj->getProperty('style-name');

        // Save style data in page style array, in common styles and set current page format
        $master_page_style_name = $format_string;
        $this->pageStyles [$master_page_style_name] = $style_name;
        $this->addAutomaticStyle($style_obj);
        $this->page->setFormat($data ['format'], $data ['orientation'], $data['margin-top'], $data['margin-right'], $data['margin-bottom'], $data['margin-left']);

        // Create paragraph style.
        $properties = array();
        $properties ['style-name']             = 'Style-'.$format_string;
        $properties ['style-parent']           = $parent;
        $properties ['style-master-page-name'] = $master_page_style_name;
        $properties ['page-number']            = 'auto';
        $style_obj = ODTParagraphStyle::createParagraphStyle($properties);
        $style_name = $style_obj->getProperty('style-name');
        $this->addAutomaticStyle($style_obj);

        // Save paragraph style name in 'Do not delete array'!
        $this->preventDeletetionStyles [] = $style_name;

        return $style_name;
    }

    public function createPagebreakStyle($parent=NULL,$before=true) {
        $style_name = 'pagebreak';
        if ( !$before ) {
            $style_name .= '_after';
        }
        if ( !empty($parent) ) {
            $style_name .= '_'.$parent;
        }
        if ( !$this->styleExists($style_name) ) {
            $style_obj = ODTParagraphStyle::createPagebreakStyle($style_name, $parent, $before);
            $this->addAutomaticStyle($style_obj);

            // Save paragraph style name in 'Do not delete array'!
            $this->preventDeletetionStyles [] = $style_name;
        }
        
        return $style_name;
    }

    /**
     * Replace XML entities
     * 
     * @param string $value
     * @return string
     */
    function replaceXMLEntities($value) {
        return str_replace( array('&','"',"'",'<','>'), array('&#38;','&#34;','&#39;','&#60;','&#62;'), $value);
    }

    /**
     * Open/start a footnote.
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    function footnoteOpen(&$content) {
        ODTFootnote::footnoteOpen($this, $content);
    }

    /**
     * Close/end a footnote.
     *
     * @author Andreas Gohr
     */
    function footnoteClose(&$content) {
        ODTFootnote::footnoteClose($this, $content);
    }

    /**
     * Opens a list.
     * The list style specifies if the list is an ordered or unordered list.
     * 
     * @param bool $continue Continue numbering?
     * @param string $styleName Name of style to use for the list
     */
    function listOpen($continue=false, $styleName, &$content) {
        ODTList::listOpen($this, $continue, $styleName, $content);
    }

    /**
     * Close a list
     */
    function listClose(&$content) {
        ODTList::listClose($this, $content);
    }

    /**
     * Open a list item
     *
     * @param int $level The nesting level
     */
    function listItemOpen($level, &$content) {
        ODTList::listItemOpen($this, $level, $content);
    }

    /**
     * Close a list item
     */
    function listItemClose(&$content) {
        ODTList::listItemClose($this, $content);
    }

    /**
     * Open list content/a paragraph in a list item
     */
    function listContentOpen(&$content) {
        ODTList::listContentOpen($this, $content);
    }

    /**
     * Close list content/a paragraph in a list item
     */
    function listContentClose(&$content) {
        ODTList::listContentClose($this, $content);
    }

    /**
     * Open/start a table
     *
     * @param int $maxcols maximum number of columns
     * @param int $numrows NOT IMPLEMENTED
     */
    function tableOpen($maxcols = NULL, $numrows = NULL, &$content){
        ODTTable::tableOpen($this, $maxcols, $numrows, $content);
    }

    /**
     * Close/finish a table
     *
     * @param int $maxcols maximum number of columns
     * @param int $numrows NOT IMPLEMENTED
     */
    function tableClose(&$content){
        ODTTable::tableClose($this, $content);
    }

    /**
     * Open a table row
     */
    function tableRowOpen(&$content){
        ODTTable::tableRowOpen($this, $content);
    }

    /**
     * Close a table row
     */
    function tableRowClose(&$content){
        ODTTable::tableRowClose($this, $content);
    }

    /**
     * Open a table header cell
     */
    function tableHeaderOpen($colspan = 1, $rowspan = 1, $align, &$content){
        ODTTable::tableHeaderOpen($this, $colspan = 1, $rowspan = 1, $align, $content);
    }

    /**
     * Close a table header cell
     */
    function tableHeaderClose(&$content){
        ODTTable::tableHeaderClose($this, $content);
    }

    /**
     * Open a table cell
     */
    function tableCellOpen($colspan, $rowspan, $align, &$content){
        ODTTable::tableCellOpen($this, $colspan, $rowspan, $align, $content);
    }

    /**
     * Close a table cell
     */
    function tableCellClose(&$content){
        ODTTable::tableCellClose($this, $content);
    }
}
