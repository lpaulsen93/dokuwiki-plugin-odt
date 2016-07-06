<?php
/**
 * docHandler: Abstract class defining the interface for classes
 * which create the ODT document file/zip archive.
 *
 * Most code was taken from renderer.php.
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Aurelien Bompard <aurelien@bompard.org>
 * @author LarsDW223
 */
// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

require_once DOKU_PLUGIN . 'odt/ODT/ODTUnits.php';

/**
 * The docHandler interface
 */
abstract class docHandler
{
    public $trace_dump = NULL;

    /**
     * Build ODT document.
     *
     * @param string      $doc
     * @param string      $meta
     * @param string      $userfields
     * @return mixed
     */
    abstract public function build(ODTInternalParams $params, $meta=null, $userfields=null, $pagestyles=null);
}
