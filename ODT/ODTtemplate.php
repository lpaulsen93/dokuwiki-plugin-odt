<?php

require_once DOKU_INC.'lib/plugins/odt/ODT/styleset.php';

/**
 * ODTTemplate: class for using the basic styles from styles.xml.
 * 
 * The class is doing nothing for import/export because it expects
 * the file styles.xml to be there. So the file is neither read nor written.
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author LarsDW223
 */
class ODTTemplate extends ODTStyleSet
{
    // Font definitions. May not be present if in template mode, in which case they will be added to styles.xml
    var $fonts = array(
        "StarSymbol"=>'<style:font-face style:name="StarSymbol" svg:font-family="StarSymbol"/>', // for bullets
        "Bitstream Vera Sans Mono"=>'<style:font-face style:name="Bitstream Vera Sans Mono" svg:font-family="\'Bitstream Vera Sans Mono\'" style:font-family-generic="modern" style:font-pitch="fixed"/>', // for source code
    );

    public function import($source=NULL) {
        // Do nothing. 
    }
    public function export($destination=NULL) {
        // Do nothing. 
    }

    /**
     * Return style name for queired basic style $style.
     *
     * The class simply returns the corresponding style names
     * used in styles.xml.
     */
    public function getStyleName($style) {
        switch ($style) {
            case 'standard':        return 'Standard';
            case 'body':            return 'Text_20_body';
            case 'heading1':        return 'Heading_20_1';
            case 'heading2':        return 'Heading_20_2';
            case 'heading3':        return 'Heading_20_3';
            case 'heading4':        return 'Heading_20_4';
            case 'heading5':        return 'Heading_20_5';
            case 'list':            return 'List_20_1';
            case 'numbering':       return 'Numbering_20_1';
            case 'table content':   return 'Table_20_Contents';
            case 'table heading':   return 'Table_20_Heading';
            case 'preformatted':    return 'Preformatted_20_Text';
            case 'source code':     return 'Source_20_Code';
            case 'source file':     return 'Source_20_File';
            case 'horizontal line': return 'Horizontal_20_Line';
            case 'footnote':        return 'Footnote';
            case 'emphasis':        return 'Emphasis';
            case 'strong':          return 'Strong_20_Emphasis';
            case 'graphics':        return 'Graphics';
            case 'monospace':       return 'Source_20_Text';
            case 'quotation1':      return 'Quotation 1';
            case 'quotation2':      return 'Quotation 2';
            case 'quotation3':      return 'Quotation 3';
            case 'quotation4':      return 'Quotation 4';
            case 'quotation5':      return 'Quotation 5';
        }
        // Not supported basic style.
        return NULL;
    }

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

