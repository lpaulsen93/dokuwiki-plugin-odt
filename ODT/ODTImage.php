<?php

require_once DOKU_PLUGIN . 'odt/ODT/ODTDocument.php';
require_once DOKU_PLUGIN . 'odt/ODT/ODTUnits.php';

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
            //$width = $this->adjustXLengthValueForODT ($width);
            $width = ODTUnits::toPoints($width, 'x');
            //$height = $this->adjustYLengthValueForODT ($height);
            $height = ODTUnits::toPoints($height, 'y');
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
}
