<?php

/**
 * @package ModSite mod
 * @version 1.0
 * @author Suki <missallsunday@simplemachines.org>
 * @copyright 2013 Suki
 */

if (!defined('SMF'))
	die('Hacking attempt...');


class ModSite extends ModSiteParser
{
	protected $_table = array(
		'name' => 'mod_site',
		'columns' => array('id', 'name', 'cat', 'downloads'),
		);

	public static $name = 'modsite';

	public function __construct()
	{
		parent::__construct();
	}

	public function add($data)
	{
		global $smcFunc;

		if (empty($data))
			return false;

		/* Clear the cache */
		$this->cleanCache();

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

		/* Clear the cache */
		$this->cleanCache();

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

	public function getAll()
	{
		global $smcFunc, $scripturl, $txt;

		/* Use the cache when possible */
		if (($return = cache_get_data(modsite::$name .'_all', 120)) == null)
		{
			$result = $smcFunc['db_query']('', '
				SELECT '. (implode(', ', $this->_table['columns'])) .'
				FROM {db_prefix}' . ($this->_table['name']) . '
				ORDER BY {raw:sort}',
				array(
					'sort' => 'name DESC',
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

			cache_put_data(modsite::$name .'_all', $return, 120);
		}

		/* Done? */
		return $return;
	}

	public function getSingle($id)
	{
		global $smcFunc, $scripturl, $txt;

		/* Can we avoid another query? */
		if (($return = cache_get_data(modsite::$name .'_all', 120)) != null)
			if (in_array($id, array_keys($return)))
				return $return[$id];

		/* No? :( */
		$result = $smcFunc['db_query']('', '
			SELECT '. (implode(', ', $this->_table['columns'])) .'
			FROM {db_prefix}' . ($this->_table['name']) . '
			LIMIT {int:limit}',
			array(
				'id' => (int) $id,
				'limit' => 1
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

		/* Done? */
		return $return;
	}

	public function updateCount($id)
	{
		global $smcFunc;

		if (empty($id))
			return false;

		/* Clear the cache */
		$this->cleanCache();

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

		/* Clear the cache */
		$this->cleanCache();

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

	public function permissions($type, $fatal_error = false)
	{
		global $modSettings;

		$type = is_array($type) ? array_unique($type) : array($type);
		$allowed = array();

		if (empty($type))
			return false;

		/* The mod must be enable */
		if (empty($modSettings['modSite_enable']))
			fatal_lang_error('modSite_error_enable', false);

		/* collect the permissions */
		foreach ($type as $t)
				$allowed[] = (allowedTo('modsite_'. $t) == true ? 1 : 0);

		/* You need at least 1 permission to be true */
		if ($fatal_error == true && !in_array(1, $allowed))
			isAllowedTo('modsite_'. $t);

		elseif ($fatal_error == false && !in_array(1, $allowed))
			return false;

		elseif ($fatal_error == false && in_array(1, $allowed))
			return true;
	}

	protected function cleanCache()
	{
		cache_put_data(modsite::$name .'_all', null, 120);
	}

	public function truncateString($string, $limit, $break = ' ', $pad = '...')
	{
		if(empty($limit))
			$limit = 30;

		 // return with no change if string is shorter than $limit
		if(strlen($string) <= $limit)
			return $string;

		// is $break present between $limit and the end of the string?
		if(false !== ($breakpoint = strpos($string, $break, $limit)))
			if($breakpoint < strlen($string) - 1)
				$string = substr($string, 0, $breakpoint) . $pad;

		return $string;
	}
}