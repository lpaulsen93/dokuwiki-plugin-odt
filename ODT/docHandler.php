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
require_once DOKU_PLUGIN . 'odt/ODT/ODTUnits.php';

/**
 * The docHandler interface
 */
abstract class docHandler
{
    public $trace_dump = NULL;
    var $manifest;
    var $ZIP;
    protected $registrations = array();
    protected $internalRegs = array('heading1' => array('element' => 'h1', 'attributes' => NULL),
                                    'heading2' => array('element' => 'h2', 'attributes' => NULL),
                                    'heading3' => array('element' => 'h3', 'attributes' => NULL),
                                    'heading4' => array('element' => 'h4', 'attributes' => NULL),
                                    'heading5' => array('element' => 'h5', 'attributes' => NULL),
                                    'horizontal line' => array('element' => 'hr', 'attributes' => NULL),
                                    'body' => array('element' => 'p', 'attributes' => NULL),
                                    'emphasis' => array('element' => 'em', 'attributes' => NULL),
                                    'strong' => array('element' => 'strong', 'attributes' => NULL),
                                    'underline' => array('element' => 'u', 'attributes' => NULL),
                                    'monospace' => array('element' => 'code', 'attributes' => NULL),
                                    'del' => array('element' => 'del', 'attributes' => NULL),
                                    'preformatted' => array('element' => 'pre', 'attributes' => NULL),
                                    'source code' => array('element' => 'pre', 'attributes' => 'class="code"'),
                                    'source file' => array('element' => 'pre', 'attributes' => 'class="file"'),
                                    'quotation1' => array('element' => 'quotation1', 'attributes' => NULL),
                                    'quotation2' => array('element' => 'quotation1', 'attributes' => NULL),
                                    'quotation3' => array('element' => 'quotation1', 'attributes' => NULL),
                                    'quotation4' => array('element' => 'quotation1', 'attributes' => NULL),
                                    'quotation5' => array('element' => 'quotation1', 'attributes' => NULL),
                                   );
    protected $table_styles = array('table' => array('element' => 'table', 'attributes' => NULL),
                                    'table header' => array('element' => 'th', 'attributes' => NULL),
                                    'table cell' => array('element' => 'td', 'attributes' => NULL)
                                   );
    protected $link_styles = array(
                                   'internet link' => array('element' => 'a',
                                                            'attributes' => NULL,
                                                            'pseudo-class' => 'link'),
                                   'visited internet link' => array('element' => 'a',
                                                                    'attributes' => NULL,
                                                                    'pseudo-class' => 'visited'),
                                   'local link' => array('element' => 'a', 
                                                         'attributes' => 'class="wikilink1"',
                                                         'pseudo-class' => NULL),
                                   'visited local link' => array('element' => 'a',
                                                                 'attributes' => 'class="wikilink1"',
                                                                 'pseudo-class' => NULL),
                                  );

    /**
     * Constructor.
     */
    public function __construct() {
        // prepare the zipper, manifest
        $this->ZIP = new ZipLib();
        $this->manifest = new ODTManifest();
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
        $odt_file = $this->addFileAsPicture($file);

        if ( $odt_file != NULL ) {
            $style->setPropertyForLevel($level, 'list-level-style', 'image');
            $style->setPropertyForLevel($level, 'href', $odt_file);
            $style->setPropertyForLevel($level, 'type', 'simple');
            $style->setPropertyForLevel($level, 'show', 'embed');
            $style->setPropertyForLevel($level, 'actuate', 'onLoad');
            $style->setPropertyForLevel($level, 'vertical-pos', 'middle');
            $style->setPropertyForLevel($level, 'vertical-rel', 'line');

            list($width, $height) = ODTUtility::getImageSize($file);
            if (empty($width) || empty($height)) {
                $width = '0.5';
                $height = $width;
            }
            $style->setPropertyForLevel($level, 'width', $width.'cm');
            $style->setPropertyForLevel($level, 'height', $height.'cm');

            // ??? Wie berechnen...
            $text_indent = ODTUnits::getDigits($style->getPropertyFromLevel($level, 'text-indent'));
            $margin_left = ODTUnits::getDigits($style->getPropertyFromLevel($level, 'margin_left'));
            $tab_stop_position =
                ODTUnits::getDigits($style->getPropertyFromLevel($level, 'list-tab-stop-position'));
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

    protected function importOrderedListStyles(cssimportnew $import, cssdocument $htmlStack, ODTUnits $units, $rootProperties=NULL, $media_path=NULL) {
        $name = $this->styleset->getStyleName('numbering');
        $style = $this->styleset->getStyle($name);
        if ($style == NULL ) {
            return;
        }

        // Reset stack to saved root so next importStyle
        // will have the same conditions
        $htmlStack->restoreToRoot ();

        for ($level = 1 ; $level < 11 ; $level++) {
            // Push our element to import on the stack
            $htmlStack->open('ol');
            $toMatch = $htmlStack->getCurrentElement();

            $properties = array();                
            $import->getPropertiesForElement($properties, $toMatch);

            // Adjust values for ODT
            ODTUtility::adjustValuesForODT ($properties, $units);

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
                        $numbering = 'I';
                        break;
                }
                $style->setPropertyForLevel($level, 'num-format', $numbering);
                if ($prefix != NULL ) {
                    $style->setPropertyForLevel($level, 'num-prefix', $prefix);
                }
                $style->setPropertyForLevel($level, 'num-suffix', $suffix);

                if ($properties ['padding-left'] != NULL) {
                    $style->setPropertyForLevel($level, 'text-min-label-distance', $properties ['padding-left']);
                }
            }
            if ($properties ['list-style-image'] !== NULL && $properties ['list-style-image'] != 'none') {
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

        // Reset stack to saved root so next importStyle
        // will have the same conditions
        $htmlStack->restoreToRoot ();

        // Eventually inherit some $rootProperties:
        // color, font-size and line-height
        $name = $this->styleset->getStyleName('numbering content');
        $style = $this->styleset->getStyle($name);
        if ($style != NULL) {
            $properties = array();
            if (empty($properties ['color']) && !empty($rootProperties ['color'])) {
                $properties ['color'] = $rootProperties ['color'];
            }
            if (empty($properties ['font-size']) && !empty($rootProperties ['font-size'])) {
                $properties ['font-size'] = $rootProperties ['font-size'];
            }
            if (empty($properties ['line-height']) && !empty($rootProperties ['line-height'])) {
                $properties ['line-height'] = $rootProperties ['line-height'];
            }
            if (count($properties) > 0) {
                $style->importProperties($properties, NULL);
            }
        }
    }

    protected function importUnorderedListStyles(cssimportnew $import, cssdocument $htmlStack, ODTUnits $units, $rootProperties=NULL, $media_path=NULL) {
        $name = $this->styleset->getStyleName('list');
        $style = $this->styleset->getStyle($name);
        if ($style == NULL ) {
            return;
        }

        // Workaround for OFT format, see end of loop
        $name = $this->styleset->getStyleName('list first paragraph');
        $firstStyle = $this->styleset->getStyle($name);
        $name = $this->styleset->getStyleName('list last paragraph');
        $lastStyle = $this->styleset->getStyle($name);

        // Reset stack to saved root so next importStyle
        // will have the same conditions
        $htmlStack->restoreToRoot ();

        for ($level = 1 ; $level < 11 ; $level++) {
            // Push our element to import on the stack
            $htmlStack->open('ul');
            $toMatch = $htmlStack->getCurrentElement();

            $properties = array();                
            $import->getPropertiesForElement($properties, $toMatch);

            // Adjust values for ODT
            ODTUtility::adjustValuesForODT ($properties, $units);
            
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
            if ($properties ['list-style-image'] !== NULL && $properties ['list-style-image'] != 'none') {
                $file = $properties ['list-style-image'];
                $file = substr($file, 4);
                $file = trim($file, "()'");
                if ($media_path [strlen($media_path)-1] != '/') {
                    $media_path .= '/';
                }
                $file = $media_path.$file;
                
                $this->setListStyleImage ($style, $level, $file);
            }

            // Workaround for OFT format:
            // We can not set margins on the list itself.
            // So we use extra paragraph styles for the first and last
            // list items to set a margin.
            if ($level == 1 &&
                ($properties ['margin-left'] != NULL ||
                 $properties ['margin-right'] != NULL ||
                 $properties ['margin-top'] != NULL ||
                 $properties ['margin-bottom'] != NULL)) {
                $set = array ();
                $set ['margin-left'] = $properties ['margin-left'];
                $set ['margin-right'] = $properties ['margin-right'];
                $set ['margin-top'] = $properties ['margin-top'];
                $set ['margin-bottom'] = '0pt';
                $firstStyle->importProperties($set);
                $set ['margin-bottom'] = $properties ['margin-bottom'];
                $set ['margin-top'] = '0pt';;
                $lastStyle->importProperties($set);
            }
        }

        // Reset stack to saved root so next importStyle
        // will have the same conditions
        $htmlStack->restoreToRoot ();

        // Eventually inherit some $rootProperties:
        // color, font-size and line-height
        $name = $this->styleset->getStyleName('list content');
        $style = $this->styleset->getStyle($name);
        if ($style != NULL) {
            $properties = array();
            if (empty($properties ['color']) && !empty($rootProperties ['color'])) {
                $properties ['color'] = $rootProperties ['color'];
            }
            if (empty($properties ['font-size']) && !empty($rootProperties ['font-size'])) {
                $properties ['font-size'] = $rootProperties ['font-size'];
            }
            if (empty($properties ['line-height']) && !empty($rootProperties ['line-height'])) {
                $properties ['line-height'] = $rootProperties ['line-height'];
            }
            if (count($properties) > 0) {
                $style->importProperties($properties, NULL);
            }
        }
    }

    protected function importTableStyles(cssimportnew $import, cssdocument $htmlStack, ODTUnits $units, $rootProperties=NULL) {
        foreach ($this->table_styles as $style_type => $elementParams) {
            $name = $this->styleset->getStyleName($style_type);
            $style = $this->styleset->getStyle($name);
            if ( $style != NULL ) {
                $element = $elementParams ['element'];
                $attributes = $elementParams ['attributes'];

                // Push our element to import on the stack
                $htmlStack->open($element, $attributes);
                $toMatch = $htmlStack->getCurrentElement();
                
                $element_to_check = 'td';

                $properties = array();                
                $import->getPropertiesForElement($properties, $toMatch);
                if (count($properties) == 0) {
                    // Nothing found. Back to top, DO NOT change existing style!

                    if ($this->trace_dump != NULL && $element == $element_to_check) {
                        $this->trace_dump .= 'Nothing found for '.$element_to_check."!\n";
                    }

                    continue;
                }

                // If colors are empty,
                // eventually inherit them from the $rootProperties
                if (empty($properties ['color']) && !empty($rootProperties ['color'])) {
                    $properties ['color'] = $rootProperties ['color'];
                }
                if (empty($properties ['background-color']) && !empty($rootProperties ['background-color'])) {
                    $properties ['background-color'] = $rootProperties ['background-color'];
                }
                if (empty($properties ['font-size']) && !empty($rootProperties ['font-size'])) {
                    $properties ['font-size'] = $rootProperties ['font-size'];
                }
                if (empty($properties ['line-height']) && !empty($rootProperties ['line-height'])) {
                    $properties ['line-height'] = $rootProperties ['line-height'];
                }

                // We have found something.
                // First clear the existing layout properties of the style.
                $style->clearLayoutProperties();

                if ($this->trace_dump != NULL && $element == $element_to_check) {
                    $this->trace_dump .= 'Checking '.$element_to_check.'['.$attributes.']';
                    $this->trace_dump .= 'BEFORE:'."\n";
                    foreach ($properties as $key => $value) {
                        $this->trace_dump .= $key.'='.$value."\n";
                    }
                    $this->trace_dump .= '---------------------------------------'."\n";
                }

                // Adjust values for ODT
                ODTUtility::adjustValuesForODT ($properties, $units);

                if ($this->trace_dump != NULL && $element == $element_to_check) {
                    $this->trace_dump .= 'AFTER:'."\n";
                    foreach ($properties as $key => $value) {
                        $this->trace_dump .= $key.'='.$value."\n";
                    }
                    $this->trace_dump .= '---------------------------------------'."\n";
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

                // Inherit properties for table header paragraph style from
                // the properties of the 'th' element
                if ($element == 'th') {
                    $name = $this->styleset->getStyleName('table heading');
                    $paragraphStyle = $this->styleset->getStyle($name);

                    // Do not set borders on our paragraph styles in the table.
                    // Otherwise we will have double borders. Around the cell and
                    // around the text in the cell!
                    $disabled = array();
                    $disabled ['border']        = 1;
                    $disabled ['border-top']    = 1;
                    $disabled ['border-right']  = 1;
                    $disabled ['border-bottom'] = 1;
                    $disabled ['border-left']   = 1;
                    // Do not set background/background-color
                    $disabled ['background-color'] = 1;

                    $paragraphStyle->clearLayoutProperties();
                    $paragraphStyle->importProperties($properties, $disabled);
                }
                // Inherit properties for table content paragraph style from
                // the properties of the 'td' element
                if ($element == 'td') {
                    $name = $this->styleset->getStyleName('table content');
                    $paragraphStyle = $this->styleset->getStyle($name);

                    // Do not set borders on our paragraph styles in the table.
                    // Otherwise we will have double borders. Around the cell and
                    // around the text in the cell!
                    $disabled = array();
                    $disabled ['border']        = 1;
                    $disabled ['border-top']    = 1;
                    $disabled ['border-right']  = 1;
                    $disabled ['border-bottom'] = 1;
                    $disabled ['border-left']   = 1;
                    // Do not set background/background-color
                    $disabled ['background-color'] = 1;
                    
                    $paragraphStyle->clearLayoutProperties();
                    $paragraphStyle->importProperties($properties, $disabled);
                }
                $style->importProperties($properties, NULL);

                // Reset stack to saved root so next importStyle
                // will have the same conditions
                $htmlStack->restoreToRoot ();
            }
        }
    }

    protected function importLinkStyles(cssimportnew $import, cssdocument $htmlStack, ODTUnits $units, $rootProperties=NULL) {
        foreach ($this->link_styles as $style_type => $elementParams) {
            $name = $this->styleset->getStyleName($style_type);
            $style = $this->styleset->getStyle($name);
            if ( $name != NULL && $style != NULL ) {
                $element = $elementParams ['element'];
                $attributes = $elementParams ['attributes'];
                $pseudo_class = $elementParams ['pseudo-class'];

                // Push our element to import on the stack
                $htmlStack->open($element, $attributes, $pseudo_class, NULL);
                $toMatch = $htmlStack->getCurrentElement();

                $properties = array();                
                $import->getPropertiesForElement($properties, $toMatch);
                if (count($properties) == 0) {
                    // Nothing found. Back to top, DO NOT change existing style!
                    continue;
                }

                // If colors are empty,
                // eventually inherit them from the $rootProperties
                if (empty($properties ['color']) && !empty($rootProperties ['color'])) {
                    $properties ['color'] = $rootProperties ['color'];
                }
                if (empty($properties ['background-color']) && !empty($rootProperties ['background-color'])) {
                    $properties ['background-color'] = $rootProperties ['background-color'];
                }
                if (empty($properties ['font-size']) && !empty($rootProperties ['font-size'])) {
                    $properties ['font-size'] = $rootProperties ['font-size'];
                }
                if (empty($properties ['line-height']) && !empty($rootProperties ['line-height'])) {
                    $properties ['line-height'] = $rootProperties ['line-height'];
                }

                // We have found something.
                // First clear the existing layout properties of the style.
                $style->clearLayoutProperties();

                // Adjust values for ODT
                ODTUtility::adjustValuesForODT ($properties, $units);

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

                $style->importProperties($properties, NULL);

                // Reset stack to saved root so next importStyle
                // will have the same conditions
                $htmlStack->restoreToRoot ();
            }
        }
    }

    protected function importStyle(cssimportnew $import, cssdocument $htmlStack, ODTUnits $units, $style_type, $element, $attributes=NULL, $rootProperties=NULL) {
        $name = $this->styleset->getStyleName($style_type);
        $style = $this->styleset->getStyle($name);
        if ( $style != NULL ) {
            // Push our element to import on the stack
            $htmlStack->open($element, $attributes);
            $toMatch = $htmlStack->getCurrentElement();
            
            $element_to_check = 'pre1234';
            
            $properties = array();
            $import->getPropertiesForElement($properties, $toMatch);
            if (count($properties) == 0) {
                // Nothing found. Return, DO NOT change existing style!

                if ($this->trace_dump != NULL && $element == $element_to_check) {
                    $this->trace_dump .= 'Nothing found for '.$element_to_check."!\n";
                }

                return;
            }

            // Eventually inherit some $rootProperties:
            // color, font-size and line-height
            if (empty($properties ['color']) && !empty($rootProperties ['color'])) {
                $properties ['color'] = $rootProperties ['color'];
            }
            if (empty($properties ['font-size']) && !empty($rootProperties ['font-size'])) {
                $properties ['font-size'] = $rootProperties ['font-size'];
            }
            if (empty($properties ['line-height']) && !empty($rootProperties ['line-height'])) {
                $properties ['line-height'] = $rootProperties ['line-height'];
            }

            // We have found something.
            // First clear the existing layout properties of the style.
            $style->clearLayoutProperties();

            if ($this->trace_dump != NULL && $element == $element_to_check) {
                $this->trace_dump .= 'Checking '.$element_to_check.'['.$attributes.']';
                $this->trace_dump .= 'BEFORE:'."\n";
                foreach ($properties as $key => $value) {
                    $this->trace_dump .= $key.'='.$value."\n";
                }
                $this->trace_dump .= '---------------------------------------'."\n";
            }

            // Adjust values for ODT
            ODTUtility::adjustValuesForODT ($properties, $units);

            if ($this->trace_dump != NULL && $element == $element_to_check) {
                $this->trace_dump .= 'AFTER:'."\n";
                foreach ($properties as $key => $value) {
                    $this->trace_dump .= $key.'='.$value."\n";
                }
                $this->trace_dump .= '---------------------------------------'."\n";
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

            // In all paragraph styles set the ODT specific attribute join-border = false
            if ($style->getFamily() == 'paragraph') {
                $properties ['join-border'] = 'false';
            }

            $style->importProperties($properties, NULL);

            // Reset stack to saved root so next importStyle
            // will have the same conditions
            $htmlStack->restoreToRoot ();
        }
    }

    public function import_styles_from_css (cssimportnew $import, cssdocument $htmlStack, ODTUnits $units, $media_sel=NULL, $media_path, $rootProperties=NULL) {
        if ( $import != NULL ) {
            $save = $import->getMedia ();
            $import->setMedia ($media_sel);
            
            // Make a copy of the stack to be sure we do not leave anything behind after import.
            $stack = clone $htmlStack;
            $stack->restoreToRoot ();

            $this->import_styles_from_css_internal ($import, $stack, $units, $media_path, $rootProperties);

            $import->setMedia ($save);
        }
    }

    protected function import_styles_from_css_internal(cssimportnew $import, cssdocument $htmlStack, ODTUnits $units, $media_path, $rootProperties=NULL) {
        // Set background-color of page
        if (!empty($rootProperties ['background-color'])) {
            $name = $this->styleset->getStyleName('first page');
            $first_page = $this->styleset->getStyle($name);
            if ($first_page != NULL) {
                $first_page->setProperty('background-color', $rootProperties ['background-color']);
            }
        }

        // Import styles which only require a simple import based on element name and attributes
        $toImport = array_merge ($this->internalRegs, $this->registrations);
        foreach ($toImport as $style => $element) {
            $this->importStyle($import, $htmlStack, $units,
                               $style,
                               $element ['element'],
                               $element ['attributes'],
                               $rootProperties);
        }

        // Import table styles
        $this->importTableStyles($import, $htmlStack, $units, $rootProperties);

        // Import link styles (require extra pseudo class handling)
        $this->importLinkStyles($import, $htmlStack, $units, $rootProperties);

        // Import list styles and list paragraph styles
        $this->importUnorderedListStyles($import, $htmlStack, $units, $rootProperties, $media_path);
        $this->importOrderedListStyles($import, $htmlStack, $units, $rootProperties, $media_path);
    }
    
    public function registerHTMLElementForCSSImport ($style_type, $element, $attributes=NULL) {
        $this->registrations [$style_type]['element'] = $element;
        $this->registrations [$style_type]['attributes'] = $attributes;
    }
}
