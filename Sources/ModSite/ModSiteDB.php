<?php

/**
 * @package ModSite mod
 * @version 1.0
 * @author Suki <missallsunday@simplemachines.org>
 * @copyright 2013 Suki
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class ModSiteDB
{
	protected $_table = array(
		'name' => 'mod_site',
		'columns' => array('id', 'name', 'cat', 'downloads'),
	);

	public static $name = 'modsite';

	public function __construct(){}

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
		if (($return = cache_get_data(modsite::$name .'_all', 3600)) == null)
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

			cache_put_data(modsite::$name .'_all', $return, 3600);
		}

		/* Done? */
		return $return;
	}

	public function getSingle($id)
	{
		global $smcFunc, $scripturl, $txt;

		/* Can we avoid another query? */
		if (($return = cache_get_data(modsite::$name .'_all', 3600)) != null)
			if (in_array($id, array_keys($return)))
				return $return[$id];

		/* No? :( */
		$result = $smcFunc['db_query']('', '
			SELECT '. (implode(', ', $this->_table['columns'])) .'
			FROM {db_prefix}' . ($this->_table['name']) . '
			WHERE id = {int:id}
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

	public function getBy($column, $value)
	{
		global $smcFunc, $scripturl, $txt;

		/* We need both  */
		if (empty($column) || empty($value))
			return false;

		/* Use the cache when possible */
		if (($return = cache_get_data(modsite::$name .'_'. $column.'_'. $value, 3600)) == null)
		{

			/* Get the data as requested */
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

			cache_put_data(modsite::$name .'_'. $column.'_'. $value, $return, 3600);
		}

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

	protected function cleanCache()
	{
		cache_put_data(modsite::$name .'_all', null, 3600);
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