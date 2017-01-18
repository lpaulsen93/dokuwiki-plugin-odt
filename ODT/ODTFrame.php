<?php
/**
 * ODTFrame: Frame handling.
 * 
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     LarsDW223
 */

/** Include ODTDocument.php */
require_once DOKU_PLUGIN . 'odt/ODT/ODTDocument.php';

/**
 * ODTFrame:
 * Class containing static code for handling frames.
 *
 * @package    ODT\Frame
 */
class ODTFrame
{
    static $frameCount = 0;
    static $fields = array('background-color'           => 'fo:background-color',
                           'fill-color'                 => 'draw:fill-color',
                           'fill'                       => 'draw:fill',
                           'stroke-color'               => 'svg:stroke-color',
                           'stroke'                     => 'draw:stroke',
                           'stroke-width'               => 'svg:stroke-width',
                           'border'                     => 'fo:border',
                           'border-left'                => 'fo:border-left',
                           'border-right'               => 'fo:border-right',
                           'border-top'                 => 'fo:border-top',
                           'border-bottom'              => 'fo:border-bottom',
                           'padding-left'               => 'fo:padding-left',
                           'padding-right'              => 'fo:padding-right',
                           'padding-top'                => 'fo:padding-top',
                           'padding-bottom'             => 'fo:padding-bottom',
                           'margin-left'                => 'fo:margin-left',
                           'margin-right'               => 'fo:margin-right',
                           'margin-top'                 => 'fo:margin-top',
                           'margin-bottom'              => 'fo:margin-bottom',
                           'vertical-align'             => 'draw:textarea-vertical-align',
                           'horizontal-align'           => 'draw:textarea-horizontal-align',
                           'min-height'                 => 'fo:min-height',
                           'background-transparency'    => 'style:background-transparency',
                           'textarea-horizontal-align'  => 'draw:textarea-horizontal-align',
                           'run-through'                => 'style:run-through',
                           'vertical-pos'               => 'style:vertical-pos',
                           'vertical-rel'               => 'style:vertical-rel',
                           'horizontal-pos'             => 'style:horizontal-pos',
                           'horizontal-rel'             => 'style:horizontal-rel',
                           'wrap'                       => 'style:wrap',
                           'number-wrapped-paragraphs'  => 'style:number-wrapped-paragraphs',
                           'wrap-influence-on-position' => 'draw:wrap-influence-on-position'
    );
    
    /**
     * This function opens a textbox in a frame using CSS.
     *
     * The currently supported CSS properties are:
     * background-color, color, padding, margin, display, border-radius, min-height.
     * The background-image is simulated using a picture frame.
     * FIXME: Find a way to successfuly use the background-image in the graphic style (see comments).
     *
     * The text box should be closed by calling 'closeTextBox()'.
     *
     * @param     ODTInternalParams $params     Commom params.
     * @param     string            $element    The element name, e.g. "div"
     * @param     string            $attributes The attributes belonging o the element, e.g. 'class="example"'
     */
    public static function openTextBoxUseCSS (ODTInternalParams $params, $element=NULL, $attributes=NULL) {
        $frame = $params->document->state->getCurrentFrame();
        if ($frame != NULL) {
            // Do not open a nested frame as this will make the content ofthe nested frame disappear.
            //return;
        }

        $properties = array();
        ODTUtility::openHTMLElement ($params, $properties, $element, $attributes);
        $params->elementObj = $params->htmlStack->getCurrentElement();

        self::openTextBoxUseProperties ($params, $properties);
    }

    /**
     * This function opens a textbox in a frame.
     *
     * The currently supported CSS properties are:
     * background-color, color, padding, margin, display, border-radius, min-height.
     * The background-image is simulated using a picture frame.
     * FIXME: Find a way to successfuly use the background-image in the graphic style (see comments).
     *
     * The text box should be closed by calling 'closeTextBox()'.
     *
     * @param     ODTInternalParams $params     Commom params.
     * @param     array             $properties Properties to use for creating the text box
     * @param     string            $element    The element name, e.g. "div"
     * @param     string            $attributes The attributes belonging o the element, e.g. 'class="example"'
     */
    public static function openTextBoxUseProperties (ODTInternalParams $params, $properties, $element=NULL, $attributes=NULL) {
        // Encode frame
        self::openFrameUseProperties ($params, $properties, $element, $attributes);
        
        // Create text box
        $box = new ODTElementTextBox();
        $box_attrs = '';
        // If required use round corners.
        if ( !empty($properties ['border-radius']) )
            $box_attrs .= 'draw:corner-radius="'.$properties ['border-radius'].'"';
        $box->setAttributes($box_attrs);
        $params->document->state->enter($box);

        // Encode box
        $params->content .= $box->getOpeningTag($params);
    }

    /**
     * This function closes a textbox (previously opened with openTextBoxUseProperties()).
     * 
     * @param     ODTInternalParams $params     Commom params.
     */
    function closeTextBox (ODTInternalParams $params) {
        // Close paragraph (if open)
        $params->document->paragraphClose();
        // Close text box
        $params->document->closeCurrentElement();
        // Close frame
        self::closeFrame($params);
    }

    /**
     * This function opens a multi column frame/text box according to the
     * parameters in $properties. Call 'closeMultiColumnTextBox()' to
     * close the text box.
     *
     * @param     ODTInternalParams $params     Commom params.
     * @param     array             $properties Properties to use
     * @see ODTUnknownStyle::createMultiColumnFrameStyle for information
     *      about supported $properties.
     */
    public static function openMultiColumnTextBoxUseProperties (ODTInternalParams $params, $properties) {
        if ($element == NULL) {
            $element = 'div';
        }

        // Create style name.
        $style_obj = ODTUnknownStyle::createMultiColumnFrameStyle ($properties);
        $params->document->addAutomaticStyle($style_obj);
        $style_name = $style_obj->getProperty('style-name');

        $width_abs = $params->document->getAbsWidthMindMargins (100);

        // Group the frame so that they are stacked one on each other.
        $params->document->paragraphClose();
        $params->document->paragraphOpen();

        // Draw a frame with a text box in it. the text box will be left opened
        // to grow with the content (requires fo:min-height in $style_name).

        if ($params->elementObj == NULL) {
            $properties = array();
            ODTUtility::openHTMLElement ($params, $properties, $element, $attributes);
        }

        // Create frame
        $frame = new ODTElementFrame($style_name);
        self::$frameCount++;
        $frame_attrs = 'draw:name="Frame'.self::$frameCount.'" text:anchor-type="paragraph" svg:width="'.$width_abs.'cm" draw:z-index="0">';
        $frame->setAttributes($frame_attrs);
        $params->document->state->enter($frame);
        $frame->setHTMLElement ($element);

        // Encode frame
        $params->content .= $frame->getOpeningTag($params);
        
        // Create text box
        $box = new ODTElementTextBox();
        $box_attrs = 'fo:min-height="1pt"';
        $box->setAttributes($box_attrs);
        $params->document->state->enter($box);

        // Encode box
        $params->content .= $box->getOpeningTag($params);
    }

    /**
     * This function closes a multi column frame (previously opened with _odtOpenMultiColumnFrame).
     *
     * @param     ODTInternalParams $params     Commom params.
     */
    public static function closeMultiColumnTextBox (ODTInternalParams $params) {
        // Close paragraph (if open)
        $params->document->paragraphClose();
        // Close text box
        $params->document->closeCurrentElement();
        // Close frame
        ODTUtility::closeHTMLElement ($params, $params->document->state->getHTMLElement());
        $params->document->closeCurrentElement();

        $params->document->paragraphClose();

        $params->document->div_z_index -= 5;
    }

    /**
     * This function opens a textbox in a frame.
     *
     * The currently supported CSS properties are:
     * background-color, color, padding, margin, display, border-radius, min-height.
     * The background-image is simulated using a picture frame.
     * FIXME: Find a way to successfuly use the background-image in the graphic style (see comments).
     *
     * The text box should be closed by calling 'closeTextBox()'.
     *
     * @param     ODTInternalParams $params     Commom params.
     * @param     array             $properties Properties to use for creating the frame
     * @param     string            $element    The element name, e.g. "div"
     * @param     string            $attributes The attributes belonging o the element, e.g. 'class="example"'
     */
    public static function openFrameUseProperties (ODTInternalParams $params, $properties, $element=NULL, $attributes=NULL) {
        $frame = $params->document->state->getCurrentFrame();
        if ($frame != NULL) {
            // Do not open a nested frame as this will make the content ofthe nested frame disappear.
            //return;
        }
        if ($element == NULL) {
            $element = 'div';
        }
        $elementObj = $params->elementObj;

        // If we are not in a paragraph then open one.
        $inParagraph = $params->document->state->getInParagraph();
        if (!$inParagraph) {
            $params->document->paragraphOpen();
        }

        $position = $properties ['position'];
        $picture = $properties ['background-image'];
        $pic_positions = preg_split ('/\s/', $properties ['background-position']);
        //$min_height = $properties ['min-height'];
        $width = $properties ['width'];

        $pic_link = '';
        $pic_width = '';
        $pic_height = '';
        if ( !empty ($picture) ) {
            // If a picture/background-image is set in the CSS, than we insert it manually here.
            // This is a workaround because ODT does not support the background-image attribute in a span.
            $pic_link = $params->document->addFileAsPicture($picture);
            list($pic_width, $pic_height) = ODTUtility::getImageSizeString($picture, NULL, NULL, true, $params->units);
        }

        if ( empty ($width) ) {
            $width = '100%';
        }
        if ( !empty($pic_positions [0]) ) {
            $pic_positions [0] = $params->document->toPoints($pic_positions [0], 'x');
        }
        //if ( empty($min_height) ) {
        //    $min_height = '1pt';
        //}

        // Get anchor type
        $anchor_type = 'paragraph';
        if (!empty($properties ['anchor-type'])) {
            $anchor_type = $properties ['anchor-type'];
        }
        
        // Get X and Y position.
        // X and Y position can be set using 'x' or 'left' and 'y' or 'top'.
        $svgX = null;
        $svgY = null;
        if (!empty($properties ['x'])) {
            $svgX = $properties ['x'];
        }
        if (!empty($properties ['left'])) {
            $svgX = $properties ['left'];
        }
        if (!empty($properties ['y'])) {
            $svgY = $properties ['y'];
        }
        if (!empty($properties ['top'])) {
            $svgY = $properties ['top'];
        }

        // Adjust properties for CSS property 'position' if given
        switch ($position) {
            case 'absolute':
                $anchor_type = 'page';
                break;
            case 'relative':
                $anchor_type = 'paragraph';
                break;
            case 'static':
                $anchor_type = 'paragraph';
                $svgX = '0cm';
                $svgY = '0cm';
                break;
        }

        // Add our styles.
        $style_name = ODTStyle::getNewStylename('Frame');
        $style  = '<style:style style:name="'.$style_name.'_text_frame" style:family="graphic" style:parent-style-name="Frame">';
        $style .= '<style:graphic-properties ';

        foreach (self::$fields as $name => $odtName) {
            if (!empty($properties [$name])) {
                $style .= $odtName.'="'.$properties [$name].'" ';
            }
        }
        $style .= '>';
        $style .= '</style:graphic-properties>';
        $style .= '</style:style>';

        // Add style to our document
        // (as unknown style because style-family graphic is not supported)
        $style_obj = ODTUnknownStyle::importODTStyle($style);
        $params->document->addAutomaticStyle($style_obj);

        // Draw a frame with a text box in it. the text box will be left opened
        // to grow with the content (requires fo:min-height in $style_name).
        if ($elementObj == NULL) {
            $throwAway = array();
            ODTUtility::openHTMLElement ($params, $throwAway, $element, $attributes);
        }

        // Create frame
        $frame = new ODTElementFrame($style_name.'_text_frame');
        self::$frameCount++;
        /*$frame_attrs .= 'draw:name="Frame'.self::$frameCount.'"
                         text:anchor-type="'.$anchor_type.'"
                         svg:width="'.$width.'" svg:min-height="'.$min_height.'"
                         draw:z-index="'.($params->document->div_z_index + 0).'"';*/
        $frame_attrs .= 'draw:name="Frame'.self::$frameCount.'"
                         text:anchor-type="'.$anchor_type.'"
                         svg:width="'.$width.'" 
                         draw:z-index="'.($params->document->div_z_index + 0).'"';
        if ($svgX !== NULL) {
            $frame_attrs .= ' svg:x="'.$svgX.'"';
        }
        if ($svgY !== NULL) {
            $frame_attrs .= ' svg:y="'.$svgY.'"';
        }
        if (!empty($properties ['min-height'])) {
            $frame_attrs .= ' svg:min-height="'.$properties ['min-height'].'"';
        }
        if (!empty($properties ['height'])) {
            $frame_attrs .= ' svg:height="'.$properties ['height'].'"';
        }

        $frame->setAttributes($frame_attrs);
        $params->document->state->enter($frame);
        $frame->setHTMLElement ($element);

        // Encode frame
        $params->content .= $frame->getOpeningTag($params);        

        $params->document->div_z_index += 1;
    }

    /**
     * This function closes a textbox (previously opened with openTextBoxUseProperties()).
     * 
     * @param     ODTInternalParams $params     Commom params.
     */
    function closeFrame (ODTInternalParams $params) {
        $frame = $params->document->state->getCurrentFrame();
        if ($frame == NULL) {
            // ??? Error. Not table found.
            return;
        }

        // Close paragraph (if open)
        $params->document->paragraphClose();

        // Eventually adjust frame width.
        $frame->adjustWidth ($params);

        // Close frame
        $element = $params->document->state->getHTMLElement();
        ODTUtility::closeHTMLElement ($params, $params->document->state->getHTMLElement());
        $params->document->closeCurrentElement();

        // Do not close the open paragraph here as it may lead to extra empty lines.

        $params->document->div_z_index -= 1;
    }
}
