<?php

/**
 * Project: Welcompose
 * File: pages_simpleguestbooks_content_edit.php
 *
 * Copyright (c) 2008 creatics
 *
 * Project owner:
 * creatics, Olaf Gleba
 * 50939 Köln, Germany
 * http://www.creatics.de
 *
 * This file is licensed under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE v3
 * http://www.opensource.org/licenses/agpl-v3.html
 *
 * $Id$
 *
 * @copyright 2008 creatics, Olaf Gleba
 * @author Olaf Gleba
 * @package Welcompose
 * @license http://www.opensource.org/licenses/agpl-v3.html GNU AFFERO GENERAL PUBLIC LICENSE v3
 */

// define area constant
define('WCOM_CURRENT_AREA', 'ADMIN');

// get loader
$path_parts = array(
	dirname(__FILE__),
	'..',
	'..',
	'core',
	'loader.php'
);
$loader_path = implode(DIRECTORY_SEPARATOR, $path_parts);
require($loader_path);

// start base
/* @var $BASE base */
$BASE = load('base:base');

// deregister globals
$deregister_globals_path = dirname(__FILE__).'/../../core/includes/deregister_globals.inc.php';
require(Base_Compat::fixDirectorySeparator($deregister_globals_path));

// admin_navigation
$admin_navigation_path = dirname(__FILE__).'/../../core/includes/admin_navigation.inc.php';
require(Base_Compat::fixDirectorySeparator($admin_navigation_path));

try {
	// start output buffering
	@ob_start();
	
	// load smarty
	$smarty_admin_conf = dirname(__FILE__).'/../../core/conf/smarty_admin.inc.php';
	$BASE->utility->loadSmarty(Base_Compat::fixDirectorySeparator($smarty_admin_conf), true);
	
	// load gettext
	$gettext_path = dirname(__FILE__).'/../../core/includes/gettext.inc.php';
	include(Base_Compat::fixDirectorySeparator($gettext_path));
	gettextInitSoftware($BASE->_conf['locales']['all']);
	
	// start session
	/* @var $SESSION session */
	$SESSION = load('base:session');
	
	// load user class
	/* @var $USER User_User */
	$USER = load('user:user');
	
	// load login class
	/* @var $LOGIN User_Login */
	$LOGIN = load('User:Login');
	
	// load project class
	/* @var $PROJECT Application_Project */
	$PROJECT = load('application:project');
	
	// load page class
	/* @var $PAGE Content_Page */
	$PAGE = load('content:page');
	
	// load simpleguestbook class
	/* @var $SIMPLEGUESTBOOK Content_SimpleGuestbook */
	$SIMPLEGUESTBOOK = load('Content:SimpleGuestbook');
	
	// load textconverter class
	/* @var $TEXTCONVERTER Application_Textconverter */
	$TEXTCONVERTER = load('application:textconverter');
	
	// load textmacro class
	/* @var $TEXTMACRO Application_Textmacro */
	$TEXTMACRO = load('application:textmacro');
	
	// load helper class
	/* @var $HELPER Utility_Helper */
	$HELPER = load('utility:helper');
	
	// init user and project
	if (!$LOGIN->loggedIntoAdmin()) {
		header("Location: ../login.php");
		exit;
	}
	$USER->initUserAdmin();
	$PROJECT->initProjectAdmin(WCOM_CURRENT_USER);
	
	// check access
	if (!wcom_check_access('Content', 'SimpleGuestbook', 'Manage')) {
		throw new Exception("Access denied");
	}
	
	// assign current user values
	$_wcom_current_user = $USER->selectUser(WCOM_CURRENT_USER);
	$BASE->utility->smarty->assign('_wcom_current_user', $_wcom_current_user);
	
	// get page
	$page = $PAGE->selectPage(Base_Cnc::filterRequest($_REQUEST['id'], WCOM_REGEX_NUMERIC));
	
	// get simple guestbook
	$simple_guestbook = $SIMPLEGUESTBOOK->selectSimpleGuestbook(Base_Cnc::filterRequest($_REQUEST['id'], WCOM_REGEX_NUMERIC));
	
	// get default text converter if set
	$default_text_converter = $TEXTCONVERTER->selectDefaultTextConverter();
	
	// prepare captcha types array
	$captcha_types = array(
		'no' => gettext('Disable captcha'),
		'image' => gettext('Use image captcha'),
		'numeral' => gettext('Use numeral captcha')
	);
	
	// start new HTML_QuickForm
	$FORM = $BASE->utility->loadQuickForm('simple_guestbook', 'post');
	
	// hidden for navigation
	$FORM->addElement('hidden', 'id');
	$FORM->applyFilter('id', 'trim');
	$FORM->applyFilter('id', 'strip_tags');
	$FORM->addRule('id', gettext('Id is not expected to be empty'), 'required');
	$FORM->addRule('id', gettext('Id is expected to be numeric'), 'numeric');
	
	// hidden for frontend view control
	$FORM->addElement('hidden', 'preview');
	$FORM->applyFilter('preview', 'trim');
	$FORM->applyFilter('preview', 'strip_tags');
	$FORM->addRule('preview', gettext('Id is expected to be numeric'), 'numeric');
	
	// textfield for title
	$FORM->addElement('text', 'title', gettext('Title'),
		array('id' => 'simple_guestbook_title', 'maxlength' => 255, 'class' => 'w300 urlify'));
	$FORM->applyFilter('title', 'trim');
	$FORM->applyFilter('title', 'strip_tags');
	$FORM->addRule('title', gettext('Please enter a title'), 'required');
	
	// textfield for URL title
	$FORM->addElement('text', 'title_url', gettext('URL title'),
		array('id' => 'simple_guestbook_title_url', 'maxlength' => 255, 'class' => 'w300 validate'));
	$FORM->applyFilter('title_url', 'trim');
	$FORM->applyFilter('title_url', 'strip_tags');
	$FORM->addRule('title_url', gettext('Enter an URL title'), 'required');
	$FORM->addRule('title_url', gettext('The URL title may only contain chars, numbers and hyphens'),
		WCOM_REGEX_URL_NAME);
	
	// textarea for content
	$FORM->addElement('textarea', 'content', gettext('Content'),
		array('id' => 'simple_guestbook_content', 'cols' => 3, 'rows' => '2', 'class' => 'w540h550'));
	$FORM->applyFilter('content', 'trim');
	
	// select for text_converter
	$FORM->addElement('select', 'text_converter', gettext('Text converter'),
		$TEXTCONVERTER->getTextConverterListForForm(), array('id' => 'simple_guestbook_text_converter'));
	$FORM->applyFilter('text_converter', 'trim');
	$FORM->applyFilter('text_converter', 'strip_tags');
	$FORM->addRule('text_converter', gettext('Chosen text converter is out of range'),
		'in_array_keys', $TEXTCONVERTER->getTextConverterListForForm());
	
	// checkbox for apply_macros
	$FORM->addElement('checkbox', 'apply_macros', gettext('Apply text macros'), null,
		array('id' => 'simple_guestbook_apply_macros', 'class' => 'chbx'));
	$FORM->applyFilter('apply_macros', 'trim');
	$FORM->applyFilter('apply_macros', 'strip_tags');
	$FORM->addRule('apply_macros', gettext('The field whether to apply text macros accepts only 0 or 1'),
		'regex', WCOM_REGEX_ZERO_OR_ONE);
	
	// select for use_captcha
	$FORM->addElement('select', 'use_captcha', gettext('Use captcha'), $captcha_types,
		array('id' => 'simple_guestbook_use_captcha'));
	$FORM->applyFilter('use_captcha', 'trim');
	$FORM->applyFilter('use_captcha', 'strip_tags');
	$FORM->addRule('use_captcha', gettext('Chosen captcha type is out of range'),
		'in_array_keys', $captcha_types);
		
	// checkbox for meta_use
	$FORM->addElement('checkbox', 'meta_use', gettext('Custom meta tags'), null,
		array('id' => 'simple_guestbook_meta_use', 'class' => 'chbx'));
	$FORM->applyFilter('meta_use', 'trim');
	$FORM->applyFilter('meta_use', 'strip_tags');
	$FORM->addRule('meta_use', gettext('The field whether to use customized meta tags accepts only 0 or 1'),
		'regex', WCOM_REGEX_ZERO_OR_ONE);
	
	// textfield for meta_title
	$FORM->addElement('text', 'meta_title', gettext('Title'),
		array('id' => 'simple_guestbook_meta_title', 'maxlength' => 255, 'class' => 'w300'));
	$FORM->applyFilter('meta_title', 'trim');
	$FORM->applyFilter('meta_title', 'strip_tags');
	
	// textarea for meta_keywords
	$FORM->addElement('textarea', 'meta_keywords', gettext('Keywords'),
		array('id' => 'simple_guestbook_meta_keywords', 'cols' => 3, 'rows' => 2, 'class' => 'w540h50'));
	$FORM->applyFilter('meta_keywords', 'trim');
	$FORM->applyFilter('meta_keywords', 'strip_tags');

	// textarea for meta_description
	$FORM->addElement('textarea', 'meta_description', gettext('Description'),
		array('id' => 'simple_guestbook_meta_description', 'cols' => 3, 'rows' => 2, 'class' => 'w540h50'));
	$FORM->applyFilter('meta_description', 'trim');
	$FORM->applyFilter('meta_description', 'strip_tags');
		
	// checkbox for allow entry
	$FORM->addElement('checkbox', 'allow_entry', gettext('Allow new entries'), null,
		array('id' => 'simple_guestbook_allow_entry', 'class' => 'chbx'));
	$FORM->applyFilter('allow_entry', 'trim');
	$FORM->applyFilter('allow_entry', 'strip_tags');
	$FORM->addRule('allow_entry', gettext('The field whether to allow new entries accepts only 0 or 1'),
		'regex', WCOM_REGEX_ZERO_OR_ONE);
		
	// checkbox for send notification
	$FORM->addElement('checkbox', 'send_notification', gettext('Send notification'), null,
		array('id' => 'simple_guestbook_send_notification', 'class' => 'chbx'));
	$FORM->applyFilter('send_notification', 'trim');
	$FORM->applyFilter('send_notification', 'strip_tags');
	$FORM->addRule('send_notification', gettext('The field whether to apply text macros accepts only 0 or 1'),
		'regex', WCOM_REGEX_ZERO_OR_ONE);
		
	// textfield for email_from
	$FORM->addElement('text', 'notification_email_from', gettext('From: address'),
		array('id' => 'simple_guestbook_email_from', 'maxlength' => 255, 'class' => 'w300 validate'));
	$FORM->applyFilter('notification_email_from', 'trim');
	$FORM->applyFilter('notification_email_from', 'strip_tags');
	if ($FORM->exportValue('send_notification')) {
		$FORM->addRule('notification_email_from', gettext('Please enter a From: address'), 'required');
		$FORM->addRule('notification_email_from', gettext('Please enter a valid From: address'), 'email');
	}
	
	// textfield for email_to
	$FORM->addElement('text', 'notification_email_to', gettext('To: address'),
		array('id' => 'simple_guestbook_email_to', 'maxlength' => 255, 'class' => 'w300 validate'));
	$FORM->applyFilter('notification_email_to', 'trim');
	$FORM->applyFilter('notification_email_to', 'strip_tags');
	if ($FORM->exportValue('send_notification')) {
		$FORM->addRule('notification_email_to', gettext('Please enter a To: address'), 'required');
		$FORM->addRule('notification_email_to', gettext('Please enter a valid To: address'), 'email');
	}
	
	// textfield for email_subject
	$FORM->addElement('text', 'notification_email_subject', gettext('Subject'),
		array('id' => 'simple_guestbook_email_subject', 'maxlength' => 255, 'class' => 'w300'));
	$FORM->applyFilter('notification_email_subject', 'trim');
	if ($FORM->exportValue('send_notification')) {
		$FORM->applyFilter('notification_email_subject', 'strip_tags');
		$FORM->addRule('notification_email_subject', gettext('Please enter a subject'), 'required');
	}
	
	// submit button (save and stay)
	$FORM->addElement('submit', 'save', gettext('Save edit'),
		array('class' => 'submit200'));
		
	// submit button (save and go back)
	$FORM->addElement('submit', 'submit', gettext('Save edit and go back'),
		array('class' => 'submit200go'));
	
	// set text converter value or get default converter
	if (isset($simple_guestbook['text_converter'])) {
		$_text_converter = $simple_guestbook['text_converter'];
	} else {
		if ($default_text_converter > 0) {
			$_text_converter = $default_text_converter['id'];
		} else {
			$_text_converter = null;
		}
	}
	
	// set defaults
	$FORM->setDefaults(array(
		'id' => Base_Cnc::ifsetor($simple_guestbook['id'], null),
		'title' => Base_Cnc::ifsetor($simple_guestbook['title'], null),
		'title_url' => Base_Cnc::ifsetor($simple_guestbook['title_url'], null),
		'content' => Base_Cnc::ifsetor($simple_guestbook['content_raw'], null),
		'text_converter' => $_text_converter,
		'apply_macros' => Base_Cnc::ifsetor($simple_guestbook['apply_macros'], null),
		'meta_use' => Base_Cnc::ifsetor($simple_guestbook['meta_use'], null),
		'meta_title' => Base_Cnc::ifsetor($simple_guestbook['meta_title_raw'], null),
		'meta_keywords' => Base_Cnc::ifsetor($simple_guestbook['meta_keywords'], null),
		'meta_description' => Base_Cnc::ifsetor($simple_guestbook['meta_description'], null),
		'use_captcha' => Base_Cnc::ifsetor($simple_guestbook['use_captcha'], null),
		'allow_entry' => Base_Cnc::ifsetor($simple_guestbook['allow_entry'], null),
		'send_notification' => Base_Cnc::ifsetor($simple_guestbook['send_notification'], null),
		'notification_email_from' => Base_Cnc::ifsetor($simple_guestbook['notification_email_from'], null),
		'notification_email_to' => Base_Cnc::ifsetor($simple_guestbook['notification_email_to'], null),
		'notification_email_subject' => Base_Cnc::ifsetor($simple_guestbook['notification_email_subject'], null),
		// ctrl var for frontend view
		'preview' => $_SESSION['preview_ctrl']
	));
	
	// validate it
	if (!$FORM->validate()) {
		// render it
		$renderer = $BASE->utility->loadQuickFormSmartyRenderer();
		$quickform_tpl_path = dirname(__FILE__).'/../quickform.tpl.php';
		include(Base_Compat::fixDirectorySeparator($quickform_tpl_path));
		
		// remove attribute on form tag for XHTML compliance
		$FORM->removeAttribute('name');
		$FORM->removeAttribute('target');
		
		$FORM->accept($renderer);
	
		// assign the form to smarty
		$BASE->utility->smarty->assign('form', $renderer->toArray());
		
		// assign paths
		$BASE->utility->smarty->assign('wcom_admin_root_www',
			$BASE->_conf['path']['wcom_admin_root_www']);
	    
		// assign current user and project id
		$BASE->utility->smarty->assign('wcom_current_user', WCOM_CURRENT_USER);
		$BASE->utility->smarty->assign('wcom_current_project', WCOM_CURRENT_PROJECT);
		
		// build session
		$session = array(
			'response' => Base_Cnc::filterRequest($_SESSION['response'], WCOM_REGEX_NUMERIC),
			'preview_ctrl' => Base_Cnc::filterRequest($_SESSION['preview_ctrl'], WCOM_REGEX_NUMERIC)
		);
		
		// assign $_SESSION to smarty
		$BASE->utility->smarty->assign('session', $session);
		
		// empty $_SESSION
		if (!empty($_SESSION['response'])) {
			$_SESSION['response'] = '';
		}
		if (!empty($_SESSION['preview_ctrl'])) {
		  	$_SESSION['preview_ctrl'] = '';
		}
		
		// assign page
		$BASE->utility->smarty->assign("page", $page);
		
		// select available projects
		$select_params = array(
			'user' => WCOM_CURRENT_USER,
			'order_macro' => 'NAME'
		);
		$BASE->utility->smarty->assign('projects', $PROJECT->selectProjects($select_params));
		
		// display the form
		define("WCOM_TEMPLATE_KEY", md5($_SERVER['REQUEST_URI']));
		$BASE->utility->smarty->display('content/pages_simpleguestbooks_content_edit.html', WCOM_TEMPLATE_KEY);
		
		// flush the buffer
		@ob_end_flush();
		
		exit;
	} else {
		// freeze the form
		$FORM->freeze();
		
		// prepare sql data
		$sqlData = array();
		$sqlData['title'] = $FORM->exportValue('title');
		$sqlData['title_url'] = $FORM->exportValue('title_url');
		$sqlData['content_raw'] = $FORM->exportValue('content');
		$sqlData['content'] = $FORM->exportValue('content');
		$sqlData['text_converter'] = ($FORM->exportValue('text_converter') > 0) ? 
			$FORM->exportValue('text_converter') : null;
		$sqlData['apply_macros'] = (string)intval($FORM->exportValue('apply_macros'));
		$sqlData['meta_use'] = $FORM->exportValue('meta_use');
		$sqlData['meta_title_raw'] = null;
		$sqlData['meta_title'] = null;
		$sqlData['meta_keywords'] = null;
		$sqlData['meta_description'] = null;
		$sqlData['use_captcha'] = $FORM->exportValue('use_captcha');
		$sqlData['allow_entry'] = (string)intval($FORM->exportValue('allow_entry'));
		$sqlData['send_notification'] = (string)intval($FORM->exportValue('send_notification'));
		$sqlData['notification_email_from'] = $FORM->exportValue('notification_email_from');
		$sqlData['notification_email_to'] = $FORM->exportValue('notification_email_to');
		$sqlData['notification_email_subject'] = $FORM->exportValue('notification_email_subject');
		
		// apply text macros and text converter if required
		if ($FORM->exportValue('text_converter') > 0 || $FORM->exportValue('apply_macros') > 0) {
			// extract content
			$content = $FORM->exportValue('content');
			
			// apply startup and pre text converter text macros 
			if ($FORM->exportValue('apply_macros') > 0) {
				$content = $TEXTMACRO->applyTextMacros($content, 'pre');
			}
			
			// apply text converter
			if ($FORM->exportValue('text_converter') > 0) {
				$content = $TEXTCONVERTER->applyTextConverter(
					$FORM->exportValue('text_converter'),
					$content
				);
			}
			
			// apply post text converter and shutdown text macros 
			if ($FORM->exportValue('apply_macros') > 0) {
				$content = $TEXTMACRO->applyTextMacros($content, 'post');
			}
			
			// assign content to sql data array
			$sqlData['content'] = $content;
		}
		
		// prepare custom meta tags
		if ($FORM->exportValue('meta_use') == 1) { 
			$sqlData['meta_title_raw'] = $FORM->exportValue('meta_title');
			$sqlData['meta_title'] = str_replace("%title", $FORM->exportValue('title'), 
				$FORM->exportValue('meta_title'));
			$sqlData['meta_keywords'] = $FORM->exportValue('meta_keywords');
			$sqlData['meta_description'] = $FORM->exportValue('meta_description');
		}
		
		// test sql data for pear errors
		$HELPER->testSqlDataForPearErrors($sqlData);
		
		// insert it
		try {
			// begin transaction
			$BASE->db->begin();
			
			// execute operation
			$SIMPLEGUESTBOOK->updateSimpleGuestbook($FORM->exportValue('id'), $sqlData);
			
			// commit
			$BASE->db->commit();
		} catch (Exception $e) {
			// do rollback
			$BASE->db->rollback();
			
			// re-throw exception
			throw $e;
		}

		// controll value
		$saveAndRemainOnPage = $FORM->exportValue('save');
		
		// add response to session
		if (!empty($saveAndRemainOnPage)) {
			$_SESSION['response'] = 1;
		}
		
		// preview control value
		$activePreview = $FORM->exportValue('preview');
				
		// add preview_ctrl to session
		if (!empty($activePreview)) {
			$_SESSION['preview_ctrl'] = 1;
		}
				
		// redirect
		$SESSION->save();
		
		// clean the buffer
		if (!$BASE->debug_enabled()) {
			@ob_end_clean();
		}
		
		// redirect
		if (!empty($saveAndRemainOnPage)) {
			header("Location: pages_simpleguestbooks_content_edit.php?id=".$FORM->exportValue('id'));
		} else {
			header("Location: pages_select.php");
		}
		exit;
	}
} catch (Exception $e) {
	// clean the buffer
	if (!$BASE->debug_enabled()) {
		@ob_end_clean();
	}
	
	// raise error
	$BASE->error->displayException($e, $BASE->utility->smarty);
	$BASE->error->triggerException($e);
	
	// exit
	exit;
}
?>