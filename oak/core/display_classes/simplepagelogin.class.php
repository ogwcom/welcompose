<?php

/**
 * Project: Oak
 * File: simplepagelogin.class.php
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

// load the display interface
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'systemlogin.class.php');

class Display_SimplePageLogin extends Display_SystemLogin {
	
	/**
	 * Singleton
	 *
	 * @var object
	 */
	private static $instance = null;
	
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
	protected $_project_info = array();
	
	/**
	 * Container for page information
	 * 
	 * @var array
	 */
	protected $_page_info = array();
	
	/**
	 * Container for simple page information
	 * 
	 * @var array
	 */
	protected $_simple_page = array();
	
/**
 * Creates new instance of display driver. Takes an array
 * with the project information as first argument, an array
 * with the information about the current page as second
 * argument.
 * 
 * @throws Display_SimplePageLoginException
 * @param array Project information
 * @param array Page information
 */
public function __construct($project_info, $page_info)
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
	if (!is_array($project_info)) {
		throw new Display_SimplePageLoginException("Input for parameter project_info is expected to be an array");
	}
	if (!is_array($page_info)) {
		throw new Display_SimplePageLoginException("Input for parameter page_info is expected to be an array");
	}
	
	// assign project, page info to class properties
	$this->_project_info = $project_info;
	$this->_page_info = $page_info;
	
	// get simple page
	$SIMPLEPAGE = load('Content:SimplePage');
	$this->_simple_page = $SIMPLEPAGE->selectSimplePage(OAK_CURRENT_PAGE);
	
	// assign simple page to smarty
	$this->base->utility->smarty->assign('simple_page', $this->_simple_page);
}

/**
 * Loads new instance of display driver. See the constructor
 * for an argument description.
 *
 * In comparison to the constructor, it can be called using
 * call_user_func_array(). Please note that's not a singleton.
 * 
 * @param array Project information
 * @param array Page information
 * @return object New display driver instance
 */
public static function instance($project_info, $page_info)
{
	return new Display_SimplePageLogin($project_info, $page_info);
}

// end of class
}

class Display_SimplePageLoginException extends Exception { }

?>