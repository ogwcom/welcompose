<?php

/**
 * Project: Welcompose
 * File: cleanup.php
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

// get loader
$path_parts = array(
	dirname(__FILE__),
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
$deregister_globals_path = dirname(__FILE__).'/../core/includes/deregister_globals.inc.php';
require(Base_Compat::fixDirectorySeparator($deregister_globals_path));

try {
	// start output buffering
	@ob_start();
	
	// load smarty
	$smarty_update_conf = dirname(__FILE__).'/smarty.inc.php';
	$BASE->utility->loadSmarty(Base_Compat::fixDirectorySeparator($smarty_update_conf), true);
	
	// start Base_Session
	/* @var $SESSION session */
	$SESSION = load('base:session');
	
	// empty session
	$_SESSION = array();
	
	// save session
	$SESSION->save();
	
	// remove the entire setup directory
	function setup_rm_rf ($directory) {
		$dir = dir($directory);
		while (false !== ($file = $dir->read())) {
			if ($file == '.' || $file == '..') {
				continue;
			}
			
			$full_path = $dir->path.DIRECTORY_SEPARATOR.$file;
			if (is_dir($full_path)) {
				setup_rm_rf($full_path);
				@rmdir($full_path);
			} else {
				@unlink($full_path);
			}
		}
		$dir->close();
	}
	setup_rm_rf(dirname(__FILE__));
	@rmdir(dirname(__FILE__));
	
	// clean the buffer
	if (!$BASE->debug_enabled()) {
		@ob_end_clean();
	}
	
	// redirect
	header("Location: ../admin/index.php");
	exit;
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