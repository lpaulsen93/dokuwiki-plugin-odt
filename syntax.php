<?php
/**
 * ODT Plugin: Exports to ODT
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 * @author     Aurelien Bompard <aurelien@bompard.org>
 */
// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

/**
 * Class syntax_plugin_odt
 */
class syntax_plugin_odt extends DokuWiki_Syntax_Plugin {

    /**
     * What kind of syntax are we?
     */
    public function getType() {
        return 'substition';
    }

    /**
     * What about paragraphs?
     */
    public function getPType() {
        return 'normal';
    }

    /**
     * Where to sort in?
     */
    public function getSort() {
        return 319; // Before image detection, which uses {{...}} and is 320
    }

    /**
     * Connect pattern to lexer
     *
     * @param string $mode
     */
    public function connectTo($mode) {
        $this->Lexer->addSpecialPattern('~~ODT~~', $mode, 'plugin_odt');
        $this->Lexer->addSpecialPattern('{{odt>.+?}}', $mode, 'plugin_odt');
    }

    /**
     * Handler to prepare matched data for the rendering process
     *
     * @param   string $match The text matched by the patterns
     * @param   int $state The lexer state for the match
     * @param   int $pos The character position of the matched text
     * @param   Doku_Handler $handler The Doku_Handler object
     * @return  bool|array Return an array with all data you want to use in render, false don't add an instruction
     */
    public function handle($match, $state, $pos, Doku_Handler $handler) {
        // Export button
        if($match == '~~ODT~~') {
            return array();
        }

        // Extended info
        $match = substr($match, 6, -2); //strip markup
        $extinfo = explode(':', $match);

        $info_type = $extinfo[0];

        if(count($extinfo) < 2) { // no value
            $info_value = '';
        } elseif(count($extinfo) == 2) {
            $info_value = $extinfo[1];
        } else { // value may contain colons
            $info_value = implode(array_slice($extinfo, 1), ':');
        }
        return array($info_type, $info_value);
    }

    /**
     * Handles the actual output creation.
     *
     * @param string $format output format being rendered
     * @param Doku_Renderer $renderer the current renderer object
     * @param array $data data created by handler()
     * @return  boolean                 rendered correctly? (however, returned value is not used at the moment)
     */
    public function render($format, Doku_Renderer $renderer, $data) {
        global $ID, $REV;

        if(!$data) { // Export button
            if($format != 'xhtml') return false;

            $renderer->doc .= '<a href="' . exportlink($ID, 'odt', ($REV != '' ? 'rev=' . $REV : '')) . '" title="' . $this->getLang('view') . '">';
            $renderer->doc .= '<img src="' . DOKU_BASE . 'lib/plugins/odt/odt.png" align="right" alt="' . $this->getLang('view') . '" width="48" height="48" />';
            $renderer->doc .= '</a>';
            return true;

        } else { // Extended info

            list($info_type, $info_value) = $data;
            if($info_type == "template") { // Template-based export
                 if($format == 'odt') {
                     /** @var renderer_plugin_odt_page $renderer */
                     $renderer->template = $info_value;

                 } elseif($format == 'metadata') {
                     /** @var Doku_Renderer_metadata $renderer */
                     $renderer->meta['relation']['odt']['template'] = $info_value;
                 }
            }
            if($info_type == "toc") { // Insert TOC in exported ODT file
                 if($format == 'odt') {
                     /** @var renderer_plugin_odt_page $renderer */
                     $renderer->toc_settings = $info_value;
                     $renderer->render_TOC();

                 } elseif($format == 'metadata') {
                     /** @var Doku_Renderer_metadata $renderer */
                     $renderer->meta['relation']['odt']['toc'] = $info_value;
                 }
            }
            if($info_type == "page") { // Set/change page format in exported ODT file
                 if($format == 'odt') {
                     /** @var renderer_plugin_odt_page $renderer */
                     $params = explode(',', $info_value);
                     $format = trim ($params [0]);
                     $orientation = trim ($params [1]);
                     for ( $index = 2 ; $index < 6 ; $index++ ) {
                         if ( empty($params [$index]) ) {
                             $params [$index] = 2;
                         }
                     }
                     $renderer->setPageFormat($format, $orientation, $params [2], $params [3], $params [4], $params [5]);
                 }
            }
        }
        return false;
    }

}
