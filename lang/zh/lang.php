<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author Charles Chan <charles@zeerd.com>
 * @author RandyChen <zzxxccvv484632@gmail.com>
 * @author RainSlide <RainSlide@outlook.com>
 * @author Wen Qixiang <wen@bootingman.org>
 * @author 2576562185 <2576562185@qq.com>
 */
$lang['encoding']              = 'UTF-8';
$lang['direction']             = 'ltr';
$lang['view']                  = '将页面以Open Document格式输出';
$lang['export_odt_button']     = '输出ODT文件';
$lang['export_odt_pdf_button'] = '输出PDF文件';
$lang['tpl_not_found']         = '错误：在模板目录“"%s"”中找不到ODT模板“"%s"”。输出已中止。';
$lang['toc_title']             = '目录';
$lang['chapter_title']         = '章节索引';
$lang['toc_msg']               = '将在此处插入目录';
$lang['chapter_msg']           = '将在此处插入章节索引';
$lang['update_toc_msg']        = '请记得在输出后更新目录';
$lang['update_chapter_msg']    = '请记得在输出后更新章节索引';
$lang['needtitle']             = '请提供标题';
$lang['needns']                = '请提供现有的命名空间';
$lang['empty']                 = '您没有选择页面';
$lang['forbidden']             = '您无权访问页面：%s。<br/><br/>请使用“跳过禁止​​访问的页面”选项来创建包含可用页面的书籍。';
$lang['conversion_failed_msg'] = '======转换ODT文档时出错：======

执行的命令行：

<code>%command%</code>

错误代码：%errorcode%

错误消息：

<code>%errormessage%</code>

[[%pageid%|返回上一页]]';
$lang['init_failed_msg']       = '====== ODT文档初始化时出错：======

你的DokuWiki版本是否兼容ODT插件？

自2017-02-11版本发布以来，ODT插件需要DokuWiki的“Detritus”版本或更新的版本！

有关详细要求信息，请参阅DokuWiki.org上ODT插件页面的[[https://www.dokuwiki.org/plugin:odt#requirements|要求部分]]。

（您的DokuWiki版本是%DWVERSION%）';
