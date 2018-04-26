<?php
/**
 * ODT export Plugin component. Mainly based at dw2pdf export action plugin component.
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Luigi Micco <l.micco@tiscali.it>
 * @author     Andreas Gohr <andi@splitbrain.org>
 * @author     Gerrit Uitslag <klapinklapin@gmail.com>
 */

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

use dokuwiki\Action\Exception\ActionException;
use dokuwiki\Action\Exception\ActionAbort;

/**
 * Class action_plugin_odt_export
 *
 * Collect pages and export these. GUI is available via bookcreator.
 * 
 * @package DokuWiki\Action\Export
 */
class action_plugin_odt_export extends DokuWiki_Action_Plugin {
    protected $config = null;

    /**
     * @var array
     */
    protected $list = array();

    /**
     * Register the events
     *
     * @param Doku_Event_Handler $controller
     */
    public function register(Doku_Event_Handler $controller) {
        $controller->register_hook('ACTION_ACT_PREPROCESS', 'BEFORE', $this, 'convert', array());
        $controller->register_hook('TEMPLATE_PAGETOOLS_DISPLAY', 'BEFORE', $this, 'addbutton_odt', array());
        $controller->register_hook('TEMPLATE_PAGETOOLS_DISPLAY', 'BEFORE', $this, 'addbutton_pdf', array());
        $controller->register_hook('MENU_ITEMS_ASSEMBLY', 'AFTER', $this, 'addbutton_odt_new', array());
        $controller->register_hook('MENU_ITEMS_ASSEMBLY', 'AFTER', $this, 'addbutton_pdf_new', array());
    }

    /**
     * Add 'export odt'-button to pagetools
     *
     * @param Doku_Event $event
     */
    public function addbutton_odt(Doku_Event $event) {
        global $ID, $REV;

        if($this->getConf('showexportbutton') && $event->data['view'] == 'main') {
            $params = array('do' => 'export_odt');
            if($REV) {
                $params['rev'] = $REV;
            }

            // insert button at position before last (up to top)
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
     * Add 'export odt=>pdf'-button to pagetools
     *
     * @param Doku_Event $event
     */
    public function addbutton_pdf(Doku_Event $event) {
        global $ID, $REV;

        if($this->getConf('showpdfexportbutton') && $event->data['view'] == 'main') {
            $params = array('do' => 'export_odt_pdf');
            if($REV) {
                $params['rev'] = $REV;
            }

            // insert button at position before last (up to top)
            $event->data['items'] = array_slice($event->data['items'], 0, -1, true) +
                array('export_odt_pdf' =>
                          '<li>'
                          . '<a href="' . wl($ID, $params) . '"  class="action export_odt_pdf" rel="nofollow" title="' . $this->getLang('export_odt_pdf_button') . '">'
                          . '<span>' . $this->getLang('export_odt_pdf_button') . '</span>'
                          . '</a>'
                          . '</li>'
                ) +
                array_slice($event->data['items'], -1, 1, true);
        }
    }

    /**
     * Add 'export odt' button to page tools, new SVG based mechanism
     *
     * @param Doku_Event $event
     */
    public function addbutton_odt_new(Doku_Event $event) {
        if($event->data['view'] != 'page') return;
        if($this->getConf('showexportbutton')) {
            array_splice($event->data['items'], -1, 0, [new \dokuwiki\plugin\odt\MenuItemODT()]);
        }
    }

    /**
     * Add 'export odt pdf' button to page tools, new SVG based mechanism
     *
     * @param Doku_Event $event
     */
    public function addbutton_pdf_new(Doku_Event $event) {
        if($event->data['view'] != 'page') return;
        if($this->getConf('showpdfexportbutton')) {
            array_splice($event->data['items'], -1, 0, [new \dokuwiki\plugin\odt\MenuItemODTPDF()]);
        }
    }

    /***********************************************************************************
     *  Book export                                                                    *
     ***********************************************************************************/

    /**
     * Do article(s) to ODT conversion work
     *
     * @param Doku_Event $event
     * @return bool
     */
    public function convert(Doku_Event $event) {
        global $ID;
        $format = NULL;

        $action = act_clean($event->data);

        // Any kind of ODT export?
        $odt_export = false;
        if (strncmp($action, 'export_odt', strlen('export_odt')) == 0) {
            $odt_export = true;
        }

        // check conversion format
        if ($odt_export && strpos($action, '_pdf') !== false) {
            $format = 'pdf';
        }

        // single page export:
        // rename action to the actual renderer component
        if($action == 'export_odt') {
            $event->data = 'export_odt_page';
        } else if ($action == 'export_odt_pdf') {
            $event->data = 'export_odt_pagepdf';
        }

        if( !is_array($action) && $odt_export ) {
            // On export to ODT load config helper if not done yet
            // and stop on errors.
            if ( $this->config == NULL ) {
                $this->config = plugin_load('helper', 'odt_config');
                $this->config->load($warning);

                if (!empty($warning)) {
                    $this->showPageWithErrorMsg($event, NULL, $warning);
                    return false;
                }
            }
            $this->config->setConvertTo($format);
        }

        // the book export?
        if(($action != 'export_odtbook') && ($action != 'export_odtns')) return false;

        // check user's rights
        if(auth_quickaclcheck($ID) < AUTH_READ) return false;

        if($data = $this->collectExportPages($event)) {
            list($title, $this->list) = $data;
        } else {
            return false;
        }

        // it's ours, no one else's
        $event->preventDefault();

        // prepare cache and its dependencies
        $depends = array();
        $cache = $this->prepareCache($title, $depends);

        // hard work only when no cache available
        if(!$this->getConf('usecache') || !$cache->useCache($depends)) {
            $this->generateODT($cache->cache, $title);
        }

        // deliver the file
        $this->sendODTFile($cache->cache, $title);
        return true;
    }


    /**
     * Obtain list of pages and title, based on url parameters
     *
     * @param Doku_Event $event
     * @return string|bool
     */
    protected function collectExportPages(Doku_Event $event) {
        global $ID;
        global $INPUT;

        // Load config helper if not done yet
        if ( $this->config == NULL ) {
            $this->config = plugin_load('helper', 'odt_config');
            $this->config->load($warning);
        }

        // list of one or multiple pages
        $list = array();

        $action = $event->data;
        if($action == 'export_odt') {
            $list[0] = $ID;
            $title = $INPUT->str('book_title');
            if(!$title) {
                $title = p_get_first_heading($ID);
            }

        } elseif($action == 'export_odtns') {
            //check input for title and ns
            if(!$title = $INPUT->str('book_title')) {
                $this->showPageWithErrorMsg($event, 'needtitle');
                return false;
            }
            $docnamespace = cleanID($INPUT->str('book_ns'));
            if(!@is_dir(dirname(wikiFN($docnamespace . ':dummy')))) {
                $this->showPageWithErrorMsg($event, 'needns');
                return false;
            }

            //sort order
            $order = $INPUT->str('book_order', 'natural', true);
            $sortoptions = array('pagename', 'date', 'natural');
            if(!in_array($order, $sortoptions)) {
                $order = 'natural';
            }

            //search depth
            $depth = $INPUT->int('book_nsdepth', 0);
            if($depth < 0) {
                $depth = 0;
            }

            //page search
            $result = array();
            $opts = array('depth' => $depth); //recursive all levels
            $dir = utf8_encodeFN(str_replace(':', '/', $docnamespace));
            search($result, $this->config->getParam('datadir'), 'search_allpages', $opts, $dir);

            //sorting
            if(count($result) > 0) {
                if($order == 'date') {
                    usort($result, array($this, '_datesort'));
                } elseif($order == 'pagename') {
                    usort($result, array($this, '_pagenamesort'));
                }
            }

            foreach($result as $item) {
                $list[] = $item['id'];
            }

        } elseif(isset($_COOKIE['list-pagelist']) && !empty($_COOKIE['list-pagelist'])) {
            // Here is $action == 'export_odtbook'

            /** @deprecated  April 2016 replaced by localStorage version of Bookcreator*/

            //is in Bookmanager of bookcreator plugin a title given?
            if(!$title = $INPUT->str('book_title')) {
                $this->showPageWithErrorMsg($event, 'needtitle');
                return false;
            } else {
                $list = explode("|", $_COOKIE['list-pagelist']);
            }

        } elseif($INPUT->has('selection')) {
            //handle Bookcreator requests based at localStorage
//            if(!checkSecurityToken()) {
//                http_status(403);
//                print $this->getLang('empty');
//                exit();
//            }

            $json = new JSON(JSON_LOOSE_TYPE);
            $list = $json->decode($INPUT->post->str('selection', '', true));
            if(!is_array($list) || empty($list)) {
                http_status(400);
                print $this->getLang('empty');
                exit();
            }

            $title = $INPUT->str('pdfbook_title'); //DEPRECATED
            $title = $INPUT->str('book_title', $title, true);
            if(empty($title)) {
                http_status(400);
                print $this->getLang('needtitle');
                exit();
            }

        } else {
            //show empty bookcreator message
            $this->showPageWithErrorMsg($event, 'empty');
            return false;
        }

        $list = array_map('cleanID', $list);

        $skippedpages = array();
        foreach($list as $index => $pageid) {
            if(auth_quickaclcheck($pageid) < AUTH_READ) {
                $skippedpages[] = $pageid;
                unset($list[$index]);
            }
        }
        $list = array_filter($list); //removes also pages mentioned '0'

        //if selection contains forbidden pages throw (overridable) warning
        if(!$INPUT->bool('book_skipforbiddenpages') && !empty($skippedpages)) {
            $msg = sprintf($this->getLang('forbidden'), hsc(join(', ', $skippedpages)));
            if($INPUT->has('selection')) {
                http_status(400);
                print $msg;
                exit();
            } else {
                $this->showPageWithErrorMsg($event, null, $msg);
                return false;
            }

        }

        return array($title, $list);
    }


    /**
     * Set error notification and reload page again
     *
     * @param Doku_Event $event
     * @param string     $msglangkey key of translation key
     */
    private function showPageWithErrorMsg(Doku_Event $event, $msglangkey, $translatedMsg=NULL) {
        if (!empty($msglangkey)) {
            // Message need to be translated.
            msg($this->getLang($msglangkey), -1);
        } else {
            // Message already has been translated.
            msg($translatedMsg, -1);
        }

        $event->data = 'show';
        $_SERVER['REQUEST_METHOD'] = 'POST'; //clears url
    }

    /**
     * Prepare cache
     *
     * @param string $title
     * @param array  $depends (reference) array with dependencies
     * @return cache
     */
    protected function prepareCache($title, &$depends) {
        global $REV;
        global $INPUT;

        //different caches for varying config settings
        $template = $this->getConf("odt_template");
        $template = $INPUT->get->str('odt_template', $template, true);


        $cachekey = join(',', $this->list)
            . $REV
            . $template
            . $title;
        $cache = new cache($cachekey, '.odt');

        $dependencies = array();
        foreach($this->list as $pageid) {
            $relations = p_get_metadata($pageid, 'relation');

            if(is_array($relations)) {
                if(array_key_exists('media', $relations) && is_array($relations['media'])) {
                    foreach($relations['media'] as $mediaid => $exists) {
                        if($exists) {
                            $dependencies[] = mediaFN($mediaid);
                        }
                    }
                }

                if(array_key_exists('haspart', $relations) && is_array($relations['haspart'])) {
                    foreach($relations['haspart'] as $part_pageid => $exists) {
                        if($exists) {
                            $dependencies[] = wikiFN($part_pageid);
                        }
                    }
                }
            }

            $dependencies[] = metaFN($pageid, '.meta');
        }

        $depends['files'] = array_map('wikiFN', $this->list);
        $depends['files'][] = __FILE__;
        $depends['files'][] = dirname(__FILE__) . '/../renderer/page.php';
        $depends['files'][] = dirname(__FILE__) . '/../renderer/book.php';
        $depends['files'][] = dirname(__FILE__) . '/../plugin.info.txt';
        $depends['files'] = array_merge(
            $depends['files'],
            $dependencies,
            getConfigFiles('main')
        );
        return $cache;
    }

    /**
     * Build a ODT from the articles
     *
     * @param string $cachefile
     * @param string $title
     */
    protected function generateODT($cachefile, $title) {
        global $ID;
        global $REV;

        /** @var renderer_plugin_odt_book $odt */
        $odt = plugin_load('renderer','odt_book');

        // store original pageid
        $keep = $ID;

        // loop over all pages
        $xmlcontent = '';
        foreach($this->list as $page) {
            $filename = wikiFN($page, $REV);

            if(!file_exists($filename)) {
                continue;
            }
            // set global pageid to the rendered page
            $ID = $page;
            $xmlcontent .= p_render('odt_book', p_cached_instructions($filename, false, $page), $info);
        }

        //restore ID
        $ID = $keep;

        $odt->doc = $xmlcontent;
        $odt->setTitle($title);
        $odt->finalize_ODTfile();

        // write to cache file
        io_savefile($cachefile, $odt->doc);
    }

    /**
     * @param string $cachefile
     * @param string $title
     */
    protected function sendODTFile($cachefile, $title) {
        header('Content-Type: application/vnd.oasis.opendocument.text');
        header('Cache-Control: must-revalidate, no-transform, post-check=0, pre-check=0');
        header('Pragma: public');
        http_conditionalRequest(filemtime($cachefile));

        $filename = rawurlencode(cleanID(strtr($title, ':/;"', '    ')));
        if($this->getConf('output') == 'file') {
            header('Content-Disposition: attachment; filename="' . $filename . '.odt";');
        } else {
            header('Content-Disposition: inline; filename="' . $filename . '.odt";');
        }

        //Bookcreator uses jQuery.fileDownload.js, which requires a cookie.
        header('Set-Cookie: fileDownload=true; path=/');

        //try to send file, and exit if done
        http_sendfile($cachefile);

        $fp = @fopen($cachefile, "rb");
        if($fp) {
            http_rangeRequest($fp, filesize($cachefile), 'application/vnd.oasis.opendocument.text');
        } else {
            header("HTTP/1.0 500 Internal Server Error");
            print "Could not read file - bad permissions?";
        }
        exit();
    }

    /**
     * Returns array of wiki pages which will be included in the exported document
     *
     * @return array
     */
    public function getExportedPages() {
        return $this->list;
    }

    /**
     * usort callback to sort by file lastmodified time
     *
     * @param array $a
     * @param array $b
     * @return int
     */
    public function _datesort($a, $b) {
        if($b['rev'] < $a['rev']) return -1;
        if($b['rev'] > $a['rev']) return 1;
        return strcmp($b['id'], $a['id']);
    }

    /**
     * usort callback to sort by page id
     *
     * @param array $a
     * @param array $b
     * @return int
     */
    public function _pagenamesort($a, $b) {
        if($a['id'] <= $b['id']) return -1;
        if($a['id'] > $b['id']) return 1;
        return 0;
    }
}
