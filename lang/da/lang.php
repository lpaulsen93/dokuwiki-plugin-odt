<?php

/**
 * Danish language file.
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @package    DokuWiki\lang\da\lang
 * Translations 
 *
 * @author Jacob Palm <jacobpalmdk@icloud.com>
 * @author Søren Birk <soer9648@eucl.dk>
 */
$lang['encoding']              = 'utf-8';
$lang['direction']             = 'ltr';
$lang['view']                  = 'Eksportér side til Open Document format';
$lang['export_odt_button']     = 'ODT-eksport';
$lang['export_odt_pdf_button'] = 'ODT=>PDF eksport';
$lang['tpl_not_found']         = 'ADVARSEL : ODT-skabelonen "%s" blev ikke fundet i skabelonmappen "%s". Benytter standardskabelon.';
$lang['toc_title']             = 'Indholdsfortegnelse';
$lang['chapter_title']         = 'Kapiteloversigt';
$lang['toc_msg']               = 'En inholdsfortegnelse vil blive indsat her.';
$lang['chapter_msg']           = 'En kapiteloversigt vil blive indsat her.';
$lang['update_toc_msg']        = 'Husk at opdatere indholdsfortegnelsen efter eksport.';
$lang['update_chapter_msg']    = 'Husk at opdatere kapiteloversigten efter eksport.';
$lang['needtitle']             = 'Angiv venligst en titel.';
$lang['needns']                = 'Angiv venligst et eksisterende navnerum.';
$lang['empty']                 = 'Du har endnu ikke valgt sider.';
$lang['forbidden']             = 'Du har ikke adgang til følgende sider: %s.<br/><br/>Brug indstillingen "Spring forbudte sider over" for at oprette din eksport kun med de sider der er adgang til.';
$lang['conversion_failed_msg'] = '====== En fejl opstod under konvertering af ODT dokumentet: ======

Eksekveret kommando:

<code>%command%</code>

Fejlkode: %errorcode%

Fejlbeskrivelse:

<code>%errormessage%</code>

[[%pageid%|Tilbage til forrige sider]]';
$lang['init_failed_msg']       = '====== En fejl opstod under initialisering af ODT dokumentet ======

Er din DokuWiki version kompatibel med ODT udvidelsen?

Siden version 2017-02-11 har ODT udvidelsen krævet DokuWiki "Detritus" eller nyere.
For en detaljeret oversigt over krav, se sektionen [[https://www.dokuwiki.org/plugin:odt#requirements|Requirements]] i udvidelses dokumentation på DokuWiki.org.';
