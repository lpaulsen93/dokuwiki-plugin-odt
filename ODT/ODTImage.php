<?php

require_once DOKU_PLUGIN . 'odt/ODT/ODTDocument.php';

/**
 * ODTImage:
 * Class containing static code for handling images.
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 */
class ODTImage
{
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
    public static function addImage(ODTInternalParams $params, $src, $width = NULL, $height = NULL, $align = NULL, $title = NULL, $style = NULL, $returnonly = false){
        static $z = 0;

        $encoded = '';
        if (file_exists($src)) {
            list($ext,$mime) = mimetype($src);
            $name = 'Pictures/'.md5($src).'.'.$ext;
            $params->document->addFile($name, $mime, io_readfile($src,false));
        } else {
            $name = $src;
        }
        // make sure width and height are available
        if (!$width && !$height) {
            list($width, $height) = ODTUtility::getImageSizeString($src, $width, $height, true, $params->units);
        } else {
            list($width, $height) = ODTUtility::getImageSizeString($src, $width, $height, false, $params->units);
        }

        if($align){
            $anchor = 'paragraph';
        }else{
            $anchor = 'as-char';
        }

        if (empty($style) || !$params->document->styleExists($style)) {
            if (!empty($align)) {
                $style = $params->document->getStyleName('media '.$align);
            } else {
                $style = $params->document->getStyleName('media');
            }
        }

        // Open paragraph if necessary
        if (!$params->document->state->getInParagraph()) {
            $params->document->paragraphOpen();
        }

        if ($title) {
            $encoded .= '<draw:frame draw:style-name="'.$style.'" draw:name="'.$params->document->replaceXMLEntities($title).' Legend"
                            text:anchor-type="'.$anchor.'" draw:z-index="0" svg:width="'.$width.'">';
            $encoded .= '<draw:text-box>';
            $encoded .= '<text:p text:style-name="'.$params->document->getStyleName('legend center').'">';
        }
        if (!empty($title)) {
            $encoded .= '<draw:frame draw:style-name="'.$style.'" draw:name="'.$params->document->replaceXMLEntities($title).'"
                            text:anchor-type="'.$anchor.'" draw:z-index="'.$z.'"
                            svg:width="'.$width.'" svg:height="'.$height.'" >';
        } else {
            $encoded .= '<draw:frame draw:style-name="'.$style.'" draw:name="'.$z.'"
                            text:anchor-type="'.$anchor.'" draw:z-index="'.$z.'"
                            svg:width="'.$width.'" svg:height="'.$height.'" >';
        }
        $encoded .= '<draw:image xlink:href="'.$params->document->replaceXMLEntities($name).'"
                        xlink:type="simple" xlink:show="embed" xlink:actuate="onLoad"/>';
        $encoded .= '</draw:frame>';
        if ($title) {
            $encoded .= $params->document->replaceXMLEntities($title).'</text:p></draw:text-box></draw:frame>';
        }

        if($returnonly) {
            return $encoded;
        } else {
            $params->content .= $encoded;
        }

        $z++;
    }

    /**
     * Adds the content of $string as a SVG picture file to the document.
     * The link name which can be used for the ODT draw:image xlink:href
     * is returned. The caller is responsible for creating the frame and image tag
     * but therefore has full control over it. This means he can also set parameters
     * in the odt frame and image tag which can not be changed using the function _odtAddImage.
     *
     * @author LarsDW223
     *
     * @param string $string SVG code to add
     * @return string
     */
    public static function addStringAsSVGImageFile(ODTDocument $doc, $string) {
        if ( empty($string) ) { return; }

        $ext  = '.svg';
        $mime = '.image/svg+xml';
        $name = 'Pictures/'.md5($string).'.'.$ext;
        $doc->addFile($name, $mime, $string);
        return $name;
    }

    /**
     * Adds the content of $string as a SVG picture to the document.
     * The other parameters behave in the same way as in _odtAddImage.
     *
     * @author LarsDW223
     *
     * @param string $string
     * @param  $width
     * @param  $height
     * @param  $align
     * @param  $title
     * @param  $style
     */
    function addStringAsSVGImage(ODTInternalParams $params, $string, $width = NULL, $height = NULL, $align = NULL, $title = NULL, $style = NULL) {
        if ( empty($string) ) { return; }

        $name = self::addStringAsSVGImageFile($params->document, $string);

        // make sure width and height are available
        if (!$width || !$height) {
            list($width, $height) = ODTUtility::getImageSizeString($string, $width, $height, true, $params->units);
        }

        if($align){
            $anchor = 'paragraph';
        }else{
            $anchor = 'as-char';
        }

        if (!$style or !$params->document->styleExists($style)) {
            $style = $params->document->getStyleName('media '.$align);
        }

        // Open paragraph if necessary
        if (!$params->document->state->getInParagraph()) {
            $params->document->paragraphOpen();
        }

        if ($title) {
            $params->content .= '<draw:frame draw:style-name="'.$style.'" draw:name="'.$params->document->replaceXMLEntities($title).' Legend"
                                 text:anchor-type="'.$anchor.'" draw:z-index="0" svg:width="'.$width.'">';
            $params->content .= '<draw:text-box>';
            $params->document->paragraphOpen($$params->document->getStyleName('legend center'));
        }
        $params->content .= '<draw:frame draw:style-name="'.$style.'" draw:name="'.$params->document->replaceXMLEntities($title).'"
                             text:anchor-type="'.$anchor.'" draw:z-index="0"
                             svg:width="'.$width.'" svg:height="'.$height.'" >';
        $params->content .= '<draw:image xlink:href="'.$params->document->replaceXMLEntities($name).'"
                             xlink:type="simple" xlink:show="embed" xlink:actuate="onLoad"/>';
        $params->content .= '</draw:frame>';
        if ($title) {
            $params->content .= $params->document->replaceXMLEntities($title);
            $params->document->paragraphClose();
            $params->content .= '</draw:text-box></draw:frame>';
        }
    }

    /**
     * Adds an image $src to the document using the parameters set in $properties.
     * The actually supported properties are:
     * - width and height
     * - title
     * - background-color
     * - margin-left, margin-right, margin-top, margin-bottom
     * 
     * @param string  $src        The path to the image file
     * @param array   $properties Properties (width, height... see ODTImage::addImageUseProperties)
     * @param boolean $returnonly Only return code
     */
    public static function addImageUseProperties(ODTInternalParams $params, $src, array $properties, $returnonly = false){
        static $z = 0;

        ODTUtility::adjustValuesForODT ($properties, $params->units);
        $width = $properties ['width'];
        $height = $properties ['height'];
        $title = $properties ['title'];
        $bg_color = $properties ['background-color'];
        
        $encoded = '';
        if (file_exists($src)) {
            list($ext,$mime) = mimetype($src);
            $name = 'Pictures/'.md5($src).'.'.$ext;
            $params->document->addFile($name, $mime, io_readfile($src,false));
        } else {
            $name = $src;
        }
        // make sure width and height are available
        if (!$width || !$height) {
            list($width, $height) = ODTUtility::getImageSizeString($src, $width, $height, true, $params->units);
        } else {
            // Adjust values for ODT
            $width = $params->document->toPoints($width, 'x').'pt';
            $height = $params->document->toPoints($height, 'y').'pt';
        }

        if($align){
            $anchor = 'paragraph';
        }else{
            $anchor = 'as-char';
        }

        // Open paragraph if necessary
        if (!$params->document->state->getInParagraph()) {
            $params->document->paragraphOpen();
        }

        // Define graphic style for picture
        $style_name = ODTStyle::getNewStylename('span_graphic');
        $image_style  = '<style:style style:name="'.$style_name.'" style:family="graphic" style:parent-style-name="'.$params->document->getStyleName('graphics').'">';
        $image_style .= '<style:graphic-properties style:vertical-pos="middle" style:vertical-rel="text" style:horizontal-pos="from-left" style:horizontal-rel="paragraph" fo:background-color="'.$bg_color.'" style:flow-with-text="true"';
        if (!empty($properties ['margin-left'])) {
            $image_style .= ' fo:margin-left="'.$properties ['margin-left'].'"';
        }
        if (!empty($properties ['margin-right'])) {
            $image_style .= ' fo:margin-right="'.$properties ['margin-right'].'"';
        }
        if (!empty($properties ['margin-top'])) {
            $image_style .= ' fo:margin-top="'.$properties ['margin-top'].'"';
        }
        if (!empty($properties ['margin-bottom'])) {
            $image_style .= ' fo:margin-bottom="'.$properties ['margin-bottom'].'"';
        }
        $image_style .= '></style:graphic-properties></style:style>';

        // Add style and image to our document
        // (as unknown style because style-family graphic is not supported)
        $style_obj = ODTUnknownStyle::importODTStyle($image_style);
        $params->document->addAutomaticStyle($style_obj);

        if ($title) {
            $encoded .= '<draw:frame draw:style-name="'.$style_name.'" draw:name="'.$params->document->replaceXMLEntities($title).' Legend"
                            text:anchor-type="'.$anchor.'" draw:z-index="0" svg:width="'.$width.'">';
            $encoded .= '<draw:text-box>';
            $encoded .= '<text:p text:style-name="'.$params->document->getStyleName('legend center').'">';
        }
        if (!empty($title)) {
            $encoded .= '<draw:frame draw:style-name="'.$style_name.'" draw:name="'.$params->document->replaceXMLEntities($title).'"
                            text:anchor-type="'.$anchor.'" draw:z-index="'.$z.'"
                            svg:width="'.$width.'" svg:height="'.$height.'" >';
        } else {
            $encoded .= '<draw:frame draw:style-name="'.$style_name.'" draw:name="'.$z.'"
                            text:anchor-type="'.$anchor.'" draw:z-index="'.$z.'"
                            svg:width="'.$width.'" svg:height="'.$height.'" >';
        }
        $encoded .= '<draw:image xlink:href="'.$params->document->replaceXMLEntities($name).'"
                        xlink:type="simple" xlink:show="embed" xlink:actuate="onLoad"/>';
        $encoded .= '</draw:frame>';
        if ($title) {
            $encoded .= $params->document->replaceXMLEntities($title).'</text:p></draw:text-box></draw:frame>';
        }

        if($returnonly) {
            return $encoded;
        } else {
            $params->content .= $encoded;
        }

        $z++;
    }
}
