<?php

/**
 * Helper class for ODT tests.
 * This class just includes utility functions and is not performing any tests.
 */
class ODTTestUtils {
    /**
     * This function renders $content using the ODT-page-renderer.
     * It then unzips the ODT document and reads in the file contents
     * of the files 'content.xml', 'meta.xml' and 'styles.xml' and
     * saves the strings in $files ['content-xml'], $files ['meta-xml']
     * and $files ['styles-xml'].
     *
     * @param array       $files
     * @param string      $content
     * @return boolean
     */
    public static function getRenderedODTDocument (array &$files, $content) {
        // Create parser instructions for wiki page $content
        $instructions = p_get_instructions($content);

        // Render the page by looping through the instructions.
        $renderer = new renderer_plugin_odt_page();
        foreach ( $instructions as $instruction ) {
            // Execute the callback against the Renderer
            if(method_exists($renderer, $instruction[0])){
                call_user_func_array(array(&$renderer, $instruction[0]), $instruction[1] ? $instruction[1] : array());
            }
        }

        io_savefile(TMP_DIR.'/odt/temp_test_doc.odt', $renderer->doc);
        $ZIP = new ZipLib();
        $ok = $ZIP->Extract(TMP_DIR.'/odt/temp_test_doc.odt', TMP_DIR.'/odt/unpacked');
        if ($ok == -1 ) {
            // Error unzipping document
            return false;
        }
        
        $files ['content-xml'] = file_get_contents(TMP_DIR.'/odt/unpacked/content.xml');
        $files ['meta-xml'] = file_get_contents(TMP_DIR.'/odt/unpacked/meta.xml');
        $files ['styles-xml'] = file_get_contents(TMP_DIR.'/odt/unpacked/styles.xml');
        
        // Success
        return true;
    }

    /**
     * This function "uploads" all files from $sourcedir to the destination
     * namespace $destns.
     *
     * @param string $destns Desinatio namespace, e.g. 'wiki'
     * @param string $souredir Directory which shall be copied
     * @author LarsDW223
     */
    public static function rcopyMedia($destns, $sourcedir) {
        // Buffer all outputs generated from media_save
        ob_start();

        // Read directory manually and call media_save for each file
        $handle = opendir($sourcedir);
        $read_res = readdir($handle);
        while ($read_res !== false) {
            if ($read_res != '.' && $read_res != '..') {
                $path = $sourcedir.'/'.$read_res;
                $ns = $destns;
                $id = $read_res;
                list($ext,$mime) = mimetype($path);
                $res = media_save(
                    array('name' => $path,
                          'mime' => $mime,
                          'ext'  => $ext),
                    $ns.':'.$id,
                    true,
                    AUTH_UPLOAD,
                    'copy'
                );
            }

            $read_res = readdir($handle);
        }
        closedir($handle);

        // Disable buffering and throw all outputs away
        ob_end_clean();
    }
}
