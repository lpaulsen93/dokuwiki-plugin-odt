<?php
/**
 * English language file.
 * 
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author  Viktor Kristian <vkristian@gmail.com>
 * @package DokuWiki\lang\en\lang
 */

// settings must be present and set appropriately for the language
$lang['encoding']   = 'utf-8';
$lang['direction']  = 'ltr';

// export button
$lang['view'] = 'Exportovať stránku do formátu Open Document';
$lang['export_odt_button'] = 'Export do ODT';
$lang['export_odt_pdf_button'] = 'Export ODT=>PDF';

// template not found in the directory
$lang['tpl_not_found'] = 'CHYBA : ODT šablóna "%s" nebola nájdená v adresári so šablónami "%s". Export je prerušený.';

// default TOC and chapter index title
$lang['toc_title']          = 'Obsah';
$lang['chapter_title']      = 'Index kapitol';
$lang['toc_msg']            = 'Sem bude vložený obsah.';
$lang['chapter_msg']        = 'Sem bude vložený index kapitol.';
$lang['update_toc_msg']     = 'Prosím nezabudnite po exporte aktualizovať obsah.';
$lang['update_chapter_msg'] = 'Prosím nezabudnite po exporte aktualizovať index kapitol.';

$lang['needtitle']         = 'Prosím izadajte nadpis.';
$lang['needns']            = 'Prosím zadajte existujúci menný priestor.';
$lang['empty']             = "Zatiaľ ste nevybrali žiadne stránky.";
$lang['forbidden']         = "Nemáte prístup k nasledujúcim stránkam: %s.<br/><br/>Na vytvorenie knihy obsahujúcej dostupné stránky prosím použite možnosť 'Preskočiť neprístupné stránky'.";

// Error message for failed conversion.
// The following replacments are supported:
// %command%      = the complete command line which was executed
// %errorcode%    = the error code reported after executing the command
// %errormessage% = the detailed error message reported after executing the command
$lang['conversion_failed_msg'] =
'====== Počas konverzie do formátu ODT nastala chyba: ======

Vykonávaný príkaz:

<code>%command%</code>

Kód chyby: %errorcode%

Chybová správa:

<code>%errormessage%</code>

[[%pageid%|Vrátiť sa na predchádzajúcu stránku]]';

// Error message for failed conversion.
$lang['init_failed_msg'] =
'====== Počas konverzie do formátu ODT nastala chyba: ======

Je vaša verzia DokuWiki kompatibilná s ODT pluginom?

Od verzie pluginu 2017-02-11 a vyššej sa vyžaduje verziu DokuWiki "Detritus" a novšej!
Detailné informácie o požiadavkach nájdete v sekcii [[https://www.dokuwiki.org/plugin:odt#requirements|"Requirements section"]] stránok ODS pluginu na DokuWiki.org.

(Používate verziu DokuWiki %DWVERSION%)';
