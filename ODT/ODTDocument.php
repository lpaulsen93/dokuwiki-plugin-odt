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
require_once DOKU_PLUGIN . 'odt/ODT/ODTFrame.php';
require_once DOKU_PLUGIN . 'odt/ODT/ODTImage.php';
require_once DOKU_PLUGIN . 'odt/ODT/ODTSpan.php';
require_once DOKU_PLUGIN . 'odt/ODT/ODTIndex.php';
require_once DOKU_PLUGIN . 'odt/ODT/ODTUnits.php';

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
    public $div_z_index = 0;
    /** @var  has any text content been added yet (excluding whitespace)? */
    public $text_empty = true;
    /** @var Debug string */
    public $trace_dump = '';

    /** @var helper_plugin_odt_units */
    protected $units = null;
    /** @var Current pageFormat */
    protected $page = null;
    /** @var changePageFormat */
    protected $changePageFormat = NULL;
    /** @var indexesData */
    protected $indexesData = array();
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

        // Set standard page format: A4, portrait, 2cm margins
        $this->page = new pageFormat();
        $this->setStartPageFormat ('A4', 'portrait', 2, 2, 2, 2);
        
        // Create units object and set default values
        $this->units = new ODTUnits();
        $this->units->setPixelPerEm(14);
        $this->units->setTwipsPerPixelX(16);
        $this->units->setTwipsPerPixelY(20);
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
     * Render plain text data
     *
     * @param string $text
     */
    function addPlainText($text, &$content) {
        // Check if there is some content in the text.
        // Only insert bookmark/pagebreak/format change if text is not empty.
        // Otherwise a empty paragraph/line would be created!
        if ( !empty($text) && !ctype_space($text) ) {
            // Insert page bookmark if requested and not done yet.
            $this->insertPendingPageBookmark($content);

            // Insert pagebreak or page format change if still pending.
            // Attention: NOT if $text is empty. This would lead to empty lines before headings
            //            right after a pagebreak!
            $in_paragraph = $this->state->getInParagraph();
            if ( ($this->pagebreakIsPending() || $this->pageFormatChangeIsPending()) ||
                  !$in_paragraph ) {
                $this->paragraphOpen(NULL, $content);
            }
        }
        $content .= $this->replaceXMLEntities($text);
        if ($this->text_empty && !ctype_space($text)) {
            $this->text_empty = false;
        }
    }

    /**
     * Open a text span.
     *
     * @param string $styleName The style to use.
     */
    function spanOpen($styleName, &$content){
        ODTSpan::spanOpen($this, $content, $styleName);
    }

    /**
     * Open a text span using CSS.
     * 
     * @see ODTSpan::spanOpenUseCSS for detailed documentation
     */
    function spanOpenUseCSS(&$content, $attributes=NULL, cssimportnew $import=NULL){
        ODTSpan::spanOpenUseCSS($this, $content, $attributes, $import);
    }

    /**
     * Open a text span using properties.
     * 
     * @see ODTSpan::spanOpenUseProperties for detailed documentation
     */
    function spanOpenUseProperties(&$content, $properties){
        ODTSpan::spanOpenUseProperties($this, $content, $properties);
    }

    /**
     * Close a text span.
     *
     * @param string $style_name The style to use.
     */    
    function spanClose(&$content) {
        ODTSpan::spanClose($this, $content);
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
     * Open a paragraph using CSS.
     * 
     * @see ODTParagraph::paragraphOpenUseCSS for detailed documentation
     */
    function paragraphOpenUseCSS(&$content, $attributes=NULL, cssimportnew $import=NULL){
        ODTParagraph::paragraphOpenUseCSS($this, $content, $attributes, $import);
    }

    /**
     * Open a paragraph using properties.
     * 
     * @see ODTParagraph::paragraphOpenUseProperties for detailed documentation
     */
    function paragraphOpenUseProperties(&$content, $properties){
        ODTParagraph::paragraphOpenUseProperties($this, $content, $properties);
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
     * Check if a page format change is pending
     * 
     * @return bool
     */
    function pageFormatChangeIsPending() {
        if ($this->changePageFormat != NULL) {
            return true;
        }
        return false;
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

    function insertIndex(&$content, $type='toc', array $settings=NULL) {
        ODTIndex::insertIndex($this, $content, $this->indexesData, $type, $settings);
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
        // Close any open paragraph if not done yet.
        $this->paragraphClose($content);

        // Replace local link placeholders with links to headings or bookmarks
        $styleName = $this->getStyleName('local link');
        $visitedStyleName = $this->getStyleName('visited local link');
        ODTUtility::replaceLocalLinkPlaceholders($content, $this->toc, $this->bookmarks, $styleName, $visitedStyleName);

        // Build indexes
        ODTIndex::replaceIndexesPlaceholders($this, $content, $this->indexesData);

        // Delete paragraphs which only contain whitespace (but keep pagebreaks!)
        ODTUtility::deleteUselessElements($content, $this->preventDeletetionStyles);

        if (!empty($this->trace_dump)) {
            $this->paragraphOpen(NULL, $content);
            $content .= 'Tracedump: '.$this->replaceXMLEntities($this->trace_dump);
            $this->paragraphClose($content);
        }
           
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
     * Add a column to a table.
     */
    function tableAddColumn (){
        ODTTable::tableAddColumn ($this);
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

    /**
     * Open a table using CSS
     */
    function tableOpenUseCSS(&$content, $maxcols=NULL, $numrows=NULL, $attributes=NULL, cssimportnew $import=NULL){
        ODTTable::tableOpenUseCSS($this, $content, $maxcols, $numrows, $attributes, $import);
    }

    /**
     * Open a table using properties
     */
    function tableOpenUseProperties (&$content, $properties, $maxcols = 0, $numrows = 0){
        ODTTable::tableOpenUseProperties($this, $content, $properties, $maxcols, $numrows);
    }

    /**
     * Add a table column using CSS
     */
    function tableAddColumnUseCSS ($attributes=NULL, cssimportnew $import=NULL){
        ODTTable::tableAddColumnUseCSS($this, $attributes, $import);
    }

    /**
     * Add a table column using properties
     */
    function tableAddColumnUseProperties ($properties){
        ODTTable::tableAddColumnUseProperties($this, $properties);
    }

    /**
     * Open a table header using CSS
     */
    function tableHeaderOpenUseCSS(&$content, $colspan = 1, $rowspan = 1, $attributes=NULL, cssimportnew $import=NULL){
        ODTTable::tableHeaderOpenUseCSS($this, $content, $colspan, $rowspan, $attributes, $import);
    }

    /**
     * Open a table header using properties
     */
    function tableHeaderOpenUseProperties(&$content, $properties, $colspan = 1, $rowspan = 1){
        ODTTable::tableHeaderOpenUseProperties($this, $content, $properties, $colspan, $rowspan);
    }

    /**
     * Open a table row using CSS
     */
    function tableRowOpenUseCSS(&$content, $attributes=NULL, cssimportnew $import=NULL){
        ODTTable::tableRowOpenUseCSS($this, $content, $attributes, $import);
    }

    /**
     * Open a table row using properties
     */
    function tableRowOpenUseProperties(&$content, $properties){
        ODTTable::tableRowOpenUseProperties($this, $content, $properties);
    }

    /**
     * Open a table cell using CSS
     */
    function tableCellOpenUseCSS(&$content, $attributes=NULL, cssimportnew $import=NULL, $colspan = 1, $rowspan = 1){
        ODTTable::tableCellOpenUseCSS($this, $content, $attributes, $import, $colspan, $rowspan);
    }

    /**
     * Open a table cell using properties
     */
    function tableCellOpenUseProperties(&$content, $properties, $colspan = 1, $rowspan = 1){
        ODTTable::tableCellOpenUseProperties($this, $content, $properties, $colspan, $rowspan);
    }

    /**
     * Open a text box in a frame using CSS.
     * 
     * @see ODTFrame::openTextBoxUseCSS for detailed documentation
     */
    function openTextBoxUseCSS (&$content, $attributes=NULL, cssimportnew $import=NULL) {
        ODTFrame::openTextBoxUseCSS($this, $content, $attributes, $import);
    }
    
    /**
     * Open a text box in a frame using properties.
     * 
     * @see ODTFrame::openTextBoxUseProperties for detailed documentation
     */
    function openTextBoxUseProperties (&$content, $properties) {
        ODTFrame::openTextBoxUseProperties($this, $content, $properties);
    }

    /**
     * This function closes a textbox.
     * 
     * @see ODTFrame::closeTextBox for detailed documentation
     */
    function closeTextBox (&$content) {
        ODTFrame::closeTextBox($this, $content);
    }

    /**
     * Open a multi column text box in a frame using properties.
     * 
     * @see ODTFrame::openMultiColumnTextBoxUseProperties for detailed documentation
     */
    function openMultiColumnTextBoxUseProperties (&$content, $properties) {
        ODTFrame::openMultiColumnTextBoxUseProperties($this, $content, $properties);
    }

    /**
     * This function closes a multi column textbox.
     * 
     * @see ODTFrame::closeTextBox for detailed documentation
     */
    function closeMultiColumnTextBox (&$content) {
        ODTFrame::closeMultiColumnTextBox($this, $content);
    }

    /**
     * Change outline style to given value.
     * Currently only 'Numbers' is supported. Any other value will
     * not change anything.
     * 
     * @param string $type Type of outline style to set
     */
    public function setOutlineStyle ($type) {
        $outline_style = $this->getStyle('Outline');
        if ($outline_style == NULL) {
            // Outline style not found!
            return;
        }
        switch ($type) {
            case 'Numbers':
                for ($level = 1 ; $level < 11 ; $level++) {
                    $outline_style->setPropertyForLevel($level, 'num-format', '1');
                    $outline_style->setPropertyForLevel($level, 'num-suffix', '.');
                    $outline_style->setPropertyForLevel($level, 'num-prefix', ' ');
                    $outline_style->setPropertyForLevel($level, 'display-levels', $level);
                }
                break;
        }
    }

    /**
     * This function creates a text style for spans with the given properties.
     * If $common is true it will be added to the common styles otherwise it
     * will be dadded to the automatic styles.
     * 
     * Common styles are visible for the user after export e.g. in LibreOffice
     * 'Styles and Formatting' view. Therefore they should have
     * $properties ['style-display-name'] set to a meaningfull name.
     * 
     * @param $properties The properties to use
     * @param $common Add style to common or automatic styles?
     */
    public function createTextStyle ($properties, $common=true) {
        $style_obj = ODTTextStyle::createTextStyle($properties, NULL, $this);
        if ($common == true) {
            $this->addStyle($style_obj);
        } else {
            $this->addAutomaticStyle($style_obj);
        }
    }

    /**
     * This function creates a paragraph style for paragraphs with the given properties.
     * If $common is true it will be added to the common styles otherwise it
     * will be dadded to the automatic styles.
     * 
     * Common styles are visible for the user after export e.g. in LibreOffice
     * 'Styles and Formatting' view. Therefore they should have
     * $properties ['style-display-name'] set to a meaningfull name.
     * 
     * @param $properties The properties to use
     * @param $common Add style to common or automatic styles?
     */
    public function createParagraphStyle ($properties, $common=true) {
        $style_obj = ODTParagraphStyle::createParagraphStyle($properties, NULL, $this);
        if ($common == true) {
            $this->addStyle($style_obj);
        } else {
            $this->addAutomaticStyle($style_obj);
        }
    }

    /**
     * This function creates a table style for tables with the given properties.
     * If $common is true it will be added to the common styles otherwise it
     * will be dadded to the automatic styles.
     * 
     * Common styles are visible for the user after export e.g. in LibreOffice
     * 'Styles and Formatting' view. Therefore they should have
     * $properties ['style-display-name'] set to a meaningfull name.
     * 
     * @param $properties The properties to use
     * @param $common Add style to common or automatic styles?
     */
    public function createTableStyle ($properties, $common=true) {
        $style_obj = ODTTableStyle::createTableTableStyle($properties);
        if ($common == true) {
            $this->addStyle($style_obj);
        } else {
            $this->addAutomaticStyle($style_obj);
        }
    }

    /**
     * This function creates a table row style for table rows with the given properties.
     * If $common is true it will be added to the common styles otherwise it
     * will be dadded to the automatic styles.
     * 
     * Common styles are visible for the user after export e.g. in LibreOffice
     * 'Styles and Formatting' view. Therefore they should have
     * $properties ['style-display-name'] set to a meaningfull name.
     * 
     * @param $properties The properties to use
     * @param $common Add style to common or automatic styles?
     */
    public function createTableRowStyle ($properties, $common=true) {
        $style_obj = ODTTableRowStyle::createTableRowStyle($properties);
        if ($common == true) {
            $this->addStyle($style_obj);
        } else {
            $this->addAutomaticStyle($style_obj);
        }
    }

    /**
     * This function creates a table cell style for table cells with the given properties.
     * If $common is true it will be added to the common styles otherwise it
     * will be dadded to the automatic styles.
     * 
     * Common styles are visible for the user after export e.g. in LibreOffice
     * 'Styles and Formatting' view. Therefore they should have
     * $properties ['style-display-name'] set to a meaningfull name.
     * 
     * @param $properties The properties to use
     * @param $common Add style to common or automatic styles?
     */
    public function createTableCellStyle ($properties, $common=true) {
        $style_obj = ODTTableCellStyle::createTableCellStyle($properties);
        if ($common == true) {
            $this->addStyle($style_obj);
        } else {
            $this->addAutomaticStyle($style_obj);
        }
    }

    /**
     * This function creates a table column style for table columns with the given properties.
     * If $common is true it will be added to the common styles otherwise it
     * will be dadded to the automatic styles.
     * 
     * Common styles are visible for the user after export e.g. in LibreOffice
     * 'Styles and Formatting' view. Therefore they should have
     * $properties ['style-display-name'] set to a meaningfull name.
     * 
     * @param $properties The properties to use
     * @param $common Add style to common or automatic styles?
     */
    public function createTableColumnStyle ($properties, $common=true) {
        $style_obj = ODTTableColumnStyle::createTableColumnStyle($properties);
        if ($common == true) {
            $this->addStyle($style_obj);
        } else {
            $this->addAutomaticStyle($style_obj);
        }
    }

    /**
     * The function tries to examine the width and height
     * of the image stored in file $src.
     * 
     * @see ODTUtility::getImageSize for a detailed description
     */
    public static function getImageSize($src, $maxwidth=NULL, $maxheight=NULL){
        return ODTUtility::getImageSize($src, $maxwidth, $maxheight);
    }

    /**
     * @param string $src
     * @param  $width
     * @param  $height
     * @return array
     */
    public static function getImageSizeString($src, $width = NULL, $height = NULL){
        return ODTUtility::getImageSizeString($src, $width, $height);
    }

    /**
     * The function adds an image.
     * 
     * @see ODTImage::addImage for a detailed description
     */
    public function addImage(&$content, $src, $width = NULL, $height = NULL, $align = NULL, $title = NULL, $style = NULL, $returnonly = false){
        ODTImage::addImage($this, $content, $src, $width, $height, $align, $title, $style, $returnonly);
    }

    /**
     * The function adds $string as an SVG image file.
     * It does NOT insert the image in the document.
     * 
     * @see ODTImage::addStringAsSVGImageFile for a detailed description
     */
    public function addStringAsSVGImageFile($string) {
        return ODTImage::addStringAsSVGImageFile($this, $string);
    }

    /**
     * Adds the content of $string as a SVG picture to the document.
     * 
     * @see ODTImage::addStringAsSVGImage for a detailed description
     */
    public function addStringAsSVGImage(&$content, $string, $width = NULL, $height = NULL, $align = NULL, $title = NULL, $style = NULL) {
        return ODTImage::addStringAsSVGImage($this, $content, $string, $width, $height, $align, $title, $style);
    }

    /**
     * Get properties defined in a CSS style statement.
     * 
     * @see ODTUtility::getCSSStylePropertiesForODT
     */
    public function getCSSStylePropertiesForODT(&$properties, $style, $baseURL = NULL){
        ODTUtility::getCSSStylePropertiesForODT($properties, $style, $baseURL);
    }

    /**
     * This function sets the page format for the FIRST page.
     * The format, orientation and page margins can be changed.
     * See function queryFormat() in ODT/page.php for supported formats.
     *
     * @param string  $format         e.g. 'A4', 'A3'
     * @param string  $orientation    e.g. 'portrait' or 'landscape'
     * @param numeric $margin_top     Top-Margin in cm, default 2
     * @param numeric $margin_right   Right-Margin in cm, default 2
     * @param numeric $margin_bottom  Bottom-Margin in cm, default 2
     * @param numeric $margin_left    Left-Margin in cm, default 2
     */
    public function setStartPageFormat ($format=NULL, $orientation=NULL, $margin_top=NULL, $margin_right=NULL, $margin_bottom=NULL, $margin_left=NULL) {
        // Setup page format.
        // Set the page format of the current page for calculation ($this->page)
        $this->page->setFormat
            ($format, $orientation, $margin_top, $margin_right, $margin_bottom, $margin_left);

        // Change the standard page layout style
        $first_page = $this->getStyleByAlias('first page');
        if ($first_page != NULL) {
            $first_page->setProperty('width', $this->page->getWidth().'cm');
            $first_page->setProperty('height', $this->page->getHeight().'cm');
            $first_page->setProperty('margin-top', $this->page->getMarginTop().'cm');
            $first_page->setProperty('margin-right', $this->page->getMarginRight().'cm');
            $first_page->setProperty('margin-bottom', $this->page->getMarginBottom().'cm');
            $first_page->setProperty('margin-left', $this->page->getMarginLeft().'cm');
        }
    }

    /**
     * This function sets the page format.
     * The format, orientation and page margins can be changed.
     * See function queryFormat() in ODT/page.php for supported formats.
     *
     * @param string  $format         e.g. 'A4', 'A3'
     * @param string  $orientation    e.g. 'portrait' or 'landscape'
     * @param numeric $margin_top     Top-Margin in cm, default 2
     * @param numeric $margin_right   Right-Margin in cm, default 2
     * @param numeric $margin_bottom  Bottom-Margin in cm, default 2
     * @param numeric $margin_left    Left-Margin in cm, default 2
     */
    public function setPageFormat (&$content, $format=NULL, $orientation=NULL, $margin_top=NULL, $margin_right=NULL, $margin_bottom=NULL, $margin_left=NULL) {
        $data = array ();

        // Fill missing values with current settings
        if ( empty($format) ) {
            $format = $this->page->getFormat();
        }
        if ( empty($orientation) ) {
            $orientation = $this->page->getOrientation();
        }
        if ( empty($margin_top) ) {
            $margin_top = $this->page->getMarginTop();
        }
        if ( empty($margin_right) ) {
            $margin_right = $this->page->getMarginRight();
        }
        if ( empty($margin_bottom) ) {
            $margin_bottom = $this->page->getMarginBottom();
        }
        if ( empty($margin_left) ) {
            $margin_left = $this->page->getMarginLeft();
        }

        // Adjust given parameters, query resulting format data and get format-string
        $this->page->queryFormat ($data, $format, $orientation, $margin_top, $margin_right, $margin_bottom, $margin_left);
        $format_string = $this->page->formatToString ($data['format'], $data['orientation'], $data['margin-top'], $data['margin-right'], $data['margin-bottom'], $data['margin-left']);

        if ( $format_string == $this->page->toString () ) {
            // Current page already uses this format, no need to do anything...
            return;
        }

        if ($this->text_empty) {
            // If the text is still empty, then we change the start page format now.
            $this->page->setFormat($data ['format'], $data ['orientation'], $data['margin-top'], $data['margin-right'], $data['margin-bottom'], $data['margin-left']);
            $first_page = $this->getStyleByAlias('first page');
            if ($first_page != NULL) {
                $first_page->setProperty('width', $this->page->getWidth().'cm');
                $first_page->setProperty('height', $this->page->getHeight().'cm');
                $first_page->setProperty('margin-top', $this->page->getMarginTop().'cm');
                $first_page->setProperty('margin-right', $this->page->getMarginRight().'cm');
                $first_page->setProperty('margin-bottom', $this->page->getMarginBottom().'cm');
                $first_page->setProperty('margin-left', $this->page->getMarginLeft().'cm');
            }
        } else {
            // Set marker and save data for pending change format.
            // The format change istelf will be done on the next call to p_open or header()
            // to prevent empty lines after the format change.
            $this->changePageFormat = $data;

            // Close paragraph if open
            $this->paragraphClose($content);
        }
    }

    /**
     * Return total page width in centimeters
     * (margins are included)
     *
     * @author LarsDW223
     */
    function getPageWidth(){
        return $this->page->getWidth();
    }

    /**
     * Return total page height in centimeters
     * (margins are included)
     *
     * @author LarsDW223
     */
    function getPageHeight(){
        return $this->page->getHeight();
    }

    /**
     * Return left margin in centimeters
     *
     * @author LarsDW223
     */
    function getLeftMargin(){
        return $this->page->getMarginLeft();
    }

    /**
     * Return right margin in centimeters
     *
     * @author LarsDW223
     */
    function getRightMargin(){
        return $this->page->getMarginRight();
    }

    /**
     * Return top margin in centimeters
     *
     * @author LarsDW223
     */
    function _getTopMargin(){
        return $this->page->getMarginTop();
    }

    /**
     * Return bottom margin in centimeters
     *
     * @author LarsDW223
     */
    function _getBottomMargin(){
        return $this->page->getMarginBottom();
    }

    /**
     * Return width percentage value if margins are taken into account.
     * Usually "100%" means 21cm in case of A4 format.
     * But usually you like to take care of margins. This function
     * adjusts the percentage to the value which should be used for margins.
     * So 100% == 21cm e.g. becomes 80.9% == 17cm (assuming a margin of 2 cm on both sides).
     *
     * @author LarsDW223
     *
     * @param int|string $percentage
     * @return int|string
     */
    function getRelWidthMindMargins ($percentage = '100'){
        return $this->page->getRelWidthMindMargins($percentage);
    }

    /**
     * Like _getRelWidthMindMargins but returns the absulute width
     * in centimeters.
     *
     * @author LarsDW223
     * @param string|int|float $percentage
     * @return float
     */
    function getAbsWidthMindMargins ($percentage = '100'){
        return $this->page->getAbsWidthMindMargins($percentage);
    }

    /**
     * Return height percentage value if margins are taken into account.
     * Usually "100%" means 29.7cm in case of A4 format.
     * But usually you like to take care of margins. This function
     * adjusts the percentage to the value which should be used for margins.
     * So 100% == 29.7cm e.g. becomes 86.5% == 25.7cm (assuming a margin of 2 cm on top and bottom).
     *
     * @author LarsDW223
     *
     * @param string|float|int $percentage
     * @return float|string
     */
    function getRelHeightMindMargins ($percentage = '100'){
        return $this->page->getRelHeightMindMargins($percentage);
    }

    /**
     * Like _getRelHeightMindMargins but returns the absulute width
     * in centimeters.
     *
     * @author LarsDW223
     *
     * @param string|int|float $percentage
     * @return float
     */
    function getAbsHeightMindMargins ($percentage = '100'){
        return $this->page->getAbsHeightMindMargins($percentage);
    }

    /**
     * Sets the twips per pixel (X axis) used for px to pt conversion.
     *
     * @param int $value The value to be set.
     */
    function setTwipsPerPixelX ($value) {
        $this->units->setTwipsPerPixelX ($value);
    }

    /**
     * Sets the twips per pixel (Y axis) unit used for px to pt conversion.
     *
     * @param int $value The value to be set.
     */
    function setTwipsPerPixelY ($value) {
        $this->units->setTwipsPerPixelY ($value);
    }

    /**
     * Sets the pixel per em unit used for px to em conversion.
     *
     * @param int $value The value to be set.
     */
    public function setPixelPerEm ($value) {
        $this->units->setPixelPerEm ($value);
    }

    /**
     * Convert length value with valid XSL unit to points.
     *
     * @param string $value  String with length value, e.g. '20px', '20cm'...
     * @param string $axis   Is the value to be converted a value on the X or Y axis? Default is 'y'.
     *        Only relevant for conversion from 'px' or 'em'.
     * @return string The current value.
     */
    public function toPoints ($value, $axis = 'y') {
        return $this->units->toPoints ($value, $axis);
    }
}
