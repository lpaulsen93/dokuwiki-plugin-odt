<?php

require_once DOKU_PLUGIN . 'odt/ODT/docHandler.php';
require_once DOKU_PLUGIN . 'odt/ODT/scratchDH.php';
require_once DOKU_PLUGIN . 'odt/ODT/ODTTemplateDH.php';
require_once DOKU_PLUGIN . 'odt/ODT/CSSTemplateDH.php';
require_once DOKU_PLUGIN . 'odt/ODT/ODTState.php';
require_once DOKU_PLUGIN . 'odt/ODT/ODTUtility.php';

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
    protected $store = '';
    /** @var array */
    protected $footnotes = array();

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
        if ( empty($styleName) ) {
            $styleName = $this->getStyleName('body');
        }

        $list = NULL;
        $listItem = $this->state->getCurrentListItem();
        if ($listItem != NULL) {
            // We are in a list item. Is this the list start?
            $list = $listItem->getList();
            if ($list != NULL) {
                // Get list count and Flag if this is the first paragraph in the list
                $listCount = $this->state->countClass('list');
                $isFirst = $list->getListFirstParagraph();
                $list->setListFirstParagraph(false);

                // Create a style for putting a top margin for this first paragraph of the list
                // (if not done yet, the name must be unique!)
                if ($listCount == 1 && $isFirst) {
                    
                    // Has the style already been created...
                    $styleNameFirst = 'FirstListParagraph_'.$styleName;
                    if (!$this->styleExists($styleNameFirst)) {

                        // ...no, create style as copy of style 'list first paragraph'
                        $styleFirstTemplate = $this->getStyleByAlias('list first paragraph');
                        if ($styleFirstTemplate != NULL) {
                            $styleBody = $this->getStyle($styleName);
                            $styleDisplayName = 'First '.$styleBody->getProperty('style-display-name');
                            $styleObj = clone $styleFirstTemplate;
                            if ($styleObj != NULL) {
                                $styleObj->setProperty('style-name', $styleNameFirst);
                                $styleObj->setProperty('style-parent', $styleName);
                                $styleObj->setProperty('style-display-name', $styleDisplayName);
                                $bottom = $styleFirstTemplate->getProperty('margin-bottom');
                                if ($bottom === NULL) {
                                    $styleObj->setProperty('margin-bottom', $styleBody->getProperty('margin-bottom'));
                                }
                                $this->addStyle($styleObj);
                                $styleName = $styleNameFirst;
                            }
                        }
                    } else {
                        // ...yes, just use the name
                        $styleName = $styleNameFirst;
                    }
                }
            }
        }
        
        // Opening a paragraph inside another paragraph is illegal
        $inParagraph = $this->state->getInParagraph();
        if (!$inParagraph) {
            if ( $this->changePageFormat != NULL ) {
                $pageStyle = $this->doPageFormatChange($styleName);
                if ( $pageStyle != NULL ) {
                    $styleName = $pageStyle;
                    // Delete pagebreak, the format change will also introduce a pagebreak.
                    $this->pagebreak = false;
                }
            }
            if ( $this->pagebreak ) {
                $styleName = $this->createPagebreakStyle ($styleName);
                $this->pagebreak = false;
            }
            
            // If we are in a list remember paragraph position
            if ($list != NULL) {
                $list->setListLastParagraphPosition(strlen($content));
            }

            $paragraph = new ODTElementParagraph($styleName);
            $this->state->enter($paragraph);
            $content .= $paragraph->getOpeningTag();
        }
    }

    /**
     * Close a paragraph
     */
    function paragraphClose(&$content){
        $paragraph = $this->state->getCurrentParagraph();
        if ($paragraph != NULL) {
            $content .= $paragraph->getClosingTag();
            $this->state->leave();
        }
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
     * Creates a linkid from a headline
     *
     * @param string $title The headline title
     * @param boolean $create Create a new unique ID?
     * @return string
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    function headerToLink($title,$create=false) {
        // FIXME: not DokuWiki dependant function woud be nicer...
        $title = str_replace(':','',cleanID($title));
        $title = ltrim($title,'0123456789._-');
        if(empty($title)) {
            $title='section';
        }

        if($create){
            // Make sure tiles are unique
            $num = '';
            while(in_array($title.$num,$this->headers)){
                ($num) ? $num++ : $num = 1;
            }
            $title = $title.$num;
            $this->headers[] = $title;
        }

        return $title;
    }

    /**
     * Creates a reference ID for the TOC
     *
     * @param string $title The headline/item title
     * @return string
     *
     * @author LarsDW223
     */
    protected function buildTOCReferenceID($title) {
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
        // Close any open paragraph first
        $this->paragraphClose($content);

        $hid = $this->headerToLink($text,true);
        $TOCRef = $this->buildTOCReferenceID($text);
        $style = $this->getStyleName('heading'.$level);

        // Change page format if pending
        if ( $this->changePageFormat != NULL ) {
            $pageStyle = $this->doPageFormatChange($style);
            if ( $pageStyle != NULL ) {
                $style = $pageStyle;

                // Delete pagebreak, the format change will also introduce a pagebreak.
                $this->pagebreak = false;
            }
        }

        // Insert pagebreak if pending
        if ( $this->pagebreak ) {
            $style = $this->createPagebreakStyle ($style);
            $this->pagebreak = false;
        }
        $content .= '<text:h text:style-name="'.$style.'" text:outline-level="'.$level.'">';

        // Insert page bookmark if requested and not done yet.
        $this->insertPendingPageBookmark($content);

        $content .= '<text:bookmark-start text:name="'.$TOCRef.'"/>';
        $content .= '<text:bookmark-start text:name="'.$hid.'"/>';
        $content .= $this->replaceXMLEntities($text);
        $content .= '<text:bookmark-end text:name="'.$TOCRef.'"/>';
        $content .= '<text:bookmark-end text:name="'.$hid.'"/>';
        $content .= '</text:h>';

        // Do not add headings in frames
        $frame = $this->state->getCurrentFrame();
        if ($frame == NULL) {
            $this->tocAddItemInternal($TOCRef, $hid, $text, $level);
        }
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
    protected function closeCurrentElement(&$content) {
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
     * All following content will go to the footnote instead of
     * the document. To achieve this the previous content
     * is moved to $store and $content is cleared
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    function footnoteOpen(&$content) {
        // Move current content to store and record footnote
        $this->store = $content;
        $content = '';
    }

    /**
     * Close/end a footnote.
     *
     * All content is moved to the $footnotes array and the old
     * content is restored from $store again.
     *
     * @author Andreas Gohr
     */
    function footnoteClose(&$content) {
        // Recover footnote into the stack and restore old content
        $footnote = $content;
        $content = $this->store;
        $this->store = '';

        // Check to see if this footnote has been seen before
        $i = array_search($footnote, $this->footnotes);

        if ($i === false) {
            $i = count($this->footnotes);
            // Its a new footnote, add it to the $footnotes array
            $this->footnotes[$i] = $footnote;

            $content .= '<text:note text:id="ftn'.$i.'" text:note-class="footnote">';
            $content .= '<text:note-citation>'.($i+1).'</text:note-citation>';
            $content .= '<text:note-body>';
            $content .= '<text:p text:style-name="'.$this->getStyleName('footnote').'">';
            $content .= $footnote;
            $content .= '</text:p>';
            $content .= '</text:note-body>';
            $content .= '</text:note>';
        } else {
            // Seen this one before - just reference it FIXME: style isn't correct yet
            $content .= '<text:note-ref text:note-class="footnote" text:ref-name="ftn'.$i.'">'.($i+1).'</text:note-ref>';
        }
    }

    /**
     * Opens a list.
     * The list style specifies if the list is an ordered or unordered list.
     * 
     * @param bool $continue Continue numbering?
     * @param string $styleName Name of style to use for the list
     */
    function listOpen($continue=false, $styleName, &$content) {
        $this->paragraphClose($content);

        $list = new ODTElementList($styleName, $continue);
        $this->state->enter($list);

        $content .= $list->getOpeningTag();
    }

    /**
     * Close a list
     */
    function listClose(&$content) {
        $table = $this->state->getCurrentTable();
        if ($table != NULL && $table->getListInterrupted()) {
            // Do not do anything as long as list is interrupted
            return;
        }

        // Eventually modify last list paragraph first
        $this->replaceLastListParagraph($content);

        $list = $this->state->getCurrent();
        $content .= $list->getClosingTag();

        $position = $list->getListLastParagraphPosition();
        $this->state->leave();
        
        // If we are still in a list save the last paragraph position
        // in the current list (needed for nested lists!).
        $list = $this->state->getCurrentList();
        if ($list != NULL) {
            $list->setListLastParagraphPosition($position);
        }
    }

    /**
     * Open a list item
     *
     * @param int $level The nesting level
     */
    function listItemOpen($level, &$content) {
        if ($this->state == NULL ) {
            // ??? Can't be...
            return;
        }

        // Set marker that list interruption has stopped!!!
        $table = $this->state->getCurrentTable();
        if ($table != NULL) {
            $table->setListInterrupted(false);
        }

        // Attention:
        // we save the list level here but it might be wrong.
        // Someone can start a list with level 2 without having created
        // a list with level 1 before.
        // When the correct list level is needed better use
        // $this->document->state->countClass('list'), see table_open().
        $list_item = new ODTElementListItem($level);
        $this->state->enter($list_item);

        $content .= $list_item->getOpeningTag();
    }

    /**
     * Close a list item
     */
    function listItemClose(&$content) {
        $table = $this->state->getCurrentTable();
        if ($table != NULL && $table->getListInterrupted()) {
            // Do not do anything as long as list is interrupted
            return;
        }
        $this->closeCurrentElement($content);
    }

    /**
     * Open list content/a paragraph in a list item
     */
    function listContentOpen(&$content) {
        // The default style for list content is body but it should always be
        // overwritten. It's just assigned here to guarantee some style name is
        // always set in case of an error also.
        $styleName = $this->getStyleName('body');
        $list = $this->state->getCurrentList();
        if ($list != NULL) {
            $listStyleName = $list->getStyleName();
            if ($listStyleName == $this->getStyleName('list')) {
                $styleName = $this->getStyleName('list content');
            }
            if ($listStyleName == $this->getStyleName('numbering')) {
                $styleName = $this->getStyleName('numbering content');
            }
        }

        $this->paragraphOpen($styleName, $content);
    }

    /**
     * Close list content/a paragraph in a list item
     */
    function listContentClose(&$content) {
        $table = $this->state->getCurrentTable();
        if ($table != NULL && $table->getListInterrupted()) {
            // Do not do anything as long as list is interrupted
            return;
        }
        $this->paragraphClose($content);
    }

    /**
     * The function replaces the last paragraph of a list
     * with a style having the properties of 'List_Last_Paragraph'.
     *
     * The function does NOT change the last paragraph of nested lists.
     */
    protected function replaceLastListParagraph(&$content) {
        $list = $this->state->getCurrentList();
        if ($list != NULL) {
            // We are in a list.
            $list_count = $this->state->countClass('list');
            $position = $list->getListLastParagraphPosition();

            if ($list_count != 1 || $position == -1) {
                // Do nothing if this is a nested list or the position was not saved
                return;
            }

            $last_p_style = NULL;
            if (preg_match('/<text:p text:style-name="[^"]*">/', $content, $matches, 0, $position) === 1) {
                $last_p_style = substr($matches [0], strlen('<text:p text:style-name='));
                $last_p_style = trim($last_p_style, '">');
            } else {
                // Nothing found???
                return;
            }

            // Create a style for putting a bottom margin for this last paragraph of the list
            // (if not done yet, the name must be unique!)
            $style_name = 'LastListParagraph_'.$last_p_style;
            $style_last = $this->getStyleByAlias('list last paragraph');
            if (!$this->styleExists($style_name)) {
                if ($style_last != NULL) {
                    $style_body = $this->getStyle($last_p_style);
                    $style_display_name = 'Last '.$style_body->getProperty('style-display-name');
                    $style_obj = clone $style_last;
                    if ($style_obj != NULL) {
                        $style_obj->setProperty('style-name', $style_name);
                        $style_obj->setProperty('style-parent', $last_p_style);
                        $style_obj->setProperty('style-display-name', $style_display_name);
                        $top = $style_last->getProperty('margin-top');
                        if ($top === NULL) {
                            $style_obj->setProperty('margin-top', $style_body->getProperty('margin-top'));
                        }
                        $this->addStyle($style_obj);
                    }
                }
            }
            
            // Finally replace style name of last paragraph.
            $content = substr_replace ($content, 
                '<text:p text:style-name="'.$style_name.'">',
                $position, strlen($matches[0]));
        }
    }
}
