<?php

/**
 * @package ModSite mod
 * @version 1.0
 * @author Suki <missallsunday@simplemachines.org>
 * @copyright 2013 Suki
 */

if (!defined('SMF'))
	die('Hacking attempt...');


class ModSiteParser
{
	protected $_jsonDir = '';

	public function __construct()
	{
		global $boarddir, $boardurl, $modSettings;

		$this->_jsonDir = !empty($modSettings['modsite_json_dir']) ? '/'. $modSettings['modsite_json_dir'] .'/%s.json' : '%s';
		$this->githubUser = $modSettings['modsite_github_username'];
		$this->_boarddir = $boarddir;
		$this->_boardurl = $boardurl;

		/* Get the cats! */
		$this->cats = $this->getCats();
	}

	protected function getFile($file)
	{
		/* There is no raw file to work with */
		if (empty($file))
			return false;

		/* Check using scan() to be sure we got something */

		return file_get_contents($this->_boarddir . sprintf($this->_jsonDir, $file));
	}

	public function parse($file)
	{
		if (empty($file))
			return false;

		/* Create the mod array */
		$modArray = json_decode($this->getFile($file), true);

		/* Append github repo info */
		$modArray['repo'] = $this->getRepoInfo();

		/* Replace the ugly number with a nice readable word */
		if (!empty($modArray['cat']) && in_array($modArray['cat'], array_keys($this->cats)))
			$modArray['cat'] = $this->cats[$modArray['cat']];

		else
			$modArray['cat'] = $this->cats[1];
	}

	public function getSingle($property)
	{
		if (!empty($modArray) && !empty($modArray[$property]))
			return $modArray[$property];

		return false;
	}

	public function getAll()
	{
		if (!empty($modArray))
			return $modArray;

		return false;
	}

	protected function getRepoInfo()
	{
		/* Init github API */
		$this->github($this->githubUser );

		/* Get the repo info */
		$return['info'] =  $this->client->api('repo')->show($this->githubUser, $modArray['githubName']);

		/* Get the collaborators for a repository if any */
		$return['collaborators'] = $this->client->api('repo')->collaborators()->all($this->githubUser, $modArray['githubName']);
	}

	public function github($username)
	{
		require_once ($this->_boarddir .'/vendor/autoload.php');

		$this->client = new Github\Client(
			new Github\HttpClient\CachedHttpClient(array('cache_dir' => $this->_boarddir .'/cache/github-api-cache'))
		);

		return $this->client;
	}

	protected function getCats()
	{
		return json_decode(file_get_contents($this->_boarddir . sprintf($this->_jsonDir, 'categories')), true);
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