<?php

/**
 * Project: Welcompose
 * File: generatorformlogin.class.php
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

// load the Display_SystemLogin class
if (!class_exists('Display_SystemLogin')) {
	$path_parts = array(
		dirname(__FILE__),
		'systemlogin.class.php'
	);
	require(implode(DIRECTORY_SEPARATOR, $path_parts));
}

/**
 * Class loader compatible to loader.php. Wrapps around constructor.
 * 
 * @param array
 * @return object
 */
function Display_GeneratorFormLogin ($args)
{
	// check input
	if (!is_array($args)) {
		trigger_error('Constructor args are not an array', E_USER_ERROR);
	}
	if (!array_key_exists(0, $args)) {
		trigger_error('Constructor arg project does not exist', E_USER_ERROR);
	}
	if (!array_key_exists(1, $args)) {
		trigger_error('Constructor arg page does not exist', E_USER_ERROR);
	}

	return new Display_GeneratorFormLogin($args[0], $args[1]);
}

class Display_GeneratorFormLogin extends Display_SystemLogin {

	/**
	 * Reference to base class
	 *
	 * @var object
	 */
	public $base = null;
	
	/**
	 * Container for project information
	 * 
	 * @var array
	 */
	protected $_project = array();
	
	/**
	 * Container for page information
	 * 
	 * @var array
	 */
	protected $_page = array();
	
	/**
	 * Container for generator form information
	 * 
	 * @var array
	 */
	protected $_generator_form = array();
	
/**
 * Creates new instance of display driver. Takes an array
 * with the project information as first argument, an array
 * with the information about the current page as second
 * argument.
 * 
 * @throws Display_GeneratorFormLoginException
 * @param array Project information
 * @param array Page information
 */
public function __construct($project, $page)
{
	try {
		// get base instance
		$this->base = load('base:base');
		
		// establish database connection
		$this->base->loadClass('database');
				
	} catch (Exception $e) {
		
		// trigger error
		printf('%s on Line %u: Unable to start base class. Reason: %s.', $e->getFile(),
			$e->getLine(), $e->getMessage());
		exit;
	}
	
	// input check
	if (!is_array($project)) {
		throw new Display_GeneratorFormLoginException("Input for parameter project is expected to be an array");
	}
	if (!is_array($page)) {
		throw new Display_GeneratorFormLoginException("Input for parameter page is expected to be an array");
	}
	
	// assign project, page info to class properties
	$this->_project = $project;
	$this->_page = $page;
	
	// get generator form
	$GENERATORFORM = load('Content:GeneratorForm');
	$this->_generator_form = $GENERATORFORM->selectGeneratorForm(WCOM_CURRENT_PAGE);
	
	// assign simple form to smarty
	$this->base->utility->smarty->assign('generator_form', $this->_generator_form);
}

// end of class
}

class Display_GeneratorFormLoginException extends Exception { }

?>