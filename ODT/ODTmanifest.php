<?php

/**
 * ODTManifest: class for maintaining the manifest data of an ODT document.
 *              Code was previously included in renderer.php.
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Aurelien Bompard <aurelien@bompard.org>
 * @author LarsDW223
 */
class ODTManifest
{
    var $manifest = array();

    /**
     * Returns the complete manifest content.
     */
    function getContent(){
        $value  =   '<' . '?xml version="1.0" encoding="UTF-8"?' . ">\n";
        $value .=   '<manifest:manifest xmlns:manifest="urn:oasis:names:tc:opendocument:xmlns:manifest:1.0">';
        $value .=   '<manifest:file-entry manifest:media-type="application/vnd.oasis.opendocument.text" manifest:full-path="/"/>';
        $value .=   '<manifest:file-entry manifest:media-type="text/xml" manifest:full-path="settings.xml"/>';
        $value .=   '<manifest:file-entry manifest:media-type="text/xml" manifest:full-path="meta.xml"/>';
        $value .=   '<manifest:file-entry manifest:media-type="text/xml" manifest:full-path="content.xml"/>';
        $value .=   '<manifest:file-entry manifest:media-type="text/xml" manifest:full-path="styles.xml"/>';
        $value .= $this->getExtraContent();
        $value .=   '</manifest:manifest>';
        return $value;
    }

    /**
     * Returns only the xml lines containing the dynamically added user content
     * files like images etc..
     */
    function getExtraContent() {
        $value = '';
        foreach($this->manifest as $path => $type){
            $value .= '<manifest:file-entry manifest:media-type="'.htmlspecialchars($type, ENT_QUOTES, 'UTF-8').
                      '" manifest:full-path="'.htmlspecialchars($path, ENT_QUOTES, 'UTF-8').'"/>';
        }
        return $value;
    }

    /**
     * Checks if $name is present or was added to the manifest data.
     *
     * @param string $name
     * @return bool
     */
    function exists($name) {
        return isset($this->manifest[$name]);
    }

    /**
     * Adds $name with $mime to the manifest data.
     *
     * @param string $name
     * @param string $mime
     */
    function add($name, $mime) {
        $this->manifest[$name] = $mime;
    }
}

