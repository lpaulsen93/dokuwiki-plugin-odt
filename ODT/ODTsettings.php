<?php

/**
 * ODTSettings: class for maintaining the settings data of an ODT document.
 *              Code was previously included in renderer.php.
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Aurelien Bompard <aurelien@bompard.org>
 * @author LarsDW223
 */
class ODTSettings
{
    var $settings = null;

    /**
     * Constructor. Set initial meta data.
     */
    public function __construct() {
        $this->settings  = '<' . '?xml version="1.0" encoding="UTF-8"?' . ">\n";
        $this->settings .= '<office:document-settings xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:config="urn:oasis:names:tc:opendocument:xmlns:config:1.0" xmlns:ooo="http://openoffice.org/2004/office" office:version="1.2"><office:settings><config:config-item-set config:name="dummy-settings"><config:config-item config:name="MakeValidatorHappy" config:type="boolean">true</config:config-item></config:config-item-set></office:settings></office:document-settings>';
    }

    /**
     * Returns the complete manifest content.
     */
    function getContent(){
        return $this->settings;
    }
}
