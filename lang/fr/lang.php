<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author NicolasFriedli <nicolas@theologique.ch>
 * @author Schplurtz le Déboulonné <schplurtz@laposte.net>
 * @author Morgan <morgan.leguen@gmail.com>
 * @author ubibene <services.m@benard.info>
 */
$lang['encoding']              = 'utf-8';
$lang['direction']             = 'gauche à droite';
$lang['view']                  = 'Exporter la page au format Open Document';
$lang['export_odt_button']     = 'Exportation ODT';
$lang['export_odt_pdf_button'] = 'Exportation ODT=>PDF';
$lang['tpl_not_found']         = 'ERREUR: le modèle ODT "%s" est introuvable dans le répertoire des modèles "%s". L\'export est abandonné.';
$lang['toc_title']             = 'Table des matières';
$lang['chapter_title']         = 'Index du Chapitre';
$lang['toc_msg']               = 'Une Table des Matières sera insérée ici.';
$lang['chapter_msg']           = 'Un Index de Chapitre sera inséré ici.';
$lang['update_toc_msg']        = 'Pensez à mettre à jour la Table des Matières après l\'exportation.';
$lang['update_chapter_msg']    = 'Pensez à mettre à jour l\'Index du Chapitre après l\'exportation.';
$lang['needtitle']             = 'Veuillez choisir un titre.';
$lang['needns']                = 'Veuillez choisir une catégorie existante.';
$lang['empty']                 = 'Vous n\'avez pas encore sélectionné de pages.';
$lang['forbidden']             = 'Vous ne pouvez pas accéder à ces pages: %s.<br/><br/>Choisissez l\'option  \'Passer les pages interdites\' pour créer votre livre avec les pages disponibles.';
$lang['conversion_failed_msg'] = '====== Une erreur a eu lieu pendant la conversion du document ODT: ====== Ligne exécutée: <code>%command%</code> Code d\'erreur: %errorcode% Message d\'erreur: <code>%errormessage%</code> [[%pageid%|Retour à la page précédente]]';
$lang['init_failed_msg']       = '====== Une erreur a eu lieu pendant l\'initialisation du document ODT: ====== Votre version de DokuWiki est-elle compatible avec le plugin ODT? Depuis la version  2017-02-11 le plugin ODT exige une version DokuWiki “Detritus” ou ultérieur !Pour des informations détaillées sur les prérequis consultez s\'il vous plaît  [[https://www.dokuwiki.org/plugin:odt#requirements|la section prérequis]] de la page du plugin ODT sur DokuWiki.org. (Votre version de DokuWiki est  %DWVERSION%)';
