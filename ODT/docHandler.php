<?php
/**
 * docHandler: Abstract class defining the interface for classes
 * which create the ODT document file/zip archive.
 *
 * Most code was taken from renderer.php.
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Aurelien Bompard <aurelien@bompard.org>
 * @author LarsDW223
 */
// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

require_once DOKU_INC.'inc/ZipLib.class.php';
require_once DOKU_INC.'lib/plugins/odt/ODT/ODTmanifest.php';

/**
 * The docHandler interface
 */
abstract class docHandler
{
    var $manifest;
    var $ZIP;
    protected $factory = NULL;

    /**
     * Constructor.
     */
    public function __construct() {
        // prepare the zipper, manifest
        $this->ZIP = new ZipLib();
        $this->manifest = new ODTManifest();

        $this->factory = plugin_load('helper', 'odt_stylefactory');
    }

    /**
     * Check if file exists.
     *
     * @param string $name
     * @return bool
     */
    public function fileExists($name) {
        return $this->manifest->exists($name);
    }

    /**
     * Add a file to the document
     *
     * @param string $name
     * @param string $mime
     * @param string $content
     * @return bool
     */
    public function addFile($name, $mime, $content) {
        if(!$this->manifest->exists($name)){
            $this->manifest->add($name, $mime);
            $this->ZIP->add_File($content, $name, 0);
            return true;
        }

        // File with that name already exists!
        return false;
    }

    /**
     * Adds the file $src as a picture file without adding it to the content.
     * Returns name of the file in the document for reference.
     *
     * @param string $src
     * @return string
     */
    function addFileAsPicture($src){
        $name = '';
        if (file_exists($src)) {
            list($ext,$mime) = mimetype($src);
            $name = 'Pictures/'.md5($src).'.'.$ext;
            $this->addFile($name, $mime, io_readfile($src,false));
        }
        return $name;
    }

    /**
     * Build ODT document.
     *
     * @param string      $doc
     * @param string      $autostyles
     * @param array       $commonstyles
     * @param string      $meta
     * @param string      $userfields
     * @param ODTStyleSet $styleset
     * @return mixed
     */
    abstract public function build($doc=null, $meta=null, $userfields=null, $pagestyles=null);

    /**
     * Get ODT document file.
     *
     * @return string
     */
    public function get() {
        return $this->ZIP->get_file();
    }

    /**
     * Each docHandler needs to provide a way to add a style to the document.
     *
     * @param $new The ODTStyle to add.
     */
    abstract public function addStyle(ODTStyle $new);

    /**
     * Each docHandler needs to provide a way to add an automatic style to the document.
     *
     * @param $new The ODTStyle to add.
     */
    abstract public function addAutomaticStyle(ODTStyle $new);

    /**
     * Each docHandler needs to provide a way to check if a style definition
     * already exists in the document.
     *
     * @param $name Name of the style to check
     * @return boolean
     */
    abstract public function styleExists ($name);

    /**
     * Each docHandler needs to provide a way to get a style definition's
     * object (if it exists).
     *
     * @param $name Name of the style
     * @return ODTStyle
     */
    abstract public function getStyle ($name);

    /**
     * The function returns the style names used for the basic syntax.
     */
    abstract public function getStyleName($style);

    protected function setListStyleImage ($style, $level, $file) {
        if ($this->units == NULL) {
            $this->units = plugin_load('helper', 'odt_units');
        }
        $odt_file = $this->addFileAsPicture($file);

        if ( $odt_file != NULL ) {
            $style->setPropertyForLevel($level, 'list-level-style', 'image');
            $style->setPropertyForLevel($level, 'href', $odt_file);
            $style->setPropertyForLevel($level, 'type', 'simple');
            $style->setPropertyForLevel($level, 'show', 'embed');
            $style->setPropertyForLevel($level, 'actuate', 'onLoad');
            $style->setPropertyForLevel($level, 'vertical-pos', 'middle');
            $style->setPropertyForLevel($level, 'vertical-rel', 'line');

            list($width, $height) = renderer_plugin_odt_page::_odtGetImageSize($file);
            if (empty($width) || empty($height)) {
                $width = '0.5';
                $height = $width;
            }
            $style->setPropertyForLevel($level, 'width', $width.'cm');
            $style->setPropertyForLevel($level, 'height', $height.'cm');

            // ??? Wie berechnen...
            $text_indent = $this->units->getDigits($style->getPropertyFromLevel($level, 'text-indent'));
            $margin_left = $this->units->getDigits($style->getPropertyFromLevel($level, 'margin_left'));
            $tab_stop_position =
                $this->units->getDigits($style->getPropertyFromLevel($level, 'list-tab-stop-position'));
            $minimum = $margin_left + $text_indent + $width;
            if ($minimum > $tab_stop_position) {
                $inc = abs($text_indent);
                if ($inc == 0 ) {
                    $inc = 0.5;
                }
                while ($minimum > $tab_stop_position) {
                    $tab_stop_position += $inc;
                }
            }
            $style->setPropertyForLevel($level, 'list-tab-stop-position', $tab_stop_position.'cm');
        }
    }

    protected function importOrderedListStyles($import, $media_sel, $media_path) {
        $name = $this->styleset->getStyleName('numbering');
        $style = $this->styleset->getStyle($name);
        if ($style == NULL ) {
            return;
        }

        for ($level = 1 ; $level < 11 ; $level++) {
            $properties = array();
            $import->getPropertiesForElement($properties, 'ol', 'level'.$level, $media_sel);

            // Adjust values for ODT
            foreach ($properties as $property => $value) {
                $properties [$property] = $this->factory->adjustValueForODT ($property, $value, 14);
            }
            
            if ($properties ['list-style-type'] !== NULL) {
                $prefix = NULL;
                $suffix = '.';
                $numbering = trim($properties ['list-style-type'],'"');
                switch ($numbering) {
                    case 'decimal':
                        $numbering = '1';
                        break;
                    case 'decimal-leading-zero':
                        $numbering = '1';
                        $prefix = '0';
                        break;
                    case 'lower-alpha':
                    case 'lower-latin':
                        $numbering = 'a';
                        break;
                    case 'lower-roman':
                        $numbering = 'i';
                        break;
                    case 'none':
                        $numbering = '';
                        $suffix = '';
                        break;
                    case 'upper-alpha':
                    case 'upper-latin':
                        $numbering = 'A';
                        break;
                    case 'upper-roman':
                        $numbering = 'i';
                        break;
                }
                $style->setPropertyForLevel($level, 'num-format', $numbering);
                if ($prefix != NULL ) {
                    $style->setPropertyForLevel($level, 'num-prefix', $prefix);
                }
                $style->setPropertyForLevel($level, 'num-suffix', $suffix);
            }
            if ($properties ['list-style-image'] !== NULL) {
                $file = $properties ['list-style-image'];
                $file = substr($file, 4);
                $file = trim($file, "()'");
                if ($media_path [strlen($media_path)-1] != '/') {
                    $media_path .= '/';
                }
                $file = $media_path.$file;
                
                $this->setListStyleImage ($style, $level, $file);
            }
        }
    }

    protected function importUnorderedListStyles($import, $media_sel, $media_path) {
        $name = $this->styleset->getStyleName('list');
        $style = $this->styleset->getStyle($name);
        if ($style == NULL ) {
            return;
        }

        for ($level = 1 ; $level < 11 ; $level++) {
            $properties = array();
            $import->getPropertiesForElement($properties, 'ul', 'level'.$level, $media_sel);

            // Adjust values for ODT
            foreach ($properties as $property => $value) {
                $properties [$property] = $this->factory->adjustValueForODT ($property, $value, 14);
            }
            
            if ($properties ['list-style-type'] !== NULL) {
                switch ($properties ['list-style-type']) {
                    case 'disc':
                    case 'bullet':
                        $sign = '•';
                        break;
                    case 'circle':
                        $sign = '∘';
                        break;
                    case 'square':
                        $sign = '▪';
                        break;
                    case 'none':
                        $sign = ' ';
                        break;
                    case 'blackcircle':
                        $sign = '●';
                        break;
                    case 'heavycheckmark':
                        $sign = '✔';
                        break;
                    case 'ballotx':
                        $sign = '✗';
                        break;
                    case 'heavyrightarrow':
                        $sign = '➔';
                        break;
                    case 'lightedrightarrow':
                        $sign = '➢';
                        break;
                    default:
                        $sign = trim($properties ['list-style-type'],'"');
                        break;
                }
                $style->setPropertyForLevel($level, 'text-bullet-char', $sign);
            }
            if ($properties ['list-style-image'] !== NULL) {
                $file = $properties ['list-style-image'];
                $file = substr($file, 4);
                $file = trim($file, "()'");
                if ($media_path [strlen($media_path)-1] != '/') {
                    $media_path .= '/';
                }
                $file = $media_path.$file;
                
                $this->setListStyleImage ($style, $level, $file);
            }
        }
    }

    protected function importStyle($import, $style_type, $element, $class, $media_sel) {
        $name = $this->styleset->getStyleName($style_type);
        $style = $this->styleset->getStyle($name);
        if ( $style != NULL ) {
            $style->clearLayoutProperties();

            $properties = array();
            $import->getPropertiesForElement($properties, $element, $class, $media_sel);

            // Adjust values for ODT
            foreach ($properties as $property => $value) {
                $properties [$property] = $this->factory->adjustValueForODT ($property, $value, 14);
            }

            // Convert 'text-decoration'.
            if ( $properties ['text-decoration'] == 'line-through' ) {
                $properties ['text-line-through-style'] = 'solid';
            }
            if ( $properties ['text-decoration'] == 'underline' ) {
                $properties ['text-underline-style'] = 'solid';
            }
            if ( $properties ['text-decoration'] == 'overline' ) {
                $properties ['text-overline-style'] = 'solid';
            }

            // If the style imported is a table adjust some properties
            if ($style->getFamily() == 'table') {
                // Move 'width' to 'rel-width' if it is relative
                $width = $properties ['width'];
                if ($width != NULL) {
                    if ($properties ['align'] == NULL) {
                        // If width is set but align not, changing the width
                        // will not work. So we set it here if not done by the user.
                        $properties ['align'] = 'center';
                    }
                }
                if ($width [strlen($width)-1] == '%') {
                    $properties ['rel-width'] = $width;
                    unset ($properties ['width']);
                }

                // Convert property 'border-model' to ODT
                if ( !empty ($properties ['border-collapse']) ) {
                    $properties ['border-model'] = $properties ['border-collapse'];
                    unset ($properties ['border-collapse']);
                    if ( $properties ['border-model'] == 'collapse' ) {
                        $properties ['border-model'] = 'collapsing';
                    } else {
                        $properties ['border-model'] = 'separating';
                    }
                }
            }

            $style->importProperties($properties, NULL);
        }
    }

    /**
     * Import CSS code for styles.
     *
     * @param string $template
     */
    public function import_css_from_string($css_code, $media_sel=NULL, $media_path) {
        $import = plugin_load('helper', 'odt_cssimport');
        if ( $import != NULL ) {
            $import->importFromString ($css_code);
            $this->import_css_internal ($import, $media_sel, $media_path);
        }
    }

    public function import_css_from_file($filename, $media_sel=NULL, $media_path) {
        $import = plugin_load('helper', 'odt_cssimport');
        if ( $import != NULL ) {
            $import->importFromFile ($filename);
            $this->import_css_internal ($import, $media_sel, $media_path);
        }
    }

    protected function import_css_internal($import, $media_sel=NULL, $media_path) {
        $this->importStyle($import, 'heading1', 'h1', NULL, $media_sel);
        $this->importStyle($import, 'heading2', 'h2', NULL, $media_sel);
        $this->importStyle($import, 'heading3', 'h3', NULL, $media_sel);
        $this->importStyle($import, 'heading4', 'h4', NULL, $media_sel);
        $this->importStyle($import, 'heading5', 'h5', NULL, $media_sel);

        $this->importStyle($import, 'horizontal line', 'hr',         NULL, $media_sel);

        $this->importStyle($import, 'body', 'body',       NULL, $media_sel);

        $this->importStyle($import, 'emphasis',     'em',     NULL, $media_sel);
        $this->importStyle($import, 'strong',       'strong', NULL, $media_sel);
        $this->importStyle($import, 'underline',    'u',      NULL, $media_sel);
        $this->importStyle($import, 'monospace',    'code',   NULL, $media_sel);
        $this->importStyle($import, 'del',          'del',    NULL, $media_sel);
        $this->importStyle($import, 'preformatted', 'pre',    NULL, $media_sel);

        $this->importStyle($import, 'quotation1', 'quotation1', NULL, $media_sel);
        $this->importStyle($import, 'quotation2', 'quotation2', NULL, $media_sel);
        $this->importStyle($import, 'quotation3', 'quotation3', NULL, $media_sel);
        $this->importStyle($import, 'quotation4', 'quotation4', NULL, $media_sel);
        $this->importStyle($import, 'quotation5', 'quotation5', NULL, $media_sel);

        $this->importStyle($import, 'table',         'table', NULL, $media_sel);
        $this->importStyle($import, 'table header',  'thead', NULL, $media_sel);
        $this->importStyle($import, 'table heading', 'th',    NULL, $media_sel);
        $this->importStyle($import, 'table cell',    'td',    NULL, $media_sel);

        $this->importUnorderedListStyles($import, $media_sel, $media_path);
        $this->importOrderedListStyles($import, $media_sel, $media_path);
        $this->importStyle($import, 'list first paragraph', NULL, 'listfirstparagraph', $media_sel);
        $this->importStyle($import, 'list last paragraph', NULL, 'listlastparagraph', $media_sel);

        $this->importStyle($import, 'internet link',         'internetlink',        NULL, $media_sel);
        $this->importStyle($import, 'visited internet link', 'visitedinternetlink', NULL, $media_sel);
        $this->importStyle($import, 'local link',            'locallink',           NULL, $media_sel);
        $this->importStyle($import, 'visited local link',    'visitedlocallink',    NULL, $media_sel);
    }
}
