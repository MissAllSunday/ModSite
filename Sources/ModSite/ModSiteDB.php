<?php

/**
 * @package ModSite mod
 * @version 1.0
 * @author Suki <missallsunday@simplemachines.org>
 * @copyright 2013 Suki
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class ModSiteDB extends ModSiteParser
{
	protected $_table = array(
		'name' => 'mod_site',
		'columns' => array('id', 'name', 'cat', 'downloads'),
	);
	protected static $_count = 0;

	public function __construct()
	{
		parent::__construct();
	}

	public function countMods()
	{
		global $smcFunc;

		// Don't need to do this that often.
		if (!empty(static::$_count))
			return static::$_count;

		$request = $smcFunc['db_query']('', '
			SELECT id
			FROM {db_prefix}' . $this->_table['name'],
			array()
		);

		static::$_count =  $smcFunc['db_num_rows']($request);
		$smcFunc['db_free_result']($request);

		return static::$_count;
	}

	public function add($data)
	{
		global $smcFunc;

		if (empty($data))
			return false;

		$smcFunc['db_insert']('',
			'{db_prefix}'. $this->_table['name'],
			array('name' => 'string-255'),
			$data,
			array('id')
		);

		return $id = $smcFunc['db_insert_id']('{db_prefix}'. $this->_table['name'], 'id');
	}

	public function edit($data)
	{
		global $smcFunc;

		if (empty($data))
			return false;

		$smcFunc['db_query']('', '
			UPDATE {db_prefix}' . ($this->_table['name']) . '
			SET name = {string:name}
			WHERE id = {int:id}',
			$data
		);
	}

	public function doesExists($id)
	{
		global $smcFunc;

		$query = $smcFunc['db_query']('', '
			SELECT id
			FROM {db_prefix}' . ($this->_table['name']) . '
			WHERE id = '. ($id) .'
		');

		return $smcFunc['db_num_rows']($query);
	}

	public function getAll($start, $maxIndex)
	{
		global $smcFunc;

		$result = $smcFunc['db_query']('', '
			SELECT '. (implode(', ', $this->_table['columns'])) .'
			FROM {db_prefix}' . ($this->_table['name']) . '
			ORDER BY {literal:name DESC}
			LIMIT {int:start}, {int:maxIndex}',
			array(
				'start' => !empty($start) ? $start : 0,
				'maxIndex' => !empty($maxIndex) ? $maxIndex : 0,
			)
		);

		while ($row = $smcFunc['db_fetch_assoc']($result))
			$return[$row['id']] = array(
				'id' => $row['id'],
				'name' => $row['name'],
				'info' => $this->parse($row['name']),
				'category' => $this->getSingleCat($row['cat']),
				'downloads' => $row['downloads'],
			);

		$smcFunc['db_free_result']($result);

		// Done!
		return $return;
	}

	public function getSingle($id)
	{
		global $smcFunc;

		if (empty($id))
			return array();

		$result = $smcFunc['db_query']('', '
			SELECT '. (implode(', ', $this->_table['columns'])) .'
			FROM {db_prefix}' . ($this->_table['name']) . '
			WHERE id = {int:id}',
			array(
				'id' => $id,
			)
		);

		while ($row = $smcFunc['db_fetch_assoc']($result))
			$return = array(
				'id' => $row['id'],
				'name' => $row['name'],
				'info' => $this->parse($row['name']),
				'category' => $this->getSingleCat($row['cat']),
				'downloads' => $row['downloads'],
			);

		$smcFunc['db_free_result']($result);

		// Done!
		return $return;
	}

	public function getBy($column, $value)
	{
		global $smcFunc, $scripturl, $txt;

		// We need both.
		if (empty($column) || empty($value))
			return false;

		// Get the data as requested.
		$result = $smcFunc['db_query']('', '
			SELECT '. (implode(', ', $this->_table['columns'])) .'
			FROM {db_prefix}' . ($this->_table['name']) . '
			WHERE '. ($column) .' = '. ($value) .'',
			array()
		);

		while ($row = $smcFunc['db_fetch_assoc']($result))
			$return[$row['id']] = array(
				'id' => $row['id'],
				'name' => $row['name'],
				'info' => $this->parse($row['name']),
				'category' => $this->getSingleCat($row['cat']),
				'downloads' => $row['downloads'],
			);

		$smcFunc['db_free_result']($result);

		// Done!
		return $return;
	}

	public function updateCount($id)
	{
		global $smcFunc;

		if (empty($id))
			return false;

		$smcFunc['db_query']('', '
			UPDATE {db_prefix}' . ($this->_table['name']) . '
			SET downloads = downloads + 1
			WHERE id = {int:id}',
			array(
				'id' => $id,
			)
		);
	}

	public function delete($id)
	{
		global $smcFunc;

		/* Do not waste my time... */
		if (empty($id))
			return false;

		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}' . ($this->_table['table']) .'
			WHERE id = {int:id}',
			array(
				'id' => (int) $id,
			)
		);
	}

	public function clean($string)
	{
		global $smcFunc;

		return $smcFunc['htmlspecialchars']($smcFunc['htmltrim']($string, ENT_QUOTES, ENT_QUOTES));
	}

	public function getPermissions($type, $fatal_error = false)
	{
		global $modSettings;

		$type = is_array($type) ? array_unique($type) : array($type);
		$allowed = array();

		if (empty($type))
			return false;

		/* The mod must be enable */
		if (empty($this->setting('enable')))
			fatal_lang_error('ModSite_error_enable', false);

		/* collect the permissions */
		foreach ($type as $t)
				$allowed[] = (allowedTo('ModSite_'. $t) == true ? 1 : 0);

		/* You need at least 1 permission to be true */
		if ($fatal_error == true && !in_array(1, $allowed))
			isAllowedTo('ModSite_'. $t);

		elseif ($fatal_error == false && !in_array(1, $allowed))
			return false;

		elseif ($fatal_error == false && in_array(1, $allowed))
			return true;
	}
}
