<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author Florian Lamml <info@florian-lamml.de>
 */
$lang['encoding']              = 'utf-8';
$lang['direction']             = 'ltr';
$lang['view']                  = 'Ins Open-Document-Format exportieren';
$lang['export_odt_button']     = 'ODT exportieren';
$lang['tpl_not_found']         = 'FEHLER: Die ODT Vorlage "%s" konnte im Vorlagenverzeichnis "%s" nicht gefunden werden. Der Export wurde abgebrochen.';
$lang['toc_title']             = 'Inhaltsverzeichnis';
$lang['chapter_title']         = 'Kapitelinhalt';
$lang['toc_msg']               = 'Ein Inhaltsverzeichnis wird an dieser Stelle eingefügt.';
$lang['chapter_msg']           = 'Ein Kapitelverzeichnis wird an dieser Stelle eingefügt.';
$lang['update_toc_msg']        = 'Bitte vergessen Sie nicht, das Inhaltsverzeichnis nach dem Export zu aktualisieren.';
$lang['update_chapter_msg']    = 'Bitte vergessen Sie nicht, das Kapitelverzeichnis nach dem Export zu aktualisieren.';
$lang['needtitle']             = 'Bitte Titel angeben!';
$lang['needns']                = 'Bitte geben Sie einen vorhandenen Namensraum an.';
$lang['empty']                 = 'Sie haben noch keine Seiten gewählt.';

// Error message for failed conversion.
// The following replacments are supported:
// %command%      = the complete command line which was executed
// %errorcode%    = the error code reported after executing the command
// %errormessage% = the detailed error message reported after executing the command
$lang['conversion_failed_msg'] =
'====== Bei der Konvertierung des ODT-Dokuments ist ein Fehler aufgetreten: ======

Ausgeführte Kommando-Zeile:

<code>%command%</code>

Fehlerwert: %errorcode%

Fehlerausgabe:

<code>%errormessage%</code>

[[%pageid%|Zurück zur vorherigen Seite]]';
