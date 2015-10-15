<?php

require_once DOKU_INC.'lib/plugins/odt/ODT/XMLUtil.php';
require_once DOKU_INC.'lib/plugins/odt/ODT/styles/ODTStyle.php';

/**
 * ODTStyleSet: Abstract class defining the interface a style set/template
 * needs to implement towards the ODT renderer.
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author LarsDW223
 */
abstract class ODTStyleSet
{
    protected $styles = array();
    protected $styles_by_name = array();
    protected $auto_styles = array();
    protected $auto_styles_by_name = array();
    
    /**
     * Read/import style source.
     *
     * @param $source (optional)
     */
    abstract public function import($source);

    /**
     * Export $element styles (e.g. 'office:styles' or 'office:automatic-styles')
     *
     * @param  $element The style element to export
     * @return string   The ODT XML encoded $element style
     */
    abstract public function export($element);

    /**
     * The function needs to be able to return a style name
     * for the following basic styles used by the renderer:
     * - standard
     * - body
     * - heading1
     * - heading2
     * - heading3
     * - heading4
     * - heading5
     * - heading6
     * - list
     * - numbering
     * - table content
     * - table heading
     * - preformatted
     * - source code
     * - source file
     * - horizontal line
     * - footnote
     * - emphasis
     * - strong
     * - graphics
     * - monospace
     * - quotation1
     * - quotation2
     * - quotation3
     * - quotation4
     * - quotation5
     *
     * @param $style
     * @return mixed
     */
    abstract public function getStyleName($style);

    /**
     * @param null $source
     */
    public function importFromODTFile($sourceFile, $root_element) {
        if (empty($sourceFile) || empty($root_element)) {
            return false;
        }
        
        // Get file contents
        $styles_xml_content = file_get_contents ($sourceFile);
        if (empty($styles_xml_content)) {
            return false;
        }

        return $this->importFromODT($styles_xml_content, $root_element);
    }

    public function importFromODT($styles_xml_content, $root_element) {
        if (empty($styles_xml_content) || empty($root_element)) {
            return false;
        }
        
        // Only import known style elements
        switch ($root_element) {
            case 'office:styles':
            case 'office:automatic-styles':
                $style_elements = XMLUtil::getElementContent($root_element, $styles_xml_content, $end);
                break;
                
            default:
                return false;
        }
        
        $pos = 0;
        $max = strlen($style_elements);
        while ($pos < $max) {
            $xml_code = XMLUtil::getNextElement($element, substr($style_elements, $pos), $end);
            if ($xml_code == NULL) {
                break;
            }
            $pos += $end+1;
            
            // Create new ODTStyle
            $object = ODTStyle::importODTStyle($xml_code);
            if ($object != NULL ) {
                // Success, add it
                switch ($root_element) {
                    case 'office:styles':
                        $this->addStyle($object);
                        break;
                    case 'office:automatic-styles':
                        $this->addAutomaticStyle($object);
                        break;
                }
            }
        }
        return true;
    }

    /**
     * @param null $destination
     */
    public function exportToODT($root_element) {
        $export = NULL;
        switch ($root_element) {
            case 'office:styles':
                $export = &$this->styles;
                break;
            case 'office:automatic-styles':
                $export = &$this->auto_styles;
                break;
        }
        if ($export != NULL) {
            $office_styles = "<".$root_element.">\n";
            foreach ($export as $style) {
                $office_styles .= $style->toString();
            }
            $office_styles .= "</".$root_element.">\n";
            return $office_styles;
        }
        return NULL;
    }

    /**
     * @param null $source
     */
    public function addStyle(ODTStyle $new) {
        $name = $new->getProperty('style-name');
        if ($this->styles_by_name [$name] == NULL) {
            $this->styles [] = $new;
            if (!empty($name)) {
                $this->styles_by_name [$name] = $new;
            }
            return true;
        }
        
        // Do not overwrite an already existing style.
        return false;
    }

    /**
     * @param null $source
     */
    public function addAutomaticStyle(ODTStyle $new) {
        $name = $new->getProperty('style-name');
        if ($this->auto_styles_by_name [$name] == NULL) {
            $this->auto_styles [] = $new;
            if (!empty($name)) {
                $this->auto_styles_by_name [$name] = $new;
            }
            return true;
        }
        
        // Do not overwrite an already existing style.
        return false;
    }

    /**
     * The function style checks if a style with the given $name already exists.
     * 
     * @param $name Name of the style to check
     * @return boolean
     */
    public function styleExists ($name) {
        if ($this->auto_styles_by_name [$name] != NULL) {
            return true;
        }
        if ($this->styles_by_name [$name] != NULL) {
            return true;
        }
        return false;
    }

    /**
     * The function returns the style with the given name
     * 
     * @param $name Name of the style
     * @return ODTStyle or NULL
     */
    public function getStyle ($name) {
        if ($this->auto_styles_by_name [$name] != NULL) {
            return $this->auto_styles_by_name [$name];
        }
        if ($this->styles_by_name [$name] != NULL) {
            return $this->styles_by_name [$name];
        }
        return NULL;
    }
}
