<?php

/**
 * ODTMeta: class for maintaining the meta data of an ODT document.
 *          Code was previously included in renderer.php.
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Aurelien Bompard <aurelien@bompard.org>
 * @author LarsDW223
 */
class ODTMeta
{
    var $meta = array();

    /**
     * Constructor. Set initial meta data.
     */
    public function __construct() {
        $this->meta = array(
                'meta:generator'            => 'DokuWiki '.getversion(),
                'meta:initial-creator'      => 'Generated',
                'meta:creation-date'        => date('Y-m-d\\TH::i:s', null), //FIXME
                'dc:creator'                => 'Generated',
                'dc:date'                   => date('Y-m-d\\TH::i:s', null),
                'dc:language'               => 'en-US',
                'meta:editing-cycles'       => '1',
                'meta:editing-duration'     => 'PT0S',
            );
    }

    /**
     * @param string $title
     */
    function setTitle ($title) {
        $this->meta ['dc:title'] = $title;
    }

    /**
     * Returns the complete meta content.
     */
    function getContent(){
        $value  =   '<' . '?xml version="1.0" encoding="UTF-8"?' . ">\n";
        $value .=   '<office:document-meta ';
        $value .=       'xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" ';
        $value .=       'xmlns:xlink="http://www.w3.org/1999/xlink" ';
        $value .=       'xmlns:dc="http://purl.org/dc/elements/1.1/" ';
        $value .=       'xmlns:meta="urn:oasis:names:tc:opendocument:xmlns:meta:1.0" ';
        $value .=       'xmlns:ooo="http://openoffice.org/2004/office" ';
        $value .=       'xmlns:grddl="http://www.w3.org/2003/g/data-view#" ';
        $value .=       'office:version="1.2">';
        $value .=       '<office:meta>';
        # FIXME
        foreach($this->meta as $meta_key => $meta_value) {
            $value .= '<' . $meta_key . '>' . htmlspecialchars($meta_value, ENT_QUOTES, 'UTF-8') . '</' . $meta_key . '>';
        }
        $value .=       '</office:meta>';
        $value .=   '</office:document-meta>';
        return $value;
    }
}
