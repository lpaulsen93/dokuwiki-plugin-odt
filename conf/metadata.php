<?php
/**
 * Options for the odt plugin
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */

$meta['tpl_dir']   = array('string');
$meta['odt_template'] = array('string');
$meta['showexportbutton'] = array('onoff');

$meta['media_sel'] = array('string');
$meta['template']  = array('dirchoice', '_dir' => DOKU_PLUGIN . 'odt/tpl/');
$meta['usestyles'] = array('string');

$meta['twips_per_pixel_x'] = array('numeric');
$meta['twips_per_pixel_y'] = array('numeric');

