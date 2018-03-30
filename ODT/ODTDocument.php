<?php

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
require_once DOKU_PLUGIN . 'odt/ODT/ODTmeta.php';
require_once DOKU_PLUGIN . 'odt/ODT/ODTmanifest.php';
require_once DOKU_PLUGIN . 'odt/ODT/css/cssimportnew.php';
require_once DOKU_PLUGIN . 'odt/ODT/ODTImport.php';
require_once DOKU_PLUGIN . 'odt/ODT/ODTExport.php';

// Siple class as storage for internal parameters passed to other
// classes to prevent to long parameter lines.
class ODTInternalParams
{
    public $document = NULL;
    public $htmlStack = NULL;
    public $import = NULL;
    public $units = NULL;
    public $content = NULL;
    public $elementObj = NULL;
    public $ZIP = NULL;
    public $manifest = NULL;
    public $styleset = NULL;
}

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
    public $div_z_index = 0;
    /** @var Debug string */
    public $trace_dump = '';

    /** @var  has any text content been added yet (excluding whitespace)? */
    protected $text_empty = true;
    /** @var array store the table of contents */
    protected $toc = array();
    /** @var ODTMeta */
    protected $meta;
    /** @var helper_plugin_odt_units */
    protected $units = null;
    /** @var Current pageFormat */
    protected $page = null;
    /** @var changePageFormat */
    protected $changePageFormat = NULL;
    /** @var indexesData */
    protected $indexesData = array();
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
    protected $quote_depth = 0;
    protected $linksEnabled = true;
    // Used by Fields Plugin
    protected $fields = array();
    // The document content
    protected $content = '';
    /** @var cssimportnew */
    protected $importnew = null;
    /** @var cssdocument */
    protected $htmlStack = null;
    protected $CSSUsage = 'off';
    protected $params = NULL;
    protected $manifest = NULL;
    protected $ZIP = NULL;
    protected $styleset = NULL;
    protected $registrations = array();

    /**
     * Constructor:
     * - initializes the state
     */
    public function __construct() {
        // Initialize state
        $this->state = new ODTState();

        // Initialize HTML state
        $this->htmlStack = new cssdocument();

        // Create default styles/styles storage.
        $this->styleset = new ODTDefaultStyles();
        $this->styleset->import();

        // Set standard page format: A4, portrait, 2cm margins
        $this->page = new pageFormat();
        $this->setStartPageFormat ('A4', 'portrait', 2, 2, 2, 2);
        
        // Create units object and set default values
        $this->units = new ODTUnits();
        $this->units->setPixelPerEm(16);
        $this->units->setTwipsPerPixelX(16);
        $this->units->setTwipsPerPixelY(20);

        // Setup meta data store/handler
        $this->meta = new ODTMeta();

        // Setup manifest data
        $this->manifest = new ODTManifest();

        // Prepare the zipper
        // (This instance is only for our to-be-created ODT-ZIP-Archive
        //  - NOT to be used for extracting any ODT-Templates!)
        if (class_exists('\splitbrain\PHPArchive\Zip'))
        {
            $this->ZIP = new \splitbrain\PHPArchive\Zip();
            $this->ZIP->create();
        }

        $this->params = new ODTInternalParams();
    }

    /**
     * Initialize the document.
     */
    public function initialize () {
        $this->state->setDocument($this);

        $this->params->document  = $this;
        $this->params->htmlStack = $this->htmlStack;
        $this->params->units     = $this->units;
        $this->params->content   = &$this->content;
        $this->params->ZIP       = $this->ZIP;
        $this->params->manifest  = $this->manifest;
        $this->params->styleset  = $this->styleset;

        if ($this->ZIP === NULL) {
            return false;
        }
        return true;
    }

    /**
     * Set CSS usage.
     *
     * @param string $usage
     */
    public function setCSSUsage ($usage) {
        switch (strtolower($usage)) {
            case 'basic':
            case 'full':
                $this->CSSUsage = $usage;
                break;
            default:
                $this->CSSUsage = 'off';
                break;
        }
    }

    protected function setupImport() {
        if ($this->importnew == NULL) {
            // No CSS imported yet. Create object.
            $this->importnew = new cssimportnew();
            if ($this->importnew == NULL) {
                return;
            }
        }
        $this->params->import = $this->importnew;
    }

    /**
     * Set commom CSS media selector.
     *
     * @param string $mediaSel
     */
    public function setMediaSelector ($mediaSel) {
        if ($this->importnew == NULL) {
            $this->setupImport();
        }
        $this->importnew->setMedia ($mediaSel);
    }

    /**
     * Callback function which adjusts all CSS length values to point.
     * 
     * @param $property The name of the current CSS property, e.g. 'border-left'
     * @param $value The current value from the original CSS code
     * @param $type There are 3 possible values:
     *              - LengthValueXAxis: the property represents a value on the X axis
     *              - LengthValueYAxis: the property represents a value on the Y axis
     *              - CSSValueType::StrokeOrBorderWidth: the property represents a stroke
     *                or border width
     * @return string The new, adjusted value for the property
     */
    public function adjustLengthCallback ($property, $value, $type, $rule) {
        // Get the digits and unit
        $digits = ODTUnits::getDigits($value);
        $unit = ODTUnits::stripDigits($value);

        if ( $unit == 'px' ) {
            // Replace px with pt (px does not seem to be supported by ODT)
            switch ($type) {
                case CSSValueType::LengthValueXAxis:
                    $adjusted = $this->toPoints($value, 'x');
                break;

                case CSSValueType::StrokeOrBorderWidth:
                    switch ($property) {
                        case 'border':
                        case 'border-left':
                        case 'border-right':
                        case 'border-top':
                        case 'border-bottom':
                            // border in ODT spans does not support 'px' units, so we convert it.
                            $adjusted = $this->toPoints($value, 'y');
                        break;

                        default:
                            $adjusted = $value;
                        break;
                    }
                break;

                case CSSValueType::LengthValueYAxis:
                default:
                    switch ($property) {
                        case 'line-height':
                            $adjusted = $this->toPoints($value, 'y');
                        break;
                        default:
                            $adjusted = $this->toPoints($value, 'y');
                        break;
                    }
                break;
            }
            return $adjusted;
        } else {
            if ($property == 'line-height' && $value != 'normal') {
                if ($unit == '%' || empty($unit)) {
                    // Relative values must be handled later
                    return $value;
                }
                $adjusted = $this->toPoints($value, 'y');
                return $adjusted;
            }
            return $value;
        }
        
        return $value;
    }

    public function replaceURLPrefixes ($callback) {
        if ($this->importnew != NULL) {
            $this->importnew->replaceURLPrefixes ($callback);
        }
    }

    public function enableLinks () {
        $this->linksEnabled = true;
    }

    public function disableLinks () {
        $this->linksEnabled = false;
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
    function addPlainText($text) {
        // Check if there is some content in the text.
        // Only insert bookmark/pagebreak/format change if text is not empty.
        // Otherwise a empty paragraph/line would be created!
        if ( !empty($text) && !ctype_space($text) ) {
            // Insert page bookmark if requested and not done yet.
            $this->insertPendingPageBookmark();

            // Insert pagebreak or page format change if still pending.
            // Attention: NOT if $text is empty. This would lead to empty lines before headings
            //            right after a pagebreak!
            $in_paragraph = $this->state->getInParagraph();
            if ( ($this->pagebreakIsPending() || $this->pageFormatChangeIsPending()) ||
                  !$in_paragraph ) {
                $this->paragraphOpen();
            }
        }
        $this->content .= $this->replaceXMLEntities($text);
        if ($this->text_empty && !ctype_space($text)) {
            $this->text_empty = false;
        }
    }

    /**
     * Open a text span.
     *
     * @param string $styleName The style to use.
     * @see ODTSpan::spanOpen for detailed documentation
     */
    function spanOpen($styleName, $element=NULL, $attributes=NULL){
        if ( !$in_paragraph ) {
            $this->paragraphOpen();
        }
        unset($this->params->elementObj);
        ODTSpan::spanOpen($this->params, $styleName, $element, $attributes);
    }

    /**
     * Open a text span using CSS.
     * 
     * @see ODTSpan::spanOpenUseCSS for detailed documentation
     */
    function spanOpenUseCSS($element=NULL, $attributes=NULL, cssimportnew $import=NULL){
        if ( !$in_paragraph ) {
            $this->paragraphOpen();
        }
        if ($import == NULL) {
            $import = $this->importnew;
        }
        if ($element == NULL) {
            $element = 'span';
        }
        unset($this->params->elementObj);
        $this->params->import = $import;
        ODTSpan::spanOpenUseCSS($this->params, $element, $attributes);
        $this->params->import = $this->importnew;
    }

    /**
     * Open a text span using properties.
     * 
     * @see ODTSpan::spanOpenUseProperties for detailed documentation
     */
    function spanOpenUseProperties($properties){
        if ( !$in_paragraph ) {
            $this->paragraphOpen();
        }
        ODTUtility::adjustValuesForODT($properties, $this->units);
        unset($this->params->elementObj);
        ODTSpan::spanOpenUseProperties($this->params, $properties);
    }

    /**
     * Close a text span.
     *
     * @param string $style_name The style to use.
     * @see ODTSpan::spanClose for detailed documentation
     */
    function spanClose() {
        unset($this->params->elementObj);
        ODTSpan::spanClose($this->params);
    }

    /**
     * Automatically generate ODT format for $HTMLCode
     * including text spans.
     *
     * @param string $style_name The style to use.
     * @see ODTSpan::generateSpansfromHTMLCode for detailed documentation
     */
    function generateSpansfromHTMLCode($HTMLCode){
        ODTSpan::generateSpansfromHTMLCode($this->params, $HTMLCode);
    }

    /**
     * Open a paragraph
     *
     * @param string $styleName The style to use.
     * @see ODTParagraph::paragraphOpen for detailed documentation
     */
    function paragraphOpen($styleName=NULL, $element=NULL, $attributes=NULL){
        unset($this->params->elementObj);
        ODTParagraph::paragraphOpen($this->params, $styleName, $element, $attributes);
    }

    /**
     * Close a paragraph
     * 
     * @see ODTParagraph::paragraphClose for detailed documentation
     */
    function paragraphClose(){
        unset($this->params->elementObj);
        ODTParagraph::paragraphClose($this->params);
    }

    /**
     * Open a paragraph using CSS.
     * 
     * @see ODTParagraph::paragraphOpenUseCSS for detailed documentation
     */
    function paragraphOpenUseCSS($element=NULL, $attributes=NULL, cssimportnew $import=NULL){
        if ($import == NULL) {
            $import = $this->importnew;
        }
        if ($element == NULL) {
            $element = 'p';
        }
        unset($this->params->elementObj);
        $this->params->import = $import;
        ODTParagraph::paragraphOpenUseCSS($this->params, $element, $attributes);
        $this->params->import = $this->importnew;
    }

    /**
     * Open a paragraph using properties.
     * 
     * @see ODTParagraph::paragraphOpenUseProperties for detailed documentation
     */
    function paragraphOpenUseProperties($properties){
        ODTUtility::adjustValuesForODT($properties, $this->units);
        unset($this->params->elementObj);
        ODTParagraph::paragraphOpenUseProperties($this->params, $properties);
    }

    /**
     * Insert a horizontal rule
     */
    function horizontalRule() {
        $this->paragraphClose();
        $styleName = $this->getStyleName('horizontal line');
        $this->paragraphOpen($styleName);
        $this->paragraphClose();

        // Save paragraph style name in 'Do not delete array'!
        $this->preventDeletetionStyles [] = $styleName;
    }

    /**
     * static call back to replace spaces
     *
     * @param array $matches
     * @return string
     */
    function _preserveSpace($matches){
        $spaces = $matches[1];
        $len    = strlen($spaces);
        return '<text:s text:c="'.$len.'"/>';
    }

    /**
     * @param string $text
     * @param string $style
     * @param bool $notescaped
     */
    function addPreformattedText($text, $style=null, $notescaped=true) {
        if (empty($style)) {
            $style = $this->getStyleName('preformatted');
        }
        if ($notescaped) {
            $text = $this->replaceXMLEntities($text);
        }
        
        // Check newline at start
        $first_newline = strpos($text, "\n");
        if ($first_newline !== FALSE and $first_newline == 0) {
            // text starts with a newline, remove it
            $text = substr($text,1);
        }
        
        // Check newline at end
        $length = strlen($text);
        if ($text[$length-1] == "\n") {
            $text = substr($text, 0, $length-1);
        }
        
        $text = str_replace("\n",'<text:line-break/>',$text);
        $text = str_replace("\t",'<text:tab/>',$text);
        $text = preg_replace_callback('/(  +)/',array($this,'_preserveSpace'),$text);

        $list_item = $this->state->getCurrentListItem();
        if ($list_item != NULL) {
            // if we're in a list item, we must close the <text:p> tag
            $this->paragraphClose();
            $this->paragraphOpen($style);
            $this->content .= $text;
            $this->paragraphClose();
            // FIXME: query previous style before preformatted text was opened and re-use it here
            $this->paragraphOpen();
        } else {
            $this->paragraphClose();
            $this->paragraphOpen($style);
            $this->content .= $text;
            $this->paragraphClose();
        }
    }

    /**
     * Add a linebreak
     */
    function linebreak() {
        $this->content .= '<text:line-break/>';
    }

    /**
     * Add a pagebreak
     */
    function pagebreak() {
        // Only set marker to insert a pagebreak on "next occasion".
        // The pagebreak will then be inserted in the next call to p_open() or header().
        // The style will be a "pagebreak" style with the paragraph or header style as the parent.
        // This prevents extra empty lines after the pagebreak.
        $this->paragraphClose();
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

    /**
     * Insert an index into the document using the parameters in $settings.
     * 
     * @see ODTIndex::insertIndex for detailed documentation
     */
    function insertIndex($type='toc', array $settings=NULL) {
        ODTIndex::insertIndex($this->params, $this->indexesData, $type, $settings);
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
    function setPageBookmark($id){
        $inParagraph = $this->state->getInParagraph();
        if ($inParagraph) {
            $this->insertBookmarkInternal($id, true);
        } else {
            $this->pageBookmark = $id;
        }
    }

    /**
     * Insert a bookmark. If $now is true then the bookmark will be created
     * immediately. This eventually causes a paragraph to be opened.
     * If $now is false then the bookmark will be inserted with the next
     * cdata text.
     *
     * @param string $id    ID of the bookmark
     * @param string $now   Insert bookmark immediately?
     */
    public function insertBookmark($id, $now) {
        if ($now) {
            $this->insertBookmarkInternal($id);
        } else {
            $this->pageBookmark = $id;
        }
    }

    /**
     * Insert a bookmark.
     *
     * @param string $id    ID of the bookmark
     */
    protected function insertBookmarkInternal($id, $openParagraph=true){
        if ($openParagraph) {
            $this->paragraphOpen();
        }
        $this->content .= '<text:bookmark text:name="'.$id.'"/>';
        $this->bookmarks [] = $id;
    }

    /**
     * Insert a pending page bookmark
     *
     * @param string $text  the text to display
     * @param int    $level header level
     * @param int    $pos   byte position in the original source
     */
    function insertPendingPageBookmark(){
        // Insert page bookmark if requested and not done yet.
        if ( !empty($this->pageBookmark) ) {
            $this->insertBookmarkInternal($this->pageBookmark, false);
            $this->pageBookmark = NULL;
        }
    }

    /**
     * Render a heading.
     *
     * @param string $text  the text to display
     * @param int    $level header level
     * @param int    $pos   byte position in the original source
     * @see ODTHeading::heading for detailed documentation
     */
    function heading($text, $level, $element=NULL, $attributes=NULL){
        ODTHeading::heading($this->params, $text, $level, $element, $attributes);
    }

    /**
     * Make sure that a user field name only contains valid sings.
     * (Code has been adopted from the fields plugin)
     *
     * @param string $name The name of the field
     * @return string The cleaned up $name
     * @author Aurelien Bompard <aurelien@bompard.org>
     */    
    protected function cleanupUserFieldName($name) {
        // Keep only allowed chars in the name
        return preg_replace('/[^a-zA-Z0-9_.]/', '', $name);
    }

    /**
     * Add a user field.
     * (Code has been adopted from the fields plugin)
     *
     * @param string $name The name of the field
     * @param string $value The value of the field
     * @author Aurelien Bompard <aurelien@bompard.org>
     */    
    public function addUserField($name, $value) {
        $name = $this->cleanupUserFieldName($name);
        $this->fields [$name] = $value;
    }

    /**
     * Insert a user field reference.
     * (Code has been adopted from the fields plugin)
     *
     * @param string $name The name of the field
     * @author Aurelien Bompard <aurelien@bompard.org>
     */    
    public function insertUserField($name) {
        $name = $this->cleanupUserFieldName($name);
        if (array_key_exists($name, $this->fields)) {
            $this->content .= '<text:user-field-get text:name="'.$name.'">'.$this->fields [$name].'</text:user-field-get>';
        }
    }

    /**
     * This function encodes the <text:user-field-decls> section of a
     * ODT document.
     * 
     * @return string
     */
    protected function getUserFieldDecls() {
        $value = '<text:user-field-decls>';
        foreach ($this->fields as $fname => $fvalue) {
            $value .= '<text:user-field-decl office:value-type="string" text:name="'.$fname.'" office:string-value="'.$fvalue.'"/>';
        }
        $value .= '</text:user-field-decls>';
        return $value;
    }

    /**
     * Get ODT file as string (ZIP archive).
     *
     * @param string $content The content
     * @return string String containing ODT ZIP stream
     */
    public function getODTFileAsString($ODTtemplate=NULL, $tempDir=NULL) {
        // Close any open paragraph if not done yet.
        $this->paragraphClose();

        // Replace local link placeholders with links to headings or bookmarks
        $styleName = $this->getStyleName('local link');
        $visitedStyleName = $this->getStyleName('visited local link');
        ODTUtility::replaceLocalLinkPlaceholders($this->content, $this->toc, $this->bookmarks, $styleName, $visitedStyleName);

        // Build indexes
        ODTIndex::replaceIndexesPlaceholders($this->params, $this->indexesData, $this->toc);

        // Delete paragraphs which only contain whitespace (but keep pagebreaks!)
        ODTUtility::deleteUselessElements($this->content, $this->preventDeletetionStyles);

        //$this->trace_dump .= $this->htmlStack->getDump();
        //$this->trace_dump .= $this->importnew->rulesToString();

        if (!empty($this->trace_dump)) {
            $this->paragraphOpen();
            $this->linebreak();
            $this->content .= 'Tracedump: ';
            $this->addPreformattedText($this->trace_dump);
            $this->paragraphClose();
        }

        // Get meta content
        $metaContent = $this->meta->getContent();

        // Get user field declarations
        $userFieldDecls = $this->getUserFieldDecls();

        // Build the document
        ODTExport::buildZIPFile($this->params,
                                $metaContent,
                                $userFieldDecls,
                                $this->pageStyles,
                                $ODTtemplate,
                                $tempDir);

        // Return document
        return $this->ZIP->getArchive();
    }

    /**
     * Import CSS code for styles from a string.
     *
     * @param string $cssCode The CSS code to import
     * @param string $mediaSel The media selector to use e.g. 'print'
     * @param string $mediaPath Local path to media files
     */
    public function importCSSFromString($cssCode, $mediaSel=NULL, $URLCallback=NULL, $forceStyles=false, $listAlign='right') {
        // Import CSS as styles?
        $importStyles = false;
        if ($this->CSSUsage == 'basic' || $this->CSSUsage == 'full' || $forceStyles) {
            $importStyles = true;
        }
        ODTImport::importCSSFromString ($this->params, $cssCode, $mediaSel, array($this, 'adjustLengthCallback'), $URLCallback, $this->registrations, $importStyles, $listAlign);
    }

    /**
     * Import CSS code for styles from a file.
     *
     * @param string $CSSTemplate String containing the path and file name of the CSS file to import
     * @param string $mediaSel The media selector to use e.g. 'print'
     * @param string $mediaPath Local path to media files
     */
    public function importCSSFromFile($CSSTemplate, $mediaSel=NULL, $URLCallback=NULL, $listAlign='right') {
        // Import CSS as styles?
        $importStyles = false;
        if ($this->CSSUsage == 'basic' || $this->CSSUsage == 'full') {
            $importStyles = true;
        }
        ODTImport::importCSSFromFile ($this->params, $CSSTemplate, $mediaSel, array($this, 'adjustLengthCallback'), $URLCallback, $this->registrations, $importStyles, $listAlign);
    }

    public function importODTStyles($template=NULL, $tempDir=NULL) {
        ODTImport::importODTStyles($this->params, $template, $tempDir);
    }

    /**
     * General internal function for closing an element.
     * Can always be used to close any open element if no more actions
     * are required apart from generating the closing tag and
     * removing the element from the state stack.
     */
    public function closeCurrentElement() {
        $current = $this->state->getCurrent();
        if ($current != NULL) {
            $this->content .= $current->getClosingTag($this->content);
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
        $style_obj = ODTPageLayoutStyle::createPageLayoutStyle($properties);
        $style_name = $style_obj->getProperty('style-name');
        
        // It is iassumed the proper media selector has been set by calling setMediaSelector()
        if (($this->CSSUsage == 'basic' || $this->CSSUsage == 'full') && $this->importnew != NULL) {
            ODTImport::set_page_properties ($this->params, $style_obj);
        }
        
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
     * @see ODTFootnote::footnoteOpen for detailed documentation
     */
    function footnoteOpen() {
        ODTFootnote::footnoteOpen($this->params);
    }

    /**
     * Close/end a footnote.
     *
     * @author Andreas Gohr
     * @see ODTFootnote::footnoteClose for detailed documentation
     */
    function footnoteClose() {
        ODTFootnote::footnoteClose($this->params);
    }

    function quoteOpen() {
        // Do not go higher than 5 because only 5 quotation styles are defined.
        if ( $this->quote_depth < 5 ) {
            $this->quote_depth++;
        }
        unset($this->params->elementObj);
        ODTTable::tableOpen($this->params, 1, 1, 'Table_Quotation'.$this->quote_depth, 'blockquote', NULL);
        $this->tableRowOpen();
        unset($this->params->elementObj);
        ODTTable::tableCellOpen($this->params, 1, 1, 'left', 'Cell_Quotation'.$this->quote_depth, NULL, NULL, NULL);
    }

    function quoteClose() {
        $this->paragraphClose();
        $this->tableCellClose();
        $this->tableRowClose();
        $this->tableClose();
        if ( $this->quote_depth > 0 ) {
            $this->quote_depth--;
        }
    }

    /**
     * Opens a list.
     * The list style specifies if the list is an ordered or unordered list.
     * 
     * @param bool $continue Continue numbering?
     * @param string $styleName Name of style to use for the list
     * @see ODTList::listOpen for detailed documentation
     */
    function listOpen($continue=false, $styleName, $element=NULL, $attributes=NULL) {
        if ($element == NULL) {
            if ($styleName == $this->getStyleName('list')) {
                $element = 'ul';
            }
            if ($styleName == $this->getStyleName('numbering')) {
                $element = 'ol';
            }
        }
        ODTList::listOpen($this->params, $continue, $styleName, $element, $attributes);
    }

    /**
     * Close a list.
     * 
     * @see ODTList::listClose for detailed documentation
     */
    function listClose() {
        ODTList::listClose($this->params);
    }

    /**
     * Open a list item.
     *
     * @param int $level The nesting level
     * @see ODTList::listItemOpen for detailed documentation
     */
    function listItemOpen($level, $element=NULL, $attributes=NULL) {
        ODTList::listItemOpen($this->params, $level, $element, $attributes);
    }

    /**
     * Close a list item.
     * 
     * @see ODTList::listItemClose for detailed documentation
     */
    function listItemClose() {
        ODTList::listItemClose($this->params);
    }

    /**
     * Open a list header.
     *
     * @param int $level The nesting level
     * @see ODTList::listHeaderOpen for detailed documentation
     */
    function listHeaderOpen($level, $element=NULL, $attributes=NULL) {
        ODTList::listHeaderOpen($this->params, $level, $element, $attributes);
    }

    /**
     * Close a list header.
     * 
     * @see ODTList::listHeaderClose for detailed documentation
     */
    function listHeaderClose() {
        ODTList::listHeaderClose($this->params);
    }

    /**
     * Open list content/a paragraph in a list item.
     * 
     * @see ODTList::listContentOpen for detailed documentation
     */
    function listContentOpen($element=NULL, $attributes=NULL) {
        ODTList::listContentOpen($this->params, $element, $attributes);
    }

    /**
     * Close list content/a paragraph in a list item.
     * 
     * @see ODTList::listContentClose for detailed documentation
     */
    function listContentClose() {
        ODTList::listContentClose($this->params);
    }

    /**
     * Open/start a table.
     *
     * @param int $maxcols maximum number of columns
     * @param int $numrows NOT IMPLEMENTED
     * @see ODTTable::tableOpen for detailed documentation
     */
    function tableOpen($maxcols = NULL, $numrows = NULL, $element=NULL, $attributes=NULL){
        unset($this->params->elementObj);
        ODTTable::tableOpen($this->params, $maxcols, $numrows, NULL, $element, $attributes);
    }

    /**
     * Close/finish a table.
     *
     * @param int $maxcols maximum number of columns
     * @param int $numrows NOT IMPLEMENTED
     * @see ODTTable::tableClose for detailed documentation
     */
    function tableClose(){
        unset($this->params->elementObj);
        ODTTable::tableClose($this->params);
    }

    /**
     * Add a column to a table.
     * 
     * @see ODTTable::tableAddColumn for detailed documentation
     */
    function tableAddColumn (){
        unset($this->params->elementObj);
        ODTTable::tableAddColumn ($this->params);
    }

    /**
     * Open a table row.
     * 
     * @see ODTTable::tableRowOpen for detailed documentation
     */
    function tableRowOpen($element=NULL, $attributes=NULL){
        unset($this->params->elementObj);
        ODTTable::tableRowOpen($this->params, NULL, $element, $attributes);
    }

    /**
     * Close a table row.
     * 
     * @see ODTTable::tableRowClose for detailed documentation
     */
    function tableRowClose(){
        unset($this->params->elementObj);
        ODTTable::tableRowClose($this->params);
    }

    /**
     * Open a table header cell.
     * 
     * @see ODTTable::tableHeaderOpen for detailed documentation
     */
    function tableHeaderOpen($colspan = 1, $rowspan = 1, $align, $element=NULL, $attributes=NULL){
        unset($this->params->elementObj);
        ODTTable::tableHeaderOpen($this->params, $colspan, $rowspan, $align, NULL, NULL, $element, $attributes);
    }

    /**
     * Close a table header cell.
     * 
     * @see ODTTable::tableHeaderClose for detailed documentation
     */
    function tableHeaderClose(){
        unset($this->params->elementObj);
        ODTTable::tableHeaderClose($this->params);
    }

    /**
     * Open a table cell.
     * 
     * @see ODTTable::tableCellOpen for detailed documentation
     */
    function tableCellOpen($colspan, $rowspan, $align, $element=NULL, $attributes=NULL){
        unset($this->params->elementObj);
        ODTTable::tableCellOpen($this->params, $colspan, $rowspan, $align, NULL, NULL, $element, $attributes);
    }

    /**
     * Close a table cell.
     * 
     * @see ODTTable::tableCellClose for detailed documentation
     */
    function tableCellClose(){
        unset($this->params->elementObj);
        ODTTable::tableCellClose($this->params);
    }

    /**
     * Open a table using CSS.
     * 
     * @see ODTTable::tableOpenUseCSS for detailed documentation
     */
    function tableOpenUseCSS($maxcols=NULL, $numrows=NULL, $element=NULL, $attributes=NULL, cssimportnew $import=NULL){
        if ($import == NULL) {
            $import = $this->importnew;
        }
        if ($element == NULL) {
            $element = 'table';
        }

        unset($this->params->elementObj);
        $this->params->import = $import;
        ODTTable::tableOpenUseCSS($this->params, $maxcols, $numrows, $element, $attributes);
        $this->params->import = $this->importnew;
    }

    /**
     * Open a table using properties.
     * 
     * @see ODTTable::tableOpenUseProperties for detailed documentation
     */
    function tableOpenUseProperties ($properties, $maxcols = 0, $numrows = 0){
        unset($this->params->elementObj);
        ODTTable::tableOpenUseProperties($this->params, $properties, $maxcols, $numrows);
    }

    /**
     * Add a table column using properties.
     * 
     * @see ODTTable::tableAddColumnUseProperties for detailed documentation
     */
    function tableAddColumnUseProperties ($properties){
        unset($this->params->elementObj);
        ODTTable::tableAddColumnUseProperties($this->params, $properties);
    }

    /**
     * Open a table header using CSS.
     * 
     * @see ODTTable::tableHeaderOpenUseCSS for detailed documentation
     */
    function tableHeaderOpenUseCSS($colspan = 1, $rowspan = 1, $element=NULL, $attributes=NULL, cssimportnew $import=NULL){
        if ($import == NULL) {
            $import = $this->importnew;
        }
        if ($element == NULL) {
            $element = 'th';
        }

        unset($this->params->elementObj);
        $this->params->import = $import;
        ODTTable::tableHeaderOpenUseCSS($this->params, $colspan, $rowspan, $element, $attributes);
        $this->params->import = $this->importnew;
    }

    /**
     * Open a table header using properties.
     * 
     * @see ODTTable::tableHeaderOpenUseProperties for detailed documentation
     */
    function tableHeaderOpenUseProperties($properties, $colspan = 1, $rowspan = 1){
        unset($this->params->elementObj);
        ODTTable::tableHeaderOpenUseProperties($this->params, $properties, $colspan, $rowspan);
    }

    /**
     * Open a table row using CSS.
     * 
     * @see ODTTable::tableRowOpenUseCSS for detailed documentation
     */
    function tableRowOpenUseCSS($element=NULL, $attributes=NULL, cssimportnew $import=NULL){
        if ($import == NULL) {
            $import = $this->importnew;
        }
        if ($element == NULL) {
            $element = 'tr';
        }

        unset($this->params->elementObj);
        $this->params->import = $import;
        ODTTable::tableRowOpenUseCSS($this->params, $element, $attributes);
        $this->params->import = $this->importnew;
    }

    /**
     * Open a table row using properties.
     * 
     * @see ODTTable::tableRowOpenUseProperties for detailed documentation
     */
    function tableRowOpenUseProperties($properties){
        unset($this->params->elementObj);
        ODTTable::tableRowOpenUseProperties($this->params, $properties);
    }

    /**
     * Open a table cell using CSS.
     * 
     * @see ODTTable::tableCellOpenUseCSS for detailed documentation
     */
    function tableCellOpenUseCSS($colspan = 1, $rowspan = 1, $element=NULL, $attributes=NULL, cssimportnew $import=NULL){
        if ($import == NULL) {
            $import = $this->importnew;
        }
        if ($element == NULL) {
            $element = 'td';
        }

        unset($this->params->elementObj);
        $this->params->import = $import;
        ODTTable::tableCellOpenUseCSS($this->params, $element, $attributes, $colspan, $rowspan);
        $this->params->import = $this->importnew;
    }

    /**
     * Open a table cell using properties.
     * 
     * @see ODTTable::tableCellOpenUseProperties for detailed documentation
     */
    function tableCellOpenUseProperties($properties, $colspan = 1, $rowspan = 1){
        unset($this->params->elementObj);
        ODTTable::tableCellOpenUseProperties($this->params, $properties, $colspan, $rowspan);
    }

    /**
     * Open a text box in a frame using CSS.
     * 
     * @see ODTFrame::openTextBoxUseCSS for detailed documentation
     */
    function openTextBoxUseCSS ($element=NULL, $attributes=NULL, cssimportnew $import=NULL) {
        if ($import == NULL) {
            $import = $this->importnew;
        }
        if ($element == NULL) {
            $element = 'div';
        }

        unset($this->params->elementObj);
        $this->params->import = $import;
        ODTFrame::openTextBoxUseCSS($this->params, $element, $attributes);
        $this->params->import = $this->importnew;
    }
    
    /**
     * Open a text box in a frame using properties.
     * 
     * @see ODTFrame::openTextBoxUseProperties for detailed documentation
     */
    function openTextBoxUseProperties ($properties) {
        unset($this->params->elementObj);
        ODTFrame::openTextBoxUseProperties($this->params, $properties);
    }

    /**
     * This function closes a textbox.
     * 
     * @see ODTFrame::closeTextBox for detailed documentation
     */
    function closeTextBox () {
        unset($this->params->elementObj);
        ODTFrame::closeTextBox($this->params);
    }

    /**
     * Open a frame using properties.
     * 
     * @see ODTFrame::openFrameUseProperties for detailed documentation
     */
    function openFrameUseProperties ($properties) {
        unset($this->params->elementObj);
        ODTFrame::openFrameUseProperties($this->params, $properties);
    }

    /**
     * This function closes a textbox.
     * 
     * @see ODTFrame::closeTextBox for detailed documentation
     */
    function closeFrame () {
        unset($this->params->elementObj);
        ODTFrame::closeFrame($this->params);
    }

    /**
     * Open a multi column text box in a frame using properties.
     * 
     * @see ODTFrame::openMultiColumnTextBoxUseProperties for detailed documentation
     */
    function openMultiColumnTextBoxUseProperties ($properties) {
        unset($this->params->elementObj);
        ODTFrame::openMultiColumnTextBoxUseProperties($this->params, $properties);
    }

    /**
     * This function closes a multi column textbox.
     * 
     * @see ODTFrame::closeTextBox for detailed documentation
     */
    function closeMultiColumnTextBox () {
        unset($this->params->elementObj);
        ODTFrame::closeMultiColumnTextBox($this->params);
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
                    $outline_style->setPropertyForLevel($level, 'num-suffix', NULL);
                    $outline_style->setPropertyForLevel($level, 'num-prefix', NULL);
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
     * @see ODTTextStyle::createTextStyle for more documentation
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
     * @see ODTParagraphStyle::createParagraphStyle for more documentation
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
     * @see ODTTableStyle::createTableTableStyle for more documentation
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
     * @see ODTTableRowStyle::createTableRowStyle for more documentation
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
     * @see ODTTableCellStyle::createTableCellStyle for more documentation
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
     * @see ODTTableColumnStyle::createTableColumnStyle for more documentation
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
    public function getImageSize($src, $maxwidth=NULL, $maxheight=NULL){
        return ODTUtility::getImageSize($src, $maxwidth, $maxheight);
    }

    /**
     * @param string $src
     * @param  $width
     * @param  $height
     * @return array
     */
    public function getImageSizeString($src, $width = NULL, $height = NULL){
        return ODTUtility::getImageSizeString($src, $width, $height, false, $this->params->units);
    }

    /**
     * Adds an image $src to the document.
     * 
     * @param string  $src        The path to the image file
     * @param string  $width      Width of the picture (NULL=original size)
     * @param string  $height     Height of the picture (NULL=original size)
     * @param string  $align      Alignment
     * @param string  $title      Title
     * @param string  $style      Optional "draw:style-name"
     * @param boolean $returnonly Only return code
     * 
     * @see ODTImage::addImage for a detailed description
     */
    public function addImage($src, $width = NULL, $height = NULL, $align = NULL, $title = NULL, $style = NULL, $returnonly = false){
        if ($returnonly) {
            return ODTImage::addImage($this->params, $src, $width, $height, $align, $title, $style, $returnonly);
        } else {
            ODTImage::addImage($this->params, $src, $width, $height, $align, $title, $style, $returnonly);
        }
    }

    /**
     * Adds an image $src to the document using the parameters set in $properties.
     * 
     * @param string  $src        The path to the image file
     * @param array   $properties Properties (width, height... see ODTImage::addImageUseProperties)
     * @param boolean $returnonly Only return code
     * 
     * @see ODTImage::addImageUseProperties for a detailed description
     */
    public function addImageUseProperties($src, $properties, $returnonly = false){
        if ($returnonly) {
            return ODTImage::addImageUseProperties($this->params, $src, $properties, $returnonly);
        } else {
            ODTImage::addImageUseProperties($this->params, $src, $properties, $returnonly);
        }
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
    public function addStringAsSVGImage($string, $width = NULL, $height = NULL, $align = NULL, $title = NULL, $style = NULL) {
        return ODTImage::addStringAsSVGImage($this->params, $string, $width, $height, $align, $title, $style);
    }

    /**
     * Get properties defined in a CSS style statement.
     * 
     * @see ODTUtility::getCSSStylePropertiesForODT
     */
    public function getCSSStylePropertiesForODT(&$properties, $style, $baseURL = NULL){
        ODTUtility::getCSSStylePropertiesForODT($properties, $style, $baseURL, $this->units);
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
    public function setPageFormat ($format=NULL, $orientation=NULL, $margin_top=NULL, $margin_right=NULL, $margin_bottom=NULL, $margin_left=NULL) {
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
            $this->paragraphClose();
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

    /**
     * Convert length value with valid XSL unit to pixel.
     *
     * @param string $value  String with length value, e.g. '20pt', '20cm'...
     * @param string $axis   Is the value to be converted a value on the X or Y axis? Default is 'y'.
     *        Only relevant for conversion from 'px' or 'em'.
     * @return string The current value.
     */
    public function toPixel ($value, $axis = 'y') {
        return $this->units->toPixel ($value, $axis);
    }

    public function setTitle ($title) {
        // Set title in meta info.
        $this->meta->setTitle($title);
    }

    /**
     * Get closest previous TOC entry with $level.
     * The function search backwards (previous) in the TOC entries
     * for the next entry with level $level and retunrs it reference ID.
     *
     * @param int    $level    the nesting level
     * @return string The reference ID or NULL
     */
    public function getPreviousToCItem($level) {
        $index = count($this->toc);
        for (; $index >= 0 ; $index--) {
            $item = $this->toc[$index];
            $params = explode (',', $item);
            if ($params [3] == $level) {
                return $params [0];
            }
        }
        return NULL;
    }

    /**
     * Insert cross reference to a "destination" inside of the ODT document.
     * To insert a link to an external destination use insertHyperlink().
     * 
     * The function only inserts a placeholder and resolves 
     * the reference on calling replaceLocalLinkPlaceholders();
     *
     * @fixme add image handling
     *
     * @param string $destination The resource to link to (e.g. heading ID)
     * @param string $text Text for the link (text inserted instead of $destination)
     */
    function insertCrossReference($destination, $text){
        $this->content .= '<locallink name="'.$text.'">'.$destination.'</locallink>';
    }

    function openImageLink ($url, $returnonly = false) {
        $encoded = '';
        if ($this->linksEnabled) {
            $url = ODTUtility::stringToIRI($url);
            $encoded = '<draw:a xlink:type="simple" xlink:href="'.$url.'">';
        }
        if ($returnonly) {
            return $encoded;
        }
        $this->content .= $encoded;
    }

    function closeImageLink ($returnonly = false) {
        $encoded = '';
        if ($this->linksEnabled) {
            $encoded = '</draw:a>';
        }
        if ($returnonly) {
            return $encoded;
        }
        $this->content .= $encoded;
    }

    function openHyperlink ($url, $styleName = NULL, $visitedStyleName = NULL, $returnonly = false) {
        $encoded = '';
        if ($url && $this->linksEnabled) {
            if (empty($styleName)) {
                $styleName = $this->getStyleName('internet link');
            }
            if (empty($visitedStyleName)) {
                $visitedStyleName = $this->getStyleName('visited internet link');
            }
            $url = ODTUtility::stringToIRI($url);
            $encoded .= '<text:a xlink:type="simple" xlink:href="'.$url.'"';
            $encoded .= ' text:style-name="'.$styleName.'"';
            $encoded .= ' text:visited-style-name="'.$visitedStyleName.'"';
            $encoded .= '>';
        }
        if ($returnonly) {
            return $encoded;
        }
        $this->content .= $encoded;
    }

    function closeHyperlink ($returnonly = false) {
        $encoded = '';
        if ($this->linksEnabled) {
            $encoded .= '</text:a>';
        }
        if ($returnonly) {
            return $encoded;
        }
        $this->content .= $encoded;
    }

    function insertHyperlink ($url, $text, $styleName = NULL, $visitedStyleName = NULL, $returnonly = false) {
        $encoded = '';
        if ($url && $this->linksEnabled) {
            if (empty($styleName)) {
                $styleName = $this->getStyleName('internet link');
            }
            if (empty($visitedStyleName)) {
                $visitedStyleName = $this->getStyleName('visited internet link');
            }
            $url = ODTUtility::stringToIRI($url);
            $encoded .= '<text:a xlink:type="simple" xlink:href="'.$url.'"';
            $encoded .= ' text:style-name="'.$styleName.'"';
            $encoded .= ' text:visited-style-name="'.$visitedStyleName.'"';
            $encoded .= '>';
        }
        // We get the text already XML encoded
        $encoded .= $text;
        if ($url && $this->linksEnabled) {
            $encoded .= '</text:a>';
        }
        if ($returnonly) {
            return $encoded;
        }
        $this->content .= $encoded;
    }

    /**
     * Get CSS properties for a given element and adjust them for ODT.
     *
     * @param array $dest Properties found will be written in $dest as key value pairs,
     *                    e.g. $dest ['color'] = 'black';
     * @param iElementCSSMatchable $element The element object for which the properties are queried.
     *                                      The class of the element needs to implement the interface
     *                                      iElementCSSMatchable.
     * @param string $media_sel The media selector to use for the query e.g. 'print'. May be empty.
     */
    public function getODTProperties (array &$dest, $element, $attributes=NULL, $media_sel=NULL, $inherit=true) {
        if ($this->importnew == NULL) {
            return;
        }

        $save = $this->importnew->getMedia();
        $this->importnew->setMedia($media_sel);

        $maxWidth = $this->getAbsWidthMindMargins().'cm';
        ODTUtility::getHTMLElementProperties($this->params, $dest, $element, $attributes, $maxWidth, $inherit);

        $this->importnew->setMedia($save);
    }

    public function getODTPropertiesFromElement (array &$dest, iElementCSSMatchable $element, $media_sel=NULL, $inherit=true) {
        if ($this->importnew == NULL) {
            return;
        }

        $save = $this->importnew->getMedia();
        $this->importnew->setMedia($media_sel);

        // Get properties for our class/element from imported CSS
        $this->importnew->getPropertiesForElement($dest, $element, $this->units, $inherit);

        // Adjust values for ODT
        $maxWidth = $this->getAbsWidthMindMargins().'cm';
        ODTUtility::adjustValuesForODT($dest, $this->units, $maxWidth);

        $this->importnew->setMedia($save);
    }

    public function adjustValuesForODT (array &$properties) {
        ODTUtility::adjustValuesForODT($properties, $this->units);
    }

    public function adjustValueForODT ($property, $value) {
        return ODTUtility::adjustValueForODT($property, $value, $this->units);
    }

    /**
     * Adds an $element with $attributes to the internal HTML stack for
     * CSS matching. HTML elments added from extern using this function
     * are supposed to never be closed so only root elements should be
     * added like 'html' or 'body' or maybe a 'div' that should always
     * be present for proper CSS matching.
     * 
     * @param string $element The element name, e.g. 'body'
     * @param string $attributes The elements attributes, e.g. 'lang="en" dir="ltr"'
     */
    public function addHTMLElement ($element, $attributes = NULL) {
        $this->htmlStack->open($element, $attributes);
        $this->htmlStack->saveRootIndex ();
    }

    public function getHTMLStack () {
        return $this->htmlStack;
    }

    public function dumpHTMLStack () {
        $this->trace_dump .= $this->htmlStack->getDump();
    }

    /**
     * Check if a file already exists in the document.
     *
     * @param string $fileName Full file name in the document
     *                         e.g. 'Pictures/myimage.png'
     * @return bool
     */    
    public function fileExists($name) {
        return $this->manifest->exists($name);
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
        if(!$this->manifest->exists($fileName)){
            $this->manifest->add($fileName, $mime);
            $this->ZIP->addData($fileName, $content);
            return true;
        }

        // File with that name already exists!
        return false;
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
        $name = '';
        if (file_exists($fileName)) {
            list($ext,$mime) = mimetype($fileName);
            $name = 'Pictures/'.md5($fileName).'.'.$ext;
            $this->addFile($name, $mime, io_readfile($fileName,false));
        }
        return $name;
    }

    /**
     * Add style object to the document as a common style.
     *
     * @param ODTStyle $new Object to add
     */
    public function addStyle(ODTStyle $new) {
        return $this->styleset->addStyle($new);
    }

    /**
     * Add style object to the document as an automatic style.
     *
     * @param ODTStyle $new Object to add
     */
    public function addAutomaticStyle(ODTStyle $new) {
        return $this->styleset->addAutomaticStyle($new);
    }

    /**
     * Check if a style with $styleName already exists.
     *
     * @param string $styleName The style name ofthe style style.
     * @return bool
     */    
    public function styleExists ($name) {
        return $this->styleset->styleExists($name);
    }

    /**
     * Get the style object with style name $styleName.
     *
     * @param string $styleName The style name ofthe style style.
     * @return ODTStyle The style object
     */    
    public function getStyle ($styleName) {
        return $this->styleset->getStyle($styleName);
    }

    public function getDefaultStyle ($family) {
        return $this->styleset->getDefaultStyle($family);
    }

    /**
     * Get the style name for a style alias.
     *
     * @param string $alias The alias for the style.
     * @return string The style name used in the ODT document
     */    
    public function getStyleName($alias) {
        return $this->styleset->getStyleName($alias);
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

    /**
     * Get the style object by $alias.
     *
     * @param string $alias The alias for the style.
     * @return ODTStyle The style object
     */    
    public function getStyleByAlias($alias) {
        return $this->styleset->getStyle($this->styleset->getStyleName($alias));
    }

    public function registerHTMLElementForCSSImport ($style_type, $element, $attributes=NULL) {
        $this->registrations [$style_type]['element'] = $element;
        $this->registrations [$style_type]['attributes'] = $attributes;
    }

    public function addToValue ($value, $add) {
        $valueInPt = $this->units->toPoints($value, 'y');
        $valueInPt = $this->units->getDigits($valueInPt);
        $addInPt = $this->units->toPoints($add, 'y');
        $addInPt = $this->units->getDigits($addInPt);
        return ($valueInPt + $addInPt).'pt';
    }

    public function subFromValue ($value, $sub) {
        $valueInPt = $this->units->toPoints($value, 'y');
        $valueInPt = $this->units->getDigits($valueInPt);
        $subInPt = $this->units->toPoints($sub, 'y');
        $subInPt = $this->units->getDigits($subInPt);
        return ($valueInPt - $subInPt).'pt';
    }

    /**
     * Adjust font sizes of all styles to $newBaseSize.
     * The $newBaseSize will be the new default font-size and all
     * other font-sizes will be re-calculated.
     *
     * @param string $newBaseSize The new base size e.g. '16pt'
     */    
    public function adjustFontSizes($newBaseSize) {
        // First get the old base size
        $default = $this->styleset->getDefaultStyle('paragraph');
        if ($default == NULL) {
            // ???
            return;
        }
        $oldBaseSize = $default->getProperty('font-size');
        if ($oldBaseSize === NULL) {
            return;
        }
        $oldBaseSizeInPt = trim($this->units->toPoints($oldBaseSize, 'y'), 'pt');

        // Convert new base size to pt        
        $newBaseSizeInPt = trim($this->units->toPoints($newBaseSize, 'y'), 'pt');

        $styles_list = array();
        $styles_list [] = $this->styleset->getStyles();
        $styles_list [] = $this->styleset->getAutomaticStyles();
        $styles_list [] = $this->styleset->getMasterStyles();

        // Go through the list of style arrays and adjust each one
        // having a 'font-size' property
        foreach ($styles_list as $styles) {
            foreach ($styles as $style) {
                $fontSize = $style->getProperty('font-size');
                if ($fontSize !== NULL) {
                    $fontSizeInPt = trim($this->units->toPoints($fontSize, 'y'), 'pt');
                    $fontSizeInPt = ($fontSizeInPt/$oldBaseSizeInPt) * $newBaseSizeInPt;
                    $fontSizeInPt = round($fontSizeInPt, 2);
                    $style->setProperty('font-size', $fontSizeInPt.'pt');
                }
            }
        }

        $this->trace_dump .= 'newBaseSize: '.$newBaseSize."\n";
        $this->trace_dump .= 'newBaseSizeInPt: '.$newBaseSizeInPt."\n";
        // Also set default font-size to the new base size!
        $default->setProperty('font-size', $newBaseSizeInPt.'pt');
    }

    /**
     * The function sets the alignment and indentation for ordered lists.
     * This means the alignment of the numbers if front of each list item.
     * For each alignment predefined values for the attributes 'list-tab-stop-position',
     * 'text-indent' and 'margin-left' is set.
     *
     * @param string  $align       Alignment to set ('left'/'start', 'center', 'right'/'end')
     * @param integer $paddingLeft Left padding in centimeters, moves the whole list to the right
     * @param integer $marginLeft  Left margin in centimeters, specifies the indent per level
     */    
    public function setOrderedListParams($setLevel=NULL, $align, $paddingLeft=0, $marginLeft=1) {
        if (empty($align)) {
            return;
        }
        $name = $this->styleset->getStyleName('numbering');
        $style = $this->styleset->getStyle($name);
        if ($style == NULL ) {
            return;
        }

        if ($setLevel === NULL ) {
            for ($level = 1 ; $level < 11 ; $level++) {
                switch ($align) {
                    case 'left':
                    case 'start':
                        $dist = 1;
                        $style->setPropertyForLevel($level, 'text-align', 'left');
                        break;
                    case 'center':
                        $dist = 0.5;
                        $style->setPropertyForLevel($level, 'text-align', 'center');
                        break;
                    case 'right':
                    case 'end':
                    default:
                        $dist = 0.25;
                        $style->setPropertyForLevel($level, 'text-align', 'end');
                        break;
                }
                $position = $paddingLeft + ($marginLeft * $level) + $dist;
                $style->setPropertyForLevel($level, 'list-level-position-and-space-mode', 'label-alignment');
                $style->setPropertyForLevel($level, 'label-followed-by', 'listtab');
                $style->setPropertyForLevel($level, 'list-tab-stop-position', $position.'cm');
                $style->setPropertyForLevel($level, 'text-indent', ($dist*-1).'cm');
                $style->setPropertyForLevel($level, 'margin-left', $position.'cm');
            }
        } else {
            switch ($align) {
                case 'left':
                case 'start':
                    $dist = 1;
                    $style->setPropertyForLevel($setLevel, 'text-align', 'left');
                    break;
                case 'center':
                    $dist = 0.5;
                    $style->setPropertyForLevel($setLevel, 'text-align', 'center');
                    break;
                case 'right':
                case 'end':
                default:
                    $dist = 0.25;
                    $style->setPropertyForLevel($setLevel, 'text-align', 'end');
                    break;
            }
            $position = $paddingLeft + ($marginLeft * $setLevel) + $dist;
            $style->setPropertyForLevel($setLevel, 'list-level-position-and-space-mode', 'label-alignment');
            $style->setPropertyForLevel($setLevel, 'label-followed-by', 'listtab');
            $style->setPropertyForLevel($setLevel, 'list-tab-stop-position', $position.'cm');
            $style->setPropertyForLevel($setLevel, 'text-indent', ($dist*-1).'cm');
            $style->setPropertyForLevel($setLevel, 'margin-left', $position.'cm');
        }
    }

    /**
     * The function sets the alignment and indentation for unordered lists.
     * This means the alignment of the icons/buttons if front of each list item.
     * For each alignment predefined values for the attributes 'list-tab-stop-position',
     * 'text-indent' and 'margin-left' is set.
     *
     * @param string  $align       Alignment to set ('left'/'start', 'center', 'right'/'end')
     * @param integer $paddingLeft Left padding in centimeters, moves the whole list to the right
     * @param integer $marginLeft  Left margin in centimeters, specifies the indent per level
     */    
    public function setUnorderedListParams($setLevel=NULL, $align, $paddingLeft=0, $marginLeft=1) {
        if (empty($align)) {
            return;
        }
        $name = $this->styleset->getStyleName('list');
        $style = $this->styleset->getStyle($name);
        if ($style == NULL ) {
            return;
        }

        if ($setLevel === NULL) {
            for ($level = 1 ; $level < 11 ; $level++) {
                switch ($align) {
                    case 'left':
                    case 'start':
                        $dist = 1;
                        $style->setPropertyForLevel($level, 'text-align', 'left');
                        break;
                    case 'center':
                        $dist = 0.5;
                        $style->setPropertyForLevel($level, 'text-align', 'center');
                        break;
                    case 'right':
                    case 'end':
                    default:
                        $dist = 0.25;
                        $style->setPropertyForLevel($level, 'text-align', 'end');
                        break;
                }
                $position = $paddingLeft + ($marginLeft * $level) + $dist;
                $style->setPropertyForLevel($level, 'list-level-position-and-space-mode', 'label-alignment');
                $style->setPropertyForLevel($level, 'label-followed-by', 'listtab');
                $style->setPropertyForLevel($level, 'list-tab-stop-position', $position.'cm');
                $style->setPropertyForLevel($level, 'text-indent', ($dist*-1).'cm');
                $style->setPropertyForLevel($level, 'margin-left', $position.'cm');
            }
        } else {
            switch ($align) {
                case 'left':
                case 'start':
                    $dist = 1;
                    $style->setPropertyForLevel($setLevel, 'text-align', 'left');
                    break;
                case 'center':
                    $dist = 0.5;
                    $style->setPropertyForLevel($setLevel, 'text-align', 'center');
                    break;
                case 'right':
                case 'end':
                default:
                    $dist = 0.25;
                    $style->setPropertyForLevel($setLevel, 'text-align', 'end');
                    break;
            }
            $position = $paddingLeft + ($marginLeft * $setLevel) + $dist;
            $style->setPropertyForLevel($setLevel, 'list-level-position-and-space-mode', 'label-alignment');
            $style->setPropertyForLevel($setLevel, 'label-followed-by', 'listtab');
            $style->setPropertyForLevel($setLevel, 'list-tab-stop-position', $position.'cm');
            $style->setPropertyForLevel($setLevel, 'text-indent', ($dist*-1).'cm');
            $style->setPropertyForLevel($setLevel, 'margin-left', $position.'cm');
        }
    }

    /**
     * Automatically generate ODT format for given $HTMLCode.
     *
     * @param string $HTMLCode
     * @see ODTUtility::generateODTfromHTMLCode for detailed documentation
     */
    public function generateODTfromHTMLCode($HTMLCode, array $options){
        ODTUtility::generateODTfromHTMLCode($this->params, $HTMLCode, $options);
    }
}
