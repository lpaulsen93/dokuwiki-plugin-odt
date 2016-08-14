<?php

require_once DOKU_PLUGIN . 'odt/ODT/ODTDocument.php';

/**
 * ODTFootnote:
 * Class containing static code for handling footnotes.
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author Andreas Gohr
 */
class ODTFootnote
{
    /**
     * Open/start a footnote.
     *
     * All following content will go to the footnote instead of
     * the document. To achieve this the previous content
     * is moved to $store and $content is cleared
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    function footnoteOpen(ODTInternalParams $params, $element=NULL, $attributes=NULL) {
        // $element and $attributes are actually unused

        // Move current content to store and record footnote
        $params->document->store = $params->content;
        $params->content = '';
    }

    /**
     * Close/end a footnote.
     *
     * All content is moved to the $footnotes array and the old
     * content is restored from $store again.
     *
     * @author Andreas Gohr
     */
    function footnoteClose(ODTInternalParams $params) {
        // Recover footnote into the stack and restore old content
        $footnote = $params->content;
        $params->content = $params->document->store;
        $params->document->store = '';

        // Check to see if this footnote has been seen before
        $i = array_search($footnote, $params->document->footnotes);

        if ($i === false) {
            $i = count($params->document->footnotes);
            // Its a new footnote, add it to the $footnotes array
            $params->document->footnotes[$i] = $footnote;

            $params->content .= '<text:note text:id="ftn'.$i.'" text:note-class="footnote">';
            $params->content .= '<text:note-citation>'.($i+1).'</text:note-citation>';
            $params->content .= '<text:note-body>';
            $params->content .= '<text:p text:style-name="'.$params->document->getStyleName('footnote').'">';
            $params->content .= $footnote;
            $params->content .= '</text:p>';
            $params->content .= '</text:note-body>';
            $params->content .= '</text:note>';
        } else {
            // Seen this one before - just reference it FIXME: style isn't correct yet
            $params->content .= '<text:note-ref text:note-class="footnote" text:ref-name="ftn'.$i.'">'.($i+1).'</text:note-ref>';
        }
    }
}
