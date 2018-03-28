<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @package DokuWiki\lang\nl\lang
 *
 * @author G. Uitslag <klapinklapin@gmail.com>
 * @author Coen Eisma <info@coeneisma.nl>
 * @author Wouter Wijsman <wwijsman@live.nl>
 * @author Arthur Buijs <arthur@artietee.nl>
 * @author Peter van Diest <peter.van.diest@xs4all.nl>
 * @author Marcel Bachus <marcel.bachus@ziggo.nl>
 */
$lang['encoding']              = 'utf-8';
$lang['direction']             = 'ltr';
$lang['view']                  = 'Exporteer pagina naar Open Document formaat';
$lang['export_odt_button']     = 'ODT exporteren';
$lang['export_odt_pdf_button'] = 'ODT=>PDF exporteren';
$lang['tpl_not_found']         = 'WAARSCHUWING : het ODT-sjabloon "%s" is niet aanwezig in de directory voor sjablonen "%s". Het standaardsjabloon zal gebruikt worden.  ';
$lang['toc_title']             = 'Inhoudsopgave';
$lang['chapter_title']         = 'Hoofdstuk Index';
$lang['toc_msg']               = 'Hier zal een inhoudsopgave geplaatst worden.';
$lang['chapter_msg']           = 'Hier zal een hoofdstuk-index geplaatst worden.';
$lang['update_toc_msg']        = 'Denk eraan om de Inhoudsopgave bij te werken na het exporteren.';
$lang['update_chapter_msg']    = 'Denk eraan om de hoofdstuk-index bij te werken na het exporteren.';
$lang['needtitle']             = 'Er moet een titel ingevuld worden';
$lang['needns']                = 'Geef een bestaande namespace.';
$lang['empty']                 = 'Er zijn nog geen pagina\'s geselecteerd.';
$lang['forbidden']             = 'Je hebt geen toegang tot deze pagina\'s: %s.<br/><br/>Gebruik de optie \'Sla verboden pagina\'s over\' om je boek met de beschikbare pagina\'s aan te maken.';
$lang['conversion_failed_msg'] = '====== Een fout tijdens de omzetting van het ODT document: ======

Uitgevoerde commandoregel:

<code>%command%</code>

Foutcode: %errorcode%

Foutmelding:

<code>%errormessage%</code>

[[%pageid%|Terug naar de vorige pagina]]';
$lang['init_failed_msg']       = '====== Een fout tijdens het initialiseren van het ODT document: ======

Is je DokuWiki versie compatibel met de ODT plugin?

Sinds release 2017-02-11 heeft de ODT plugin DokuWiki versie "Detritus" of nieuwer nodig!
Voor gedetailleerde informatie over de vereisten, zie alsjeblieft de 
 [[https://www.dokuwiki.org/plugin:odt#requirements|Requirements sectie]] van de ODT plugin pagina op DokuWiki.org.

(Je DokuWiki versie is %DWVERSION%)';
