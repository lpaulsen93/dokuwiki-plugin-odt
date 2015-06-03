<?php
/**
 * ODT Plugin: Exports to ODT
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Aurelien Bompard <aurelien@bompard.org>
 * @author       Florian Lamml <info@florian-lamml.de>
 */
// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

/**
 * Add the template as a page dependency for the caching system
 */
class action_plugin_odt extends DokuWiki_Action_Plugin {

    /**
     * return some info
     */
    public function getInfo() {
        return confToHash(dirname(__FILE__) . '/info.txt');
    }

    /**
     * Registers a callback function for a given event
     *
     * @param Doku_Event_Handler $controller
     */
    public function register(Doku_Event_Handler $controller) {
        $controller->register_hook('PARSER_CACHE_USE', 'BEFORE', $this, 'handle_cache_prepare');
        $controller->register_hook('TEMPLATE_PAGETOOLS_DISPLAY', 'BEFORE', $this, 'addbutton', array());
    }

    /**
     * Add 'export odt'-button to pagetools
     *
     * @param Doku_Event $event
     * @param mixed $param not defined
     */
    public function addbutton(Doku_Event $event, $param) {
        global $ID, $REV;

        if($this->getConf('showexportbutton') && $event->data['view'] == 'main') {
            $params = array('do' => 'export_odt');
            if($REV) {
                $params['rev'] = $REV;
            }

            $event->data['items'] = array_slice($event->data['items'], 0, -1, true) +
                array('export_odt' =>
                        '<li>'
                        . '<a href="' . wl($ID, $params) . '"  class="action export_odt" rel="nofollow" title="' . $this->getLang('export_odt_button') . '">'
                        . '<span>' . $this->getLang('export_odt_button') . '</span>'
                        . '</a>'
                        . '</li>'
                ) +
                array_slice($event->data['items'], -1, 1, true);
        }
    }

    /**
     * Add dependencies to cache
     *
     * @param Doku_Event $event
     * @param mixed $param
     */
    public function handle_cache_prepare(Doku_Event $event, $param) {
        global $conf, $ID;

        $cache =& $event->data;
        // only the ODT rendering mode needs caching tweaks
        if($cache->mode != "odt") return;

        $odt_meta = p_get_metadata($ID, 'relation odt');
        $template_name = $odt_meta["template"];
        if(!$template_name) {
            return;
        }
        $template_path = $conf['mediadir'] . '/' . $this->getConf("tpl_dir") . "/" . $template_name;
        if(file_exists($template_path)) {
            $cache->depends['files'][] = $template_path;
        }
    }

}
