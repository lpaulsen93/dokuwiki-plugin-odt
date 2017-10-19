<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author Gianfranco <gpd@iol.it>
 */
$lang['encoding']              = 'utf-8';
$lang['direction']             = 'ltr';
$lang['view']                  = 'Esporta pagina in formato Open Document';
$lang['export_odt_button']     = 'esportazione ODT';
$lang['export_odt_pdf_button'] = 'esportazione ODT=>PDF';
$lang['tpl_not_found']         = 'ERRORE : il modello ODT "%s" non è stato trovato nella directory dei modelli "%s". L\'esportazione non è andata a buon fine.';
$lang['toc_title']             = 'Indice';
$lang['chapter_title']         = 'Indice di Capitolo';
$lang['toc_msg']               = 'Un indice sarà inserito in questa posizione.';
$lang['chapter_msg']           = 'Un Indice di Capitolo sarà inserito in questa posizione.';
$lang['update_toc_msg']        = 'Per favore, ricorda di aggiornare l\'indice dopo l\'esportazione.';
$lang['update_chapter_msg']    = 'Per favore, ricorda di aggiornare l\'indice di capitolo dopo l\'esportazione.';
$lang['needtitle']             = 'Indica un titolo.';
$lang['needns']                = 'Indica un namespace già esistente.';
$lang['empty']                 = 'Non hai ancora selezionato pagine.';
$lang['forbidden']             = 'Non hai accesso a queste pagine: %s.<br/><br/>Usa l\'opzione \'Salta Pagine Nascoste\' per creare il tuo libro con le pagine disponibili.';
$lang['conversion_failed_msg'] = '====== Si è verificato un errore durante la conversione del documento ODT: ======

Comando eseguito:

<code>%command%</code>

Codice errore: %errorcode%

Messaggio di errore:

<code>%errormessage%</code>

[[%pageid%|Torna alla pagina precedente]]';
$lang['init_failed_msg']       = '====== Si è verificato un errore durante l\'inizializzazione del documento ODT: ======

La versione del tuo DokuWiki è compatibile con il plugin ODT?

A partire dalla versione 2017-02-11 il plugin ODT richiede la versione DokuWiki “Detritus” o una più recente!
Per informazioni dettagliate sui requisiti vedi la [[https://www.dokuwiki.org/plugin:odt#requirements|sezione Requisiti]] della pagina sul plugin ODT in DokuWiki.org.

(La versione del tuo DokuWiki è %DWVERSION%)';
