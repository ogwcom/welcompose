<?php

/**
 * Project: Welcompose
 * File: antispamplugins_add.php
 *
 * Copyright (c) 2008-2012 creatics, Olaf Gleba <og@welcompose.de>
 *
 * Project owner:
 * creatics, Olaf Gleba
 * 50939 Köln, Germany
 * http://www.creatics.de
 *
 * This file is licensed under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE v3
 * http://www.opensource.org/licenses/agpl-v3.html
 * 
 * @author Andreas Ahlenstorf
 * @package Welcompose
 * @link http://welcompose.de
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
	
	// load antispamplugin class
	/* @var $ANTISPAMPLUGIN Community_AntiSpamPlugin */
	$ANTISPAMPLUGIN = load('Community:AntiSpamPlugin');
	
	// init user and project
	if (!$LOGIN->loggedIntoAdmin()) {
		header("Location: ../login.php");
		exit;
	}
	$USER->initUserAdmin();
	$PROJECT->initProjectAdmin(WCOM_CURRENT_USER);
	
	// check access
	if (!wcom_check_access('Community', 'AntiSpamPlugin', 'Manage')) {
		throw new Exception("Access denied");
	}
	
	// assign current user values
	$_wcom_current_user = $USER->selectUser(WCOM_CURRENT_USER);
	$BASE->utility->smarty->assign('_wcom_current_user', $_wcom_current_user);
	
	// assign current project values
	$_wcom_current_project = $PROJECT->selectProject(WCOM_CURRENT_PROJECT);
	$BASE->utility->smarty->assign('_wcom_current_project', $_wcom_current_project);
	
	// prepare types
	$types = array(
		'comment' => gettext('Comment plugin'),
		'trackback' => gettext('Trackback plugin')
	);
	
	// start new HTML_QuickForm
	$FORM = $BASE->utility->loadQuickForm('anti_spam_plugin');

	// apply filters to all fields
	$FORM->addRecursiveFilter('trim');
	
	// select for type
	$type = $FORM->addElement('select', 'type',
	 	array('id' => 'anti_spam_plugin_type'),
		array('label' => gettext('Plugin type'), 'options' => $types)
		);
	
	// textfield for name
	$name = $FORM->addElement('text', 'name', 
		array('id' => 'anti_spam_plugin_name', 'maxlength' => 255, 'class' => 'w300'),
		array('label' => gettext('Name'))
		);
	$name->addRule('required', gettext('Please enter a name'));
	$name->addRule('callback', gettext('An anti spam plugin with the desired name is already registered'), 
		array(
			'callback' => array($ANTISPAMPLUGIN, 'testForUniqueName')
		)
	);

	// textfield for internal name
	$internal_name = $FORM->addElement('text', 'internal_name', 
		array('id' => 'anti_spam_plugin_internal_name', 'maxlength' => 255, 'class' => 'w300 validate'),
		array('label' => gettext('Internal name'))
		);
	$internal_name->addRule('required', gettext('Please enter an internal name'));
	$internal_name->addRule('regex', gettext('Please enter a valid internal name'), WCOM_REGEX_ANTI_SPAM_PLUGIN_INTERNAL_NAME);
	$internal_name->addRule('callback', gettext('A anti spam plugin with the given internal name already exists'), 
		array(
			'callback' => array($ANTISPAMPLUGIN, 'testForUniqueInternalName')
		)
	);

	// textfield for priority
	$priority = $FORM->addElement('text', 'priority', 
		array('id' => 'anti_spam_plugin_priority', 'maxlength' => 4, 'class' => 'w300'),
		array('label' => gettext('Priority'))
		);
	$priority->addRule('required', gettext('Please enter a priority'));
	$priority->addRule('regex', gettext('The field priority is expected to be numeric'), WCOM_REGEX_NUMERIC);
	
	// checkbox for active
	$active = $FORM->addElement('checkbox', 'active',
		array('id' => 'anti_spam_plugin_active', 'class' => 'chbx'),
		array('label' => gettext('Active'))
		);
	$active->addRule('regex', gettext('The field whether the plugin is active accepts only 0 or 1'), WCOM_REGEX_ZERO_OR_ONE);
	
	// submit button
	$submit = $FORM->addElement('submit', 'submit', 
		array('class' => 'submit200', 'value' => gettext('Save'))
		);
	
	// set defaults
	$FORM->addDataSource(new HTML_QuickForm2_DataSource_Array(array(
		'priority' => '10'
	)));
		
	// validate it
	if (!$FORM->validate()) {
		// render it
		$renderer = $BASE->utility->loadQuickFormSmartyRenderer();
		
		// fetch {function} template to set
		// required/error markup on each form fields
		$BASE->utility->smarty->fetch(dirname(__FILE__).'/../quickform.tpl');
	
		// assign the form to smarty
		$BASE->utility->smarty->assign('form', $FORM->render($renderer)->toArray());
		
		// assign paths
		$BASE->utility->smarty->assign('wcom_admin_root_www',
			$BASE->_conf['path']['wcom_admin_root_www']);
		
		// build session
		$session = array(
			'response' => Base_Cnc::filterRequest($_SESSION['response'], WCOM_REGEX_NUMERIC)
		);
		
		// assign prepared session array to smarty
		$BASE->utility->smarty->assign('session', $session);
		
		// empty $_SESSION
		if (!empty($_SESSION['response'])) {
			$_SESSION['response'] = '';
		}
		
		// assign current user and project id
		$BASE->utility->smarty->assign('wcom_current_user', WCOM_CURRENT_USER);
		$BASE->utility->smarty->assign('wcom_current_project', WCOM_CURRENT_PROJECT);

		// select available projects
		$select_params = array(
			'user' => WCOM_CURRENT_USER,
			'order_macro' => 'NAME'
		);
		$BASE->utility->smarty->assign('projects', $PROJECT->selectProjects($select_params));
		
		// display the form
		define("WCOM_TEMPLATE_KEY", md5($_SERVER['REQUEST_URI']));
		$BASE->utility->smarty->display('community/antispamplugins_add.html', WCOM_TEMPLATE_KEY);
		
		// flush the buffer
		@ob_end_flush();
		
		exit;
	} else {
		// freeze the form
		$FORM->toggleFrozen(true);
		
		// create the article group
		$sqlData = array();
		$sqlData['project'] = WCOM_CURRENT_PROJECT;
		$sqlData['type'] = $type->getValue();
		$sqlData['name'] = $name->getValue();
		$sqlData['internal_name'] = $internal_name->getValue();
		$sqlData['priority'] = $priority->getValue();
		$sqlData['active'] = $active->getValue();
		
		// check sql data
		$HELPER = load('utility:helper');
		$HELPER->testSqlDataForPearErrors($sqlData);
		
		// insert it
		try {
			// begin transaction
			$BASE->db->begin();
			
			// execute operation
			$ANTISPAMPLUGIN->addAntiSpamPlugin($sqlData);
			
			// commit
			$BASE->db->commit();
		} catch (Exception $e) {
			// do rollback
			$BASE->db->rollback();
			
			// re-throw exception
			throw $e;
		}
	
		// add response to session
		$_SESSION['response'] = 1;
	
		// redirect
		$SESSION->save();
		
		// clean the buffer
		if (!$BASE->debug_enabled()) {
			@ob_end_clean();
		}
		
		// redirect
		header("Location: antispamplugins_add.php");
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