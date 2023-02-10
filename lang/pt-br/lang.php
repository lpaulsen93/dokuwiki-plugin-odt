<?php
/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * 
 * @author Eduardo Mozart de Oliveira <eduardomozart182@gmail.com>
 * @package DokuWiki\lang\en\lang
 */

// settings must be present and set appropriately for the language
$lang['encoding']   = 'utf-8';
$lang['direction']  = 'ltr';

// export button
$lang['view'] = 'Exportar página para o formato Open Document';
$lang['export_odt_button'] = 'Exportar para ODT';
$lang['export_odt_pdf_button'] = 'Exportar ODT=>PDF';

// template not found in the directory
$lang['tpl_not_found'] = 'ERRO : o template ODT "%s" não foi encontrado no diretório de templates "%s". A exportação foi abortada.';

// default TOC and chapter index title
$lang['toc_title']          = 'Sumário';
$lang['chapter_title']      = 'Índice de Capítulos';
$lang['toc_msg']            = 'Um Sumário será inserido aqui.';
$lang['chapter_msg']        = 'Um Índice de Capítulos será inserido aqui.';
$lang['update_toc_msg']     = 'Por favor, lembre-se de atualizar o Índice de Capítulos após a exportação.';
$lang['update_chapter_msg'] = 'Por favor, lembre-se de atualizar o Índice de Capítulos após a exportação.';

$lang['needtitle']         = 'Por favor, informe um título.';
$lang['needns']            = 'Por favor, informe um namespace existente.';
$lang['empty']             = "Você não selecionou nenhuma página ainda.";
$lang['forbidden']         = "Você não possuí acesso às páginas: %s.<br/><br/>Use a opção 'Pular Páginas Proibidas' para criar seu livro com as páginas disponíveis.";

// Error message for failed conversion.
// The following replacments are supported:
// %command%      = the complete command line which was executed
// %errorcode%    = the error code reported after executing the command
// %errormessage% = the detailed error message reported after executing the command
$lang['conversion_failed_msg'] =
'====== Um erro ocorreu durante a conversão do documento ODT: ======

Linha de comando executada:

<code>%command%</code>

Código de erro: %errorcode%

Mensagem de erro:

<code>%errormessage%</code>

[[%pageid%|Voltar a página anterior]]';

// Error message for failed conversion.
$lang['init_failed_msg'] =
'====== Um erro ocorreu durante a inicialização do documento ODT: ======

Sua versão do DokuWiki é compatível com o plug-in ODT?

Desde o lançamento da versão 2017-02-11, o plug-in ODT requer o DokuWiki "Detritus" ou posterior!
Para obter mais informações sobre os requisitos por favor visite a seção [[https://www.dokuwiki.org/plugin:odt#requirements|Requirements]] (em inglês) da página do plug-in ODT no DokuWiki.org.

(Sua versão do DokuWiki é %DWVERSION%)';
