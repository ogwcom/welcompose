<?php

/**
 * Project: Oak
 * File: pingservices_edit.php
 *
 * Copyright (c) 2006 sopic GmbH
 *
 * Project owner:
 * sopic GmbH
 * 8472 Seuzach, Switzerland
 * http://www.sopic.com/
 *
 * This file is licensed under the terms of the Open Software License
 * http://www.opensource.org/licenses/osl-2.1.php
 *
 * $Id$
 *
 * @copyright 2006 sopic GmbH
 * @author Andreas Ahlenstorf
 * @package Oak
 * @license http://www.opensource.org/licenses/osl-2.1.php Open Software License
 */

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
	
	// load project class
	/* @var $PROJECT Application_Project */
	$PROJECT = load('application:project');
	
	// load pingservice class
	/* @var $PINGSERVICE Application_Pingservice */
	$PINGSERVICE = load('application:pingservice');
	
	// init user and project
	$USER->initUserAdmin();
	$PROJECT->initProjectAdmin(OAK_CURRENT_USER);
	
	// load ping service
	$ping_service = $PINGSERVICE->selectPingService(Base_Cnc::filterRequest($_REQUEST['id'],
		OAK_REGEX_NUMERIC));
	
	// start new HTML_QuickForm
	$FORM = $BASE->utility->loadQuickForm('ping_service', 'post');
	$FORM->registerRule('testForNameUniqueness', 'callback', 'testForUniqueName', $PINGSERVICE);
	
	// hidden for id
	$FORM->addElement('hidden', 'id');
	$FORM->applyFilter('id', 'trim');
	$FORM->applyFilter('id', 'strip_tags');
	$FORM->addRule('id', gettext('Id is not expected to be empty'), 'required');
	$FORM->addRule('id', gettext('Id is expected to be numeric'), 'numeric');
	
	// textfield for name
	$FORM->addElement('text', 'name', gettext('Name'), 
		array('id' => 'ping_service_name', 'maxlength' => 255, 'class' => 'w300'));
	$FORM->applyFilter('name', 'trim');
	$FORM->applyFilter('name', 'strip_tags');
	$FORM->addRule('name', gettext('Please enter a name'), 'required');
	$FORM->addRule('name', gettext('A ping service with the given name already exists'),
		'testForNameUniqueness', $FORM->exportValue('id'));
	
	// textfield for host
	$FORM->addElement('text', 'host', gettext('Host'), 
		array('id' => 'ping_service_host', 'maxlength' => 255, 'class' => 'w300'));
	$FORM->applyFilter('host', 'trim');
	$FORM->applyFilter('host', 'strip_tags');
	$FORM->addRule('host', gettext('Please enter a host name'), 'required');
	$FORM->addRule('host', gettext('Please enter a valid host name'), 'regex', OAK_REGEX_PING_SERVICE_HOST);
	
	// textfield for port
	$FORM->addElement('text', 'port', gettext('Port'), 
		array('id' => 'ping_service_port', 'maxlength' => 255, 'class' => 'w300'));
	$FORM->applyFilter('port', 'trim');
	$FORM->applyFilter('port', 'strip_tags');
	$FORM->addRule('port', gettext('Please enter a port number'), 'required');
	$FORM->addRule('port', gettext('Please enter a valid port number'), 'regex', OAK_REGEX_NUMERIC);
	
	// textfield for path
	$FORM->addElement('text', 'path', gettext('Path'), 
		array('id' => 'ping_service_path', 'maxlength' => 255, 'class' => 'w300'));
	$FORM->applyFilter('path', 'trim');
	$FORM->applyFilter('path', 'strip_tags');
	$FORM->addRule('path', gettext('Please enter a request path'), 'required');
	
	// submit button
	$FORM->addElement('submit', 'submit', gettext('Update ping service'),
		array('class' => 'submitbut200'));
	
	// set defaults
	$FORM->setDefaults(array(
		'id' => Base_Cnc::ifsetor($ping_service['id'], null),
		'name' => Base_Cnc::ifsetor($ping_service['name'], null),
		'host' => Base_Cnc::ifsetor($ping_service['host'], null),
		'port' => Base_Cnc::ifsetor($ping_service['port'], null),
		'path' => Base_Cnc::ifsetor($ping_service['path'], null)
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
		$BASE->utility->smarty->assign('oak_admin_root_www',
			$BASE->_conf['path']['oak_admin_root_www']);
		
	    // build session
	    $session = array(
			'response' => Base_Cnc::filterRequest($_SESSION['response'], OAK_REGEX_NUMERIC)
	    );
	    
	    // assign prepared session array to smarty
	    $BASE->utility->smarty->assign('session', $session);
	    
	    // empty $_SESSION
	    if (!empty($_SESSION['response'])) {
	        $_SESSION['response'] = '';
	    }
	    
		// assign current user and project id
		$BASE->utility->smarty->assign('oak_current_user', OAK_CURRENT_USER);
		$BASE->utility->smarty->assign('oak_current_project', OAK_CURRENT_PROJECT);

		// select available projects
		$select_params = array(
			'user' => OAK_CURRENT_USER,
			'order_macro' => 'NAME'
		);
		$BASE->utility->smarty->assign('projects', $PROJECT->selectProjects($select_params));
		
		// display the form
		define("OAK_TEMPLATE_KEY", md5($_SERVER['REQUEST_URI']));
		$BASE->utility->smarty->display('application/pingservices_edit.html', OAK_TEMPLATE_KEY);
		
		// flush the buffer
		@ob_end_flush();
		
		exit;
	} else {
		// freeze the form
		$FORM->freeze();
		
		// create the article group
		$sqlData = array();
		$sqlData['name'] = $FORM->exportValue('name');
		$sqlData['host'] = $FORM->exportValue('host');
		$sqlData['port'] = $FORM->exportValue('port');
		$sqlData['path'] = $FORM->exportValue('path');
		
		// check sql data
		$HELPER = load('utility:helper');
		$HELPER->testSqlDataForPearErrors($sqlData);
		
		// insert it
		try {
			// begin transaction
			$BASE->db->begin();
			
			// execute operation
			$PINGSERVICE->updatePingService($FORM->exportValue('id'), $sqlData);
			
			// commit
			$BASE->db->commit();
		} catch (Exception $e) {
			// do rollback
			$BASE->db->rollback();
			
			// re-throw exception
			throw $e;
		}
	
		// redirect
		$SESSION->save();
		
		// clean the buffer
		if (!$BASE->debug_enabled()) {
			@ob_end_clean();
		}
		
		// redirect
		header("Location: pingservices_select.php");
		exit;
	}
} catch (Exception $e) {
	// clean the buffer
	if (!$BASE->debug_enabled()) {
		@ob_end_clean();
	}
	
	// raise error
	Base_Error::triggerException($BASE->utility->smarty, $e);	
	
	// exit
	exit;
}

?>