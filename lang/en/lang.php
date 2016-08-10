<?php

// settings must be present and set appropriately for the language
$lang['encoding']   = 'utf-8';
$lang['direction']  = 'ltr';

// export button
$lang['view'] = 'Export page to Open Document format';
$lang['export_odt_button'] = "ODT export";

// template not found in the directory
$lang['tpl_not_found'] = 'ERROR : the ODT template "%s" was not found in the templates directory "%s". The export has been aborted.';

// default TOC and chapter index title
$lang['toc_title']          = 'Table of Contents';
$lang['chapter_title']      = 'Chapter Index';
$lang['toc_msg']            = 'A Table of Contents will be inserted here.';
$lang['chapter_msg']        = 'A Chapter Index will be inserted here.';
$lang['update_toc_msg']     = 'Please remember to update the Table Of Contents after export.';
$lang['update_chapter_msg'] = 'Please remember to update the Chapter Index after export.';

$lang['needtitle']         = "Please provide a title.";
$lang['needns']            = "Please provide an existing namespace.";
$lang['empty']             = "You don't have pages selected yet.";
$lang['forbidden']         = "You have no access to these pages: %s.<br/><br/>Use option 'Skip Forbidden Pages' to create your book with the available pages.";

// Error message for failed conversion.
// The following replacments are supported:
// %command%      = the complete command line which was executed
// %errorcode%    = the error code reported after executing the command
// %errormessage% = the detailed error message reported after executing the command
$lang['conversion_failed_msg'] =
'====== An error occured during conversion of the ODT document: ======

Executed command line:

<code>%command%</code>

Error code: %errorcode%

Error message:

<code>%errormessage%</code>

[[%pageid%|Back to previous page]]';
