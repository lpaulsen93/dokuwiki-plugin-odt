<?php

require_once DOKU_PLUGIN . 'odt/ODT/ODTDocument.php';

/**
 * ODTIndex:
 * Class containing static code for handling indexes.
 * Actually these are the table of contents and the chapter index.
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 */
class ODTIndex
{
    /**
     * This function does not really render/insert an index but inserts a placeholder.
     * See also replaceIndexesPlaceholders().
     *
     * @return string
     */
    function insertIndex(ODTInternalParams $params, array &$indexesData, $type='toc', array $settings=NULL) {        
        // Insert placeholder
        $index_count = count ($indexesData);

        $params->document->paragraphClose();
        $params->content .= '<index-placeholder no="'.($index_count+1).'"/>';

        // Prepare index data
        $new = array();
        foreach ($settings as $key => $value) {
            $new [$key] = $value;
        }
        $new ['type'] = $type;

        if ($type == 'chapter') {
            $new ['start_ref'] = $params->document->getPreviousToCItem(1);
        } else {
            $new ['start_ref'] = NULL;
        }

        // Add new index data
        $indexesData [] = $new;

        return '';
    }
    
    /**
     * This function builds the actual TOC and replaces the placeholder with it.
     * It is called in document_end() after all headings have been added to the TOC, see toc_additem().
     * The page numbers are just a counter. Update the TOC e.g. in LibreOffice to get the real page numbers!
     *
     * The TOC is inserted by the syntax tag '{{odt>toc:setting=value;}};'.
     * The following settings are supported:
     * - Title e.g. '{{odt>toc:title=Example;}}'.
     *   Default is 'Table of Contents' (for english, see language files for other languages default value).
     * - Leader sign, e.g. '{{odt>toc:leader-sign=.;}}'.
     *   Default is '.'.
     * - Indents (in cm), e.g. '{{odt>toc:indents=indents=0,0.5,1,1.5,2,2.5,3;}};'.
     *   Default is 0.5 cm indent more per level.
     * - Maximum outline/TOC level, e.g. '{{odt>toc:maxtoclevel=5;}}'.
     *   Default is taken from DokuWiki config setting 'maxtoclevel'.
     * - Insert pagebreak after TOC, e.g. '{{odt>toc:pagebreak=1;}}'.
     *   Default is '1', means insert pagebreak after TOC.
     * - Set style per outline/TOC level, e.g. '{{odt>toc:styleL2="color:red;font-weight:900;";}}'.
     *   Default is 'color:black'.
     *
     * It is allowed to use defaults for all settings by using '{{odt>toc}}'.
     * Multiple settings can be combined, e.g. '{{odt>toc:leader-sign=.;indents=0,0.5,1,1.5,2,2.5,3;}}'.
     */
    public static function replaceIndexesPlaceholders(ODTInternalParams $params, array $indexesData, array $toc) {
        $index_count = count($indexesData);
        for ($index_no = 0 ; $index_no < $index_count ; $index_no++) {
            $data = $indexesData [$index_no];

            // At the moment it does not make sense to disable links for the TOC
            // because LibreOffice will insert links on updating the TOC.
            $data ['create_links'] = true;
            $indexContent = self::buildIndex($params->document, $toc, $data, $index_no+1);

            // Replace placeholder with TOC content.
            $params->content = str_replace ('<index-placeholder no="'.($index_no+1).'"/>', $indexContent, $params->content);
        }
    }

    /**
     * This function builds a TOC or chapter index.
     * The page numbers are just a counter. Update the TOC e.g. in LibreOffice to get the real page numbers!
     *
     * The layout settings are taken from the configuration and $settings.
     * $settings can include the following options syntax:
     * - Title e.g. 'title=Example;'.
     *   Default is 'Table of Contents' (for english, see language files for other languages default value).
     * - Leader sign, e.g. 'leader-sign=.;'.
     *   Default is '.'.
     * - Indents (in cm), e.g. 'indents=indents=0,0.5,1,1.5,2,2.5,3;'.
     *   Default is 0.5 cm indent more per level.
     * - Maximum outline/TOC level, e.g. 'maxtoclevel=5;'.
     *   Default is taken from DokuWiki config setting 'maxtoclevel'.
     * - Insert pagebreak after TOC, e.g. 'pagebreak=1;'.
     *   Default is '1', means insert pagebreak after TOC.
     * - Set style per outline/TOC level, e.g. 'styleL2="color:red;font-weight:900;";'.
     *   Default is 'color:black'.
     *
     * It is allowed to use defaults for all settings by omitting $settings.
     * Multiple settings can be combined, e.g. 'leader-sign=.;indents=0,0.5,1,1.5,2,2.5,3;'.
     */
    protected static function buildIndex(ODTDocument $doc, array $toc, array $settings, $indexNo) {
        $stylesL = array();
        $stylesLNames = array();

        // Get index type
        $type = $settings ['type'];

        // It seems to be not supported in ODT to have a different start
        // outline level than 1.
        $max_outline_level = 10;
        if (!empty($settings ['maxlevel'])) {
            $max_outline_level = $settings ['maxlevel'];
        }

        // Determine title, default for table of contents is 'Table of Contents'.
        // Default for chapter index is empty.
        // Syntax for 'Test' as title would be "title=test;".
        $title = '';
        if (!empty($settings ['title'])) {
            $title = $settings ['title'];
        }

        // Determine leader-sign, default is '.'.
        // Syntax for '.' as leader-sign would be "leader_sign=.;".
        $leader_sign = '.';
        if (!empty($settings ['leader_sign'])) {
            $leader_sign = $settings ['leader_sign'];
        }

        // Determine indents, default is '0.5' (cm) per level.
        // Syntax for a indent of '0.5' for 5 levels would be "indents=0,0.5,1,1.5,2;".
        // The values are absolute for each level, not relative to the higher level.
        $indents = '0, 0.5, 1, 1.5, 2, 2.5, 3, 3.5, 4, 4.5';
        if (!empty($settings ['indents'])) {
            $indents = $settings ['indents'];
        }

        // Determine pagebreak, default is on '1'.
        // Syntax for pagebreak off would be "pagebreak=0;".
        $pagebreak = true;
        if (!empty($settings ['pagebreak'])) {
            $temp = $settings ['pagebreak'];
            $pagebreak = false;            
            if ( $temp == '1' ) {
                $pagebreak = true;
            } else if ( strcasecmp($temp, 'true') == 0 ) {
                $pagebreak = true;
            }
        }

        // Determine text style for the index heading.
        $styleH = '';
        if (!empty($settings ['style_heading'])) {
            $styleH = $settings ['style_heading'];
        }

        // Determine text styles per level.
        // Syntax for a style level 1 is "styleL1="color:black;"".
        // The default style is just 'color:black;'.
        for ( $count = 0 ; $count < $max_outline_level ; $count++ ) {
            $stylesL [$count + 1] = 'color:black;';
            if (!empty($settings ['styleL'.($count + 1)])) {
                $stylesL [$count + 1] = $settings ['styleL'.($count + 1)];
            }
        }

        // Create Heading style if not empty.
        // Default index heading style is taken from styles.xml
        $title_style = $doc->getStyleName('contents heading');
        if (!empty($styleH)) {
            $properties = array();
            $doc->getCSSStylePropertiesForODT ($properties, $styleH);
            $properties ['style-parent'] = 'Heading';
            $properties ['style-class'] = 'index';
            $this->style_count++;
            $properties ['style-name'] = 'Contents_20_Heading_'.$this->style_count;
            $properties ['style-display-name'] = 'Contents Heading '.$this->style_count;
            $style_obj = ODTParagraphStyle::createParagraphStyle($properties);
            $doc->addStyle($style_obj);
            $title_style = $style_obj->getProperty('style-name');
        }
        
        // Create paragraph styles
        $p_styles = array();
        $p_styles_auto = array();
        $indent = 0;
        for ( $count = 0 ; $count < $max_outline_level ; $count++ )
        {
            $indent = $indents [$count];
            $properties = array();
            $doc->getCSSStylePropertiesForODT ($properties, $stylesL [$count+1]);
            $properties ['style-parent'] = 'Index';
            $properties ['style-class'] = 'index';
            $properties ['style-position'] = 17 - $indent .'cm';
            $properties ['style-type'] = 'right';
            $properties ['style-leader-style'] = 'dotted';
            $properties ['style-leader-text'] = $leader_sign;
            $properties ['margin-left'] = $indent.'cm';
            $properties ['margin-right'] = '0cm';
            $properties ['text-indent'] = '0cm';
            $properties ['style-name'] = 'ToC '.$indexNo.'- Level '.($count+1);
            $properties ['style-display-name'] = 'ToC '.$indexNo.', Level '.($count+1);
            $style_obj = ODTParagraphStyle::createParagraphStyle($properties);

            // Add paragraph style to common styles.
            // (It MUST be added to styles NOT to automatic styles. Otherwise LibreOffice will
            //  overwrite/change the style on updating the TOC!!!)
            $doc->addStyle($style_obj);
            $p_styles [$count+1] = $style_obj->getProperty('style-name');

            // Create a copy of that but with parent set to the copied style
            // and no class
            $properties ['style-parent'] = $style_obj->getProperty('style-name');
            $properties ['style-class'] = NULL;
            $properties ['style-name'] = 'ToC Auto '.$indexNo.'- Level '.($count+1);
            $properties ['style-display-name'] = NULL;
            $style_obj_auto = ODTParagraphStyle::createParagraphStyle($properties);
            
            // Add paragraph style to automatic styles.
            // (It MUST be added to automatic styles NOT to styles. Otherwise LibreOffice will
            //  overwrite/change the style on updating the TOC!!!)
            $doc->addAutomaticStyle($style_obj_auto);
            $p_styles_auto [$count+1] = $style_obj_auto->getProperty('style-name');
        }

        // Create text style for TOC text.
        // (this MUST be a text style (not paragraph!) and MUST be placed in styles (not automatic styles) to work!)
        for ( $count = 0 ; $count < $max_outline_level ; $count++ ) {
            $properties = array();
            $doc->getCSSStylePropertiesForODT ($properties, $stylesL [$count+1]);
            $properties ['style-name'] = 'ToC '.$indexNo.'- Text Level '.($count+1);
            $properties ['style-display-name'] = 'ToC '.$indexNo.', Level '.($count+1);
            $style_obj = ODTTextStyle::createTextStyle($properties);
            $stylesLNames [$count+1] = $style_obj->getProperty('style-name');
            $doc->addStyle($style_obj);
        }

        // Generate ODT toc tag and content
        switch ($type) {
            case 'toc':
                $tag = 'table-of-content';
                $name = 'Table of Contents';
                $index_name = 'Table of Contents';
                $source_attrs = 'text:outline-level="'.$max_outline_level.'" text:use-index-marks="false"';
            break;
            case 'chapter':
                $tag = 'table-of-content';
                $name = 'Table of Contents';
                $index_name = 'Table of Contents';
                $source_attrs = 'text:outline-level="'.$max_outline_level.'" text:use-index-marks="false" text:index-scope="chapter"';
            break;
        }

        $content  = '<text:'.$tag.' text:style-name="Standard" text:protected="true" text:name="'.$name.'">';
        $content .= '<text:'.$tag.'-source '.$source_attrs.'>';
        if (!empty($title)) {
            $content .= '<text:index-title-template text:style-name="'.$title_style.'">'.$title.'</text:index-title-template>';
        } else {
            $content .= '<text:index-title-template text:style-name="'.$title_style.'"/>';
        }

        // Create TOC templates per outline level.
        // The styles listed here need to be the same as later used for the headers.
        // Otherwise the style of the TOC entries/headers will change after an update.
        for ( $count = 0 ; $count < $max_outline_level ; $count++ )
        {
            $level = $count + 1;
            $content .= '<text:'.$tag.'-entry-template text:outline-level="'.$level.'" text:style-name="'.$p_styles [$level].'">';
            $content .= '<text:index-entry-link-start text:style-name="'.$stylesLNames [$level].'"/>';
            $content .= '<text:index-entry-chapter/>';
            $content .= '<text:index-entry-text/>';
            $content .= '<text:index-entry-tab-stop style:type="right" style:leader-char="'.$leader_sign.'"/>';
            $content .= '<text:index-entry-page-number/>';
            $content .= '<text:index-entry-link-end/>';
            $content .= '</text:'.$tag.'-entry-template>';
        }

        $content .= '</text:'.$tag.'-source>';
        $content .= '<text:index-body>';
        if (!empty($title)) {
            $content .= '<text:index-title text:style-name="Standard" text:name="'.$index_name.'_Head">';
            $content .= '<text:p text:style-name="'.$title_style.'">'.$title.'</text:p>';
            $content .= '</text:index-title>';
        }

        // Add headers to TOC.
        $page = 0;
        $links = $settings ['create_links'];
        if ($type == 'toc') {
            $content .= self::getTOCBody ($toc, $p_styles_auto, $stylesLNames, $max_outline_level, $links);
        } else {
            $startRef = $settings ['start_ref'];
            $content .= self::getChapterIndexBody ($toc, $p_styles_auto, $stylesLNames, $max_outline_level, $links, $startRef);
        }

        $content .= '</text:index-body>';
        $content .= '</text:'.$tag.'>';

        // Add a pagebreak if required.
        if ( $pagebreak ) {
            $style_name = $doc->createPagebreakStyle(NULL, false);
            $content .= '<text:p text:style-name="'.$style_name.'"/>';
        }

        // Return index content.
        return $content;
    }

    /**
     * This function creates the entries for a table of contents.
     * All heading are included up to level $max_outline_level.
     *
     * @param array   $p_styles            Array of style names for the paragraphs.
     * @param array   $stylesLNames        Array of style names for the links.
     * @param array   $max_outline_level   Depth of the table of contents.
     * @param boolean $links               Shall links be created.
     * @return string TOC body entries
     */
    protected function getTOCBody(array $toc, $p_styles, $stylesLNames, $max_outline_level, $links) {
        $page = 0;
        $content = '';
        foreach ($toc as $item) {
            $params = explode (',', $item);

            // Only add the heading to the TOC if its <= $max_outline_level
            if ( $params [3] <= $max_outline_level ) {
                $level = $params [3];
                $content .= '<text:p text:style-name="'.$p_styles [$level].'">';
                if ( $links == true ) {
                    $content .= '<text:a xlink:type="simple" xlink:href="#'.$params [0].'" text:style-name="'.$stylesLNames [$level].'" text:visited-style-name="'.$stylesLNames [$level].'">';
                }
                $content .= $params [2];
                $content .= '<text:tab/>';
                $page++;
                $content .= $page;
                if ( $links == true ) {
                    $content .= '</text:a>';
                }
                $content .= '</text:p>';
            }
        }
        return $content;
    }

    /**
     * This function creates the entries for a chapter index.
     * All headings of the chapter are included uo to level $max_outline_level.
     *
     * @param array   $p_styles            Array of style names for the paragraphs.
     * @param array   $stylesLNames        Array of style names for the links.
     * @param array   $max_outline_level   Depth of the table of contents.
     * @param boolean $links               Shall links be created.
     * @param string  $startRef            Reference-ID of chapter main heading.
     * @return string TOC body entries
     */
    protected function getChapterIndexBody(array $toc, $p_styles, $stylesLNames, $max_outline_level, $links, $startRef) {
        $start_outline = 1;
        $in_chapter = false;
        $first = true;
        $content = '';
        foreach ($toc as $item) {
            $params = explode (',', $item);

            if ($in_chapter == true || $params [0] == $startRef ) {
                $in_chapter = true;

                // Is this the start of a new chapter?
                if ( $first == false && $params [3] <= $start_outline ) {
                    break;
                }
                
                // Only add the heading to the TOC if its <= $max_outline_level
                if ( $params [3] <= $max_outline_level ) {
                    $level = $params [3];
                    $content .= '<text:p text:style-name="'.$p_styles [$level].'">';
                    if ( $links == true ) {
                        $content .= '<text:a xlink:type="simple" xlink:href="#'.$params [0].'" text:style-name="'.$stylesLNames [$level].'" text:visited-style-name="'.$stylesLNames [$level].'">';
                    }
                    $content .= $params [2];
                    $content .= '<text:tab/>';
                    $page++;
                    $content .= $page;
                    if ( $links == true ) {
                        $content .= '</text:a>';
                    }
                    $content .= '</text:p>';
                }
                $first = false;
            }
        }
        return $content;
    }
}
