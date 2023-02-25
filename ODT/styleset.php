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
    protected $master_styles = array();
    protected $master_styles_by_name = array();
    
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
    public function importFromODTFile($sourceFile, $root_element, $overwrite=false) {
        if (empty($sourceFile) || empty($root_element)) {
            return false;
        }
        
        // Get file contents
        $styles_xml_content = file_get_contents ($sourceFile);
        if (empty($styles_xml_content)) {
            return false;
        }

        return $this->importFromODT($styles_xml_content, $root_element, $overwrite);
    }

    public function importFromODT($styles_xml_content, $root_element, $overwrite=false) {
        if (empty($styles_xml_content) || empty($root_element)) {
            return false;
        }
        
        // Only import known style elements
        switch ($root_element) {
            case 'office:styles':
            case 'office:automatic-styles':
            case 'office:master-styles':
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
            $pos += $end;
            
            // Create new ODTStyle
            $object = ODTStyle::importODTStyle($xml_code);
            if ($object != NULL ) {
                // Success, add it
                switch ($root_element) {
                    case 'office:styles':
                        $this->addStyle($object, $overwrite);
                        break;
                    case 'office:automatic-styles':
                        $this->addAutomaticStyle($object, $overwrite);
                        break;
                    case 'office:master-styles':
                        $this->addMasterStyle($object, $overwrite);
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
            case 'office:master-styles':
                $export = &$this->master_styles;
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
    public function addStyle(ODTStyle $new, $overwrite=false) {
        return $this->addStyleInternal
            ($this->styles, $this->styles_by_name, $new, $overwrite);
    }

    /**
     * @param null $source
     */
    public function addAutomaticStyle(ODTStyle $new, $overwrite=false) {
        return $this->addStyleInternal
            ($this->auto_styles, $this->auto_styles_by_name, $new, $overwrite);
    }

    /**
     * @param null $source
     */
    public function addMasterStyle(ODTStyle $new, $overwrite=false) {
        return $this->addStyleInternal
            ($this->master_styles, $this->master_styles_by_name, $new, $overwrite);
    }

    /**
     * @param null $source
     */
    public function addStyleInternal(&$dest, &$dest_by_name, ODTStyle $new, $overwrite=false) {
        if ($new->isDefault()) {
            // The key for a default style is the family.
            $family = $new->getFamily();
            
            // Search for default style with same family.
            for ($index = 0 ; $index < count($dest) ; $index++) {
                if ($dest [$index]->isDefault() &&
                    $dest [$index]->getFamily() == $family) {
                    // Only overwrite it if allowed.
                    if ($overwrite) {
                        $dest [$index] = $new;
                    }
                    return false;
                }
            }
            
            // Default style for that family does not exist yet, add it.
            $dest [] = $new;
        } else {
            // The key for a normal style is the name.
            $name = $new->getProperty('style-name');

            if ($dest_by_name [$name] == NULL) {
                $dest [] = $new;
                if (!empty($name)) {
                    $dest_by_name [$name] = $new;
                }
                return true;
            } elseif ($overwrite) {
                for ($index = 0 ; $index < count($dest) ; $index++) {
                    if ($dest [$index] == $dest_by_name [$name]) {
                        $dest [$index] = $new;
                        break;
                    }
                }
                $dest_by_name [$name] = $new;
                return true;
            }
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
        if ($this->master_styles_by_name [$name] != NULL) {
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
        if ($this->master_styles_by_name [$name] != NULL) {
            return $this->master_styles_by_name [$name];
        }
        return NULL;
    }

    /**
     * The function returns the style at the given index
     * 
     * @param $element Element of the style e.g. 'office:styles'
     * @return ODTStyle or NULL
     */
    public function getStyleAtIndex($element, $index) {
        switch ($element) {
            case 'office:styles':
                return $this->styles [$index];
            case 'office:automatic-styles':
                return $this->auto_styles [$index];
            case 'office:master-styles':
                return $this->master_styles [$index];
        }
        return NULL;
    }

    public function getStyleCount($element) {
        switch ($element) {
            case 'office:styles':
                return count($this->styles);
            case 'office:automatic-styles':
                return count($this->auto_styles);
            case 'office:master-styles':
                return count($this->master_styles);
        }
        return -1;
    }

    /**
     * @param null $source
     */
    public function getDefaultStyle($family) {
        // Search for default style with same family.
        for ($index = 0 ; $index < count($this->styles) ; $index++) {
            if ($this->styles [$index]->isDefault() &&
                $this->styles [$index]->getFamily() == $family) {
                return $this->styles [$index];
            }
        }
        return NULL;
    }

    /**
     * Get styles array.
     */
    public function getStyles() {
        return $this->styles;
    }

    /**
     * Get automatci/common styles array.
     */
    public function getAutomaticStyles() {
        return $this->auto_styles;
    }

    /**
     * Get master styles array.
     */
    public function getMasterStyles() {
        return $this->master_styles;
    }
}
