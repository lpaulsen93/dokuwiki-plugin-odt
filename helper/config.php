<?php
/**
 * Helper class handling ODT plugin configuration stuff.
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     LarsDW223
 */

// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

/**
 * Class helper_plugin_odt_config
 */
class helper_plugin_odt_config extends DokuWiki_Plugin {
    /** @var array Central storage for config parameters. */
    protected $config = array();

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
     * Constructor. Loads helper plugins.
     */
    public function __construct() {
        // Set up empty array with known config parameters

        // Template directory.
        $this->config ['tpl_dir'] =
            array('value'              => NULL,
                  'DWGlobalName'       => NULL,
                  'hasGlobal'          => true,
                  'hasURL'             => false,
                  'hasMeta'            => false,
                  'addMetaAtStartOnly' => false,
                  'refresh'            => false);
        // ODT template.
        $this->config ['odt_template'] =
            array('value'              => NULL,
                  'DWGlobalName'       => NULL,
                  'hasGlobal'          => true,
                  'hasURL'             => true,
                  'hasMeta'            => true,
                  'addMetaAtStartOnly' => false,
                  'refresh'            => false);
        // CSS template.
        $this->config ['css_template'] =
            array('value'              => NULL,
                  'DWGlobalName'       => NULL,
                  'hasGlobal'          => true,
                  'hasURL'             => true,
                  'hasMeta'            => true,
                  'addMetaAtStartOnly' => false,
                  'refresh'            => false);
        // CSS media selector (screen or print)
        $this->config ['media_sel'] =
            array('value'              => NULL,
                  'DWGlobalName'       => NULL,
                  'hasGlobal'          => true,
                  'hasURL'             => true,
                  'hasMeta'            => true,
                  'addMetaAtStartOnly' => false,
                  'refresh'            => false);
        // Usestyles: list of plugins for which screen styles should be loaded
        $this->config ['usestyles'] =
            array('value'              => NULL,
                  'DWGlobalName'       => NULL,
                  'hasGlobal'          => true,
                  'hasURL'             => true,
                  'hasMeta'            => true,
                  'addMetaAtStartOnly' => false,
                  'refresh'            => false);
        // Twips per pixel x and y
        $this->config ['twips_per_pixel_x'] =
            array('value'              => NULL,
                  'DWGlobalName'       => NULL,
                  'hasGlobal'          => true,
                  'hasURL'             => true,
                  'hasMeta'            => true,
                  'addMetaAtStartOnly' => false,
                  'refresh'            => false);
        $this->config ['twips_per_pixel_y'] =
            array('value'              => NULL,
                  'DWGlobalName'       => NULL,
                  'hasGlobal'          => true,
                  'hasURL'             => true,
                  'hasMeta'            => true,
                  'addMetaAtStartOnly' => false,
                  'refresh'            => false);
        // Page format, orientation and margins
        //
        // This settings also have a syntax tag changing the page format
        // and introducing a pagebreak. The meta setting changes the start
        // page format and may only be set if at the start of the document.
        // Otherwise changing the page fomat in the document would also
        // change the format of the first page!
        $this->config ['format']        =
            array('value'              => NULL,
                  'DWGlobalName'       => NULL,
                  'hasGlobal'          => true,
                  'hasURL'             => true,
                  'hasMeta'            => true,
                  'addMetaAtStartOnly' => true,
                  'refresh'            => false);
        $this->config ['orientation']   =
            array('value'              => NULL,
                  'DWGlobalName'       => NULL,
                  'hasGlobal'          => true,
                  'hasURL'             => true,
                  'hasMeta'            => true,
                  'addMetaAtStartOnly' => true,
                  'refresh'            => false);
        $this->config ['margin_top']    =
            array('value'              => NULL,
                  'DWGlobalName'       => NULL,
                  'hasGlobal'          => true,
                  'hasURL'             => true,
                  'hasMeta'            => true,
                  'addMetaAtStartOnly' => true,
                  'refresh'            => false);
        $this->config ['margin_right']  =
            array('value'              => NULL,
                  'DWGlobalName'       => NULL,
                  'hasGlobal'          => true,
                  'hasURL'             => true,
                  'hasMeta'            => true,
                  'addMetaAtStartOnly' => true,
                  'refresh'            => false);
        $this->config ['margin_bottom'] =
            array('value'              => NULL,
                  'DWGlobalName'       => NULL,
                  'hasGlobal'          => true,
                  'hasURL'             => true,
                  'hasMeta'            => true,
                  'addMetaAtStartOnly' => true,
                  'refresh'            => false);
        $this->config ['margin_left']   =
            array('value'              => NULL,
                  'DWGlobalName'       => NULL,
                  'hasGlobal'          => true,
                  'hasURL'             => true,
                  'hasMeta'            => true,
                  'addMetaAtStartOnly' => true,
                  'refresh'            => false);
        $this->config ['page']          =
            array('value'              => NULL,
                  'DWGlobalName'       => NULL,
                  'hasGlobal'          => false,
                  'hasURL'             => true,
                  'hasMeta'            => true,
                  'addMetaAtStartOnly' => true,
                  'refresh'            => false);
        // Disable links
        $this->config ['disable_links'] =
            array('value'              => NULL,
                  'DWGlobalName'       => NULL,
                  'hasGlobal'          => true,
                  'hasURL'             => true,
                  'hasMeta'            => true,
                  'addMetaAtStartOnly' => false,
                  'refresh'            => true);
        // TOC: maxlevel
        $this->config ['toc_maxlevel'] =
            array('value'              => NULL,
                  'DWGlobalName'       => 'maxtoclevel',
                  'hasGlobal'          => true,
                  'hasURL'             => true,
                  'hasMeta'            => true,
                  'addMetaAtStartOnly' => false,
                  'refresh'            => false);
        // TOC: toc_leader_sign
        $this->config ['toc_leader_sign'] =
            array('value'              => NULL,
                  'DWGlobalName'       => NULL,
                  'hasGlobal'          => true,
                  'hasURL'             => true,
                  'hasMeta'            => true,
                  'addMetaAtStartOnly' => false,
                  'refresh'            => false);
        // TOC: toc_indents
        $this->config ['toc_indents'] =
            array('value'              => NULL,
                  'DWGlobalName'       => NULL,
                  'hasGlobal'          => true,
                  'hasURL'             => true,
                  'hasMeta'            => true,
                  'addMetaAtStartOnly' => false,
                  'refresh'            => false);
        // TOC: toc_pagebreak
        $this->config ['toc_pagebreak'] =
            array('value'              => NULL,
                  'DWGlobalName'       => NULL,
                  'hasGlobal'          => true,
                  'hasURL'             => true,
                  'hasMeta'            => true,
                  'addMetaAtStartOnly' => false,
                  'refresh'            => false);
        // TOC-Style (default, assigned to each level)
        $this->config ['toc_style'] =
            array('value'              => NULL,
                  'DWGlobalName'       => NULL,
                  'hasGlobal'          => true,
                  'hasURL'             => true,
                  'hasMeta'            => true,
                  'addMetaAtStartOnly' => false,
                  'refresh'            => false);
    }

    /**
     * Set a config parameter.
     */
    public function setParam($name, $value) {
        if (!empty($name)) {
            $this->config [$name]['value'] = $value;
        }
    }

    /**
     * Get a config parameter.
     */
    public function getParam($name) {
        return $this->config [$name]['value'];
    }

    /**
     * Is the $name specified the name of a ODT plugin config parameter?
     *
     * @return bool Is it a config parameter?
     */
    public function isParam($name) {
        if (!empty($name)) {
            return array_key_exists($name, $this->config);
        }
        return false;
    }

    /**
     * Does the config parameter need a refresh?
     *
     * @return bool
     */
    public function isRefreshable($name) {
        if (!empty($name)) {
            return $this->config [$name]['refresh'];
        }
        return false;
    }

    /**
     * Does the config parameter have a DokuWiki global config setting?
     *
     * @return string Name of global DokuWiki option or NULL
     */
    public function hasDWGlobalSetting($name) {
        if (!empty($name)) {
            return $this->config [$name]['DWGlobalName'];
        }
        return false;
    }

    /**
     * Does the config parameter have a global config setting?
     *
     * @return bool
     */
    public function isGlobalSetting($name) {
        if (!empty($name)) {
            return $this->config [$name]['hasGlobal'];
        }
        return false;
    }

    /**
     * Does the config parameter have a URL config setting?
     *
     * @return bool
     */
    public function isURLSetting($name) {
        if (!empty($name)) {
            return $this->config [$name]['hasURL'];
        }
        return false;
    }

    /**
     * Does the config parameter have a Meta-Data config setting?
     *
     * @return bool
     */
    public function isMetaSetting($name) {
        if (!empty($name)) {
            return $this->config [$name]['hasMeta'];
        }
        return false;
    }

    /**
     * May the parameter be added to the Meta data?
     *
     * @return bool
     */
    public function addingToMetaIsAllowed($name, $pos) {
        if (!empty($name) and $this->isMetaSetting($name)) {
            if ($pos != 0 and $this->config [$name]['addMetaAtStartOnly']) {
                return false;
            }
            return true;
        }
        return false;
    }

    /**
     * Load all config parameters: global config options, URL and syntax tag.
     * Check export mode: scratch, ODT template or CSS template?
     *
     * @param string $warning (reference) warning message
     * @return string Export mode to be used
     */
    protected function loadIntern(&$warning, $refresh) {
        global $conf, $ID, $INPUT;

        $mode = 'scratch';

        // Get all known config parameters, see __construct().
        $odt_meta = p_get_metadata($ID, 'relation odt');
        foreach ($this->config as $name => $value) {
            if ( !$refresh || $this->isRefreshable($name) ) {
                $value = $this->getParam ($name);

                // Check DokuWiki global configuration.
                $dw_name = $this->hasDWGlobalSetting ($name);
                if (!$value && $conf[$dw_name]) {
                    $this->setParam ($name, $conf[$dw_name]);
                }
                
                // Check plugin configuration.
                if (!$value && $this->isGlobalSetting($name) && $this->getConf($name)) {
                    $this->setParam ($name, $this->getConf($name));
                }

                // Check if parameter is provided in the URL.
                $url_param = $INPUT->get->str($name, $value, true);
                if ($this->isURLSetting($name) && isset($url_param)) {
                    $this->setParam ($name, $url_param);
                }

                // Check meta data in case syntax tags have written
                // the config parameters to it.
                $value = $odt_meta[$name];
                if($this->isMetaSetting($name) && !empty($value)) {
                    $this->setParam ($name, $value);
                }

                // ODT-Template based export required?
                if ( $name == 'odt_template' && !empty($this->getParam ('odt_template'))) {
                    // ODT-Template chosen
                    if (file_exists($this->getParam('mediadir').'/'.$this->getParam('tpl_dir')."/".$this->getParam ('odt_template'))) {
                        //template found
                        $mode = 'ODT template';
                    } else {
                        if ($warning) {
                            // template chosen but not found : warn the user and use the default template
                            $warning = $this->_xmlEntities( sprintf($this->getLang('tpl_not_found'),$this->getParam ('odt_template'),$this->getParam ('tpl_dir')) );
                        }
                    }
                }

                // Convert Yes/No-String in 'disable_links' to boolean.
                if ( $name == 'disable_links' ) {
                    if ( strcasecmp($this->getParam ('disable_links'), 'Yes') != 0 ) {
                        $this->setParam ('disable_links', false);
                    } else {
                        $this->setParam ('disable_links', true);
                    }
                }

                // Convert Yes/No-String in 'toc_pagebreak' to boolean.
                if ( $name == 'toc_pagebreak' ) {
                    if ( strcasecmp($this->getParam ('toc_pagebreak'), 'Yes') == 0 ) {
                        $this->setParam ('toc_pagebreak', true);
                    } else {
                        $this->setParam ('toc_pagebreak', false);
                    }
                }
            }
        }

        return $mode;
    }

    /**
     * Load config parameters. See loadIntern().
     *
     * @param string $warning (reference) warning message
     * @return string Export mode to be used
     */
    public function load(&$warning) {
        return $this->loadIntern($warning, false);
    }

    /**
     * Refresh config parameters. See loadIntern().
     */
    public function refresh() {
        $this->loadIntern($warning, true);
    }

    /**
     * Get hash for current config content.
     *
     * @return string The calculated hash
     */
    public function hash() {
        $content = '';
        
        // Get all known config parameters in one string
        foreach ($this->config as $name => $value) {
            $content .= $name.'='.$this->getParam ($name).';';
        }
        
        // Return the md5 hash for it.
        return hash('md5', $content);
    }
}
