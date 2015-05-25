<?php
/**
 * Options for the odt plugin
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */

$meta['tpl_dir']   = array('string');
$meta['tpl_default'] = array('string');
$meta['showexportbutton'] = array('onoff');

$meta['media_sel'] = array('string');
$meta['template']  = array('dirchoice', '_dir' => DOKU_PLUGIN . 'odt/tpl/');
$meta['usestyles'] = array('string');

