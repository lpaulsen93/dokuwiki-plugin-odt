<?php
/**
 * ODT Plugin: Exports to ODT
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Aurelien Bompard <aurelien@bompard.org>
 */
// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

//require_once DOKU_PLUGIN . 'odt/helper/cssimport.php';
//require_once DOKU_PLUGIN . 'odt/ODT/ODTDefaultStyles.php';

// Central class for ODT export
//require_once DOKU_PLUGIN . 'odt/ODT/ODTDocument.php';

/**
 * The Page Renderer (for PDF format)
 * 
 * @package DokuWiki\Renderer\Page
 */
class renderer_plugin_odt_pagepdf extends renderer_plugin_odt_page {

    /**
     * Constructor. Loads helper plugins.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Returns the format produced by this renderer.
     */
    function getFormat(){
        return "odt_pdf";
    }
    
    /**
     * Initialize the rendering
     */
    function document_start() {
        global $ID;

        // Initialize the document
        $this->document_setup();

        // Create HTTP headers
        $output_filename = str_replace(':','-',$ID).'.pdf';
        $headers = array(
            'Content-Type' => 'application/pdf',
            'Cache-Control' => 'must-revalidate, no-transform, post-check=0, pre-check=0',
            'Pragma' => 'public',
            'Content-Disposition' => 'attachment; filename="'.$output_filename.'";',
        );

        // store the content type headers in metadata
        p_set_metadata($ID,array('format' => array('odt_pagepdf' => $headers) ));
    }

    /**
     * Closes the document
     */
    function document_end(){        
        parent::document_end();
        $this->convert();
    }

    /**
     * Convert exported ODT file if required.
     * Supported formats: pdf
     */
    protected function convert () {
        global $ID;
                
        $format = $this->config->getConvertTo ();
        if ($format == 'pdf') {
            // Prepare temp directory
            $temp_dir = $this->config->getParam('tmpdir');
            $temp_dir = $temp_dir."/odt/".str_replace(':','-',$ID);
            if (is_dir($temp_dir)) { io_rmdir($temp_dir,true); }
            io_mkdir_p($temp_dir);

            // Set source and dest file path
            $file = $temp_dir.'/convert.odt';
            $pdf_file = $temp_dir.'/convert.pdf';

            // Prepare command line
            $command = $this->config->getParam('convert_to_pdf');
            $command = str_replace('%outdir%', $temp_dir, $command);
            $command = str_replace('%sourcefile%', $file, $command);

            // Convert file
            io_saveFile($file, $this->doc);
            exec ($command, $output, $result);
            if ($result) {
                $errormessage = '';
                foreach ($output as $line) {
                    $errormessage .= $this->_xmlEntities($line);
                }
                $message = $this->getLang('conversion_failed_msg');
                $message = str_replace('%command%', $command, $message);
                $message = str_replace('%errorcode%', $result, $message);
                $message = str_replace('%errormessage%', $errormessage, $message);
                $message = str_replace('%pageid%', $ID, $message);
                
                $instructions = p_get_instructions($message);
                $this->doc = p_render('xhtml', $instructions, $info);

                $headers = array(
                    'Content-Type' =>  'text/html; charset=utf-8',
                );
                p_set_metadata($ID,array('format' => array('odt_pagepdf' => $headers) ));
            } else {
                $this->doc = io_readFile($pdf_file, false);
            }
            io_rmdir($temp_dir,true);
        }
    }
}

//Setup VIM: ex: et ts=4 enc=utf-8 :
