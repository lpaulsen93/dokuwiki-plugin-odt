<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author Vyacheslav Strenadko <bryanskmap@ya.ru>
 * @author Yuriy Skalko <yuriy.skalko@gmail.com>
 * @author Anotheroneuser <w20151222@ya.ru>
 * @author Wirbel78 <bryanskmap@yandex.ru>
 */
$lang['encoding']              = 'utf-8';
$lang['direction']             = 'ltr';
$lang['view']                  = 'Преобразовать страницу в формат Open Document';
$lang['export_odt_button']     = 'ODT преобразование';
$lang['export_odt_pdf_button'] = 'экспорт ODT=>PDF';
$lang['tpl_not_found']         = 'ОШИБКА: шаблон ODT "%s" не найден в каталоге шаблонов "%s". Преобразование отменено. ';
$lang['toc_title']             = 'Оглавление';
$lang['chapter_title']         = 'Индекс главы';
$lang['toc_msg']               = 'Оглавление будет размещено здесь.';
$lang['chapter_msg']           = 'Индекс главы будет находиться здесь.';
$lang['update_toc_msg']        = 'Не забудьте, пожалуйста, обновить оглавление по окончании преобразования. ';
$lang['update_chapter_msg']    = 'Не забудьте, пожалуйста, обновить индекс главы по окончании преобразования. ';
$lang['needtitle']             = 'Пожалуйста, укажите название. ';
$lang['needns']                = 'Пожалуйста, укажите существующее пространство имён. ';
$lang['empty']                 = 'Страницы не выбраны. ';
$lang['forbidden']             = 'У вас нет доступа к этим страницам: %s.<br/><br/>Используйте опцию "Пропустить запрещенные страницы", чтобы создать документ с доступными страницами.';
$lang['conversion_failed_msg'] = '====== При преобразовании документа ODT произошла ошибка: ======

Выполнена командная строка:

<code>%command%</code>

Код ошибки: %errorcode%

Сообщение об ошибке:

<code>%errormessage%</code>

[[%pageid%|Вернуться на предыдущую страницу]]';
$lang['init_failed_msg']       = '====== При инициализации документа ODT произошла ошибка: ======

Совместима ли ваша версия DokuWiki с плагином ODT?

Начиная с выпуска 2017-02-11, для плагина ODT требуется версия DokuWiki “Detritus” или новее!
Подробную информацию о требованиях смотрите в [[https://www.dokuwiki.org/plugin:odt#requirements|Разделе требований]] на странице плагина ODT по адресу DokuWiki.org.

(Ваш выпуск DokuWiki называется %DWVERSION%)';
