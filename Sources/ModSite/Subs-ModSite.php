<?php

/**
 * @package modsite mod
 * @version 1.0
 * @author Suki <missallsunday@simplemachines.org>
 * @copyright 2013 Suki
 */

if (!defined('SMF'))
	die('Hacking attempt...');


class ModSite {

	protected $_table = array(
		'modsite' => array(
			'name' => 'modsite',
			'columns' => array('id', 'name', 'github', 'id_user', 'id_cat', 'id_topic', 'downloads', 'desc', 'body', 'file'),
		),
		'cats' = array(
			'name' => 'modsite_cat',
			'columns' => array('id', 'cat_name',),
		),
	);

	public static $name = 'modsite';

	public function __construct(){}

	public function add($data)
	{
		global $smcFunc;

		if (empty($data))
			return false;

		/* Clear the cache */
		$this->cleanCache(array('latest', 'all'));

		$smcFunc['db_insert']('',
			'{db_prefix}modsite',
			array(
				'id' => 'int', 'name' => 'string-255', 'github' => 'string-255', 'id_user' => 'int', 'id_topic' => 'int', 'downloads' => 'int', 'desc' => 'string-65534', 'body' => 'string-65534', 'file' => 'string-65534'
			),
			$data,
			array('id')
		);

		return $id = $smcFunc['db_insert_id']('{db_prefix}modsite', 'id');
	}

	public function edit($data)
	{
		global $smcFunc;

		if (empty($data))
			return false;

		/* Clear the cache */
		$this->cleanCache(array('latest', 'all'));

		$smcFunc['db_query']('', '
			UPDATE {db_prefix}' . ($this->_table['table']) . '
			SET name = {string:name}, github = {string:github}, id_user = {int:user}, id_topic = {int:topic}, downloads = {int:downloads}, desc = {string:desc}, body = {string:body}, file = {string:file}
			WHERE id = {int:id}',
			$data
		);
	}

	public function getLatest($limit = 10)
	{
		global $smcFunc, $scripturl, $txt;

		 /* Use the cache when possible */
		if (($return = cache_get_data(modsite::$name .'_latest', 120)) == null)
		{
			$result = $smcFunc['db_query']('', '
				SELECT '. (implode(', s.', $this->_table['modsite']['columns'])) .', '. (implode(', c.', $this->_table['cats']['columns'])) .', m.member_name, m.real_name
				FROM {db_prefix}' . ($this->_table['modsite']['name']) . ' AS s
					LEFT JOIN {db_prefix}' . ($this->_table['cats']['name']) . ' AS c ON (c.id = s.id_cat)
					LEFT JOIN {db_prefix}members AS m ON (m.id_member = s.id_user)
				ORDER BY {raw:sort}
				LIMIT {int:limit}',
				array(
					'sort' => 'id DESC',
					'limit' => $limit
				)
			);

			while ($row = $smcFunc['db_fetch_assoc']($result))
				$return[$row['id']] = array(
					'id' => $row['id'],
					'name' => $row['name'],
					'github' => $row['github'],
					'topic' => $row['id_topic'],
					'downloads' => $row['downloads'],
					'desc' => $row['desc'],
					'file' => !empty($row['file']) ? json_decode($row['file'], true) : array(),
					'body' => parse_bbc($row['body']),
					'cat' => array(
						'id' => $row['id_cat'],
						'name' => $row['cat_name'],
						'link' => '<a href="' . $scripturl . '?action=modsite;sa=cat;mid=' . $row['id_cat'] . '">' . $row['cat_name'] . '</a>',
					),
					'user' => array(
						'id' => $row['id_user'],
						'username' => $row['member_name'],
						'name' => isset($row['real_name']) ? $row['real_name'] : '',
						'href' => $scripturl . '?action=profile;u=' . $row['id_user'],
						'link' => '<a href="' . $scripturl . '?action=profile;u=' . $row['id_user'] . '" title="' . $txt['profile_of'] . ' ' . $row['real_name'] . '">' . $row['real_name'] . '</a>',
					),
				);

			$smcFunc['db_free_result']($result);

			cache_put_data(modsite::$name .'_latest', $return, 120);
		}

		/* Done? */
		return $return;
	}

	public function getSingle($id)
	{
		global $smcFunc, $scripturl, $txt;

		$result = $smcFunc['db_query']('', '
			SELECT '. (implode(', l.', $this->_table['columns'])) .', m.member_name, m.real_name
			FROM {db_prefix}' . ($this->_table['table']) . ' AS l
				LEFT JOIN {db_prefix}members AS m ON (m.id_member = l.user)
			WHERE id = ({int:id})
			LIMIT {int:limit}',
			array(
				'id' => (int) $id,
				'limit' => 1
			)
		);

		while ($row = $smcFunc['db_fetch_assoc']($result))
			$return = array(
				'id' => $row['id'],
				'artist' => $row['artist'],
				'title' => $row['title'],
				'keywords' => $row['keywords'],
				'body' => parse_bbc($row['body']),
				'user' => array(
					'id' => $row['id_user'],
					'username' => $row['member_name'],
					'name' => isset($row['real_name']) ? $row['real_name'] : '',
					'href' => $scripturl . '?action=profile;u=' . $row['id_user'],
					'link' => '<a href="' . $scripturl . '?action=profile;u=' . $row['id_user'] . '" title="' . $txt['profile_of'] . ' ' . $row['real_name'] . '">' . $row['real_name'] . '</a>',
				),
			);

		$smcFunc['db_free_result']($result);

		/* Done? */
		return $return;
	}

	public function getByLetter($l)
	{
		if (empty($l) || strlen($l) != 1)
			return false;
	}

	public function getBy($column, $value, $limit = false)
	{
		global $smcFunc, $scripturl, $txt;

		if (empty($column) || !in_array($column, $this->_table['columns']) || empty($value))
			return false;

		$return = array();

		$result = $smcFunc['db_query']('', '
			SELECT '. (implode(', l.', $this->_table['columns'])) .', m.member_name, m.real_name
			FROM {db_prefix}' . ($this->_table['table']) . ' AS l
				LEFT JOIN {db_prefix}members AS m ON (m.id_member = l.user)
			WHERE '. $column .' '. (is_int($value) ? '= {int:value} ' : 'LIKE {string:value} ') .'
			ORDER BY {raw:sort}
			'. (!empty($limit) ? '
			LIMIT {int:limit}' : '') .'',
			array(
				'sort' => 'title ASC',
				'value' => $value,
				'column' => $column,
				'limit' => !empty($limit) ? (int) $limit : 0,
			)
		);

		while ($row = $smcFunc['db_fetch_assoc']($result))
			$return[$row['id']] = array(
				'id' => $row['id'],
				'artist' => $row['artist'],
				'title' => $row['title'],
				'keywords' => $row['keywords'],
				'body' => parse_bbc($row['body']),
				'user' => array(
					'id' => $row['id_user'],
					'username' => $row['member_name'],
					'name' => isset($row['real_name']) ? $row['real_name'] : '',
					'href' => $scripturl . '?action=profile;u=' . $row['id_user'],
					'link' => '<a href="' . $scripturl . '?action=profile;u=' . $row['id_user'] . '" title="' . $txt['profile_of'] . ' ' . $row['real_name'] . '">' . $row['real_name'] . '</a>',
				),
			);

		$smcFunc['db_free_result']($result);

		/* Done? */
		return !empty($return) ? $return : false;
	}

	public function getAll($page = 'list')
	{
		global $smcFunc, $scripturl, $txt, $modSettings, $context;

		$total = $this->getCount();
		$maxIndex = !empty($modSettings['modsite_pag_limit']) ? $modSettings['modsite_pag_limit'] : 20;

		/* Safety first! */
		$sortArray = array('title', 'artist', 'latest');

		$result = $smcFunc['db_query']('', '
			SELECT '. (implode(', ', $this->_table['columns'])) .', m.member_name, m.real_name
			FROM {db_prefix}' . ($this->_table['table']) . ' AS l
				LEFT JOIN {db_prefix}members AS m ON (m.id_member = l.user)
			ORDER BY {raw:sort} ASC
			LIMIT {int:start}, {int:maxindex}',
			array(
				'start' => $_REQUEST['start'],
				'maxindex' => $maxIndex,
				'sort' => isset($_REQUEST['lSort']) && in_array(trim(htmlspecialchars($_REQUEST['lSort'])), $sortArray) ? $_REQUEST['lSort'] : 'title'
			)
		);

		while ($row = $smcFunc['db_fetch_assoc']($result))
			$return[$row['id']] = array(
				'id' => $row['id'],
				'artist' => $row['artist'],
				'title' => $row['title'],
				'keywords' => $row['keywords'],
				'body' => parse_bbc($row['body']),
				'user' => array(
					'id' => $row['id_user'],
					'username' => $row['member_name'],
					'name' => isset($row['real_name']) ? $row['real_name'] : '',
					'href' => $scripturl . '?action=profile;u=' . $row['id_user'],
					'link' => '<a href="' . $scripturl . '?action=profile;u=' . $row['id_user'] . '" title="' . $txt['profile_of'] . ' ' . $row['real_name'] . '">' . $row['real_name'] . '</a>',
				),
			);

		$smcFunc['db_free_result']($result);

		/* Build the pagination */
		$context['page_index'] = constructPageIndex($scripturl . '?action=modsite;sa='. $page .'', $_REQUEST['start'], $total, $maxIndex, false);

		/* Done? */
		return $return;
	}

	protected function getCount()
	{
		global $smcFunc;

		$result = $smcFunc['db_query']('', '
			SELECT id
			FROM {db_prefix}' . ($this->_table['table']),
			array()
		);

		return $smcFunc['db_num_rows']($result);
	}

	public function delete($id)
	{
		global $smcFunc;

		/* Clear the cache */
		cache_put_data(modsite::$name .'_latest', '', 120);

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

	public function clean($string, $body = false)
	{
		global $smcFunc, $sourcedir;

		$string = $smcFunc['htmlspecialchars']($smcFunc['htmltrim']($string, ENT_QUOTES, ENT_QUOTES));

		if ($body)
		{
			require_once($sourcedir.'/Subs-Post.php');
			preparsecode($string);
		}

		return $string;
	}

	public function permissions($type, $fatal_error = false)
	{
		global $modSettings;

		$type = is_array($type) ? array_unique($type) : array($type);
		$allowed = array();

		if (empty($type))
			return false;

		/* The mod must be enable */
		if (empty($modSettings['modsite_enable']))
			fatal_lang_error('modsite_error_enable', false);

		/* colect the permissions */
		foreach ($type as $t)
				$allowed[] = (allowedTo('modsiteMod_'. $t .'modsite') == true ? 1 : 0);


		/* You need at least 1 permission to be true */
		if ($fatal_error == true && !in_array(1, $allowed))
			isAllowedTo('modsiteMod_'. $t .'modsite');

		elseif ($fatal_error == false && !in_array(1, $allowed))
			return false;

		elseif ($fatal_error == false && in_array(1, $allowed))
			return true;
	}

	/* Creates simple links to edit/delete based on the users permissions */
	public function crud($id)
	{
		global $scripturl, $txt;

		/* By default lets send nothing! */
		$return = '';

		/* We need an ID... */
		if (empty($id))
			return $return;

		/* Set the pertinent permissions */
		$edit = $this->permissions('edit');
		$delete = $this->permissions('delete');

		/* Let's check if you have what it takes... */
		if ($edit == true)
			$return .= '<a href="'. $scripturl .'?action=modsite;sa=edit;lid='. $this->clean($id) .'">'. $txt['modsite_edit'] .'</a>';

		if ($delete == true)
			$return .= ($edit == true ? ' | ': '') .'<a href="'. $scripturl .'?action=modsite;sa=delete;lid='. $this->clean($id) .'">'. $txt['modsite_delete'] .'</a>';

		/* Send the string */
		return $return;
	}

	/* A function to cut-off a string */
	protected function truncateString($string, $limit, $break = ' ', $pad = '...')
	{
		if(empty($limit))
			$limit = 30;

		 /* return with no change if string is shorter than $limit */
		if(strlen($string) <= $limit)
			return $string;

		/* is $break present between $limit and the end of the string? */
		if(false !== ($breakpoint = strpos($string, $break, $limit)))
			if($breakpoint < strlen($string) - 1)
				$string = substr($string, 0, $breakpoint) . $pad;

		return $string;
	}

	public function status()
	{
		$v = json_decode($this->fetch_web_data('https://status.github.com/api/status.json'));

		if (!empty($v) && trim($v->status) == 'good')
			return true;

		else
			return false;
	}

	public function github($username)
	{
		global $boarddir;

		require_once ($boarddir .'/vendor/autoload.php');

		$this->client = new Github\Client(
			new Github\HttpClient\CachedHttpClient(array('cache_dir' => $boarddir .'/cache/github-api-cache'))
		);
		$this->username = $username;

		return $this->client;
	}

	/**
	 * Tries to fetch the content of a given url
	 *
	 * @access protected
	 * @param string $url the url to call
	 * @return mixed either the page requested or a boolean false
	 */
	protected function fetch_web_data($url)
	{
		global $sourcedir;

		/* Safety first! */
		if (empty($url))
			return false;

		/* I can haz cURL? */
		if (function_exists ('curl_init'))
		{
			$ch = curl_init();

			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_HEADER, false);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$content = curl_exec($ch);
			curl_close($ch);

			/* Send the data directly, evil, I'm evil! :P */
			return $content;
		}

		/* Good old SMF's fetch_web_data to the rescue! */
		else
		{
			/* Requires a function in a source file far far away... */
			require_once($sourcedir .'/Subs-Package.php');

			/* Send the result directly, we are gonna handle it on every case */
			return fetch_web_data($url);
		}
	}

	protected function cleanCache($type)
	{
		if (empty($type))
			return;

		if (!is_array($type))
			$type = array($type);

		foreach ($type as $t)
			cache_put_data(modsite::$name .'_'. $type, '', 120);
	}
}