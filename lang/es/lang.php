<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author Adolfo Jayme Barrientos <fito@libreoffice.org>
 */
$lang['encoding']              = 'utf-8';
$lang['direction']             = 'ltr';
$lang['view']                  = 'Exportar página al formato OpenDocument';
$lang['export_odt_button']     = 'Exportación a ODT';
$lang['export_odt_pdf_button'] = 'Exportación ODT → PDF';
$lang['tpl_not_found']         = 'ERROR: no se encontró la plantilla ODT «%s» en el directorio de plantillas «%s». Se interrumpió la exportación.';
$lang['toc_title']             = 'Sumario';
$lang['chapter_title']         = 'Índice de capítulos';
$lang['toc_msg']               = 'Aquí se insertará un sumario.';
$lang['chapter_msg']           = 'Aquí se insertará un índice de capítulos.';
$lang['update_toc_msg']        = 'Acuérdese de actualizar el sumario después de la exportación.';
$lang['update_chapter_msg']    = 'Acuérdese de actualizar el índice de capítulos después de la exportación.';
$lang['needtitle']             = 'Proporcione un título.';
$lang['needns']                = 'Proporcione un espacio de nombres existente.';
$lang['empty']                 = 'Aún no ha seleccionado ninguna página.';
$lang['forbidden']             = 'No tiene permitido acceder a las páginas siguientes: %s.<br/><br/>Utilice la opción «Omitir páginas prohibidas» para crear el libro con las páginas disponibles.';
$lang['conversion_failed_msg'] = '====== Se produjo un error durante la conversión del documento ODT: ======

Orden ejecutada:

<code>%command%</code>

Código de error: %errorcode%

Mensaje de error:

<code>%errormessage%</code>

[[%pageid%|Volver a la página anterior]]';
$lang['init_failed_msg']       = '====== Se produjo un error durante la inicialización del documento ODT: ======

Revise que su versión de DokuWiki sea compatible con el complemento ODT.

A partir de la versión publicada el 11 de febrero de 2017, el complemento ODT necesita la versión «Detritus» de DokuWiki, o una más reciente.
Para obtener información detallada sobre los requisitos, consulte la  [[https://www.dokuwiki.org/plugin:odt#requirements|sección Requisitos]] de la página del complemento ODT en DokuWiki.org (en inglés).

(Su versión de DokuWiki es %DWVERSION%)';
