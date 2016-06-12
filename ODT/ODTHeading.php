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
    static public function heading(ODTDocument $doc, $text, $level, &$content, $element=NULL, $attributes=NULL){
        // Close any open paragraph first
        $doc->paragraphClose($content);

        $hid = self::headerToLink($doc, $text, true);
        $TOCRef = $doc->buildTOCReferenceID($text);
        $style = $doc->getStyleName('heading'.$level);

        // Change page format if pending
        if ( $doc->pageFormatChangeIsPending() ) {
            $pageStyle = $doc->doPageFormatChange($style);
            if ( $pageStyle != NULL ) {
                $style = $pageStyle;

                // Delete pagebreak, the format change will also introduce a pagebreak.
                $doc->setPagebreakPending(false);
            }
        }

        // Insert pagebreak if pending
        if ( $doc->pagebreakIsPending() ) {
            $style = $doc->createPagebreakStyle ($style);
            $doc->setPagebreakPending(false);
        }
        $content .= '<text:h text:style-name="'.$style.'" text:outline-level="'.$level.'">';

        // Insert page bookmark if requested and not done yet.
        $doc->insertPendingPageBookmark($content);

        $content .= '<text:bookmark-start text:name="'.$TOCRef.'"/>';
        $content .= '<text:bookmark-start text:name="'.$hid.'"/>';
        $content .= $doc->replaceXMLEntities($text);
        $content .= '<text:bookmark-end text:name="'.$TOCRef.'"/>';
        $content .= '<text:bookmark-end text:name="'.$hid.'"/>';
        $content .= '</text:h>';

        // Do not add headings in frames
        $frame = $doc->state->getCurrentFrame();
        if ($frame == NULL) {
            $doc->tocAddItemInternal($TOCRef, $hid, $text, $level);
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
