<?php
/**
 * Options for the odt plugin
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * 
 * @package DokuWiki\Conf\Metadata
 */

$meta['tpl_dir']   = array('string');
$meta['odt_template'] = array('string');
$meta['showexportbutton'] = array('onoff');
$meta['showpdfexportbutton'] = array('onoff');

$meta['css_usage']     = array('multichoice', '_choices' => array('off (plugins only)', 'basic style import'));
$meta['media_sel']     = array('string');
$meta['css_font_size'] = array('numeric');
$meta['css_template']  = array('dirchoice', '_dir' => DOKU_INC . 'lib/tpl/');

$meta['apply_fs_to_non_css'] = array('onoff');

$meta['twips_per_pixel_x'] = array('numeric');
$meta['twips_per_pixel_y'] = array('numeric');

$meta['format']        = array('multichoice', '_choices' => array('A6', 'A5', 'A4', 'A3',
                                                                  'B6 (ISO)', 'B5 (ISO)', 'B4 (ISO)',
                                                                  'Letter', 'Legal', 'Long Bond', 'Tabloid',
                                                                  'B6 (JIS)', 'B5 (JIS)', 'B4 (JIS)',
                                                                  '16 Kai', '32 Kai', 'Big 32 Kai',
                                                                  'DL Envelope',
                                                                  'C6 Envelope', 'C6/5 Envelope', 'C5 Envelope', 'C4 Envelope',
                                                                  '#6 3/4 Envelope', '#7 3/4 (Monarch) Envelope',
                                                                  '#9 Envelope', '#10 Envelope', '#11 Envelope', '#12 Envelope',
                                                                  'Japanese Postcard'));
$meta['orientation']   = array('multichoice', '_choices' => array('portrait', 'landscape'));
$meta['margin_top']    = array('numeric');
$meta['margin_right']  = array('numeric');
$meta['margin_bottom'] = array('numeric');
$meta['margin_left']   = array('numeric');

$meta['disable_links'] = array('multichoice', '_choices' => array('No', 'Yes'));

$meta['toc_maxlevel']     = array('numeric');
$meta['toc_leader_sign']  = array('string');
$meta['toc_indents']      = array('string');
$meta['toc_pagebreak']    = array('multichoice', '_choices' => array('Yes', 'No'));
$meta['toc_style']        = array('string');
$meta['index_in_browser'] = array('multichoice', '_choices' => array('hide', 'display placeholder'));

$meta['outline_list_style'] = array('multichoice', '_choices' => array('Normal', 'Numbers'));

$meta['olist_label_align'] = array('multichoice', '_choices' => array('left', 'center', 'right'));

$meta['convert_to_pdf'] = array('string');
