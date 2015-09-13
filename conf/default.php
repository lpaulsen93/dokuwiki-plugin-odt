<?php
/**
 * @author Aurelien Bompard <aurelien@bompard.org>
 */

// Directory of the templates in the media manager
$conf['tpl_dir'] = 'odt';

// Default ODT template (filename only)
$conf['odt_template'] = '';

$conf['showexportbutton'] = 1;

// Parameters for CSS import
$conf['media_sel']     = 'print';
$conf['css_template']  = 'dokuwiki';
$conf['usestyles']     = '';

// Parameters for converting pixel to points
$conf['twips_per_pixel_x'] = '16';
$conf['twips_per_pixel_y'] = '20';

// Page format, orientation and margins in 'cm'
$conf['format']        = 'A4';
$conf['orientation']   = 'portrait';
$conf['margin_top']    = '2';
$conf['margin_right']  = '2';
$conf['margin_bottom'] = '2';
$conf['margin_left']   = '2';

// Disable link creation?
$conf['disable_links'] = 'No';

// TOC settings
$conf['toc_maxlevel']    = '';
$conf['toc_leader_sign'] = '.';
$conf['toc_indents']     = '0, 0.5, 1, 1.5, 2, 2.5, 3, 3.5, 4, 4.5';
$conf['toc_pagebreak']   = 'Yes';
$conf['toc_style']       = 'color:black;';
