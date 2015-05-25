<?php
/**
 * Helper class to load standrad DokuWiki CSS files.
 * Adopted code from dw2pdf plugin by Andreas Gohr <andi@splitbrain.org>.
 * 
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     LarsDW223
 */

// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

if (!defined('DOKU_LF')) define('DOKU_LF', "\n");
if (!defined('DOKU_TAB')) define('DOKU_TAB', "\t");
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC.'lib/plugins/');

class helper_plugin_odt_dwcssloader extends DokuWiki_Plugin {
    function getMethods() {
        $result = array();
        $result[] = array(
                'name'   => 'load',
                'desc'   => 'Loads standard DokuWiki, plugin specific and format specific CSS files and templates. Includes handling of replacements and less parsing.',
                'params' => array('$plugin_name' => 'string'),
                'params' => array('$format' => 'string'),
                'params' => array('$template' => 'string'),
                'return' => array('All CSS styles' => 'string'),
                );
        return $result;
    }

    /**
     * Load all the style sheets and apply the needed replacements
     */
    public function load($plugin_name, $format, $template) {
        global $conf;
        //reusue the CSS dispatcher functions without triggering the main function
        define('SIMPLE_TEST', 1);
        require_once(DOKU_INC . 'lib/exe/css.php');

        // Always only use small letters in format
        $format = strtolower ($format);

        // prepare CSS files
        $files = array_merge(
            array(
                DOKU_INC . 'lib/styles/screen.css'
                    => DOKU_BASE . 'lib/styles/',
                DOKU_INC . 'lib/styles/print.css'
                    => DOKU_BASE . 'lib/styles/',
            ),
            css_pluginstyles('all'),
            $this->css_pluginFormatStyles($format),
            array(
                DOKU_PLUGIN . $plugin_name.'/conf/style.css'
                    => DOKU_BASE . 'lib/plugins/'.$plugin_name.'/conf/',
                DOKU_PLUGIN . $plugin_name.'/tpl/' . $template . '/style.css'
                    => DOKU_BASE . 'lib/plugins/'.$plugin_name.'/tpl/' . $template . '/',
                DOKU_PLUGIN . $plugin_name.'/conf/style.local.css'
                    => DOKU_BASE . 'lib/plugins/'.$plugin_name.'/conf/',
            )
        );
        $css = '';
        foreach($files as $file => $location) {
            $display = str_replace(fullpath(DOKU_INC), '', fullpath($file));
            $css .= "\n/* XXXXXXXXX $display XXXXXXXXX */\n";
            $css .= css_loadfile($file, $location);
        }

        if(function_exists('css_parseless')) {
            // apply pattern replacements
            $styleini = css_styleini($conf['template']);
            $css = css_applystyle($css, $styleini['replacements']);

            // parse less
            $css = css_parseless($css);
        } else {
            // @deprecated 2013-12-19: fix backward compatibility
            $css = css_applystyle($css, DOKU_INC . 'lib/tpl/' . $conf['template'] . '/');
        }

        return $css;
    }

    /**
     * Returns a list of possible Plugin Styles for format $format
     *
     * Checks for a $format.'.css', falls back to print.css
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    protected function css_pluginFormatStyles($format) {
        $list = array();
        $plugins = plugin_list();

        $usestyle = explode(',', $this->getConf('usestyles'));
        foreach($plugins as $p) {
            if(in_array($p, $usestyle)) {
                $list[DOKU_PLUGIN . "$p/screen.css"] = DOKU_BASE . "lib/plugins/$p/";
                $list[DOKU_PLUGIN . "$p/style.css"] = DOKU_BASE . "lib/plugins/$p/";
            }

            if(file_exists(DOKU_PLUGIN . "$p/".$format.".css")) {
                $list[DOKU_PLUGIN . "$p/".$format.".css"] = DOKU_BASE . "lib/plugins/$p/";
            } else {
                $list[DOKU_PLUGIN . "$p/print.css"] = DOKU_BASE . "lib/plugins/$p/";
            }
        }
        return $list;
    }
}
?>
