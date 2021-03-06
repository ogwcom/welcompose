<?php

/**
 * Project: Welcompose
 * File: globalfile.class.php
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
 * Singleton. Returns instance of the Templating_GlobalFile object.
 * 
 * @return object
 */
function Templating_GlobalFile ()
{ 
	if (Templating_GlobalFile::$instance == null) {
		Templating_GlobalFile::$instance = new Templating_GlobalFile(); 
	}
	return Templating_GlobalFile::$instance;
}

class Templating_GlobalFile {
	
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
 * Creates new global file. Takes field=>value array with global
 * file data as first argument. Returns insert id.
 * 
 * @throws Templating_GlobalFileException
 * @param array Row data
 * @return int Global file id
 */
public function addGlobalFile ($sqlData)
{
	// access check
	if (!wcom_check_access('Templating', 'GlobalFile', 'Manage')) {
		throw new Templating_GlobalFileException("You are not allowed to perform this action");
	}
	
	// input check
	if (!is_array($sqlData)) {
		throw new Templating_GlobalFileException('Input for parameter sqlData is not an array');	
	}
	
	// make sure that the new global file will be assigned to the current project
	$sqlData['project'] = WCOM_CURRENT_PROJECT;
	
	// insert row
	$insert_id = $this->base->db->insert(WCOM_DB_TEMPLATING_GLOBAL_FILES, $sqlData);
	
	// test if global file belongs to current user/project
	if (!$this->globalFileBelongsToCurrentUser($insert_id)) {
		throw new Templating_GlobalFileException('Global file does not belong to current user or project');
	}
	
	return $insert_id;
}

/**
 * Updates global file. Takes the global file id as first argument,
 * a field=>value array with the new global file data as second
 * argument. Returns amount of affected rows.
 *
 * @throws Templating_GlobalFileException
 * @param int Global file id
 * @param array Row data
 * @return int Affected rows
*/
public function updateGlobalFile ($id, $sqlData)
{
	// access check
	if (!wcom_check_access('Templating', 'GlobalFile', 'Manage')) {
		throw new Templating_GlobalFileException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Templating_GlobalFileException('Input for parameter id is not an array');
	}
	if (!is_array($sqlData)) {
		throw new Templating_GlobalFileException('Input for parameter sqlData is not an array');	
	}
	
	// test if global file belongs to current user/project
	if (!$this->globalFileBelongsToCurrentUser($id)) {
		throw new Templating_GlobalFileException('Global file does not belong to current user or project');
	}
	
	// prepare where clause
	$where = " WHERE `id` = :id AND `project` = :project ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id,
		'project' => WCOM_CURRENT_PROJECT
	);
	
	// update row
	return $this->base->db->update(WCOM_DB_TEMPLATING_GLOBAL_FILES, $sqlData,
		$where, $bind_params);
}

/**
 * Removes global file from the global file table. Takes the
 * global file id as first argument. Returns amount of affected
 * rows.
 * 
 * @throws Templating_GlobalFileException
 * @param int Global file id
 * @return int Amount of affected rows
 */
public function deleteGlobalFile ($id)
{
	// access check
	if (!wcom_check_access('Templating', 'GlobalFile', 'Manage')) {
		throw new Templating_GlobalFileException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Templating_GlobalFileException('Input for parameter id is not numeric');
	}
	
	// test if global file belongs to current user/project
	if (!$this->globalFileBelongsToCurrentUser($id)) {
		throw new Templating_GlobalFileException('Global file does not belong to current user or project');
	}
	
	// unlink file
	if (!$this->removeGlobalFileFromStore($id)) {
		throw new Templating_GlobalFileException("Global file could not be removed from disk");
	}
	
	// prepare where clause
	$where = " WHERE `id` = :id AND `project` = :project ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id,
		'project' => WCOM_CURRENT_PROJECT
	);
	
	// execute query
	return $this->base->db->delete(WCOM_DB_TEMPLATING_GLOBAL_FILES,	
		$where, $bind_params);
}

/**
 * Selects one global file. Takes the global file id as first
 * argument. Returns array with global file information.
 * 
 * @throws Templating_GlobalFileException
 * @param int Global file id
 * @return array
 */
public function selectGlobalFile ($id)
{
	// access check
	if (!wcom_check_access('Templating', 'GlobalFile', 'Use')) {
		throw new Templating_GlobalFileException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Templating_GlobalFileException('Input for parameter id is not numeric');
	}
	
	// initialize bind params
	$bind_params = array();
	
	// prepare query
	$sql = "
		SELECT
			`templating_global_files`.`id` AS `id`,
			`templating_global_files`.`project` AS `project`,
			`templating_global_files`.`name` AS `name`,
			`templating_global_files`.`description` AS `description`,
			`templating_global_files`.`name_on_disk` AS `name_on_disk`,
			`templating_global_files`.`mime_type` AS `mime_type`,
			`templating_global_files`.`size` AS `size`,
			`templating_global_files`.`date_modified` AS `date_modified`,
			`templating_global_files`.`date_added` AS `date_added`
		FROM
			".WCOM_DB_TEMPLATING_GLOBAL_FILES." AS `templating_global_files`
		WHERE 
			`templating_global_files`.`id` = :id
		  AND
			`templating_global_files`.`project` = :project
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
 * Method to select one or more global files. Takes key=>value
 * array with select params as first argument. Returns array.
 * 
 * <b>List of supported params:</b>
 * 
 * <ul>
 * <li>start, int, optional: row offset</li>
 * <li>limit, int, optional: amount of rows to return</li>
 * </ul>
 * 
 * @throws Templating_GlobalFileException
 * @param array Select params
 * @return array
 */
public function selectGlobalFiles ($params = array())
{
	// access check
	if (!wcom_check_access('Templating', 'GlobalFile', 'Use')) {
		throw new Templating_GlobalFileException("You are not allowed to perform this action");
	}
	
	// define some vars
	$start = null;
	$limit = null;
	$bind_params = array();
	
	// input check
	if (!is_array($params)) {
		throw new Templating_GlobalFileException('Input for parameter params is not an array');	
	}
	
	// import params
	foreach ($params as $_key => $_value) {
		switch ((string)$_key) {
			case 'start':
			case 'limit':
					$$_key = (int)$_value;
				break;
			default:
				throw new Templating_GlobalFileException("Unknown parameter $_key");
		}
	}
	
	// prepare query
	$sql = "
		SELECT
			`templating_global_files`.`id` AS `id`,
			`templating_global_files`.`project` AS `project`,
			`templating_global_files`.`name` AS `name`,
			`templating_global_files`.`description` AS `description`,
			`templating_global_files`.`name_on_disk` AS `name_on_disk`,
			`templating_global_files`.`mime_type` AS `mime_type`,
			`templating_global_files`.`size` AS `size`,
			`templating_global_files`.`date_modified` AS `date_modified`,
			`templating_global_files`.`date_added` AS `date_added`
		FROM
			".WCOM_DB_TEMPLATING_GLOBAL_FILES." AS `templating_global_files`
		WHERE
			`templating_global_files`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'project' => WCOM_CURRENT_PROJECT
	);
	
	// add sorting
	$sql .= " ORDER BY `templating_global_files`.`name` ";
	
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
 * Method to count global files. Takes key=>value array with count params as
 * first argument. Returns array.
 * 
 * <b>List of supported params:</b>
 * 
 * <ul>
 * <li>n/a</li>
 * </ul>
 * 
 * @throws Templating_GlobalFileException
 * @param array Count params
 * @return array
 */
public function countGlobalFiles ($params = array())
{
	// access check
	if (!wcom_check_access('Templating', 'GlobalFile', 'Use')) {
		throw new Templating_GlobalFileException("You are not allowed to perform this action");
	}
	
	// define some vars
	$bind_params = array();
	
	// input check
	if (!is_array($params)) {
		throw new Templating_GlobalFileException('Input for parameter params is not an array');	
	}
	
	// import params
	foreach ($params as $_key => $_value) {
		switch ((string)$_key) {
			default:
				throw new Templating_GlobalFileException("Unknown parameter $_key");
		}
	}
	
	// prepare query
	$sql = "
		SELECT
			COUNT(*) AS `total`
		FROM
			".WCOM_DB_TEMPLATING_GLOBAL_FILES." AS `templating_global_files`
		WHERE
			`templating_global_files`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'project' => WCOM_CURRENT_PROJECT
	);
	
	return (int)$this->base->db->select($sql, 'field', $bind_params);
}

/**
 * Moves global file to store. Takes the real name (~ file name on user's disk)
 * as first argument, the path to the uploaded file as second argument. Returns
 * the new name on disk (uniqid + real name).
 *
 * @throws Templating_GlobalFileException
 * @param string File name
 * @param string Path to uploaded file
 * @return string File name on disk
 */
public function moveGlobalFileToStore ($name, $path)
{
	// access check
	if (!wcom_check_access('Templating', 'GlobalFile', 'Manage')) {
		throw new Templating_GlobalFileException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($name) || !is_scalar($name)) {
		throw new Templating_GlobalFileException("Input for parameter name is expected to be a non-empty scalar value");
	}
	if (empty($path) || !is_scalar($path)) {
		throw new Templating_GlobalFileException("Input for parameter path is expected to be a non-empty scalar value");
	}
	
	// get unique id
	//$uniqid = Base_Cnc::uniqueId();
	
	// prepare file name
	//$file_name = $uniqid.'_'.$name;
	$file_name = $name;
	
	// prepare target path
	$target_path = $this->base->_conf['global_file']['store_disk'].DIRECTORY_SEPARATOR.$file_name;
	
	// move file
	move_uploaded_file($path, $target_path);
	
	if (!empty($this->base->_conf['global_file']['chmod']) && is_numeric($this->base->_conf['global_file']['chmod'])) {
		@chmod($target_path, octdec($this->base->_conf['global_file']['chmod']));
	}
	
	// return file name
	return $file_name;
}

/**
 * Removes global file form store. Takes the id of the global file as
 * first argument. Returns bool.
 *
 * @throws Templating_GlobalFileException
 * @param int Global file id
 * @return bool
 */
public function removeGlobalFileFromStore ($global_file)
{
	// access check
	if (!wcom_check_access('Templating', 'GlobalFile', 'Manage')) {
		throw new Templating_GlobalFileException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($global_file) || !is_numeric($global_file)) {
		throw new Templating_GlobalFileException("Input for parameter global_file is expected to be numeric");
	}
	
	// get global file
	$file = $this->selectGlobalFile($global_file);
	
	// if the global file is empty, we can skip here
	if (empty($file)) {
		return false;
	}
	
	// prepare path to file on disk
	$path = $this->base->_conf['global_file']['store_disk'].DIRECTORY_SEPARATOR.$file['name_on_disk'];
	
	//$path = $this->getPathToGlobalFile($file);
	
	// unlink global file
	if (file_exists($path)) {
		if (unlink($path)) {
			// update global file in database
			$sqlData = array(
				'name' => null,
				'name_on_disk' => null
			);
			$this->updateGlobalFile($file['id'], $sqlData);
			
			return true;
		}
	}
	
	return false;
}

/**
 * Selects one global file. Takes the global file name as first
 * argument. Returns array with global file information.
 * 
 * @throws Templating_GlobalFileException
 * @param int Global file name
 * @return array
 */
public function selectGlobalFileUsingName ($name)
{
	// access check
	if (!wcom_check_access('Templating', 'GlobalFile', 'Use')) {
		throw new Templating_GlobalFileException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($name) || !is_scalar($name)) {
		throw new Templating_GlobalFileException('Input for parameter name is expected to be a non-empty scalar numeric');
	}
	
	// initialize bind params
	$bind_params = array();
	
	// prepare query
	$sql = "
		SELECT
			`templating_global_files`.`id` AS `id`,
			`templating_global_files`.`project` AS `project`,
			`templating_global_files`.`name` AS `name`,
			`templating_global_files`.`description` AS `description`,
			`templating_global_files`.`name_on_disk` AS `name_on_disk`,
			`templating_global_files`.`mime_type` AS `mime_type`,
			`templating_global_files`.`size` AS `size`,
			`templating_global_files`.`date_modified` AS `date_modified`,
			`templating_global_files`.`date_added` AS `date_added`
		FROM
			".WCOM_DB_TEMPLATING_GLOBAL_FILES." AS `templating_global_files`
		WHERE 
			`templating_global_files`.`name` = :name
		  AND
			`templating_global_files`.`project` = :project
		LIMIT
			1
	";
	
	// prepare bind params
	$bind_params = array(
		'name' => (string)$name,
		'project' => WCOM_CURRENT_PROJECT
	);
	
	// execute query and return result
	return $this->base->db->select($sql, 'row', $bind_params);
}

/**
 * Checks whether the given global file belongs to the current project or not.
 * Takes the id of the global file as first argument. Returns bool.
 *
 * @throws Templating_GlobalFileException
 * @param int Global file id
 * @return bool
 */
public function globalFileBelongsToCurrentProject ($file)
{
	// access check
	if (!wcom_check_access('Templating', 'GlobalFile', 'Use')) {
		throw new Templating_GlobalFileException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($file) || !is_numeric($file)) {
		throw new Templating_GlobalFileException('Input for parameter file is expected to be a numeric value');
	}
	
	// prepare query
	$sql = "
		SELECT
			COUNT(*)
		FROM
			".WCOM_DB_TEMPLATING_GLOBAL_FILES." AS `templating_global_files`
		WHERE
			`templating_global_files`.`id` = :file
		  AND
			`templating_global_files`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'file' => (int)$file,
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
 * Tests whether global file belongs to current user or not. Takes
 * the global file id as first argument. Returns bool.
 *
 * @throws Templating_GlobalFileException
 * @param int Global file id
 * @return bool
 */
public function globalFileBelongsToCurrentUser ($global_file)
{
	// access check
	if (!wcom_check_access('Templating', 'GlobalFile', 'Use')) {
		throw new Templating_GlobalFileException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($global_file) || !is_numeric($global_file)) {
		throw new Templating_GlobalFileException('Input for parameter global is expected to be a numeric value');
	}
	
	// load user class
	$USER = load('user:user');
	
	if (!$this->globalFileBelongsToCurrentProject($global_file)) {
		return false;
	}
	if (!$USER->userBelongsToCurrentProject(WCOM_CURRENT_USER)) {
		return false;
	}
	
	return true;
}

/**
 * Tests given global file name for uniqueness. Takes the global file
 * name as first argument and an optional global file id as second argument.
 * If the global file id is given, this global file won't be considered
 * when checking for uniqueness (useful for updates). Returns boolean true if
 * global file name is unique.
 *
 * @throws Templating_GlobalFileException
 * @param string Global file name
 * @param int Global file id
 * @return bool
 */
// public function testForUniqueName ($name, $id = null)
// {
// 	// access check
// 	if (!wcom_check_access('Templating', 'GlobalFile', 'Use')) {
// 		throw new Templating_GlobalFileException("You are not allowed to perform this action");
// 	}
// 	
// 	// input check
// 	if (empty($name)) {
// 		throw new Templating_GlobalFileException("Input for parameter name is not expected to be empty");
// 	}
// 	if (!is_scalar($name)) {
// 		throw new Templating_GlobalFileException("Input for parameter name is expected to be scalar");
// 	}
// 	if (!is_null($id) && ((int)$id < 1 || !is_numeric($id))) {
// 		throw new Templating_GlobalFileException("Input for parameter id is expected to be numeric");
// 	}
// 	
// 	// prepare query
// 	$sql = "
// 		SELECT 
// 			COUNT(*) AS `total`
// 		FROM
// 			".WCOM_DB_TEMPLATING_GLOBAL_FILES." AS `templating_global_files`
// 		WHERE
// 			`project` = :project
// 		  AND
// 			`name` = :name
// 	";
// 	
// 	// prepare bind params
// 	$bind_params = array(
// 		'project' => WCOM_CURRENT_PROJECT,
// 		'name' => $name
// 	);
// 	
// 	// if id isn't empty, add id check
// 	if (!empty($id) && is_numeric($id)) {
// 		$sql .= " AND `id` != :id ";
// 		$bind_params['id'] = (int)$id;
// 	} 
// 	
// 	// execute query and evaluate result
// 	if (intval($this->base->db->select($sql, 'field', $bind_params)) > 0) {
// 		return false;
// 	} else {
// 		return true;
// 	}
// }

/**
 * Tests given global file for uniqueness. Takes the global file name as first argument
 * and the id as second argument as an option.
 * 
 * The second argument is used to compare if the uploaded global file name equals the
 * former global file name that is already on disk. When the global file name is identical,
 * we allow to replace this file.
 *   
 * Returns boolean true if global file name is unique.
 *
 * @throws Templating_GlobalFileException
 * @param string file name
 * @param integer Id of current file
 * @param string action What condition may occur
 * @return bool
 */
public function testForUniqueFilename ($file_name, $id = null, $action)
{
	// access check
	if (!wcom_check_access('Templating', 'GlobalFile', 'Use')) {
		throw new Templating_GlobalFileException("You are not allowed to perform this action");
	}	
	// input check
	if (empty($file_name)) {
		throw new Templating_GlobalFileException("Input for parameter file_name is not expected to be empty");
	}
	if (!is_null($id) && !is_scalar($id)) {
		throw new Templating_GlobalFileException("Input for parameter id is expected to be scalar");
	}
	if (empty($action) && !is_string($action)) {
		throw new Templating_GlobalFileException("Input for parameter action is not expected to be empty");
	}
	
	if (!empty($id) && is_numeric($id)) {
		// get object path
		$object_path = $this->getPathToGlobalFileUsingId($id);
	}
	
	// get file path on disk
	$target_path = $this->getPathToGlobalFile($file_name);
	
	// evaluate result
	if (file_exists($target_path)) {
		if (!empty($object_path) && $object_path == $target_path) {
			return true;
		} else {
			return false;
		}
	} else {
		if (!empty($action) && $action == 'edit' || $action == 'upload') {
			return false;
		} else {
			return true;
		}
	}
}

/**
 * Returns full path to global file object. Takes global file object name on disk
 * as first argument. Please note that the object doesn't have to exist
 * to get the path to a object.
 *
 * @param string Object name
 * @return mixed
 */
public function getPathToGlobalFile ($object_name)
{
	// access check
	if (!wcom_check_access('Templating', 'GlobalFile', 'Use')) {
		throw new Templating_GlobalFileException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($object_name) || !is_scalar($object_name)) {
		throw new Templating_GlobalFileException("Object name is supposed to be a non-empty scalar value");
	}
	
	return $this->base->_conf['global_file']['store_disk'].DIRECTORY_SEPARATOR.$object_name;
}

/**
 * Takes global file object id as first argument. Returns full disk path to
 * global file object.
 *
 * @param string Object id
 * @return mixed
 */
public function getPathToGlobalFileUsingId ($object_id)
{
	// access check
	if (!wcom_check_access('Templating', 'GlobalFile', 'Use')) {
		throw new Templating_GlobalFileException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($object_id) || !is_scalar($object_id)) {
		throw new Templating_GlobalFileException("Object id is supposed to be a non-empty numeric value");
	}
	
	// get object
	$object = $this->selectGlobalFile($object_id);
	
	return $this->base->_conf['global_file']['store_disk'].DIRECTORY_SEPARATOR.$object['name_on_disk'];
}


// end of class
}

class Templating_GlobalFileException extends Exception { }

?>