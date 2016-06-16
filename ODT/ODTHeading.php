<?php

require_once DOKU_PLUGIN . 'odt/ODT/ODTDocument.php';

/**
 * ODTHeading:
 * Class containing static code for handling headings.
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 */
class ODTHeading
{
    /**
     * Render a heading
     *
     * @param string $text  the text to display
     * @param int    $level header level
     * @param int    $pos   byte position in the original source
     */
    static public function heading(ODTInternalParams $params, $text, $level, $element=NULL, $attributes=NULL){
        // Close any open paragraph first
        $params->document->paragraphClose();

        $hid = self::headerToLink($params->document, $text, true);
        $TOCRef = $params->document->buildTOCReferenceID($text);
        $style = $params->document->getStyleName('heading'.$level);

        // Change page format if pending
        if ( $params->document->pageFormatChangeIsPending() ) {
            $pageStyle = $params->document->doPageFormatChange($style);
            if ( $pageStyle != NULL ) {
                $style = $pageStyle;

                // Delete pagebreak, the format change will also introduce a pagebreak.
                $params->document->setPagebreakPending(false);
            }
        }

        // Insert pagebreak if pending
        if ( $params->document->pagebreakIsPending() ) {
            $style = $params->document->createPagebreakStyle ($style);
            $params->document->setPagebreakPending(false);
        }
        $params->content .= '<text:h text:style-name="'.$style.'" text:outline-level="'.$level.'">';

        // Insert page bookmark if requested and not done yet.
        $params->document->insertPendingPageBookmark();

        $params->content .= '<text:bookmark-start text:name="'.$TOCRef.'"/>';
        $params->content .= '<text:bookmark-start text:name="'.$hid.'"/>';
        $params->content .= $params->document->replaceXMLEntities($text);
        $params->content .= '<text:bookmark-end text:name="'.$TOCRef.'"/>';
        $params->content .= '<text:bookmark-end text:name="'.$hid.'"/>';
        $params->content .= '</text:h>';

        // Do not add headings in frames
        $frame = $params->document->state->getCurrentFrame();
        if ($frame == NULL) {
            $params->document->tocAddItemInternal($TOCRef, $hid, $text, $level);
        }
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
    static protected function headerToLink(ODTDocument $doc, $title,$create=false) {
        // FIXME: not DokuWiki dependant function woud be nicer...
        $title = str_replace(':','',cleanID($title));
        $title = ltrim($title,'0123456789._-');
        if(empty($title)) {
            $title='section';
        }

        if($create){
            // Make sure tiles are unique
            $num = '';
            while($doc->headerExists($title.$num)){
                ($num) ? $num++ : $num = 1;
            }
            $title = $title.$num;
            $doc->addHeader($title);
        }

        return $title;
    }
}
