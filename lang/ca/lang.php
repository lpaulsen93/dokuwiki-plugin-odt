<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author Adolfo Jayme Barrientos <fito@libreoffice.org>
 */
$lang['encoding']              = 'utf-8';
$lang['direction']             = 'ltr';
$lang['view']                  = 'Exporta la pàgina al format OpenDocument';
$lang['export_odt_button']     = 'Exportació ODT';
$lang['export_odt_pdf_button'] = 'Exportació ODT → PDF';
$lang['tpl_not_found']         = 'ERROR: no s’ha trobat la plantilla ODT «%s» al directori de plantilles «%s». S’ha interromput l’exportació.';
$lang['toc_title']             = 'Taula de continguts';
$lang['chapter_title']         = 'Índex de capítols';
$lang['toc_msg']               = 'Aquí s’inserirà una taula de continguts.';
$lang['chapter_msg']           = 'Aquí s’inserirà un índex de capítols.';
$lang['update_toc_msg']        = 'Recordeu-vos d’actualitzar la taula de continguts després de l’exportació.';
$lang['update_chapter_msg']    = 'Recordeu-vos d’actualitzar l’índex de capítols després de l’exportació.';
$lang['needtitle']             = 'Proporcioneu un títol.';
$lang['needns']                = 'Proporcioneu un espai de noms existent.';
$lang['empty']                 = 'Encara no heu seleccionat cap pàgina.';
$lang['forbidden']             = 'No teniu accés a les pàgines següents: %s.<br/><br/>Feu servir l’opció «Omet les pàgines prohibides» per a crear el llibre amb les pàgines disponibles.';
$lang['conversion_failed_msg'] = '====== S’ha produït un error durant la conversió del document ODT: ======

Línia d’ordres executada:

<code>%command%</code>

Codi d’error: %errorcode%

Missatge d’error:

<code>%errormessage%</code>

[[%pageid%|Torna a la pàgina anterior]]';
$lang['init_failed_msg']       = '====== S’ha produït un error durant la inicialització del document ODT: ======

Comproveu si la versió del DokuWiki és compatible amb el connector ODT.

A partir de la versió de l’11 de febrer del 2017, el connector ODT requereix la versió «Detritus», o una de més recent, del DokuWiki.
Per a informació detallada sobre els requisits, vegeu la [[https://www.dokuwiki.org/plugin:odt#requirements|secció Requisits]] (en anglès) de la pàgina del connector ODT a DokuWiki.org.

(La vostra versió del DokuWiki és %DWVERSION%)';
