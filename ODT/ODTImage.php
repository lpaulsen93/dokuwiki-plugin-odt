<?php

require_once DOKU_PLUGIN . 'odt/ODT/ODTDocument.php';

/**
 * ODTFrame:
 * Class containing static code for handling images.
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 */
class ODTImage
{
    /**
     * @param string $src
     * @param  $width
     * @param  $height
     * @param  $align
     * @param  $title
     * @param  $style
     * @param  $returnonly
     */
    public static function addImage(ODTDocument $doc, &$content, $src, $width = NULL, $height = NULL, $align = NULL, $title = NULL, $style = NULL, $returnonly = false){
        static $z = 0;

        $encoded = '';
        if (file_exists($src)) {
            list($ext,$mime) = mimetype($src);
            $name = 'Pictures/'.md5($src).'.'.$ext;
            $doc->addFile($name, $mime, io_readfile($src,false));
        } else {
            $name = $src;
        }
        // make sure width and height are available
        if (!$width || !$height) {
            list($width, $height) = ODTUtility::getImageSizeString($src, $width, $height);
        } else {
            // Adjust values for ODT
            $width = $doc->toPoints($width, 'x');
            $height = $doc->toPoints($height, 'y');
        }

        if($align){
            $anchor = 'paragraph';
        }else{
            $anchor = 'as-char';
        }

        if (empty($style) || !$doc->styleExists($style)) {
            if (!empty($align)) {
                $style = $doc->getStyleName('media '.$align);
            } else {
                $style = $doc->getStyleName('media');
            }
        }

        // Open paragraph if necessary
        if (!$doc->state->getInParagraph()) {
            $doc->paragraphOpen(NULL, $content);
        }

        if ($title) {
            $encoded .= '<draw:frame draw:style-name="'.$style.'" draw:name="'.$doc->replaceXMLEntities($title).' Legend"
                            text:anchor-type="'.$anchor.'" draw:z-index="0" svg:width="'.$width.'">';
            $encoded .= '<draw:text-box>';
            $encoded .= '<text:p text:style-name="'.$doc->getStyleName('legend center').'">';
        }
        if (!empty($title)) {
            $encoded .= '<draw:frame draw:style-name="'.$style.'" draw:name="'.$doc->replaceXMLEntities($title).'"
                            text:anchor-type="'.$anchor.'" draw:z-index="'.$z.'"
                            svg:width="'.$width.'" svg:height="'.$height.'" >';
        } else {
            $encoded .= '<draw:frame draw:style-name="'.$style.'" draw:name="'.$z.'"
                            text:anchor-type="'.$anchor.'" draw:z-index="'.$z.'"
                            svg:width="'.$width.'" svg:height="'.$height.'" >';
        }
        $encoded .= '<draw:image xlink:href="'.$doc->replaceXMLEntities($name).'"
                        xlink:type="simple" xlink:show="embed" xlink:actuate="onLoad"/>';
        $encoded .= '</draw:frame>';
        if ($title) {
            $encoded .= $doc->replaceXMLEntities($title).'</text:p></draw:text-box></draw:frame>';
        }

        if($returnonly) {
          return $encoded;
        } else {
          $content .= $encoded;
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
    function addStringAsSVGImage(ODTDocument $doc, &$content, $string, $width = NULL, $height = NULL, $align = NULL, $title = NULL, $style = NULL) {
        if ( empty($string) ) { return; }

        $name = self::addStringAsSVGImageFile($doc, $string);

        // make sure width and height are available
        if (!$width || !$height) {
            list($width, $height) = ODTUtility::getImageSizeString($string, $width, $height);
        }

        if($align){
            $anchor = 'paragraph';
        }else{
            $anchor = 'as-char';
        }

        if (!$style or !$doc->styleExists($style)) {
            $style = $doc->getStyleName('media '.$align);
        }

        // Open paragraph if necessary
        if (!$doc->state->getInParagraph()) {
            $doc->paragraphOpen(NULL, $content);
        }

        if ($title) {
            $content .= '<draw:frame draw:style-name="'.$style.'" draw:name="'.$doc->replaceXMLEntities($title).' Legend"
                            text:anchor-type="'.$anchor.'" draw:z-index="0" svg:width="'.$width.'">';
            $content .= '<draw:text-box>';
            $doc->paragraphOpen($doc->getStyleName('legend center'));
        }
        $content .= '<draw:frame draw:style-name="'.$style.'" draw:name="'.$doc->replaceXMLEntities($title).'"
                        text:anchor-type="'.$anchor.'" draw:z-index="0"
                        svg:width="'.$width.'" svg:height="'.$height.'" >';
        $content .= '<draw:image xlink:href="'.$doc->replaceXMLEntities($name).'"
                        xlink:type="simple" xlink:show="embed" xlink:actuate="onLoad"/>';
        $content .= '</draw:frame>';
        if ($title) {
            $content .= $doc->replaceXMLEntities($title);
            $doc->paragraphClose($content);
            $content .= '</draw:text-box></draw:frame>';
        }
    }
}
