<?php

/**
 * Project: Welcompose
 * File: templatetype.class.php
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

/**
 * Singleton. Returns instance of the Templating_TemplateType object.
 * 
 * @return object
 */
function Templating_TemplateType ()
{ 
	if (Templating_TemplateType::$instance == null) {
		Templating_TemplateType::$instance = new Templating_TemplateType(); 
	}
	return Templating_TemplateType::$instance;
}

class Templating_TemplateType {
	
	/**
	 * Singleton
	 * 
	 * @var object
	 */
	public static $instance = null;
	
	/**
	 * Reference to base class
	 * 
	 * @var object
	 */
	public $base = null;

/**
 * Start instance of base class, load configuration and
 * establish database connection. Please don't call the
 * constructor direcly, use the singleton pattern instead.
 */
public function __construct()
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
}

/**
 * Creates new template type. Takes field=>value array with template
 * type data as first argument. Returns insert id.
 * 
 * @throws Templating_TemplateTypeException
 * @param array Row data
 * @return int Template type id
 */
public function addTemplateType ($sqlData)
{
	// access check
	if (!wcom_check_access('Templating', 'TemplateType', 'Manage')) {
		throw new Templating_TemplateTypeException("You are not allowed to perform this action");
	}
	
	// input check
	if (!is_array($sqlData)) {
		throw new Templating_TemplateTypeException('Input for parameter sqlData is not an array');	
	}
	
	// make sure that the new template type will be assigned to the current project
	$sqlData['project'] = WCOM_CURRENT_PROJECT;
	
	// insert row
	$insert_id = $this->base->db->insert(WCOM_DB_TEMPLATING_TEMPLATE_TYPES, $sqlData);
	
	// test if template type belongs to current user/project
	if (!$this->templateTypeBelongsToCurrentUser($insert_id)) {
		throw new Templating_TemplateTypeException('Template type does not belong to current user or project');
	}
	
	return $insert_id;
}

/**
 * Updates template type. Takes the template type id as first argument,
 * a field=>value array with the new template type data as second
 * argument. Returns amount of affected rows.
 *
 * @throws Templating_TemplateTypeException
 * @param int Template type id
 * @param array Row data
 * @return int Affected rows
*/
public function updateTemplateType ($id, $sqlData)
{
	// access check
	if (!wcom_check_access('Templating', 'TemplateType', 'Manage')) {
		throw new Templating_TemplateTypeException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Templating_TemplateTypeException('Input for parameter id is not an array');
	}
	if (!is_array($sqlData)) {
		throw new Templating_TemplateTypeException('Input for parameter sqlData is not an array');	
	}
	
	// test if template type belongs to current user/project
	if (!$this->templateTypeBelongsToCurrentUser($id)) {
		throw new Templating_TemplateTypeException('Template type does not belong to current user or project');
	}
	
	// prepare where clause
	$where = " WHERE `id` = :id AND `project` = :project AND `editable` = '1' ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id,
		'project' => WCOM_CURRENT_PROJECT
	);
	
	// update row
	return $this->base->db->update(WCOM_DB_TEMPLATING_TEMPLATE_TYPES, $sqlData,
		$where, $bind_params);	
}

/**
 * Removes template type from the template type table. Takes the
 * template type id as first argument. Returns amount of affected
 * rows.
 * 
 * @throws Templating_TemplateTypeException
 * @param int Template type id
 * @return int Amount of affected rows
 */
public function deleteTemplateType ($id)
{
	// access check
	if (!wcom_check_access('Templating', 'TemplateType', 'Manage')) {
		throw new Templating_TemplateTypeException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Templating_TemplateTypeException('Input for parameter id is not numeric');
	}
	
	// test if template type belongs to current user/project
	if (!$this->templateTypeBelongsToCurrentUser($id)) {
		throw new Templating_TemplateTypeException('Template type does not belong to current user or project');
	}
	
	// prepare where clause
	$where = " WHERE `id` = :id AND `project` = :project AND `editable` = '1' ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id,
		'project' => WCOM_CURRENT_PROJECT
	);
	
	// execute query
	return $this->base->db->delete(WCOM_DB_TEMPLATING_TEMPLATE_TYPES,	
		$where, $bind_params);
}

/**
 * Selects one template type. Takes the template type id as first
 * argument. Returns array with template type information.
 * 
 * @throws Templating_TemplateTypeException
 * @param int Template type id
 * @return array
 */
public function selectTemplateType ($id)
{
	// access check
	if (!wcom_check_access('Templating', 'TemplateType', 'Use')) {
		throw new Templating_TemplateTypeException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Templating_TemplateTypeException('Input for parameter id is not numeric');
	}
	
	// initialize bind params
	$bind_params = array();
	
	// prepare query
	$sql = "
		SELECT
			`templating_template_types`.`id` AS `id`,
			`templating_template_types`.`project` AS `project`,
			`templating_template_types`.`name` AS `name`,
			`templating_template_types`.`description` AS `description`,
			`templating_template_types`.`editable` AS `editable`
		FROM
			".WCOM_DB_TEMPLATING_TEMPLATE_TYPES." AS `templating_template_types`
		WHERE 
			`templating_template_types`.`id` = :id
		  AND
			`templating_template_types`.`project` = :project
		LIMIT
			1
	";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id,
		'project' => WCOM_CURRENT_PROJECT
	);
	
	// execute query and return result
	return $this->base->db->select($sql, 'row', $bind_params);
}

/**
 * Method to select one or more template types. Takes key=>value
 * array with select params as first argument. Returns array.
 * 
 * <b>List of supported params:</b>
 * 
 * <ul>
 * <li>start, int, optional: row offtype</li>
 * <li>limit, int, optional: amount of rows to return</li>
 * </ul>
 * 
 * @throws Templating_TemplateTypeException
 * @param array Select params
 * @return array
 */
public function selectTemplateTypes ($params = array())
{
	// access check
	if (!wcom_check_access('Templating', 'TemplateType', 'Use')) {
		throw new Templating_TemplateTypeException("You are not allowed to perform this action");
	}
	
	// define some vars
	$start = null;
	$limit = null;
	$bind_params = array();
	
	// input check
	if (!is_array($params)) {
		throw new Templating_TemplateTypeException('Input for parameter params is not an array');	
	}
	
	// import params
	foreach ($params as $_key => $_value) {
		switch ((string)$_key) {
			case 'start':
			case 'limit':
					$$_key = (int)$_value;
				break;
			default:
				throw new Templating_TemplateTypeException("Unknown parameter $_key");
		}
	}
	
	// prepare query
	$sql = "
		SELECT
			`templating_template_types`.`id` AS `id`,
			`templating_template_types`.`project` AS `project`,
			`templating_template_types`.`name` AS `name`,
			`templating_template_types`.`description` AS `description`,
			`templating_template_types`.`editable` AS `editable`
		FROM
			".WCOM_DB_TEMPLATING_TEMPLATE_TYPES." AS `templating_template_types`
		WHERE 
			`templating_template_types`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'project' => WCOM_CURRENT_PROJECT
	);
	
	// add sorting
	$sql .= " ORDER BY `templating_template_types`.`name` ";
	
	// add limits
	if (empty($start) && is_numeric($limit)) {
		$sql .= sprintf(" LIMIT %u", $limit);
	}
	if (!empty($start) && is_numeric($start) && !empty($limit) && is_numeric($limit)) {
		$sql .= sprintf(" LIMIT %u, %u", $start, $limit);
	}
	
	return $this->base->db->select($sql, 'multi', $bind_params);
}

/**
 * Checks whether the given template type belongs to the current project or not. Takes
 * the id of the template type as first argument. Returns bool.
 *
 * @throws Templating_TemplateTypeException
 * @param int Template type id
 * @return bool
 */
public function templateTypeBelongsToCurrentProject ($type)
{
	// access check
	if (!wcom_check_access('Templating', 'TemplateType', 'Use')) {
		throw new Templating_TemplateTypeException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($type) || !is_numeric($type)) {
		throw new Templating_TemplateTypeException('Input for parameter type is expected to be a numeric value');
	}
	
	// prepare query
	$sql = "
		SELECT
			COUNT(*)
		FROM
			".WCOM_DB_TEMPLATING_TEMPLATE_TYPES." AS `templating_template_types`
		WHERE
			`templating_template_types`.`id` = :type
		  AND
			`templating_template_types`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'type' => (int)$type,
		'project' => WCOM_CURRENT_PROJECT
	);
	
	// execute query and evaluate result
	if (intval($this->base->db->select($sql, 'field', $bind_params)) === 1) {
		return true;
	} else {
		return false;
	}
}

/**
 * Tests whether template type belongs to current user or not. Takes
 * the template type id as first argument. Returns bool.
 *
 * @throws Templating_TemplateTypeException
 * @param int Template type id
 * @return bool
 */
public function templateTypeBelongsToCurrentUser ($template_type)
{
	// access check
	if (!wcom_check_access('Templating', 'TemplateType', 'Use')) {
		throw new Templating_TemplateTypeException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($template_type) || !is_numeric($template_type)) {
		throw new Templating_TemplateTypeException('Input for parameter template tyoe is expected to be a numeric value');
	}
	
	// load user class
	$USER = load('user:user');
	
	if (!$this->templateTypeBelongsToCurrentProject($template_type)) {
		return false;
	}
	if (!$USER->userBelongsToCurrentProject(WCOM_CURRENT_USER)) {
		return false;
	}
	
	return true;
}

/**
 * Tests given template type name for uniqueness. Takes the template type
 * name as first argument and an optional template type id as second argument.
 * If the template type id is given, this template type won't be considered
 * when checking for uniqueness (useful for updates). Returns boolean true if
 * template type name is unique.
 *
 * @throws Templating_TemplateTypeException
 * @param string Template type name
 * @param int Template type id
 * @return bool
 */
public function testForUniqueName ($name, $id = null)
{
	// access check
	if (!wcom_check_access('Templating', 'TemplateType', 'Use')) {
		throw new Templating_TemplateTypeException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($name)) {
		throw new Templating_TemplateTypeException("Input for parameter name is not expected to be empty");
	}
	if (!is_scalar($name)) {
		throw new Templating_TemplateTypeException("Input for parameter name is expected to be scalar");
	}
	if (!is_null($id) && ((int)$id < 1 || !is_numeric($id))) {
		throw new Templating_TemplateTypeException("Input for parameter id is expected to be numeric");
	}
	
	// prepare query
	$sql = "
		SELECT 
			COUNT(*) AS `total`
		FROM
			".WCOM_DB_TEMPLATING_TEMPLATE_TYPES." AS `templating_template_types`
		WHERE
			`project` = :project
		  AND
			`name` = :name
	";
	
	// prepare bind params
	$bind_params = array(
		'project' => WCOM_CURRENT_PROJECT,
		'name' => $name
	);
	
	// if id isn't empty, add id check
	if (!empty($id) && is_numeric($id)) {
		$sql .= " AND `id` != :id ";
		$bind_params['id'] = (int)$id;
	} 
	
	// execute query and evaluate result
	if (intval($this->base->db->select($sql, 'field', $bind_params)) > 0) {
		return false;
	} else {
		return true;
	}
}

// end of class
}

class Templating_TemplateTypeException extends Exception { }

?>