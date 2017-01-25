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
 * 
 * @package helper\config
 */
class helper_plugin_odt_config extends DokuWiki_Plugin {
    /** @var array Central storage for config parameters. */
    protected $config = array();
    protected $mode = null;
    protected $messages = null;
    protected $convert_to = null;

    /**
     * @return array
     */
    function getMethods() {
        $result = array();
        $result[] = array(
                'name'   => 'setParam',
                'desc'   => 'set config param $name to $value.',
                'params' => array('name' => 'string', 'value' => 'mixed'),
                );
        $result[] = array(
                'name'   => 'getParam',
                'desc'   => 'returns the current value for config param $value',
                'params' => array('name' => 'string'),
                'return' => array('value' => 'mixed'),
                );
        $result[] = array(
                'name'   => 'isParam',
                'desc'   => 'Is $name a known config param?',
                'params' => array('name' => 'string'),
                'return' => array('isParam' => 'bool'),
                );
        $result[] = array(
                'name'   => 'isRefreshable',
                'desc'   => 'Is $name a refreshable config param?',
                'params' => array('name' => 'string'),
                'return' => array('isRefreshable' => 'bool'),
                );
        $result[] = array(
                'name'   => 'hasDWGlobalSetting',
                'desc'   => 'Does param $name have a corresponding global DW param to inherit from?',
                'params' => array('name' => 'string'),
                'return' => array('hasDWGlobalSetting' => 'bool'),
                );
        $result[] = array(
                'name'   => 'isGlobalSetting',
                'desc'   => 'Does param $name have a global config setting?',
                'params' => array('name' => 'string'),
                'return' => array('isGlobalSetting' => 'bool'),
                );
        $result[] = array(
                'name'   => 'isURLSetting',
                'desc'   => 'Does param $name have a URL setting?',
                'params' => array('name' => 'string'),
                'return' => array('isURLSetting' => 'bool'),
                );
        $result[] = array(
                'name'   => 'isMetaSetting',
                'desc'   => 'Does param $name have a Metadata setting?',
                'params' => array('name' => 'string'),
                'return' => array('isMetaSetting' => 'bool'),
                );
        $result[] = array(
                'name'   => 'addingToMetaIsAllowed',
                'desc'   => 'Is it allowed to add $name (at position $pos) to the meta data?',
                'params' => array('name' => 'string', 'pos' => 'integer'),
                'return' => array('addingAllowed' => 'bool'),
                );
        $result[] = array(
                'name'   => 'load',
                'desc'   => 'Load the corrent settings from the global config, URL params or syntax tags/meta data',
                'params' => array('warnings' => 'string'),
                'return' => array('mode' => 'string'),
                );
        $result[] = array(
                'name'   => 'refresh',
                'desc'   => 'Refresh the corrent settings from the global config, URL params or syntax tags/meta data',
                );
        $result[] = array(
                'name'   => 'hash',
                'desc'   => 'Get MD5 hash of currently stored settings.',
                'return' => array('hash' => 'string'),
                );
        return $result;
    }

    /**
     * Constructor. Loads helper plugins.
     */
    public function __construct() {
        // Set up empty array with known config parameters

        // Option 'dformat', taken from global value.
        $this->config ['dformat'] =
            array('value'              => NULL,
                  'DWGlobalName'       => 'dformat',
                  'hasGlobal'          => false,
                  'hasURL'             => false,
                  'hasMeta'            => false,
                  'addMetaAtStartOnly' => false,
                  'refresh'            => false);
        // Option 'useheading', taken from global value.
        $this->config ['useheading'] =
            array('value'              => NULL,
                  'DWGlobalName'       => 'useheading',
                  'hasGlobal'          => false,
                  'hasURL'             => false,
                  'hasMeta'            => false,
                  'addMetaAtStartOnly' => false,
                  'refresh'            => false);
        // Temp directory, taken from global value.
        $this->config ['tmpdir'] =
            array('value'              => NULL,
                  'DWGlobalName'       => 'tmpdir',
                  'hasGlobal'          => false,
                  'hasURL'             => false,
                  'hasMeta'            => false,
                  'addMetaAtStartOnly' => false,
                  'refresh'            => false);
        // Media directory, taken from global value.
        $this->config ['mediadir'] =
            array('value'              => NULL,
                  'DWGlobalName'       => 'mediadir',
                  'hasGlobal'          => false,
                  'hasURL'             => false,
                  'hasMeta'            => false,
                  'addMetaAtStartOnly' => false,
                  'refresh'            => false);
        // Data directory, taken from global value.
        $this->config ['datadir'] =
            array('value'              => NULL,
                  'DWGlobalName'       => 'datadir',
                  'hasGlobal'          => false,
                  'hasURL'             => false,
                  'hasMeta'            => false,
                  'addMetaAtStartOnly' => false,
                  'refresh'            => false);
        // Save directory, taken from global value.
        $this->config ['savedir'] =
            array('value'              => NULL,
                  'DWGlobalName'       => 'savedir',
                  'hasGlobal'          => false,
                  'hasURL'             => false,
                  'hasMeta'            => false,
                  'addMetaAtStartOnly' => false,
                  'refresh'            => false);
        // Option 'showexportbutton'
        $this->config ['showexportbutton'] =
            array('value'              => NULL,
                  'DWGlobalName'       => NULL,
                  'hasGlobal'          => true,
                  'hasURL'             => false,
                  'hasMeta'            => false,
                  'addMetaAtStartOnly' => false,
                  'refresh'            => false);
        // Option 'showpdfexportbutton'
        $this->config ['showpdfexportbutton'] =
            array('value'              => NULL,
                  'DWGlobalName'       => NULL,
                  'hasGlobal'          => true,
                  'hasURL'             => false,
                  'hasMeta'            => false,
                  'addMetaAtStartOnly' => false,
                  'refresh'            => false);
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
        // Template =ODT template, old parameter,
        // included for backwards compatibility.
        $this->config ['template'] =
            array('value'              => NULL,
                  'DWGlobalName'       => NULL,
                  'hasGlobal'          => true,
                  'hasURL'             => true,
                  'hasMeta'            => true,
                  'addMetaAtStartOnly' => false,
                  'refresh'            => false);
        // CSS usage.
        $this->config ['css_usage'] =
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
        // Standard font size for CSS import = value for 1em/100%
        $this->config ['css_font_size'] =
            array('value'              => NULL,
                  'DWGlobalName'       => NULL,
                  'hasGlobal'          => true,
                  'hasURL'             => true,
                  'hasMeta'            => true,
                  'addMetaAtStartOnly' => false,
                  'refresh'            => false);
        // Apply CSS font size to ODT template styles/scratch styles
        $this->config ['apply_fs_to_non_css'] =
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
        // Index display in browser
        $this->config ['index_in_browser'] =
            array('value'              => NULL,
                  'DWGlobalName'       => NULL,
                  'hasGlobal'          => true,
                  'hasURL'             => true,
                  'hasMeta'            => true,
                  'addMetaAtStartOnly' => false,
                  'refresh'            => false);
        // Index display in browser
        $this->config ['outline_list_style'] =
            array('value'              => NULL,
                  'DWGlobalName'       => NULL,
                  'hasGlobal'          => true,
                  'hasURL'             => true,
                  'hasMeta'            => true,
                  'addMetaAtStartOnly' => false,
                  'refresh'            => false);
        // Command line template for pdf conversion
        $this->config ['convert_to_pdf']  =
            array('value'              => NULL,
                  'DWGlobalName'       => NULL,
                  'hasGlobal'          => true,
                  'hasURL'             => true,
                  'hasMeta'            => false,
                  'addMetaAtStartOnly' => false,
                  'refresh'            => false);
        // List-Label-Alignment (ordered lists)
        $this->config ['olist_label_align'] =
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
     * @param string $name Name of the config param
     * @param string $value Value to be set
     */
    public function setParam($name, $value) {
        if (!empty($name)) {
            $this->config [$name]['value'] = $value;
        }
    }

    /**
     * Get a config parameter.
     *
     * @param string $name Name of the config param
     * @return mixed Current value of param $name
     */
    public function getParam($name) {
        return $this->config [$name]['value'];
    }

    /**
     * Is the $name specified the name of a ODT plugin config parameter?
     *
     * @param string $name Name of the config param
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
     * @param string $name Name of the config param
     * @return bool is refreshable
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
     * @param string $name Name of the config param
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
     * @param string $name Name of the config param
     * @return bool is global setting
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
     * @param string $name Name of the config param
     * @return bool is URL Setting
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
     * @param string $name Name of the config param
     * @return bool is Meta Setting
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
     * @param string $name Name of the config param
     * @param string $pos  Poistion in wiki page
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

        if ( $this->mode == null ) {
            $this->mode = 'scratch';
        }

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
                // (old parameter)
                $template = $this->getParam ('template');
                if ( $name == 'template' && !empty($template) ) {
                    // ODT-Template chosen
                    if (file_exists($this->getParam('mediadir').'/'.$this->getParam('tpl_dir')."/".$this->getParam ('template'))) {
                        //template found
                        $this->mode = 'ODT template';
                    } else {
                        // template chosen but not found : warn the user and use the default template
                        $warning = sprintf($this->getLang('tpl_not_found'),$this->getParam ('template'),$this->getParam ('tpl_dir'));
                        $this->messages .= $warning;
                    }
                }

                // ODT-Template based export required?
                $odt_template = $this->getParam ('odt_template');
                if ( $name == 'odt_template' && !empty($odt_template) ) {
                    // ODT-Template chosen
                    if (file_exists($this->getParam('mediadir').'/'.$this->getParam('tpl_dir')."/".$this->getParam ('odt_template'))) {
                        // Template found: ODT or CSS?
                        if ( strpos ($odt_template, '.css') === false ) {
                            $this->mode = 'ODT template';
                        } else {
                            $this->mode = 'CSS template';
                        }
                    } else {
                        // template chosen but not found : warn the user and use the default template
                        $warning = sprintf($this->getLang('tpl_not_found'),$this->getParam ('odt_template'),$this->getParam ('tpl_dir'));
                    }
                }

                // Convert Yes/No-String in 'disable_links' to boolean.
                if ( $name == 'disable_links' ) {
                    $temp = $this->getParam ('disable_links');
                    if ( strcasecmp($temp, 'Yes') != 0 && $temp !== true ) {
                        $this->setParam ('disable_links', false);
                    } else {
                        $this->setParam ('disable_links', true);
                    }
                }

                // Convert Yes/No-String in 'toc_pagebreak' to boolean.
                if ( $name == 'toc_pagebreak' ) {
                    $temp = $this->getParam ('toc_pagebreak');
                    if ( strcasecmp($temp, 'Yes') == 0 || $temp === true ) {
                        $this->setParam ('toc_pagebreak', true);
                    } else {
                        $this->setParam ('toc_pagebreak', false);
                    }
                }
            }
        }

        $template = $this->getParam ('template');
        $odt_template = $this->getParam ('odt_template');
        if (!empty($template) && empty($odt_template)) {
            $this->setParam ('odt_template', $this->getParam ('template'));
        }

        return $this->mode;
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

    /**
     * Get warning messages from loading the config which
     * can be presented to the user.
     *
     * @return string Collected messages.
     */
    public function getMessages() {
        return $this->messages;
    }

    /**
     * Set conversion option.
     *
     * @param string $format Conversion format (e.g. 'pdf')
     */
    public function setConvertTo($format) {
        $this->convert_to = $format;
    }

    /**
     * Set conversion option.
     *
     * @return string Currently set conversion option
     *                or NULL (output ODT format)
     */
    public function getConvertTo() {
        return ($this->convert_to);
    }
}
