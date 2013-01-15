<?php

/**
 * Mod Site (SMF)
 *
 * @package SMF
 * @author Suki <suki@missallsunday.com>
 * @copyright 2013 Jessica González
 * @license http://www.mozilla.org/MPL/ MPL 2.0
 *
 * @version 1.0 Alpha 1
 */

/*
 * Version: MPL 2.0
 *
 * This Source Code Form is subject to the terms of the Mozilla Public License, v. 2.0.
 * If a copy of the MPL was not distributed with this file,
 * You can obtain one at http://mozilla.org/MPL/2.0/
 *
 * Software distributed under the License is distributed on an "AS IS" basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
 * for the specific language governing rights and limitations under the
 * License.
 *
 */

if (!defined('SMF'))
	die('No direct access...');

class ModSitePage
{
	public function __construct($settings, $text, $query)
	{
		global $scripturl;

		/* Load stuff */
		loadtemplate(ModSite::$name);

		$this->text = $text;
		$this->settings = $settings;
		$this->query = $query;
		$this->scripturl = $scripturl;

		/* We need a brand new globals object */
		$this->globals = new ModSiteGlobals('get');
	}

	public function call()
	{
		$subActions = array(
			'post' => 'doPost',
			'post2' => 'doPost2',
			'single' => 'doSingle',
			'download' => 'doDownload',
			'delete' => 'doDelete',
			'all' => 'doAll',
			'categories' => 'doCategories',
		);

		/* Does the subaction even exist? */
		if (in_array($this->globals->getValue('sa'), array_keys($subActions)))
			$this->$subActions[$this->globals->getValue('sa')]();

		/* No?  redirect them to the main page */
		else
			$this->main();
	}

	public function main()
	{
		global $context;

		/* Meh... I haz all tha powerz */
		if (!$context['user']['is_admin'])
			redirectexit();

		/* Set all the page stuff */
		$context['sub_template'] = ModSite::$name.'_main';
		$context['page_title'] = $this->text->getText('title_main');
		$context['canonical_url'] = $this->scripturl . '?action=mods';

		/* Set the pagination stuff */

		/* Get all the mods */

	}

	public function doPost()
	{
		global $context;

		/* Meh... I haz all tha powerz */
		if (!$context['user']['is_admin'])
			redirectexit();

		/* Set all the page stuff */
		$context['sub_template'] = ModSite::$name.'_post';
		$context['page_title'] = $this->text->getText('title_post');
		$context['canonical_url'] = $this->scripturl . '?action=mods;sa=post';

		/* Build the form */
		$form = $this->buildForm();

		/* Pass it to the template */
		$context['ModSite']['Form'] = $form;
		$context['page_desc'] = 'Some description here...';
	}

	public function doPost2()
	{
		global $context, $boarddir, $user_info;

		/* Safety first! */
		checkSession('post', '', false);

		/* Set what we need */
		$file = array();

		/* We need a new instance for globals... */
		$globals = new ModSiteGlobals('post');

		/* Meh... I haz all tha powerz */
		if (!$context['user']['is_admin'])
			redirectexit();

		/* Old fashined checks... */
		if (!$globals->getValue('name') || !$globals->getValue('file'))
			redirectexit('action=mods;sa=post');

		/* Hardcoded zip extension FTW! rar files are for douches! */
		$file['name'] = $globals->getValue('file') . '.zip';

		/* First,lets handle the file... */
		if (!empty($file['name']))
		{
			$file['path'] = $boarddir . Modsite::$downloads_folder . $file['name'];

			/* Get info about it */
			$fileStats = stat($file['path']);
			$file['sha1'] = sha1_file($file['path']);

			if (!empty($fileStats))
			{
				$file['size'] = $this->formatBytes($fileStats['size']);
				$file['changed'] = $this->timeElapsed($fileStats['atime']);
				$file['accessed'] = $this->timeElapsed($fileStats['mtime']);
			}
		}

		/* Format the array */
		$data = array(
			'id_category' => $globals->getValue('category'),
			'id_user' => $user_info['id'],
			'downloads' => 0,
			'name' => $globals->getValue('name'),
			'file' => json_encode($file),
			'demo' => !$globals->getValue('demo') ? '' : $globals->getValue('demo'),
			'version' => !$globals->getValue('version') ? '' : $globals->getValue('version'),
			'id_topic' => !$globals->getValue('topic') ? 0 : $globals->getValue('topic'),
			'smf_version' => !$globals->getValue('smf') ? '' : $globals->getValue('smf'),
			'smf_download' => !$globals->getValue('smfd') ? '' : $globals->getValue('smfd'),
			'description' => !$globals->getValue('desc') ? '' : $globals->getValue('desc'),
			'github' => !$globals->getValue('github') ? '' : $globals->getValue('github'),
			'info' => !$globals->getValue('info') ? '' : $globals->getValue('info'),
			'time' => time(),
		);

		/* Store this already! */
		$this->query->insertMod($data);

		/* Thank you for your services... */
		unset($file['path']);

		/* Back to the main page we go! */
		redirectexit('action=mods');
	}

	public function doDownload()
	{
		global $context;

	}

	public function doSingle()
	{
		/* Set all the page stuff */
		$context['sub_template'] = 'single';
		$context['page_title'] = $this->text->getText('title_single');
		$context['canonical_url'] = $this->scripturl . '?action=mods;sa=single;mid=';
	}

	protected function buildForm($editing = false)
	{
		/* Get all categories */
		$categories = $this->query->getAllCategories();

		/* The form loves multidimensional arrays... */
		foreach($categories as $k => $v)
			$categories[$k] = array(
				$v,
				!empty($editing['cat']) && $editing['cat'] == $v ? true : false,
			);

		/* Build the form */
		$form = new ModSiteForm($this->text);

		/* Common text entries */
		$commonText = array('name', 'file', 'demo', 'version', 'topic', 'github', 'smf', 'smfd');

		/* CommonTextArea */
		$commonTextArea = array('desc', 'info',);

		foreach ($commonText as $c)
			$form->addText(
				$c,
				$c,
				$editing ? $editing[$c] : '',
				55,55
			);

		if (!empty($categories) && is_array($categories))
			$form->addSelect(
				'category',
				'mod_category',
				$categories
			);

		foreach ($commonTextArea  as $c)
			$form->addTextArea(
				$c,
				$c,
				$editing ? $editing[$c] : ''
			);

		return $form->display();
	}

	protected function formatBytes($a_bytes)
	{
		 if ($a_bytes < 1024)
			 return $a_bytes .' B';

		 elseif ($a_bytes < 1048576)
			return round($a_bytes / 1024, 2) .' KiB';

		 elseif ($a_bytes < 1073741824)
			 return round($a_bytes / 1048576, 2) . ' MiB';

		elseif ($a_bytes < 1099511627776)
			 return round($a_bytes / 1073741824, 2) . ' GiB';

		elseif ($a_bytes < 1125899906842624)
			 return round($a_bytes / 1099511627776, 2) .' TiB';

		elseif ($a_bytes < 1152921504606846976)
			 return round($a_bytes / 1125899906842624, 2) .' PiB';

		elseif ($a_bytes < 1180591620717411303424)
			 return round($a_bytes / 1152921504606846976, 2) .' EiB';

		elseif ($a_bytes < 1208925819614629174706176)
			 return round($a_bytes / 1180591620717411303424, 2) .' ZiB';

		else
			 return round($a_bytes / 1208925819614629174706176, 2) .' YiB';
	}

	public function timeElapsed($ptime)
	{
		$etime = time() - $ptime;

		if ($etime < 1)
			return $this->text->getText('time_just_now');

		$a = array(
			12 * 30 * 24 * 60 * 60	=> $this->text->getText('time_year'),
			30 * 24 * 60 * 60		=> $this->text->getText('time_month'),
			24 * 60 * 60			=> $this->text->getText('time_day'),
			60 * 60					=> $this->text->getText('time_hour'),
			60						=> $this->text->getText('time_minute'),
			1						=> $this->text->getText('time_second')
		);

		foreach ($a as $secs => $str)
		{
			$d = $etime / $secs;
			if ($d >= 1)
			{
				$r = round($d);
				return $r . ' ' . $str . ($r > 1 ? 's ' : ' '). $this->text->getText('time_ago');
			}
		}
	}
}