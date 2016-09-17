<?php
/**
 * ODT Plugin: extends the dependencies of the cache with ODT related files
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Aurelien Bompard <aurelien@bompard.org>
 * @author       Florian Lamml <info@florian-lamml.de>
 */
// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

/**
 * Add the template as a page dependency for the caching system
 * 
 * @package DokuWiki\Action\Cache
 */
class action_plugin_odt_cache extends DokuWiki_Action_Plugin {
    protected $config = null;

    /**
     * Register the event
     *
     * @param Doku_Event_Handler $controller
     */
    public function register(Doku_Event_Handler $controller) {
        $controller->register_hook('PARSER_CACHE_USE', 'BEFORE', $this, 'handle_cache_prepare');
    }

    /**
     * Add dependencies to cache
     *
     * @param Doku_Event $event
     */
    public function handle_cache_prepare(Doku_Event $event) {
        // Load config helper if not done yet
        if ( $this->config == NULL ) {
            $this->config = plugin_load('helper', 'odt_config');
            $this->config->load($warning);
        }

        $cache =& $event->data;
        // only the ODT rendering mode needs caching tweaks
        if($cache->mode != "odt") return;

        $template_name = $this->config->getParam('odt_template');
        if(!$template_name) {
            return;
        }
        $template_path = $this->config->getParam('mediadir') . '/' . $this->config->getParam('tpl_dir') . "/" . $template_name;
        if(file_exists($template_path)) {
            $cache->depends['files'][] = $template_path;
        }
    }

}
