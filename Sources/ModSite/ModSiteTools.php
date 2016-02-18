<?php

/**
 * @package ModSite mod
 * @version 2.0
 * @author Suki <suki@missallsunday.com>
 * @copyright 2013 Suki
 * @license http://www.mozilla.org/MPL/MPL-1.1.html
 */

if (!defined('SMF'))
	die('No direct access...');

class ModSiteTools extends Suki\Ohara
{
	public $name = 'ModSite';
	protected $_useConfig = true;

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

		// Do not waste my time...
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

	public function getPermissions($type, $fatal_error = false)
	{
		global $modSettings;

		$type = is_array($type) ? array_unique($type) : array($type);
		$allowed = array();

		if (empty($type))
			return false;

		// The mod must be enable.
		if (empty($this->setting('enable')))
			fatal_lang_error($this->name .'_error_enable', false);

		// collect the permissions.
		foreach ($type as $t)
				$allowed[] = (allowedTo($this->name .'_'. $t) == true ? 1 : 0);

		// You need at least 1 permission to be true.
		if ($fatal_error == true && !in_array(1, $allowed))
			isAllowedTo($this->name .'_'. $t);

		elseif ($fatal_error == false && !in_array(1, $allowed))
			return false;

		elseif ($fatal_error == false && in_array(1, $allowed))
			return true;
	}

	public function github()
	{
		global $githubClient, $githubPass;

		$this->client = new Github\Client(
			new Github\HttpClient\CachedHttpClient(array('cache_dir' => $this->boardDir .'/cache/github-api-cache'))
		);

		// Make this an authenticate call.
		$this->client->authenticate($githubClient, $githubPass, Github\Client::AUTH_URL_CLIENT_ID);

		return $this->client;
	}

	public function getAPIStatus()
	{
		if ($return = cache_get_data($this->name .'_status', 600) == null)
		{
			// Github API url check.
			$apiUrl = 'https://status.github.com/api/status.json';

			// Get the data./
			$check = $this->fetch_web_data($apiUrl);

			// Site is down :(
			if (empty($check))
			{
				cache_put_data($this->name .'_status', 'major', 600);
				$return = 'major';
			}

			elseif(!empty($check))
			{
				$check = json_decode($check);
				cache_put_data($this->name .'_status', $check->status, 600);
				$return = $check->status;
			}
		}

		return $return;
	}
}
