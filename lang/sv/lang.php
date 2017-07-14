<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author Tor Härnqvist <tor@harnqvist.se>
 */
$lang['encoding']              = 'utf-8';
$lang['direction']             = 'ltr';
$lang['view']                  = 'Exportera sida till Open Document-format';
$lang['export_odt_button']     = 'ODT-export';
$lang['export_odt_pdf_button'] = 'ODT=>PDF-export';
$lang['tpl_not_found']         = 'FEL : ODT-templatet "%s" kunde inte hittas i templatförteckningen "%s". Exporten avbröts.';
$lang['toc_title']             = 'Innehållsförteckning';
$lang['chapter_title']         = 'Kapitelförteckning';
$lang['toc_msg']               = 'En innehållsförteckning kommer att införas här.';
$lang['chapter_msg']           = 'En förteckning över kapitel kommer att införas här.';
$lang['update_toc_msg']        = 'Var god kom ihåg att uppdatera innehållsförteckningen efter export.';
$lang['update_chapter_msg']    = 'Var god kom ihåg att uppdatera kapitelförteckningen efter export.';
$lang['needtitle']             = 'Fyll i en rubrik.';
$lang['needns']                = 'Fyll i en existerande namnrymd.';
$lang['empty']                 = 'Du har inte valt några sidor än.';
$lang['forbidden']             = 'Du har ingen åtkomst till dessa sidor: %s.<br/><br/>Använd inställningen \'Skippa förbjudna sidor (Skip Forbidden Pages)\' för att skapa din bok med de tillgängliga sidorna.';
$lang['conversion_failed_msg'] = '====== Ett fel inträffade under konvertering av ODT-dokumentet: ======

Utfört i kommandotolk:

<code>%command%</code>

Felkod: %errorcode%

Felmeddelande:

<code>%errormessage%</code>

[[%pageid%|Tillbaka till föregående sida]]
';
$lang['init_failed_msg']       = '====== Ett fel inträffade vid initiering ODT-dokumentet: ======

Är din DokuWiki-version kompatibelt med ODT-pluginet?

Efter utgåvan 2017-02-11 kräver ODT-pluginet DokuWiki-utgåva “Detritus” eller senare!
För detaljerad kravspecifikation var god läs [[https://www.dokuwiki.org/plugin:odt#requirements|avsnittet Krav "Requirements"]] på ODT-pluginsidan på DokuWiki.org.

(Din DokuWiki-utgåva är %DWVERSION%)';
