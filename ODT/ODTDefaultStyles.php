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
    protected $automatic =
        '<office:automatic-styles>
            <style:page-layout style:name="pm1">
                <style:page-layout-properties fo:page-width="21cm" fo:page-height="29.7cm" style:num-format="1" style:print-orientation="portrait" fo:margin-top="2cm" fo:margin-bottom="2cm" fo:margin-left="2cm" fo:margin-right="2cm" style:writing-mode="lr-tb" style:footnote-max-height="0cm">
                    <style:footnote-sep style:width="0.018cm" style:distance-before-sep="0.1cm" style:distance-after-sep="0.1cm" style:adjustment="left" style:rel-width="25%" style:color="#000000"/>
                </style:page-layout-properties>
                <style:header-style/>
                <style:footer-style/>
            </style:page-layout>
            <style:style style:name="sub" style:family="text">
                <style:text-properties style:text-position="-33% 80%"/>
            </style:style>
            <style:style style:name="sup" style:family="text">
                <style:text-properties style:text-position="33% 80%"/>
            </style:style>
            <style:style style:name="del" style:family="text">
                <style:text-properties style:text-line-through-style="solid"/>
            </style:style>
            <style:style style:name="underline" style:family="text">
              <style:text-properties style:text-underline-style="solid"
                 style:text-underline-width="auto" style:text-underline-color="font-color"/>
            </style:style>
            <style:style style:name="media" style:family="graphic" style:parent-style-name="Graphics">
                <style:graphic-properties style:run-through="foreground" style:wrap="parallel" style:number-wrapped-paragraphs="no-limit"
                   style:wrap-contour="false" style:vertical-pos="top" style:vertical-rel="baseline" style:horizontal-pos="left"
                   style:horizontal-rel="paragraph"/>
            </style:style>
            <style:style style:name="medialeft" style:family="graphic" style:parent-style-name="Graphics">
              <style:graphic-properties style:run-through="foreground" style:wrap="parallel" style:number-wrapped-paragraphs="no-limit"
                 style:wrap-contour="false" style:horizontal-pos="left" style:horizontal-rel="paragraph"/>
            </style:style>
            <style:style style:name="mediaright" style:family="graphic" style:parent-style-name="Graphics">
              <style:graphic-properties style:run-through="foreground" style:wrap="parallel" style:number-wrapped-paragraphs="no-limit"
                 style:wrap-contour="false" style:horizontal-pos="right" style:horizontal-rel="paragraph"/>
            </style:style>
            <style:style style:name="mediacenter" style:family="graphic" style:parent-style-name="Graphics">
               <style:graphic-properties style:run-through="foreground" style:wrap="none" style:horizontal-pos="center"
                  style:horizontal-rel="paragraph"/>
            </style:style>
            <style:style style:name="Table" style:family="table">
                <style:table-properties table:border-model="collapsing" fo:margin-top="0.25cm" fo:margin-bottom="0.25cm"/>
            </style:style>
            <style:style style:name="tablealigncenter" style:family="paragraph" style:parent-style-name="Table_20_Contents">
                <style:paragraph-properties fo:text-align="center"/>
            </style:style>
            <style:style style:name="tablealignright" style:family="paragraph" style:parent-style-name="Table_20_Contents">
                <style:paragraph-properties fo:text-align="end"/>
            </style:style>
            <style:style style:name="tablealignleft" style:family="paragraph" style:parent-style-name="Table_20_Contents">
                <style:paragraph-properties fo:text-align="left"/>
            </style:style>
            <style:style style:name="tableheader" style:family="table-cell">
                <style:table-cell-properties fo:padding="0.05cm" fo:border-left="0.002cm solid #000000" fo:border-right="0.002cm solid #000000" fo:border-top="0.002cm solid #000000" fo:border-bottom="0.002cm solid #000000"/>
            </style:style>
            <style:style style:name="tablecell" style:family="table-cell">
                <style:table-cell-properties fo:padding="0.05cm" fo:border-left="0.002cm solid #000000" fo:border-right="0.002cm solid #000000" fo:border-top="0.002cm solid #000000" fo:border-bottom="0.002cm solid #000000"/>
            </style:style>
            <style:style style:name="legendcenter" style:family="paragraph" style:parent-style-name="Illustration">
                <style:paragraph-properties fo:text-align="center"/>
            </style:style>
            <style:style style:name="Table_Quotation1" style:family="table">
                <style:table-properties table:border-model="collapsing" fo:margin-top="0pt" fo:margin-bottom="16.8pt"/>
            </style:style>
            <style:style style:name="Table_Quotation2" style:family="table">
                <style:table-properties table:border-model="collapsing" fo:margin-top="0pt" fo:margin-bottom="0pt"/>
            </style:style>
            <style:style style:name="Table_Quotation3" style:family="table">
                <style:table-properties table:border-model="collapsing" fo:margin-top="0pt" fo:margin-bottom="0pt"/>
            </style:style>
            <style:style style:name="Table_Quotation4" style:family="table">
                <style:table-properties table:border-model="collapsing" fo:margin-top="0pt" fo:margin-bottom="0pt"/>
            </style:style>
            <style:style style:name="Table_Quotation5" style:family="table">
                <style:table-properties table:border-model="collapsing" fo:margin-top="0pt" fo:margin-bottom="0pt"/>
            </style:style>
            <style:style style:name="Cell_Quotation1" style:family="table-cell">
                <style:table-cell-properties fo:margin="0cm" fo:padding-left="6pt" fo:padding-right="6pt" fo:padding-top="0pt" fo:padding-bottom="0pt" fo:border-left="3pt solid #cccccc" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
            </style:style>
            <style:style style:name="Cell_Quotation2" style:family="table-cell">
                <style:table-cell-properties fo:margin="0cm" fo:padding-left="6pt" fo:padding-right="6pt" fo:padding-top="0pt" fo:padding-bottom="0pt" fo:border-left="3pt solid #cccccc" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
            </style:style>
            <style:style style:name="Cell_Quotation3" style:family="table-cell">
                <style:table-cell-properties fo:margin="0cm" fo:padding-left="6pt" fo:padding-right="6pt" fo:padding-top="0pt" fo:padding-bottom="0pt" fo:border-left="3pt solid #cccccc" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
            </style:style>
            <style:style style:name="Cell_Quotation4" style:family="table-cell">
                <style:table-cell-properties fo:margin="0cm" fo:padding-left="6pt" fo:padding-right="6pt" fo:padding-top="0pt" fo:padding-bottom="0pt" fo:border-left="3pt solid #cccccc" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
            </style:style>
            <style:style style:name="Cell_Quotation5" style:family="table-cell">
                <style:table-cell-properties fo:margin="0cm" fo:padding-left="6pt" fo:padding-right="6pt" fo:padding-top="0pt" fo:padding-bottom="0pt" fo:border-left="3pt solid #cccccc" fo:border-right="none" fo:border-top="none" fo:border-bottom="none"/>
            </style:style>
        </office:automatic-styles>';

    // Font definitions. May not be present if in template mode, in which case they will be added to styles.xml
    var $fonts = array(
        "StarSymbol"=>'<style:font-face style:name="StarSymbol" svg:font-family="StarSymbol"/>', // for bullets
        "Bitstream Vera Sans Mono"=>'<style:font-face style:name="Bitstream Vera Sans Mono" svg:font-family="\'Bitstream Vera Sans Mono\'" style:font-family-generic="modern" style:font-pitch="fixed"/>', // for source code
    );

    /**
     * @param null $source
     */
    public function import($source=NULL) {
        $auto_styles_ret = parent::importFromODT($this->automatic, 'office:automatic-styles');
        $styles_ret = parent::importFromODTFile(DOKU_INC.'lib/plugins/odt/styles.xml', 'office:styles');
        $master_styles_ret = parent::importFromODTFile(DOKU_INC.'lib/plugins/odt/styles.xml', 'office:master-styles');
        if (!$auto_styles_ret || !$styles_ret || !$master_styles_ret) {
            return false;
        }
        return true;
    }

    /**
     * @param null $destination
     */
    public function export($root_element) {
        return parent::exportToODT($root_element);
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
            case 'standard':              return 'Standard';
            case 'body':                  return 'Text_20_body';
            case 'heading1':              return 'Heading_20_1';
            case 'heading2':              return 'Heading_20_2';
            case 'heading3':              return 'Heading_20_3';
            case 'heading4':              return 'Heading_20_4';
            case 'heading5':              return 'Heading_20_5';
            case 'list':                  return 'List_20_1';
            case 'list content':          return 'List_20_1_Content';
            case 'list first':            return 'List_20_1_Content_First';
            case 'list last':             return 'List_20_1_Content_Last';
            case 'numbering':             return 'Numbering_20_1';
            case 'numbering content':     return 'Numbering_20_1_Content';
            case 'numbering first':       return 'Numbering_20_1_Content_First';
            case 'numbering last':        return 'Numbering_20_1_Content_Last';
            case 'table':                 return 'Table';
            case 'table content':         return 'Table_20_Contents';
            case 'table heading':         return 'Table_20_Heading';
            case 'table header':          return 'tableheader';
            case 'table cell':            return 'tablecell';
            case 'tablealign center':     return 'tablealigncenter';
            case 'tablealign right':      return 'tablealignright';
            case 'tablealign left':       return 'tablealignleft';
            case 'preformatted':          return 'Preformatted_20_Text';
            case 'source code':           return 'Source_20_Code';
            case 'source file':           return 'Source_20_File';
            case 'horizontal line':       return 'Horizontal_20_Line';
            case 'footnote':              return 'Footnote';
            case 'footnote anchor':       return 'Footnote_20_Anchor';
            case 'footnote characters':   return 'Footnote_20_Symbol';
            case 'emphasis':              return 'Emphasis';
            case 'strong':                return 'Strong_20_Emphasis';
            case 'underline':             return 'underline';
            case 'sub':                   return 'sub';
            case 'sup':                   return 'sup';
            case 'del':                   return 'del';
            case 'media':                 return 'media';
            case 'media left':            return 'medialeft';
            case 'media right':           return 'mediaright';
            case 'media center':          return 'mediacenter';
            case 'legend center':         return 'legendcenter';
            case 'graphics':              return 'Graphics';
            case 'monospace':             return 'Source_20_Text';
            case 'table quotation1':      return 'Table_Quotation1';
            case 'table quotation2':      return 'Table_Quotation2';
            case 'table quotation3':      return 'Table_Quotation3';
            case 'table quotation4':      return 'Table_Quotation4';
            case 'table quotation5':      return 'Table_Quotation5';
            case 'cell quotation1':       return 'Cell_Quotation1';
            case 'cell quotation2':       return 'Cell_Quotation2';
            case 'cell quotation3':       return 'Cell_Quotation3';
            case 'cell quotation4':       return 'Cell_Quotation4';
            case 'cell quotation5':       return 'Cell_Quotation5';
            case 'first page':            return 'pm1';
            case 'internet link':         return 'Internet_20_link';
            case 'visited internet link': return 'Visited_20_Internet_20_Link';
            case 'local link':            return 'Local_20_link';
            case 'visited local link':    return 'Visited_20_Local_20_Link';
            case 'contents heading':      return 'Contents_20_Heading';
        }
        // Not supported basic style.
        return NULL;
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

