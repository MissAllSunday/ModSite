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

// Use Ohara! manually :(
require_once ($sourcedir .'/ohara/src/Suki/Ohara.php');
require_once ($sourcedir .'/ModSiteTools.php');

class ModSite extends ModSiteTools
{
	public $name = __CLASS__;
	public $subActions = array(
		'single',
		'tags',
		'tag',
		'create',
		'update',
		'delete',
	);

	public function __construct()
	{
		parent::__construct();
	}

	function menu(&$menu_buttons)
	{
		$insert = $this->enable('menuPosition') ? $this->setting('menuPosition') : 'home';
		$counter = 0;

		foreach ($menu_buttons as $area => $dummy)
			if (++$counter && $area == $insert )
				break;

		$menu_buttons = array_merge(
			array_slice($menu_buttons, 0, $counter),
			array('modsite' => array(
				'title' => $this->text('modName'),
				'href' => $this->scriptUrl . '?action='. $this->name,
				'show' => $this->setting('enable') ? true : false,
			)),
			array_slice($menu_buttons, $counter)
		);
	}

	function call()
	{
		global $context;

		// Load both language and template files.
		loadLanguage($this->name);
		loadtemplate($this->name);

		$this->_sa = $this->data('sa');
		$this->_mid = $this->data('mid');

		$context['linktree'][] = array(
			'url' => $this->scriptUrl .'?action='. $this->name,
			'name' => $this->text('modName'),
		);

		// Get the right subaction.
		$call = $this->_sa && in_array($this->_sa, $this->subActions) ?  $this->_sa : 'main';

		// Call the appropriate method.
		if ($this->enable('master'))
		{
			// Use the default template unless a method says otherwise.
			$context['sub_template'] = $this->name .'_'. $call;

			// Set a nice canonical page.
			$context['canonical_url'] = $this->scriptUrl . '?action='. $this->name .';' .($call != 'main' ? ('sa='. $call) : '');

			// Prepare the pagination vars.
			$this->maxIndex = 10;
			$this->start = (int) $this->data('start');

			// Re-declare all the context stuff.
			$context['canonical_url'] = $this->scriptUrl . '?action='. $this->name .';sa='. $call . ($this->_mid ? ';mid='. $this->_mid : '');
			$context['page_title'] = $this->text('action_'. $call);
			$context['linktree'][] = array(
				'url' => $context['canonical_url'],
				'name' => $context['page_title'],
			);

			// To infinity and beyond!
			$this->{$call}();
		}

		// Ain't nobody got time for that!
		else
			fatal_lang_error($this->name .'_error_enable', false);
	}

	protected function main()
	{
		global $context;

		// Get stuff.
		$context['data'] = $this->getAll($start, $maxIndex);

		// Pagination.
		$context['pagination'] = constructPageIndex($this->scriptUrl . '?action='. $this->name .'', $this->start, $this->countMods(), $this->maxIndex, false);
	}

	protected function tag()
	{
		global $context, $txt, $modSettings;

		// We need a valid ID.
		$tagID = $this->data('tag');

		if (!$tagID)
			return fatal_lang_error($this->name .'_error_no_valid_id', false);

		// Get the cat name.
		$tagName = $this->getSingleTag($tagID);

		// Get all mods within tag X, we are gonna reuse the main template ^-^
		$context[$this->name]['data'] = $this->getBy('tag', $tagID);
	}

	function single()
	{
		global $context, $txt, $user_info;

		// Kinda need an ID.
		if (!$this->_mid)
			return fatal_lang_error('ModSite_error_no_valid_id', false);

		// Get the data, getSingle() uses cache when possible.
		$context[$this->name]['data'] = $pages->getSingle($id);

		// Set all we need.
		$context['sub_template'] = $this->name .'_single';
	}

	function download()
	{
		global $context, $boarddir, $modSettings, $user_info;

		// We need a valid ID and a valid downloads dir.
		if (!$this->data('mid') || !$this->setting('download_path'))
			return fatal_lang_error('ModSite_error_no_valid_id', false);

		// You're not welcome here Mr. Roboto...
		if (true == $user_info['possibly_robot'])
			redirectexit();

		// All good, get the file info.
		$mod = $pages->getSingle((int) $pages->clean($_GET['mid']));

		// Build a correct path, the downloads dir ideally should be outside the web-accessible dir.
		$file_path = $boarddir .'/'. $modSettings['ModSite_download_path'] .'/'. $mod['name'] .'.zip';

		// Oops!.
		if(!file_exists($file_path))
		{
			global $txt;

			loadLanguage('Errors');
			header('HTTP/1.0 404 ' . $txt['attachment_not_found']);
			header('Content-Type: text/plain; charset=' . (empty($context['character_set']) ? 'ISO-8859-1' : $context['character_set']));

			// Nothing more to say really.
			die('404 - ' . $txt['attachment_not_found']);
		}

		else
		{
			// Update the downloads stat.
			$pages->updateCount($mod['id']);

			// Get the file's extension
			$ext = substr($file_path, strrpos($file_path, '.') + 1);

			// Turn off gzip for IE browsers
			if(ini_get('zlib.output_compression'))
				ini_set('zlib.output_compression', 'Off');

			// clear anything that is in the buffers
			while (@ob_get_level() > 0)
				@ob_end_clean();

			// Set headers to force file download
			header('Pragma: ');
			if (!$context['browser']['is_gecko'])
				header('Content-Transfer-Encoding: binary');
			header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($file_path)) . ' GMT');
			header('Accept-Ranges: bytes');
			header('Content-Length: ' . filesize($file_path));
			header('Content-Encoding: none');
			header('Connection: close');
			header('ETag: ' . md5_file($file_path));
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename="' . basename($file_path) .'"');

			// Read the file and write it to the output buffer
			readfile($file_path);

			// done so we need to end
			exit;
		}
	}

	public function addAdminArea(&$areas)
	{
		$areas['config']['areas'][$this->name] = array(
			'label' => $this->text('modName'),
			'file' => $this->name .'.php',
			'function' => $this->name .'::adminCall#',
			'icon' => 'posts',
			'subsections' => array(
				'settings' => array($this->text('modName')),
			),
		);
		$areas['maintenance']['areas']['logs']['subsections']['topicsolvedlog'] = array($this->text('modName'), 'TopicSolved::displayLog#', 'disabled' => !$this->enable('master'));
	}

	public function adminCall()
	{
		global $context;
		require_once($this->sourceDir . '/ManageSettings.php');
		$context['page_title'] = $this->text('modName');
		// Redundant much!?
		$subActions = array(
			'settings' => 'settings',
		);
		loadGeneralSettingParameters($subActions, 'settings');
		$context[$context['admin_menu_name']]['tab_data'] = array(
			'tabs' => array(
				'settings' => array(),
			),
		);
		$this->_sa = isset($subActions[$this->data('sa')]) ? $subActions[$this->data('sa')] : 'settings';
		$this->{$this->_sa}();
	}

	function addModifications(&$sub_actions)
	{
		global $context;

		$sub_actions['modsite'] = 'modify_modsite_post_settings';
		$context[$context['admin_menu_name']]['tab_data']['tabs']['modsite'] = array();
	}

	function settings(&$return_config = false)
	{
		global $context, $txt;

		$config_vars = array(
			array('desc', $this->name .'_admin_sub'),
			array('check', $this->name .'_enable', 'subtext' => $this->text('enable_sub')),
			array('int', $this->name .'_latest_limit', 'subtext' => $this->text('latest_limit_sub'), 'size' => 3),
			array('int', $this->name .'_pag_limit', 'subtext' => $this->text('pag_limit_sub'), 'size' => 3),
			array('text', $this->name .'_json_dir', 'subtext' => $this->text('json_dir_sub')),
			array('text', $this->name .'_github_username', 'subtext' => $this->text('github_username_sub')),
			array(
				'select',
				$this->name .'_menuPosition',
				array(
					'home' => $txt['home'],
					'help' => $txt['help'],
					'search' => $txt['search'],
					'login' => $txt['login'],
					'register' => $txt['register']
				),
				'subtext' => $this->text('menuPosition_sub')
			),
			array('text', $this->name .'_download_path'),
		);

		if ($return_config)
			return $config_vars;

		$context['post_url'] = $this->scriptUrl . '?action=admin;area=modsettings;save;sa=modsite';
		$context['settings_title'] = $this->text('modName');

		if (empty($config_vars))
		{
			$context['settings_save_dont_show'] = true;
			$context['settings_message'] = '<div align="center">' . $txt['modification_no_misc_settings'] . '</div>';

			return prepareDBSettingContext($config_vars);
		}

		if ($this->validate('save'))
		{
			checkSession();

			// Should put some checks here but then again, this mod isn't intended for the regular "noob" user...
			saveDBSettings($config_vars);
			redirectexit('action=admin;area=modsettings;sa=modsite');
		}
		prepareDBSettingContext($config_vars);
	}
}
