<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author Petr Kajzar <petr.kajzar@centrum.cz>
 * @author Jaroslav Lichtblau <jlichtblau@seznam.cz>
 * @author Ing. Jan Kaláb <jan+dokuwiki@kalabovi.org>
 * @author Vojta Olsan <olsh0@seznam.cz>
 */
$lang['encoding']              = 'utf-8';
$lang['direction']             = 'ltr';
$lang['view']                  = 'Exportovat stránku ve formátu Open Document';
$lang['export_odt_button']     = 'ODT export';
$lang['export_odt_pdf_button'] = 'Export ODT=>PDF';
$lang['tpl_not_found']         = 'CHYBA : ODT šablona "%s" nebyla nalezena v adresáři se šablonami "%s". Bude použita výchozí šablona.';
$lang['toc_title']             = 'Obsah';
$lang['chapter_title']         = 'Index kapitol';
$lang['toc_msg']               = 'Sem bude vložen obsah.';
$lang['chapter_msg']           = 'Sem bude vložen index kapitol.';
$lang['update_toc_msg']        = 'Prosím nezapomeňte aktualizovat obsah po exportu.';
$lang['update_chapter_msg']    = 'Prosím nezapomeňte aktualizovat index kapitol po exportu.';
$lang['needtitle']             = 'Vlože prosím nadpis.';
$lang['needns']                = 'Vložte prosím existující jmenný prostor.';
$lang['empty']                 = 'Zatím jste nevybrali žádné stránky.';
$lang['forbidden']             = 'K těmto stránkám nemáte přístup: %s.<br/><br/>Použijte volbu „Přeskočit nepřístupné stránky“ a vytvořte svou knihu z dostupných stránek.';
$lang['conversion_failed_msg'] = '====== Při převodu dokumentu ODT došlo k chybě: ======

Spuštěný příkaz:

<code>%command%</code>

Kód chyby: %errorcode%

Chybová zpráva:

<code>%errormessage%</code>

[[%pageid%|Zpět na předchozí stránku]]';
$lang['init_failed_msg']       = '====== Při inicializaci dokumentu ODT došlo k chybě: ======

Je vaše verze DokuWiki kompatibilní s pluginem ODT?

Od vydání 2017-02-11 vyžaduje plugin ODT verzi DokuWiki „Detritus“ nebo novější!
Podrobné požadavky najdete v [[https://www.dokuwiki.org/plugin:odt#requirements|sekci Požadavky]] na stránce pluginu ODT na DokuWiki.org.

(Vaše verze DokuWiki je %DWVERSION%)';
