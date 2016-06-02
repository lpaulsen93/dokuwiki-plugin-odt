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
    /** @var changePageFormat */
    public $changePageFormat = NULL;
    /** @var Current pageFormat */
    public $page = null;
    /** @var Array of used page styles. Will stay empty if only A4-portrait is used */
    public $page_styles = array ();
    /** @var Array of paragraph style names that prevent an empty paragraph from being deleted */
    public $preventDeletetionStyles = array ();
    /** @var pagebreak */
    public $pagebreak = false;

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
     * Add a linebreak
     */
    function linebreak(&$content) {
        $content .= '<text:line-break/>';
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
    public function getODTFileAsString(&$content, $metaContent, $userFieldDecls, array $pageStyles) {
        // Delete paragraphs which only contain whitespace (but keep pagebreaks!)
        $this->deleteUselessElements($content);

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
        $this->page_styles [$master_page_style_name] = $style_name;
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

    /**
     * This function deletes the useless elements. Right now, these are empty paragraphs
     * or paragraphs that only include whitespace.
     *
     * IMPORTANT:
     * Paragraphs can be used for pagebreaks/changing page format.
     * Such paragraphs may not be deleted!
     */
    protected function deleteUselessElements(&$docContent) {
        $length_open = strlen ('<text:p');
        $length_close = strlen ('</text:p>');
        $max = strlen ($docContent);
        $pos = 0;

        while ($pos < $max) {
            $start_open = strpos ($docContent, '<text:p', $pos);
            if ( $start_open === false ) {
                break;
            }
            $start_close = strpos ($docContent, '>', $start_open + $length_open);
            if ( $start_close === false ) {
                break;
            }
            $end = strpos ($docContent, '</text:p>', $start_close + 1);
            if ( $end === false ) {
                break;
            }

            $deleted = false;
            $length = $end - $start_open + $length_close;
            $content = substr ($docContent, $start_close + 1, $end - ($start_close + 1));

            if ( empty($content) || ctype_space ($content) ) {
                // Paragraph is empty or consists of whitespace only. Check style name.
                $style_start = strpos ($docContent, '"', $start_open);
                if ( $style_start === false ) {
                    // No '"' found??? Ignore this paragraph.
                    break;
                }
                $style_end = strpos ($docContent, '"', $style_start+1);
                if ( $style_end === false ) {
                    // No '"' found??? Ignore this paragraph.
                    break;
                }
                $style_name = substr ($docContent, $style_start+1, $style_end - ($style_start+1));

                // Only delete empty paragraph if not listed in 'Do not delete' array!
                if ( !in_array($style_name, $this->preventDeletetionStyles) )
                {
                    $docContent = substr_replace($docContent, '', $start_open, $length);

                    $deleted = true;
                    $max -= $length;
                    $pos = $start_open;
                }
            }

            if ( $deleted == false ) {
                $pos = $start_close;
            }
        }
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
}
