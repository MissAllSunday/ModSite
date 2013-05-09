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
	public $modObject;

	public function __construct()
	{
		global $boarddir, $boardurl, $modSettings;

		$this->_jsonDir = !empty($modSettings['modsite_json_dir']) ? '/'. $modSettings['modsite_json_dir'] .'/%s.json' : '%s';
		$this->_boarddir = $boarddir;
		$this->_boardurl = $boardurl;
	}

	protected function getFile()
	{
		/* There is no raw file to work with */
		if (empty($this->rawFile ))
			return false;

		$this->jsonFile = file_get_contents($this->_boarddir . sprintf($this->_jsonDir, $this->rawFile ));
	}

	public function parse($file)
	{
		if (empty($file))
			return false;

		/* Set the raw file */
		$this->rawFile = $file

		/* Get the file */
		$this->getFile();

		/* Create the object */
		$this->modObject = json_decode($this->jsonFile);
	}

	public function github($username)
	{
		require_once ($this->_boarddir .'/vendor/autoload.php');

		$this->client = new Github\Client(
			new Github\HttpClient\CachedHttpClient(array('cache_dir' => $this->_boarddir .'/cache/github-api-cache'))
		);
		$this->githubUsername = $username;

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