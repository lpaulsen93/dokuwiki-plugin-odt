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
 * 
 * @package DokuWiki\Syntax
 */
class syntax_plugin_odt extends DokuWiki_Syntax_Plugin {
    protected $config = NULL;
    
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
        return array($info_type, $info_value, $pos);
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
            // Load config helper if not done yet
            if ( $this->config == NULL ) {
                $this->config = plugin_load('helper', 'odt_config');
            }

            list($info_type, $info_value, $pos) = $data;

            // If it is a config option store it in the meta data
            // and set the config parameter in the renderer.
            if ( $this->config->isParam($info_type) ) {
                if($format == 'odt') {
                    /** @var renderer_plugin_odt_page $renderer */
                    $renderer->setConfigParam($info_type, $info_value);
                } elseif($format == 'metadata') {
                    if ($this->config->addingToMetaIsAllowed($info_type, $pos)) {
                        /** @var Doku_Renderer_metadata $renderer */
                        $renderer->meta['relation']['odt'][$info_type] = $info_value;
                    }
                }
            }

            // Do some more work for the tags which are not just a config parameter setter
            switch($info_type)
            {
                case 'toc': // Insert TOC in exported ODT file
                    if($format == 'odt') {
                        /** @var renderer_plugin_odt_page $renderer */
                        $renderer->render_index('toc', $info_value);
                    } elseif($format == 'metadata') {
                        /** @var Doku_Renderer_metadata $renderer */
                        $renderer->meta['relation']['odt']['toc'] = $info_value;
                    } elseif($format == 'xhtml') {
                        $this->insert_index_preview ($renderer, 'toc');
                    }
                break;
                case 'chapter-index': // Insert chapter index in exported ODT file
                    if($format == 'odt') {
                        /** @var renderer_plugin_odt_page $renderer */
                        $renderer->render_index('chapter', $info_value);
                    } elseif($format == 'xhtml') {
                        $this->insert_index_preview ($renderer, 'chapter');
                    }
                break;
                case 'disablelinks': // Disable creating links and only show the text instead
                    if($format == 'odt') {
                        $renderer->disable_links();
                    }
                break;
                case 'enablelinks': // Re-enable creating links
                    if($format == 'odt') {
                        $renderer->enable_links();
                    }
                break;
                case 'page':
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
                break;
                case 'format':
                    if($format == 'odt') {
                        /** @var renderer_plugin_odt_page $renderer */
                        $format = trim ($info_value);
                        $renderer->setPageFormat($format);
                    }
                break;
                case 'orientation':
                    if($format == 'odt') {
                        /** @var renderer_plugin_odt_page $renderer */
                        $orientation = trim ($info_value);
                        $renderer->setPageFormat(NULL,$orientation);
                    }
                break;
                case 'margin_top':
                    if($format == 'odt') {
                        /** @var renderer_plugin_odt_page $renderer */
                        $margin = trim ($info_value);
                        $renderer->setPageFormat(NULL,NULL,$margin);
                    }
                break;
                case 'margin_right':
                    if($format == 'odt') {
                        /** @var renderer_plugin_odt_page $renderer */
                        $margin = trim ($info_value);
                        $renderer->setPageFormat(NULL,NULL,NULL,$margin);
                    }
                break;
                case 'margin_bottom':
                    if($format == 'odt') {
                        /** @var renderer_plugin_odt_page $renderer */
                        $margin = trim ($info_value);
                        $renderer->setPageFormat(NULL,NULL,NULL,NULL,$margin);
                    }
                break;
                case 'margin_left':
                    if($format == 'odt') {
                        /** @var renderer_plugin_odt_page $renderer */
                        $margin = trim ($info_value);
                        $renderer->setPageFormat(NULL,NULL,NULL,NULL,NULL,$margin);
                    }
                break;
                case 'templatepage': // Take wiki page content as additional CSS input
                    if($format == 'odt' || $format == 'xhtml' ) {
                        if ($this->check_templatepage ($info_value, $format) == true &&
                            $format == 'odt' ) {
                            /** @var renderer_plugin_odt_page $renderer */
                            $renderer->read_templatepage($info_value);
                        }
                    }
                break;
                case 'frame-open': // Insert/Open ODT frame
                    if($format == 'odt' ) {
                        /** @var renderer_plugin_odt_page $renderer */
                        $this->frame_open($renderer, $info_value);
                    }
                break;
                case 'frame-close': // Close ODT frame
                    if($format == 'odt' ) {
                        /** @var renderer_plugin_odt_page $renderer */
                        $this->frame_close($renderer);
                    }
                break;
            }
        }
        return false;
    }

    /**
     * Insert a browser preview for an index.
     *
     * @param  Doku_Renderer $renderer The current renderer
     * @param  string        $type     The index type ('toc' or 'chapter)'
     */
    function insert_index_preview ($renderer, $type='toc') {
        if ($this->config->getParam ('index_in_browser') == 'hide') {
            return;
        }
        switch ($type) {
            case 'toc':
                $msg = $this->getLang('toc_msg');
                $reminder = $this->getLang('update_toc_msg');
            break;
            case 'chapter':
                $msg = $this->getLang('chapter_msg');
                $reminder = $this->getLang('update_chapter_msg');
            break;
        }
        $renderer->doc .= '<p class="index_preview_odt">';
        $renderer->doc .= '<span id="text" class="index_preview_odt">'.$msg.'</span><br>';
        $renderer->doc .= '<span id="reminder" class="index_preview_odt">'.$reminder.'</span>';
        $renderer->doc .= '</p>';
    }

    /**
     * Checl existance of the template page and display error
     * message in case of xhtml rendering.
     *
     * @param  string $pagename The page to check
     * @param  string $format   The render format ('xhtml' or 'odt')
     */
    protected function check_templatepage ($pagename, $format) {
        $exists = false;
        if (empty($pagename)) {
            if ($format == 'xhtml') {
                msg(sprintf("No page specified!", html_wikilink($pagename)), -1);
            }
            return (false);
        }
        resolve_pageid($INFO['namespace'], $pagename, $exists);
        if(!$exists) {
            if ($format == 'xhtml') {
                msg(sprintf("Page not found!", html_wikilink($pagename)), -1);
            }
            return (false);
        }
        return (true);
    }

    /**
     * Open a frame with a text box.
     *
     * @param  Doku_Renderer $renderer The current renderer object
     * @param  string        $params   Parameters for the frame
     */
    protected function frame_open ($renderer, $params) {
        // Get inline CSS for ODT frame
        $odt_css = '';
        if ( preg_match('/odt-css="[^"]+";/', $params, $matches) === 1 ) {
            $quote = strpos ($matches [0], '"');
            $temp = substr ($matches [0], $quote+1);
            $temp = trim ($temp, '";');
            $odt_css = $temp.';';
        }
        $odt_css_id = '';
        if ( preg_match('/odt-css-id="[^"]+";/', $params, $matches) === 1 ) {
            $quote = strpos ($matches [0], '"');
            $temp = substr ($matches [0], $quote+1);
            $temp = trim ($temp, '";');
            $odt_css_id = $temp;
        }
        
        $properties = array();
        
        $renderer->getODTPropertiesNew ($properties, NULL, 'id="'.$odt_css_id.'" style="'.$odt_css.'"');

        if (empty($properties ['page'])) {
            $properties ['anchor-type'] = 'page';
        }
        if (empty($properties ['wrap'])) {
            $properties ['wrap'] = 'run-through';
        }
        if (empty($properties ['number-wrapped-paragraphs'])) {
            $properties ['number-wrapped-paragraphs'] = 'no-limit';
        }
        if (empty($properties ['vertical-pos'])) {
            $properties ['vertical-pos'] = 'from-top';
        }
        if (empty($properties ['vertical-rel'])) {
            $properties ['vertical-rel'] = 'page';
        }
        if (empty($properties ['horizontal-pos'])) {
            $properties ['horizontal-pos'] = 'from-left';
        }
        if (empty($properties ['horizontal-rel'])) {
            $properties ['horizontal-rel'] = 'page';
        }
        if (empty($properties ['wrap-influence-on-position'])) {
            $properties ['wrap-influence-on-position'] = 'once-concurrent';
        }
        if (empty($properties ['flow-with-text'])) {
            $properties ['flow-with-text'] = 'false';
        }
        if (empty($properties ['margin-top'])) {
            $properties ['margin-top'] = '0cm';
        }
        if (empty($properties ['margin-right'])) {
            $properties ['margin-right'] = '0cm';
        }
        if (empty($properties ['margin-bottom'])) {
            $properties ['margin-bottom'] = '0cm';
        }
        if (empty($properties ['margin-left'])) {
            $properties ['margin-left'] = '0cm';
        }
        if (empty($properties ['padding-top'])) {
            $properties ['padding-top'] = '0cm';
        }
        if (empty($properties ['padding-right'])) {
            $properties ['padding-right'] = '0cm';
        }
        if (empty($properties ['padding-bottom'])) {
            $properties ['padding-bottom'] = '0cm';
        }
        if (empty($properties ['padding-left'])) {
            $properties ['padding-left'] = '0cm';
        }
        if (empty($properties ['border-top'])) {
            $properties ['border-top'] = 'none';
        }
        if (empty($properties ['border-right'])) {
            $properties ['border-right'] = 'none';
        }
        if (empty($properties ['border-bottom'])) {
            $properties ['border-bottom'] = 'none';
        }
        if (empty($properties ['border-left'])) {
            $properties ['border-left'] = 'none';
        }
        if (empty($properties ['horizontal-align'])) {
            $properties ['horizontal-align'] = 'left';
        }
                
        $renderer->_odtOpenTextBoxUseProperties ($properties);
        $renderer->p_open();
    }

    /**
     * Close a frame with a text box.
     *
     * @param  Doku_Renderer $renderer The current renderer object
     */
    protected function frame_close ($renderer) {        
        $renderer->p_close();
        $renderer->_odtCloseTextBox ();
    }
}
