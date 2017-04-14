<?php

require_once DOKU_PLUGIN . 'odt/ODT/ODTDocument.php';

/**
 * ODTImport:
 * Class containing static code for importing ODT or CSS code.
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 */
class ODTImport
{
    static public $trace_dump = NULL;
    static protected $internalRegs = array('heading1' => array('element' => 'h1', 'attributes' => NULL),
                                    'heading2' => array('element' => 'h2', 'attributes' => NULL),
                                    'heading3' => array('element' => 'h3', 'attributes' => NULL),
                                    'heading4' => array('element' => 'h4', 'attributes' => NULL),
                                    'heading5' => array('element' => 'h5', 'attributes' => NULL),
                                    'horizontal line' => array('element' => 'hr', 'attributes' => NULL),
                                    'body' => array('element' => 'p', 'attributes' => NULL),
                                    'emphasis' => array('element' => 'em', 'attributes' => NULL, 'compare' => true),
                                    'strong' => array('element' => 'strong', 'attributes' => NULL, 'compare' => true),
                                    'underline' => array('element' => 'u', 'attributes' => NULL, 'compare' => true),
                                    'monospace' => array('element' => 'code', 'attributes' => NULL),
                                    'del' => array('element' => 'del', 'attributes' => NULL, 'compare' => true),
                                    'preformatted' => array('element' => 'pre', 'attributes' => NULL),
                                    'source code' => array('element' => 'pre', 'attributes' => 'class="code"'),
                                    'source file' => array('element' => 'pre', 'attributes' => 'class="file"'),
                                   );
    static protected $table_styles = array('table' => array('element' => 'table', 'attributes' => NULL),
                                    'table header' => array('element' => 'th', 'attributes' => NULL),
                                    'table cell' => array('element' => 'td', 'attributes' => NULL)
                                   );
    static protected $link_styles = array(
                                   'internet link' => array('element' => 'a',
                                                            'attributes' => NULL,
                                                            'pseudo-class' => 'link'),
                                   'visited internet link' => array('element' => 'a',
                                                                    'attributes' => NULL,
                                                                    'pseudo-class' => 'visited'),
                                   'local link' => array('element' => 'a', 
                                                         'attributes' => 'class="wikilink1"',
                                                         'pseudo-class' => 'link'),
                                   'visited local link' => array('element' => 'a',
                                                                 'attributes' => 'class="wikilink1"',
                                                                 'pseudo-class' => 'visited'),
                                  );

    /**
     * Import CSS code.
     * This is the CSS code import for the new API.
     * That means in this function the CSS code is only parsed and stored
     * but not immediately imported as styles like in the old API.
     * 
     * The function can be called multiple times.
     * All CSS code is handled like being appended.
     *
     * @param string $cssCode The CSS code to be imported
     */
    static protected function importCSSCodeInternal (ODTInternalParams $params, $isFile, $CSSSource, $mediaSel=NULL, $lengthCallback=NULL, $URLCallback=NULL) {
        if ($params->import == NULL) {
            // No CSS imported yet. Create object.
            $params->import = new cssimportnew();
            if ( $params->import == NULL ) {
                return;
            }
            $params->import->setMedia ($mediaSel);
        }

        if ($isFile == false) {
            $params->import->importFromString($CSSSource);
        } else {
            $params->import->importFromFile($CSSSource);
        }

        // Call adjustLengthValues to make our callback function being called for every
        // length value imported. This gives us the chance to convert it once from
        // pixel to points.
        if ($lengthCallback != NULL) {
            $params->import->adjustLengthValues ($lengthCallback);
        }

        // Call replaceURLPrefixes to make the callers (renderer/page.php) callback
        // function being called for every URL to convert it to an absolute path.
        if ($URLCallback != NULL) {
            $params->import->replaceURLPrefixes ($URLCallback);
        }
    }

    /**
     * Import CSS code from a file.
     *
     * @param ODTInternalParams $params Common params
     * @param string $CSSTemplate String containing the path and file name of the CSS file to import
     * @param string $media_sel String containing the media selector to use for import (e.g. 'print' or 'screen')
     * @param callable $callback Callback for adjusting length values
     */
    static public function importCSSFromFile (ODTInternalParams $params, $CSSTemplate, $media_sel=NULL, $lengthCallback=NULL, $URLCallback=NULL, $registrations=NULL, $importStyles=true, $listAlign='right') {
        self::importCSSCodeInternal ($params, true, $CSSTemplate, $media_sel, $lengthCallback, $URLCallback);
        if ($importStyles) {
            self::import_styles_from_css ($params, $media_sel, $registrations, $listAlign);
        }
    }

    /**
     * Import CSS code for styles from a string.
     *
     * @param string $cssCode The CSS code to import
     * @param string $mediaSel The media selector to use e.g. 'print'
     * @param string $mediaPath Local path to media files
     */
    static public function importCSSFromString(ODTInternalParams $params, $cssCode, $media_sel=NULL, $lengthCallback=NULL, $URLCallback=NULL, $registrations=NULL, $importStyles=true, $listAlign='right')
    {
        self::importCSSCodeInternal ($params, false, $cssCode, $media_sel, $lengthCallback, $URLCallback);
        if ($importStyles) {
            self::import_styles_from_css ($params, $media_sel, $registrations, $listAlign);
        }
    }

    static protected function importQuotationStyles(ODTInternalParams $params, cssdocument $htmlStack) {
        // Reset stack to saved root so next importStyle
        // will have the same conditions
        $htmlStack->restoreToRoot ();

        $disabled = array();
        $disabled ['margin']         = 1;
        $disabled ['margin-left']    = 1;
        $disabled ['margin-right']   = 1;
        $disabled ['margin-top']     = 1;
        $disabled ['margin-bottom']  = 1;
        $disabled ['padding']        = 1;
        $disabled ['padding-left']   = 1;
        $disabled ['padding-right']  = 1;
        $disabled ['padding-top']    = 1;
        $disabled ['padding-bottom'] = 1;

        for ($level = 1 ; $level < 6 ; $level++) {
            // Push our element to import on the stack
            $htmlStack->open('blockquote');
            $toMatch = $htmlStack->getCurrentElement();

            $properties = array();                
            $params->import->getPropertiesForElement($properties, $toMatch, $params->units);
            if (count($properties) == 0) {
                // Nothing found. Go to next, DO NOT change existing style!
                continue;
            }

            // Adjust values for ODT
            ODTUtility::adjustValuesForODT ($properties, $params->units);

            $name = $params->styleset->getStyleName('table quotation'.$level);
            $style = $params->styleset->getStyle($name);
            if ($style != NULL ) {
                if ($level == 1) {
                    $style->importProperties($properties);
                } else {
                    $style->importProperties($properties, $disabled);
                }
            }

            $name = $params->styleset->getStyleName('cell quotation'.$level);
            $style = $params->styleset->getStyle($name);
            if ($style != NULL ) {
                $style->importProperties($properties);
            }
        }

        // Reset stack to saved root so next importStyle
        // will have the same conditions
        $htmlStack->restoreToRoot ();
    }

    static protected function setListStyleImage (ODTInternalParams $params, $style, $level, $file) {
        $odt_file = $params->document->addFileAsPicture($file);

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

    static protected function importOrderedListStyles(ODTInternalParams $params, cssdocument $htmlStack, $listAlign='right') {
        $name = $params->styleset->getStyleName('numbering');
        $style = $params->styleset->getStyle($name);
        if ($style == NULL ) {
            return;
        }

        // Workaround for ODT format, see end of loop
        $name = $params->styleset->getStyleName('numbering first');
        $firstStyle = $params->styleset->getStyle($name);
        $name = $params->styleset->getStyleName('numbering last');
        $lastStyle = $params->styleset->getStyle($name);

        // Reset stack to saved root so next importStyle
        // will have the same conditions
        $htmlStack->restoreToRoot ();

        for ($level = 1 ; $level < 11 ; $level++) {
            // Push our element to import on the stack
            $htmlStack->open('ol');
            $toMatch = $htmlStack->getCurrentElement();

            $properties = array();                
            $params->import->getPropertiesForElement($properties, $toMatch, $params->units);
            if (count($properties) == 0) {
                // Nothing found. Return, DO NOT change existing style!
                return;
            }

            // Push list item element to import on the stack
            // (Required to get left margin)
            $htmlStack->open('li');
            $toMatch = $htmlStack->getCurrentElement();

            $li_properties = array();                
            $params->import->getPropertiesForElement($li_properties, $toMatch, $params->units);

            // Adjust values for ODT
            ODTUtility::adjustValuesForODT ($properties, $params->units);

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
                if ($prefix !== NULL ) {
                    $style->setPropertyForLevel($level, 'num-prefix', $prefix);
                }
                $style->setPropertyForLevel($level, 'num-suffix', $suffix);

                // Padding is not inherited so we will only get it for the list root!
                if ($level == 1 ) {
                    $paddingLeft = 0;
                    if ($properties ['padding-left'] !== NULL) {
                        $paddingLeft = $params->units->toCentimeters($properties ['padding-left'], 'y');
                        $paddingLeft = substr($paddingLeft, 0, -2);
                    }
                }
                $marginLeft = 1;
                if ($li_properties ['margin-left'] !== NULL) {
                    $marginLeft = $params->units->toCentimeters($li_properties ['margin-left'], 'y');
                    $marginLeft = substr($marginLeft, 0, -2);
                }
                // Set list params.
                $params->document->setOrderedListParams($level, $listAlign, $paddingLeft, $marginLeft);
            }
            if ($properties ['list-style-image'] !== NULL && $properties ['list-style-image'] != 'none') {
                // It is assumed that the CSS already contains absolute path values only!
                // (see replaceURLPrefixes)
                $file = $properties ['list-style-image'];
                
                $this->setListStyleImage ($params, $style, $level, $file);
            }

            // Workaround for ODT format:
            // We can not set margins on the list itself.
            // So we use extra paragraph styles for the first and last
            // list items to set a margin.
            if ($level == 1 &&
                ($properties ['margin-top'] != NULL ||
                 $properties ['margin-bottom'] != NULL)) {
                $set = array ();
                $disabled = array ();
                // Delete left and right margins as setting them
                // would destroy list item indentation
                $set ['margin-left'] = NULL;
                $set ['margin-right'] = NULL;
                $set ['margin-top'] = $properties ['margin-top'];
                $set ['margin-bottom'] = '0pt';
                $firstStyle->importProperties($set, $disabled);
                $set ['margin-bottom'] = $properties ['margin-bottom'];
                $set ['margin-top'] = '0pt';
                $lastStyle->importProperties($set, $disabled);
            }

            // Import properties for list paragraph style once.
            // Margins MUST be ignored! See extra handling above.
            if ($level == 1) {
                $disabled = array();
                $disabled ['margin-left'] = 1;
                $disabled ['margin-right'] = 1;
                $disabled ['margin-top'] = 1;
                $disabled ['margin-bottom'] = 1;

                $name = $params->styleset->getStyleName('numbering content');
                $paragraphStyle = $params->styleset->getStyle($name);
                $paragraphStyle->importProperties($properties, $disabled);
            }
        }

        // Reset stack to saved root so next importStyle
        // will have the same conditions
        $htmlStack->restoreToRoot ();
    }

    static protected function importUnorderedListStyles(ODTInternalParams $params, cssdocument $htmlStack, $listAlign='right') {
        $name = $params->styleset->getStyleName('list');
        $style = $params->styleset->getStyle($name);
        if ($style == NULL ) {
            return;
        }

        // Workaround for ODT format, see end of loop
        $name = $params->styleset->getStyleName('list first');
        $firstStyle = $params->styleset->getStyle($name);
        $name = $params->styleset->getStyleName('list last');
        $lastStyle = $params->styleset->getStyle($name);

        // Reset stack to saved root so next importStyle
        // will have the same conditions
        $htmlStack->restoreToRoot ();

        for ($level = 1 ; $level < 11 ; $level++) {
            // Push our element to import on the stack
            $htmlStack->open('ul');
            $toMatch = $htmlStack->getCurrentElement();

            $properties = array();                
            $params->import->getPropertiesForElement($properties, $toMatch, $params->units);
            if (count($properties) == 0) {
                // Nothing found. Return, DO NOT change existing style!
                return;
            }

            // Push list item element to import on the stack
            // (Required to get left margin)
            $htmlStack->open('li');
            $toMatch = $htmlStack->getCurrentElement();

            $li_properties = array();                
            $params->import->getPropertiesForElement($li_properties, $toMatch, $params->units);

            // Adjust values for ODT
            ODTUtility::adjustValuesForODT ($properties, $params->units);
            
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

                // Padding is not inherited so we will only get it for the list root!
                if ($level == 1 ) {
                    $paddingLeft = 0;
                    if ($properties ['padding-left'] !== NULL) {
                        $paddingLeft = $params->units->toCentimeters($properties ['padding-left'], 'y');
                        $paddingLeft = substr($paddingLeft, 0, -2);
                    }
                }
                $marginLeft = 1;
                if ($li_properties ['margin-left'] !== NULL) {
                    $marginLeft = $params->units->toCentimeters($li_properties ['margin-left'], 'y');
                    $marginLeft = substr($marginLeft, 0, -2);
                }
                // Set list params.
                $params->document->setUnorderedListParams($level, $listAlign, $paddingLeft, $marginLeft);
            }
            if ($properties ['list-style-image'] !== NULL && $properties ['list-style-image'] != 'none') {
                // It is assumed that the CSS already contains absolute path values only!
                // (see replaceURLPrefixes)
                $file = $properties ['list-style-image'];
                /*$file = substr($file, 4);
                $file = trim($file, "()'");
                if ($media_path [strlen($media_path)-1] != '/') {
                    $media_path .= '/';
                }
                $file = $media_path.$file;*/
                
                $this->setListStyleImage ($params, $style, $level, $file);
            }

            // Workaround for ODT format:
            // We can not set margins on the list itself.
            // So we use extra paragraph styles for the first and last
            // list items to set a margin.
            if ($level == 1 &&
                ($properties ['margin-top'] != NULL ||
                 $properties ['margin-bottom'] != NULL)) {
                $set = array ();
                $disabled = array ();
                // Delete left and right margins as setting them
                // would destroy list item indentation
                $set ['margin-left'] = NULL;
                $set ['margin-right'] = NULL;
                $set ['margin-top'] = $properties ['margin-top'];
                $set ['margin-bottom'] = '0pt';
                $firstStyle->importProperties($set, $disabled);
                $set ['margin-bottom'] = $properties ['margin-bottom'];
                $set ['margin-top'] = '0pt';
                $lastStyle->importProperties($set, $disabled);
            }

            // Import properties for list paragraph style once.
            // Margins MUST be ignored! See extra handling above.
            if ($level == 1) {
                $disabled = array();
                $disabled ['margin-left'] = 1;
                $disabled ['margin-right'] = 1;
                $disabled ['margin-top'] = 1;
                $disabled ['margin-bottom'] = 1;

                $name = $params->styleset->getStyleName('list content');
                $paragraphStyle = $params->styleset->getStyle($name);
                $paragraphStyle->importProperties($properties, $disabled);
            }
        }

        // Reset stack to saved root so next importStyle
        // will have the same conditions
        $htmlStack->restoreToRoot ();
    }


    static protected function importTableStyles(ODTInternalParams $params, cssdocument $htmlStack) {
        foreach (self::$table_styles as $style_type => $elementParams) {
            $name = $params->styleset->getStyleName($style_type);
            $style = $params->styleset->getStyle($name);
            if ( $style != NULL ) {
                $element = $elementParams ['element'];
                $attributes = $elementParams ['attributes'];

                // Push our element to import on the stack
                $htmlStack->open($element, $attributes);
                $toMatch = $htmlStack->getCurrentElement();

                $properties = array();                
                $params->import->getPropertiesForElement($properties, $toMatch, $params->units);
                if (count($properties) == 0) {
                    // Nothing found. Back to top, DO NOT change existing style!
                    continue;
                }

                // We have found something.
                // First clear the existing layout properties of the style.
                $style->clearLayoutProperties();

                // Adjust values for ODT
                ODTUtility::adjustValuesForODT ($properties, $params->units);

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
                    $name = $params->styleset->getStyleName('table heading');
                    $paragraphStyle = $params->styleset->getStyle($name);

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
                    $name = $params->styleset->getStyleName('table content');
                    $paragraphStyle = $params->styleset->getStyle($name);

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
                $disabled = array();
                $style->importProperties($properties, $disabled);

                // Reset stack to saved root so next importStyle
                // will have the same conditions
                $htmlStack->restoreToRoot ();
            }
        }
    }

    static protected function importLinkStyles(ODTInternalParams $params, cssdocument $htmlStack) {
        foreach (self::$link_styles as $style_type => $elementParams) {
            $name = $params->styleset->getStyleName($style_type);
            $style = $params->styleset->getStyle($name);
            if ( $name != NULL && $style != NULL ) {
                $element = $elementParams ['element'];
                $attributes = $elementParams ['attributes'];
                $pseudo_class = $elementParams ['pseudo-class'];

                // Push our element to import on the stack
                $htmlStack->open($element, $attributes, $pseudo_class, NULL);
                $toMatch = $htmlStack->getCurrentElement();

                $properties = array();
                $params->import->getPropertiesForElement($properties, $toMatch, $params->units);
                if (count($properties) == 0) {
                    // Nothing found. Back to top, DO NOT change existing style!
                    continue;
                }

                // We have found something.
                // First clear the existing layout properties of the style.
                $style->clearLayoutProperties();

                // Adjust values for ODT
                ODTUtility::adjustValuesForODT ($properties, $params->units);

                $disabled = array();
                $style->importProperties($properties, $disabled);

                // Reset stack to saved root so next importStyle
                // will have the same conditions
                $htmlStack->restoreToRoot ();
            }
        }
    }

    static protected function importStyle(ODTInternalParams $params, cssdocument $htmlStack, $style_type, $element, $attributes=NULL, array $plain=NULL) {
        $name = $params->styleset->getStyleName($style_type);
        $style = $params->styleset->getStyle($name);
        if ( $style != NULL ) {
            // Push our element to import on the stack
            $htmlStack->open($element, $attributes);
            $toMatch = $htmlStack->getCurrentElement();
            
            $properties = array();
            $params->import->getPropertiesForElement($properties, $toMatch, $params->units);
            if (count($properties) == 0) {
                // Nothing found. Return, DO NOT change existing style!
                return;
            }
            if ($plain != NULL)
            {
                $diff = array_diff ($properties, $plain);
                if (count($diff) == 0) {
                    // Workaround for some elements, e.g. 'em' and 'del':
                    // They may have default values from the browser only.
                    // In that case do not import the style otherwise
                    // 'em' and 'del' will look like plain text.

                    // Reset stack to saved root so next importStyle
                    // will have the same conditions
                    $htmlStack->restoreToRoot ();
                    return;
                }
            }

            // We have found something.
            // First clear the existing layout properties of the style.
            $style->clearLayoutProperties();

            // Adjust values for ODT
            ODTUtility::adjustValuesForODT ($properties, $params->units);

            // In all paragraph styles set the ODT specific attribute join-border = false
            if ($style->getFamily() == 'paragraph') {
                $properties ['join-border'] = 'false';
            }

            $disabled = array();
            if ($style_type == 'horizontal line') {
                // Do not use margin and padding on horizontal line paragraph style!
                $disabled ['margin'] = 1;
                $disabled ['margin-top'] = 1;
                $disabled ['margin-right'] = 1;
                $disabled ['margin-bottom'] = 1;
                $disabled ['margin-left'] = 1;
                $disabled ['padding'] = 1;
                $disabled ['padding-top'] = 1;
                $disabled ['padding-right'] = 1;
                $disabled ['padding-bottom'] = 1;
                $disabled ['padding-left'] = 1;
            }
            $style->importProperties($properties, $disabled);

            // Reset stack to saved root so next importStyle
            // will have the same conditions
            $htmlStack->restoreToRoot ();
        }
    }

    static public function import_styles_from_css (ODTInternalParams $params, $media_sel=NULL, $registrations=NULL, $listAlign='right') {
        if ( $params->import != NULL ) {
            $save = $params->import->getMedia ();
            $params->import->setMedia ($media_sel);
            
            // Make a copy of the stack to be sure we do not leave anything behind after import.
            $stack = clone $params->htmlStack;
            $stack->restoreToRoot ();

            self::import_styles_from_css_internal ($params, $stack, $registrations, $listAlign);

            $params->import->setMedia ($save);
        }
    }

    static public function set_page_properties (ODTInternalParams $params, ODTPageLayoutStyle $pageStyle, $media_sel=NULL) {
        if ( $params->import != NULL ) {
            if ($media_sel != NULL ) {
                $save = $params->import->getMedia ();
                $params->import->setMedia ($media_sel);
            }

            $stack = clone $params->htmlStack;
            $stack->restoreToRoot ();

            // Set background-color of page
            // It is assumed that the last element of the "root" elements hold the backround-color.
            // For DokuWiki this is <div class="page group">, see renderer/page.php, function 'load_css()'
            $stack->restoreToRoot ();
            $properties = array();
            $params->import->getPropertiesForElement($properties, $stack->getCurrentElement(), $params->units);
            ODTUtility::adjustValuesForODT ($properties, $params->units);
            if (!empty($properties ['background-color'])) {
                if ($pageStyle != NULL) {
                    $pageStyle->setProperty('background-color', $properties ['background-color']);
                }
            }

            if ($media_sel != NULL ) {
                $params->import->setMedia ($save);
            }
        }
    }

    static protected function importParagraphDefaultStyle(ODTInternalParams $params) {
        // This function MUST be called at the end of import_styles_from_css_internal
        // ==> the 'body' paragraph style must have alread been imported!

        // Get standard text style ('body')
        $styleName = $params->styleset->getStyleName('body');
        $body = $params->styleset->getStyle($styleName);

        // Copy body paragraph properties to the paragraph default styles
        // But not margins and paddings:
        // That would also influence the margin and paddings in the
        // Table of Contents or in lists
        $disabled = array();
        $disabled ['margin'] = 1;
        $disabled ['margin-top'] = 1;
        $disabled ['margin-right'] = 1;
        $disabled ['margin-bottom'] = 1;
        $disabled ['margin-left'] = 1;
        $disabled ['padding'] = 1;
        $disabled ['padding-top'] = 1;
        $disabled ['padding-right'] = 1;
        $disabled ['padding-bottom'] = 1;
        $disabled ['padding-left'] = 1;
        
        $default = $params->styleset->getDefaultStyle ('paragraph');
        if ($default != NULL && $body != NULL) {
            ODTParagraphStyle::copyLayoutProperties ($body, $default, $disabled);
        }
    }

    static protected function importFootnoteStyle(ODTInternalParams $params) {
        // This function MUST be called at the end of import_styles_from_css_internal
        // ==> the 'body' paragraph style must have alread been imported!

        // Get standard text style ('body')
        $styleName = $params->styleset->getStyleName('body');
        $body = $params->styleset->getStyle($styleName);

        // Copy body paragraph properties to the footnote style
        // But not margins and paddings.
        $disabled = array();
        $disabled ['margin'] = 1;
        $disabled ['margin-top'] = 1;
        $disabled ['margin-right'] = 1;
        $disabled ['margin-bottom'] = 1;
        $disabled ['margin-left'] = 1;
        $disabled ['padding'] = 1;
        $disabled ['padding-top'] = 1;
        $disabled ['padding-right'] = 1;
        $disabled ['padding-bottom'] = 1;
        $disabled ['padding-left'] = 1;
        
        $styleName = $params->styleset->getStyleName (footnote);
        $footnote = $params->styleset->getStyle($styleName);
        if ($footnote != NULL && $body != NULL) {
            ODTParagraphStyle::copyLayoutProperties ($body, $footnote, $disabled);
        }
    }

    static protected function import_styles_from_css_internal(ODTInternalParams $params, $htmlStack, $registrations=NULL, $listAlign='right') {
        // Import page layout
        $name = $params->styleset->getStyleName('first page');
        $first_page = $params->styleset->getStyle($name);
        if ($first_page != NULL) {
            self::set_page_properties ($params, $first_page, $htmlStack, NULL);
        }

        // Import styles which only require a simple import based on element name and attributes

        // Get style of plain text paragraph for comparison
        // See importStyle()
        $htmlStack->restoreToRoot ();
        $htmlStack->open('p');
        $toMatch = $htmlStack->getCurrentElement();
        $properties = array();
        $params->import->getPropertiesForElement($properties, $toMatch, $params->units);
        $htmlStack->restoreToRoot ();

        $toImport = array_merge (self::$internalRegs, $registrations);
        foreach ($toImport as $style => $element) {
            if ($element ['compare']) {
                self::importStyle($params, $htmlStack,
                                  $style,
                                  $element ['element'],
                                  $element ['attributes'],
                                  $properties);
            } else {
                self::importStyle($params, $htmlStack,
                                  $style,
                                  $element ['element'],
                                  $element ['attributes'],
                                  NULL);
            }
        }

        // Import table styles
        self::importTableStyles($params, $htmlStack);

        // Import link styles (require extra pseudo class handling)
        self::importLinkStyles($params, $htmlStack);

        // Import list styles and list paragraph styles
        self::importUnorderedListStyles($params, $htmlStack, $listAlign);
        self::importOrderedListStyles($params, $htmlStack, $listAlign);

        self::importParagraphDefaultStyle($params);
        self::importFootnoteStyle($params);

        self::importQuotationStyles($params, $htmlStack);
    }

    static public function importODTStyles(ODTInternalParams $params, $template=NULL, $tempDir=NULL){
        if ($template == NULL || $tempDir == NULL) {
            return;
        }

        // Temp dir
        if (is_dir($tempDir)) { io_rmdir($tempDir,true); }
        io_mkdir_p($tempDir);

        // Extract template
        try {
            $ZIPextract = new \splitbrain\PHPArchive\Zip();
            $ZIPextract->open($template);
            $ZIPextract->extract($tempDir);
            $ZIPextract->close();
        } catch (\splitbrain\PHPArchive\ArchiveIOException $e) {
            throw new Exception(' Error extracting the zip archive:'.$template.' to '.$tempDir);
        }

        // Import styles from ODT template        
        $params->styleset->importFromODTFile($tempDir.'/content.xml', 'office:automatic-styles', true);
        $params->styleset->importFromODTFile($tempDir.'/styles.xml', 'office:automatic-styles', true);
        $params->styleset->importFromODTFile($tempDir.'/styles.xml', 'office:styles', true);
        $params->styleset->importFromODTFile($tempDir.'/styles.xml', 'office:master-styles', true);

        // Cleanup temp dir.
        io_rmdir($tempDir,true);
    }
}
