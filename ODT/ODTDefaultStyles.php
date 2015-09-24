<?php

require_once DOKU_INC.'lib/plugins/odt/ODT/styleset.php';
require_once DOKU_INC.'lib/plugins/odt/ODT/page.php';

/**
 * ODTDefaultStyles: class for using the basic styles from styles.xml.
 *                   This is also used if a ODT template is used, as the style names
 *                   need to match the names in styles.xml.
 *
 * The class is doing nothing for import/export because it expects
 * the file styles.xml to be there. So the file is neither read nor written.
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author LarsDW223
 */
class ODTDefaultStyles extends ODTStyleSet
{
    // Font definitions. May not be present if in template mode, in which case they will be added to styles.xml
    var $fonts = array(
        "StarSymbol"=>'<style:font-face style:name="StarSymbol" svg:font-family="StarSymbol"/>', // for bullets
        "Bitstream Vera Sans Mono"=>'<style:font-face style:name="Bitstream Vera Sans Mono" svg:font-family="\'Bitstream Vera Sans Mono\'" style:font-family-generic="modern" style:font-pitch="fixed"/>', // for source code
    );

    /**
     * @param null $source
     */
    public function import($source=NULL) {
        // Do nothing.
    }

    /**
     * @param null $destination
     */
    public function export($destination=NULL) {
        // Do nothing.
    }

    /**
     * Return style name for queired basic style $style.
     *
     * The class simply returns the corresponding style names
     * used in styles.xml.
     *
     * @param string $style
     * @return null|string
     */
    public function getStyleName($style) {
        switch ($style) {
            case 'standard':          return 'Standard';
            case 'body':              return 'Text_20_body';
            case 'heading1':          return 'Heading_20_1';
            case 'heading2':          return 'Heading_20_2';
            case 'heading3':          return 'Heading_20_3';
            case 'heading4':          return 'Heading_20_4';
            case 'heading5':          return 'Heading_20_5';
            case 'list':              return 'List_20_1';
            case 'numbering':         return 'Numbering_20_1';
            case 'table content':     return 'Table_20_Contents';
            case 'table heading':     return 'Table_20_Heading';
            case 'table header':      return 'tableheader';
            case 'table cell':        return 'tablecell';
            case 'tablealign center': return 'tablealigncenter';
            case 'tablealign right':  return 'tablealignright';
            case 'tablealign left':   return 'tablealignleft';
            case 'preformatted':      return 'Preformatted_20_Text';
            case 'source code':       return 'Source_20_Code';
            case 'source file':       return 'Source_20_File';
            case 'horizontal line':   return 'Horizontal_20_Line';
            case 'footnote':          return 'Footnote';
            case 'emphasis':          return 'Emphasis';
            case 'strong':            return 'Strong_20_Emphasis';
            case 'underline':         return 'underline';
            case 'sub':               return 'sub';
            case 'sup':               return 'sup';
            case 'del':               return 'del';
            case 'media':             return 'media';
            case 'media left':        return 'medialeft';
            case 'media right':       return 'mediaright';
            case 'media center':      return 'mediacenter';
            case 'legend center':     return 'legendcenter';
            case 'graphics':          return 'Graphics';
            case 'monospace':         return 'Source_20_Text';
            case 'quotation1':        return 'Quotation 1';
            case 'quotation2':        return 'Quotation 2';
            case 'quotation3':        return 'Quotation 3';
            case 'quotation4':        return 'Quotation 4';
            case 'quotation5':        return 'Quotation 5';
        }
        // Not supported basic style.
        return NULL;
    }

    /**
     * @return array
     */
    function getStyles (){
        $styles = array(
        "Source_20_Text"=>'
            <style:style style:name="Source_20_Text" style:display-name="Source Text" style:family="text">
                <style:text-properties style:font-name="Bitstream Vera Sans Mono" style:font-name-asian="Bitstream Vera Sans Mono" style:font-name-complex="Bitstream Vera Sans Mono"/>
            </style:style>',
        "Preformatted_20_Text"=>'
            <style:style style:name="Preformatted_20_Text" style:display-name="Preformatted Text" style:family="paragraph" style:parent-style-name="Standard" style:class="html">
                <style:paragraph-properties fo:margin-top="0cm" fo:margin-bottom="0.2cm"/>
                <style:text-properties style:font-name="Bitstream Vera Sans Mono" style:font-name-asian="Bitstream Vera Sans Mono" style:font-name-complex="Bitstream Vera Sans Mono"/>
            </style:style>',
        "Source_20_Code"=>'
            <style:style style:name="Source_20_Code" style:display-name="Source Code" style:family="paragraph" style:parent-style-name="Preformatted_20_Text">
                <style:paragraph-properties fo:padding="0.05cm" style:shadow="none" fo:border="0.002cm solid #8cacbb" fo:background-color="#f7f9fa"/>
            </style:style>',
        "Source_20_File"=>'
            <style:style style:name="Source_20_File" style:display-name="Source File" style:family="paragraph" style:parent-style-name="Preformatted_20_Text">
                <style:paragraph-properties fo:padding="0.05cm" style:shadow="none" fo:border="0.002cm solid #8cacbb" fo:background-color="#f1f4f5"/>
            </style:style>',
        "Horizontal_20_Line"=>'
            <style:style style:name="Horizontal_20_Line" style:display-name="Horizontal Line" style:family="paragraph" style:parent-style-name="Standard" style:next-style-name="Text_20_body" style:class="html">
                <style:paragraph-properties fo:margin-top="0cm" fo:margin-bottom="0.5cm" style:border-line-width-bottom="0.002cm 0.035cm 0.002cm" fo:padding="0cm" fo:border-left="none" fo:border-right="none" fo:border-top="none" fo:border-bottom="0.04cm double #808080" text:number-lines="false" text:line-number="0" style:join-border="false"/>
                <style:text-properties fo:font-size="6pt" style:font-size-asian="6pt" style:font-size-complex="6pt"/>
            </style:style>',
        "Footnote"=>'
            <style:style style:name="Footnote" style:family="paragraph" style:parent-style-name="Standard" style:class="extra">
                <style:paragraph-properties fo:margin-left="0.5cm" fo:margin-right="0cm" fo:text-indent="-0.5cm" style:auto-text-indent="false" text:number-lines="false" text:line-number="0"/>
                <style:text-properties fo:font-size="10pt" style:font-size-asian="10pt" style:font-size-complex="10pt"/>
            </style:style>',
        "Emphasis"=>'
            <style:style style:name="Emphasis" style:family="text">
                <style:text-properties fo:font-style="italic" style:font-style-asian="italic" style:font-style-complex="italic"/>
            </style:style>',
        "Strong_20_Emphasis"=>'
            <style:style style:name="Strong_20_Emphasis" style:display-name="Strong Emphasis" style:family="text">
                <style:text-properties fo:font-weight="bold" style:font-weight-asian="bold" style:font-weight-complex="bold"/>
            </style:style>',);
        return $styles;
    }

    /**
     * Return autostyles initial content.
     * Uses page parameters.
     *
     * @author LarsDW223
     *
     * @param pageFormat $page
     * @return array
     */
    function getAutoStyles (pageFormat $page){
        $autostyles = array(
        "pm1"=>'
            <style:page-layout style:name="pm1">
                <style:page-layout-properties fo:page-width="'.$page->getWidth().'cm" fo:page-height="'.$page->getHeight().'cm" style:num-format="1" style:print-orientation="portrait" fo:margin-top="'.$page->getMarginTop().'cm" fo:margin-bottom="'.$page->getMarginBottom().'cm" fo:margin-left="'.$page->getMarginLeft().'cm" fo:margin-right="'.$page->getMarginRight().'cm" style:writing-mode="lr-tb" style:footnote-max-height="0cm">
                    <style:footnote-sep style:width="0.018cm" style:distance-before-sep="0.1cm" style:distance-after-sep="0.1cm" style:adjustment="left" style:rel-width="25%" style:color="#000000"/>
                </style:page-layout-properties>
                <style:header-style/>
                <style:footer-style/>
            </style:page-layout>',
        "sub"=>'
            <style:style style:name="sub" style:family="text">
                <style:text-properties style:text-position="-33% 80%"/>
            </style:style>',
        "sup"=>'
            <style:style style:name="sup" style:family="text">
                <style:text-properties style:text-position="33% 80%"/>
            </style:style>',
        "del"=>'
            <style:style style:name="del" style:family="text">
                <style:text-properties style:text-line-through-style="solid"/>
            </style:style>',
        "underline"=>'
            <style:style style:name="underline" style:family="text">
              <style:text-properties style:text-underline-style="solid"
                 style:text-underline-width="auto" style:text-underline-color="font-color"/>
            </style:style>',
        "media"=>'
            <style:style style:name="media" style:family="graphic" style:parent-style-name="'.$this->getStyleName('graphics').'">
                <style:graphic-properties style:run-through="foreground" style:wrap="parallel" style:number-wrapped-paragraphs="no-limit"
                   style:wrap-contour="false" style:vertical-pos="top" style:vertical-rel="baseline" style:horizontal-pos="left"
                   style:horizontal-rel="paragraph"/>
            </style:style>',
        "medialeft"=>'
            <style:style style:name="medialeft" style:family="graphic" style:parent-style-name="'.$this->getStyleName('graphics').'">
              <style:graphic-properties style:run-through="foreground" style:wrap="parallel" style:number-wrapped-paragraphs="no-limit"
                 style:wrap-contour="false" style:horizontal-pos="left" style:horizontal-rel="paragraph"/>
            </style:style>',
        "mediaright"=>'
            <style:style style:name="mediaright" style:family="graphic" style:parent-style-name="'.$this->getStyleName('graphics').'">
              <style:graphic-properties style:run-through="foreground" style:wrap="parallel" style:number-wrapped-paragraphs="no-limit"
                 style:wrap-contour="false" style:horizontal-pos="right" style:horizontal-rel="paragraph"/>
            </style:style>',
        "mediacenter"=>'
            <style:style style:name="mediacenter" style:family="graphic" style:parent-style-name="'.$this->getStyleName('graphics').'">
               <style:graphic-properties style:run-through="foreground" style:wrap="none" style:horizontal-pos="center"
                  style:horizontal-rel="paragraph"/>
            </style:style>',
        "tablealigncenter"=>'
            <style:style style:name="tablealigncenter" style:family="paragraph" style:parent-style-name="'.$this->getStyleName('table content').'">
                <style:paragraph-properties fo:text-align="center"/>
            </style:style>',
        "tablealignright"=>'
            <style:style style:name="tablealignright" style:family="paragraph" style:parent-style-name="'.$this->getStyleName('table content').'">
                <style:paragraph-properties fo:text-align="end"/>
            </style:style>',
        "tablealignleft"=>'
            <style:style style:name="tablealignleft" style:family="paragraph" style:parent-style-name="'.$this->getStyleName('table content').'">
                <style:paragraph-properties fo:text-align="left"/>
            </style:style>',
        "tableheader"=>'
            <style:style style:name="tableheader" style:family="table-cell">
                <style:table-cell-properties fo:padding="0.05cm" fo:border-left="0.002cm solid #000000" fo:border-right="0.002cm solid #000000" fo:border-top="0.002cm solid #000000" fo:border-bottom="0.002cm solid #000000"/>
            </style:style>',
        "tablecell"=>'
            <style:style style:name="tablecell" style:family="table-cell">
                <style:table-cell-properties fo:padding="0.05cm" fo:border-left="0.002cm solid #000000" fo:border-right="0.002cm solid #000000" fo:border-top="0.002cm solid #000000" fo:border-bottom="0.002cm solid #000000"/>
            </style:style>',
        "legendcenter"=>'
            <style:style style:name="legendcenter" style:family="paragraph" style:parent-style-name="Illustration">
                <style:paragraph-properties fo:text-align="center"/>
            </style:style>', );
        return $autostyles;
    }

    /**
     * @param string $filename
     * @return string
     */
    function getMissingStyles($filename) {
        $styles = $this->getStyles();
        $value = '';
        //$existing_styles = io_readFile($this->temp_dir.'/styles.xml');
        $existing_styles = io_readFile($filename);
        foreach ($styles as $stylename=>$stylexml) {
            if (strpos($existing_styles, 'style:name="'.$stylename.'"') === FALSE) {
                $value .= $stylexml;
            }
        }
        // Loop on bullet/numerotation styles
        if (strpos($existing_styles, 'style:name="List_20_1"') === FALSE) {
            $value .= '<text:list-style style:name="List_20_1" style:display-name="List 1">';
            for ($i=1;$i<=10;$i++) {
                $value .= '<text:list-level-style-bullet text:level="'.$i.'" text:style-name="Numbering_20_Symbols" text:bullet-char="•">
                               <style:list-level-properties text:space-before="'.(0.4*($i-1)).'cm" text:min-label-width="0.4cm"/>
                               <style:text-properties style:font-name="StarSymbol"/>
                           </text:list-level-style-bullet>';
            }
            $value .= '</text:list-style>';
        }
        if (strpos($existing_styles, 'style:name="Numbering_20_1"') === FALSE) {
            $value .= '<text:list-style style:name="Numbering_20_1" style:display-name="Numbering 1">';
            for ($i=1;$i<=10;$i++) {
                $value .= '<text:list-level-style-number text:level="'.$i.'" text:style-name="Numbering_20_Symbols" style:num-suffix="." style:num-format="1">
                               <style:list-level-properties text:space-before="'.(0.5*($i-1)).'cm" text:min-label-width="0.5cm"/>
                           </text:list-level-style-number>';
            }
            $value .= '</text:list-style>';
        }
        return $value;
    }

    /**
     * @param string $filename
     * @return string
     */
    function getMissingFonts($filename) {
        $value = '';
        $existing_styles = io_readFile($filename);
        foreach ($this->fonts as $name=>$xml) {
            if (strpos($existing_styles, 'style:name="'.$name.'"') === FALSE) {
                $value .= $xml;
            }
        }
        return $value;
    }
}

