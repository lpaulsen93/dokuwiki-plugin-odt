<?php
/**
 * Simple helper class to query CSS color values and names.
 * 
 * This is only a wrapper for csscolor in ODT/css/csscolors.php
 * making the functions accessible as a helper plugin.
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     LarsDW223
 */

// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

require_once DOKU_PLUGIN . 'odt/ODT/css/csscolors.php';

/**
 * Class helper_plugin_odt_csscolors
 */
class helper_plugin_odt_csscolors extends DokuWiki_Plugin {
    /**
     * @return array
     */
    function getMethods() {
        $result = array();
        $result[] = array(
                'name'   => 'getColorValue',
                'desc'   => 'returns the color value for a given CSS color name. Returns "#000000" if the name is unknown',
                'params' => array('name' => 'string'),
                'return' => array('color value' => 'string'),
                );
        $result[] = array(
                'name'   => 'getValueName',
                'desc'   => 'returns the CSS color name for a given color value. Returns "Black" if the value is unknown',
                'params' => array('value' => 'string'),
                'return' => array('name' => 'string'),
                );
        return $result;
    }

    /**
     * @param string|null $name
     * @return string
     */
    public static function getColorValue ($name=NULL) {
        return csscolors::getColorValue ($name);
    }

    /**
     * @param null $value
     * @return string
     */
    public static function getValueName ($value=NULL) {
        return csscolors::getValueName ($value);
    }
}
