<?php

require_once DOKU_PLUGIN . 'odt/ODT/ODTDocument.php';
require_once DOKU_PLUGIN . 'odt/ODT/ODTUnits.php';

/**
 * ODTFrame:
 * Class containing static code for handling frames.
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 */
class ODTFrame
{
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
     * @author LarsDW223
     *
     * @param array $properties
     */
    public static function openTextBoxUseCSS (ODTDocument $doc, &$content, $attributes=NULL, cssimportnew $import=NULL) {
        $frame = $doc->state->getCurrentFrame();
        if ($frame != NULL) {
            // Do not open a nested frame as this will make the content ofthe nested frame disappear.
            //return;
        }

        $properties = array();

        // FIXME: delete old outcommented code below and re-write using new CSS import class

        //if ( empty($element) ) {
        //    $element = 'div';
        //}

        //$import->getPropertiesForElement($properties, $element, $classes);
        //foreach ($properties as $property => $value) {
        //    $properties [$property] = $this->adjustValueForODT ($value, 14);
        //}
        self::openTextBoxUseProperties ($doc, $content, $properties);
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
     * @author LarsDW223
     *
     * @param array $properties
     */
    public static function openTextBoxUseProperties (ODTDocument $doc, &$content, $properties) {
        $frame = $doc->state->getCurrentFrame();
        if ($frame != NULL) {
            // Do not open a nested frame as this will make the content ofthe nested frame disappear.
            //return;
        }

        $doc->div_z_index += 5;

        $valign = $properties ['vertical-align'];
        $top = $properties ['top'];
        $left = $properties ['left'];
        $position = $properties ['position'];
        $bg_color = $properties ['background-color'];
        $color = $properties ['color'];
        $padding_left = $properties ['padding-left'];
        $padding_right = $properties ['padding-right'];
        $padding_top = $properties ['padding-top'];
        $padding_bottom = $properties ['padding-bottom'];
        $margin_left = $properties ['margin-left'];
        $margin_right = $properties ['margin-right'];
        $margin_top = $properties ['margin-top'];
        $margin_bottom = $properties ['margin-bottom'];
        $display = $properties ['display'];
        $border = $properties ['border'];
        $border_color = $properties ['border-color'];
        $border_width = $properties ['border-width'];
        $radius = $properties ['border-radius'];
        $picture = $properties ['background-image'];
        $pic_positions = preg_split ('/\s/', $properties ['background-position']);

        $min_height = $properties ['min-height'];
        $width = $properties ['width'];
        $horiz_pos = $properties ['float'];

        $pic_link = '';
        $pic_width = '';
        $pic_height = '';
        if ( !empty ($picture) ) {
            // If a picture/background-image is set in the CSS, than we insert it manually here.
            // This is a workaround because ODT does not support the background-image attribute in a span.
            $pic_link = $doc->addFileAsPicture($picture);
            list($pic_width, $pic_height) = ODTUtility::getImageSizeString($picture);
        }

        if ( empty($horiz_pos) ) {
            $horiz_pos = 'center';
        }
        if ( empty ($width) ) {
            $width = '100%';
        }
        if ( !empty($pic_positions [0]) ) {
            $pic_positions [0] = ODTUnits::toPoints($pic_positions [0], 'x');
        }
        if ( empty($min_height) ) {
            $min_height = '1pt';
        }
        if ( empty($top) ) {
            $top = '0cm';
        }
        if ( empty($left) ) {
            $left = '0cm';
        } else {
            $horiz_pos = 'from-left';
        }

        // Different handling for relative and absolute size...
        if ( $width [strlen($width)-1] == '%' ) {
            // Convert percentage values to absolute size, respecting page margins
            $width = trim($width, '%');
            $width_abs = $doc->page->getAbsWidthMindMargins($width).'cm';
        } else {
            // Absolute values may include not supported units.
            // Adjust.
            $width_abs = ODTUnits::toPoints($width, 'x');
        }


        // Add our styles.
        $style_name = ODTStyle::getNewStylename('Frame');

        switch ($position) {
            case 'absolute':
                $anchor_type = 'page';
                break;
            case 'relative':
                $anchor_type = 'paragraph';
                break;
            case 'static':
            default:
                $anchor_type = 'paragraph';
                $top = '0cm';
                $left = '0cm';
                break;
        }
        // FIXME: Later try to get nested frames working - probably with anchor = as-char
        //$frame = $this->document->state->getCurrentFrame();
        //if ($frame != NULL) {
        //    $anchor_type = 'as-char';
        //}
        switch ($anchor_type) {
            case 'page':
                $style =
                '<style:style style:name="'.$style_name.'_text_frame" style:family="graphic">
                     <style:graphic-properties style:run-through="foreground" style:wrap="run-through"
                      style:number-wrapped-paragraphs="no-limit" style:vertical-pos="from-top" style:vertical-rel="page"
                      style:horizontal-pos="from-left" style:horizontal-rel="page"
                      draw:wrap-influence-on-position="once-concurrent" style:flow-with-text="false" ';
                break;
            default:
                $style =
                '<style:style style:name="'.$style_name.'_text_frame" style:family="graphic">
                     <style:graphic-properties
                      draw:textarea-horizontal-align="left"
                    style:horizontal-pos="'.$horiz_pos.'" style:background-transparency="100%" style:wrap="none" ';
                break;
        }

        if ( !empty($valign) ) {
            $style .= 'draw:textarea-vertical-align="'.$valign.'" ';
        }
        if ( !empty($bg_color) ) {
            $style .= 'fo:background-color="'.$bg_color.'" ';
            $style .= 'draw:fill="solid" draw:fill-color="'.$bg_color.'" ';
        } else {
            $style .= 'draw:fill="none" ';
        }
        if ( !empty($border_color) ) {
            $style .= 'svg:stroke-color="'.$border_color.'" ';
        } else {
            $style .= 'draw:stroke="none" ';
        }
        if ( !empty($border_width) ) {
            $style .= 'svg:stroke-width="'.$border_width.'" ';
        }
        if ( !empty($padding_left) ) {
            $style .= 'fo:padding-left="'.$padding_left.'" ';
        }
        if ( !empty($padding_right) ) {
            $style .= 'fo:padding-right="'.$padding_right.'" ';
        }
        if ( !empty($padding_top) ) {
            $style .= 'fo:padding-top="'.$padding_top.'" ';
        }
        if ( !empty($padding_bottom) ) {
            $style .= 'fo:padding-bottom="'.$padding_bottom.'" ';
        }
        if ( !empty($margin_left) ) {
            $style .= 'fo:margin-left="'.$margin_left.'" ';
        }
        if ( !empty($margin_right) ) {
            $style .= 'fo:margin-right="'.$margin_right.'" ';
        }
        if ( !empty($margin_top) ) {
            $style .= 'fo:margin-top="'.$margin_top.'" ';
        }
        if ( !empty($margin_bottom) ) {
            $style .= 'fo:margin-bottom="'.$margin_bottom.'" ';
        }
        if ( !empty ($fo_border) ) {
            $style .= 'fo:border="'.$fo_border.'" ';
        }
        $style .= 'fo:min-height="'.$min_height.'" ';
        $style .= '>';

        // FIXME: Delete the part below 'if ( $picture != NULL ) {...}'
        // and use this background-image definition. For some reason the background-image is not displayed.
        // Help is welcome.
        /*$style .= '<style:background-image ';
        $style .= 'xlink:href="'.$pic_link.'" xlink:type="simple" xlink:actuate="onLoad"
                   style:position="center center" style:repeat="no-repeat" draw:opacity="100%"/>';*/
        $style .= '</style:graphic-properties>';
        $style .= '</style:style>';
        $style .= '<style:style style:name="'.$style_name.'_image_frame" style:family="graphic">
             <style:graphic-properties
                 draw:stroke="none"
                 draw:fill="none"
                 draw:textarea-horizontal-align="left"
                 draw:textarea-vertical-align="center"
                 style:wrap="none"/>
         </style:style>';

        // Add style to our document
        // (as unknown style because style-family graphic is not supported)
        $style_obj = ODTUnknownStyle::importODTStyle($style);
        $doc->addAutomaticStyle($style_obj);

        // Group the frame so that they are stacked one on each other.
        $doc->paragraphClose($content);
        $doc->paragraphOpen(NULL, $content);
        $doc->linebreak($content);
        if ( $display == NULL ) {
            $content .= '<draw:g draw:z-index="'.($doc->div_z_index + 0).'">';
        } else {
            $content .= '<draw:g draw:display="' . $display . '">';
        }

        // Draw a frame with the image in it, if required.
        // FIXME: delete this part if 'background-image' in graphic style is working.
        if ( $picture != NULL )
        {
            $content .= '<draw:frame draw:style-name="'.$style_name.'_image_frame" draw:name="Bild1"
                                svg:x="'.$pic_positions [0].'" svg:y="'.$pic_positions [0].'"
                                svg:width="'.$pic_width.'" svg:height="'.$pic_height.'"
                                draw:z-index="'.($doc->div_z_index + 1).'">
                               <draw:image xlink:href="'.$pic_link.'"
                                xlink:type="simple" xlink:show="embed" xlink:actuate="onLoad"/>
                                </draw:frame>';
        }

        // Draw a frame with a text box in it. the text box will be left opened
        // to grow with the content (requires fo:min-height in $style_name).

        // Create frame
        $frame = new ODTElementFrame($style_name.'_text_frame');
        $frame_attrs .= 'draw:name="Bild1"
                         svg:x="'.$left.'" svg:y="'.$top.'"
                         svg:width="'.$width_abs.'" svg:height="'.$min_height.'"
                         draw:z-index="'.($doc->div_z_index + 0).'">';
        $frame->setAttributes($frame_attrs);
        $doc->state->enter($frame);

        // Encode frame
        $content .= $frame->getOpeningTag();
        
        // Create text box
        $box = new ODTElementTextBox();
        $box_attrs = '';
        // If required use round corners.
        if ( !empty($radius) )
            $box_attrs .= 'draw:corner-radius="'.$radius.'"';
        $box->setAttributes($box_attrs);
        $doc->state->enter($box);

        // Encode box
        $content .= $box->getOpeningTag();
    }

    /**
     * This function closes a textbox (previously opened with openTextBoxUseProperties()).
     */
    function closeTextBox (ODTDocument $doc, &$content) {
        // Close paragraph (if open)
        $doc->paragraphClose($content);
        // Close text box
        $doc->closeCurrentElement($content);
        // Close frame
        $doc->closeCurrentElement($content);

        $content .= '</draw:g>';
        $doc->paragraphClose($content);

        $doc->div_z_index -= 5;
    }

    /**
     * This function opens a multi column frame/text box according to the
     * parameters in $properties. Call 'closeMultiColumnTextBox()' to
     * close the text box.
     *
     * @see ODTUnknownStyle::createMultiColumnFrameStyle for information
     *      about supported $properties.
     * @author LarsDW223
     */
    public static function openMultiColumnTextBoxUseProperties (ODTDocument $doc, &$content, $properties) {
        // Create style name.
        $style_obj = ODTUnknownStyle::createMultiColumnFrameStyle ($properties);
        $doc->addAutomaticStyle($style_obj);
        $style_name = $style_obj->getProperty('style-name');

        $width_abs = $doc->page->getAbsWidthMindMargins (100);

        // Group the frame so that they are stacked one on each other.
        $doc->paragraphClose($content);
        $doc->paragraphOpen(NULL, $content);

        // Draw a frame with a text box in it. the text box will be left opened
        // to grow with the content (requires fo:min-height in $style_name).

        // Create frame
        $frame = new ODTElementFrame($style_name);
        $frame_attrs = 'draw:name="Frame1" text:anchor-type="paragraph" svg:width="'.$width_abs.'cm" draw:z-index="0">';
        $frame->setAttributes($frame_attrs);
        $doc->state->enter($frame);

        // Encode frame
        $content .= $frame->getOpeningTag();
        
        // Create text box
        $box = new ODTElementTextBox();
        $box_attrs = 'fo:min-height="1pt"';
        $box->setAttributes($box_attrs);
        $doc->state->enter($box);

        // Encode box
        $content .= $box->getOpeningTag();
    }

    /**
     * This function closes a multi column frame (previously opened with _odtOpenMultiColumnFrame).
     *
     * @author LarsDW223
     */
    public static function closeMultiColumnTextBox (ODTDocument $doc, &$content) {
        // Close paragraph (if open)
        $doc->paragraphClose($content);
        // Close text box
        $doc->closeCurrentElement($content);
        // Close frame
        $doc->closeCurrentElement($content);

        $doc->paragraphClose($content);

        $doc->div_z_index -= 5;
    }
}
