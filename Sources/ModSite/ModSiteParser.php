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
	protected $_apiAcceptableStatus = array('good', 'minor');

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

	public function getFile($file)
	{
		/* There is no raw file to work with */
		if (empty($file))
			return false;

		if (file_exists($this->_boarddir . sprintf($this->_jsonDir, $file)))
			return file_get_contents($this->_boarddir . sprintf($this->_jsonDir, $file));

		else
			return false;
	}

	public function parse($file)
	{
		if (empty($file))
			return false;

		/* Create the mod array */
		$mod = json_decode($this->getFile($file), true);

		/* Bad file? some kind of error? */
		if (!$mod)
			return false;

		/* Append github repo info */
		try{
			$repoInfo = $this->getRepoInfo('ShareThis');
		}
		catch (RuntimeException $e)
		{
			log_error('issues with github API');
		}

		/* Merge the info */
		if (is_array($repoInfo))
			$mod = array_merge($mod, $this->getRepoInfo('ShareThis'));

		/* Parse the desc */
		if (!empty($mod['desc']))
			$mod['desc'] = parse_bbc($mod['desc']);

		return $mod;
	}

	public function getRepoInfo($repoName)
	{
		/* Don't even think about it... */
		if ($this->getAPIStatus() == 'major')
			return false;

		/* Init github API */
		if (!isset($this->client))
			$this->github();

		/* Get the repo info */
		return  $this->client->api('repo')->show($this->githubUser, $repoName);
	}

	protected function getRepoCollaborators($repoName)
	{
		/* Don't even think about it... */
		if ($this->getAPIStatus() == 'major')
			return false;

		/* Init github API */
		if (!isset($this->client))
			$this->github($this->githubUser);

		/* Get the collaborators for a repository if any */
		return $this->client->api('repo')->collaborators()->all($this->githubUser, $repoName);
	}

	public function github()
	{
		global $githubClient, $githubPass;

		require_once ($this->_boarddir .'/vendor/autoload.php');

		$this->client = new Github\Client(
			new Github\HttpClient\CachedHttpClient(array('cache_dir' => $this->_boarddir .'/cache/github-api-cache'))
		);

		/* Make this an authenticate call */
		$this->client->authenticate($githubClient, $githubPass, Github\Client::AUTH_URL_CLIENT_ID);

		return $this->client;
	}

	public function getCats()
	{
		if (file_exists($this->_boarddir . sprintf($this->_jsonDir, 'categories')))
			return json_decode(file_get_contents($this->_boarddir . sprintf($this->_jsonDir, 'categories')), true);

		else
			return false;
	}

	public function getSingleCat($id)
	{
		/* Do not waste my time */
		if (empty($id))
			return 'Default';

		/* Get all the cats */
		$this->getCats();

		/* Does the category exists? */
		if (in_array($id, array_keys($this->cats)))
			return $this->cats[$id];

		/* No? the send the default */
		else
			return $this->cats[1];
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

	public function getAPIStatus()
	{
		if ($return = cache_get_data('modsite_status', 120) == null)
		{
			/* Github API url check */
			$apiUrl = 'https://status.github.com/api/status.json';

			/* Get the data */
			$check = $this->fetch_web_data($apiUrl);

			/* Site is down :(  */
			if (empty($check))
			{
				cache_put_data('modsite_status', 'major', 120);
				$return = 'major';
			}

			elseif(!empty($check))
			{
				$check = json_decode($check);
				cache_put_data('modsite_status', $check->status, 120);
				$return = $check->status;
			}
		}

		return $return;
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