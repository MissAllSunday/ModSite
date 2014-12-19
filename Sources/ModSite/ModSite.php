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

class ModSite extends ModSiteDB
{
	public $name = __CLASS__;
	public $subActions = array(
		'add',
		'add2',
		'delete',
		'edit',
		'listing',
		'search',
		'single',
		'success',
		'download',
		'category',
	);

	public function __construct()
	{
		$this->setRegistry();
	}

	protected function adminAreas(&$areas)
	{
		$areas['config']['areas']['modsettings']['subsections']['modsite'] = array($this->text('title_main'));
	}

	function actions(&$actions)
	{
		$actions['modsite'] = array($this->name .'.php', $this->name .'::call#');
	}

	function menu(&$menu_buttons)
	{
		$insert = $this->setting('menu_position') ? $this->setting('menu_position') : 'home';
		$counter = 0;

		foreach ($menu_buttons as $area => $dummy)
			if (++$counter && $area == $insert )
				break;

		$menu_buttons = array_merge(
			array_slice($menu_buttons, 0, $counter),
			array('modsite' => array(
				'title' => $this->text('title_main'),
				'href' => $this->scriptUrl . '?action=modsite',
				'show' => $this->setting('enable') ? true : false,
			)),
			array_slice($menu_buttons, $counter)
		);
	}

	function modifications(&$sub_actions)
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
				$this->name .'_menu_position',
				array(
					'home' => $txt['home'],
					'help' => $txt['help'],
					'search' => $txt['search'],
					'login' => $txt['login'],
					'register' => $txt['register']
				),
				'subtext' => $this->text('menu_position_sub')
			),
			array('text', $this->name .'_download_path'),
		);

		if ($return_config)
			return $config_vars;

		$context['post_url'] = $this->scriptUrl . '?action=admin;area=modsettings;save;sa=modsite';
		$context['settings_title'] = $this->text('title_main');

		if (empty($config_vars))
		{
			$context['settings_save_dont_show'] = true;
			$context['settings_message'] = '<div align="center">' . $txt['modification_no_misc_settings'] . '</div>';

			return prepareDBSettingContext($config_vars);
		}

		if (isset($_GET['save']))
		{
			checkSession();

			// Should put some checks here but then again, this mod isn't intended for the regular "noob" user...
			saveDBSettings($config_vars);
			redirectexit('action=admin;area=modsettings;sa=modsite');
		}
		prepareDBSettingContext($config_vars);
	}

	// Should really build a function on Ohara to handle adding permissions and avoid building redundant code...
	function permissions(&$permissionGroups, &$permissionList)
	{
		$permissionGroups['membergroup']['simple'] = array($this->name .'_per_simple');
		$permissionGroups['membergroup']['classic'] = array($this->name .'_per_classic');

		$permissionList['membergroup'][$this->name .'_view'] = array(
			false,
			$this->name .'_classic',
			$this->name .'_per_simple');

		$permissionList['membergroup'][$this->name .'_delete'] = array(
			false,
			$this->name .'_per_classic',
			$this->name .'_per_simple');
		$permissionList['membergroup'][$this->name .'_add'] = array(
			false,
			$this->name .'_per_classic',
			$this->name .'_simple');
		$permissionList['membergroup'][$this->name .'_edit'] = array(
			false,
			$this->name .'_per_classic',
			$this->name .'_per_simple');
	}

	function call()
	{
		global $context;

		/* Load both language and template files */
		loadLanguage($this->name);
		loadtemplate($this->name, 'gh-fork-ribbon');

		$context['linktree'][] = array(
			'url' => $this->scriptUrl .'?action=modsite',
			'name' => $this->text('title_main'),
		);

		/* Set some JavaScript to hide blocks */
		$context['html_headers'] .= '
		<script language="JavaScript"  type="text/javascript">
		<!--
		function toggleDiv(divid, obj){
			if(document.getElementById(divid).style.display == \'none\'){
				obj.innerHTML= "Hide";
				document.getElementById(divid).style.display = \'block\';
			}
			else{
				obj.innerHTML= "Expand";
				document.getElementById(divid).style.display = \'none\';
			}
		}
		//-->
		</script>';

		// Get the right subaction.
		$call = $this->data('sa') && in_array($this->data('sa'), $this->subActions) ?  $this->data('sa') : 'main';

		// Call the appropriate method.
		if (!empty($this->setting('enable')) && allowedTo($this->name .'_view'))
		{
			// Use the default template unless a method says otherwise.
			$context['sub_template'] = 'main';

			// Set a nice canonical page.
			$context['canonical_url'] = $this->scriptUrl . '?action=modsite;' .($call != 'main' ? ('sa='. $call) : '');

			// Prepare the pagination vars.
			$this->maxIndex = 10;
			$this->start = $this->data('start') ? $this->data('start') : 0;

			$this->$call();
		}

		// Ain't nobody got time for that!
		else
			fatal_lang_error($this->name .'_error_enable', false);
	}

	protected function main()
	{
		global $context;

		// Get stuff.
		$context[$this->name]['data'] = $this->getAll($start, $maxIndex);

		// Pagination.
		$context['pagination'] = constructPageIndex($this->scriptUrl . '?action=modsite', $this->start, $this->countMods(), $this->maxIndex, false);
	}

	protected function category()
	{
		global $context, $txt, $modSettings;


		// We need a valid ID.
		$catID = $this->data('mid');

		if (!$catID)
			fatal_lang_error($this->name .'_error_no_valid_id', false);

		// Get the cat name.
		$cat = $this->getSingleCat($catID);

		// Get all mods within category X, we are gonna reuse the main template ^-^
		$context[$this->name]['data'] = $this->getBy('cat', $catID);
	}

	function single($pages)
	{
		global $context, $txt, $user_info;

		/* Forget it... */
		if (!isset($_GET['mid']) || empty($_GET['mid']))
			fatal_lang_error('ModSite_error_no_valid_id', false);

		/* Are you allowed to see this page? */
		$pages->permissions('view', true);

		/* Get a valid ID */
		$id = $pages->clean($_GET['mid']);

		if (empty($id))
			fatal_lang_error('ModSite_error_no_valid_id', false);

		/* Get the data, getSingle() uses cache when possible */
		$context['modSite']['single'] = $pages->getSingle($id);

		/* Set all we need */
		$context['sub_template'] = 'ModSite_single';
		$context['canonical_url'] = $this->scriptUrl . '?action=modsite;sa=single;mid=' . $id;
		$context['page_title'] = $context['modSite']['single']['info']['publicName'];
		$context['linktree'][] = array(
			'url' => $context['canonical_url'],
			'name' => $context['page_title'],
		);

		/* Pass the object to the template */
		$context['modSite']['object'] = $pages;
	}

	function listing($pages)
	{
		global $context, $txt;

		/* Are you allowed to see this page? */
		$pages->permissions('view', true);

		/* Page stuff */
		$context['sub_template'] = 'ModSite_list';
		$context['page_title'] = $txt['ModSite_list_title'];
		$context['linktree'][] = array(
			'url' => $this->scriptUrl. '?action=modsite;sa=list',
			'name' => $txt['ModSite_list_title'],
		);

		/* No letter? then show the main page */
		if (!isset($_GET['lidletter']) || empty($_GET['lidletter']))
			$context['modSite']['list'] = $pages->getAll();

		/* Show a list of modsite starting with X letter */
		elseif (isset($_GET['lidletter']))
		{
			$midletter = $pages->clean($_GET['lidletter']);

			/* Replace the linktree and title with something more specific */
			$context['page_title'] = $txt['ModSite_list_title_by_letter'] . $midletter;
			$context['linktree'][] = array(
				'url' => $this->scriptUrl. '?action=modsite;sa=list;lidletter='. $midletter,
				'name' => $txt['ModSite_list_title_by_letter'] . $midletter,
			);

			$context['modSite']['list'] = $pages->getBy('title', $midletter .'%');

			if (empty($context['modSite']['list']))
				fatal_lang_error('ModSite_no_modsite_with_letter', false);
		}

		/* Pass the object to the template */
		$context['modSite']['object'] = $pages;
	}

	function search($pages)
	{
		global $context, $txt;

		/* Are you allowed to see this page? */
		$pages->permissions('view', true);

		/* We need a valur to serch and a column */
		if (!isset($_REQUEST['l_search_value']) || empty($_REQUEST['l_search_value']) || !isset($_REQUEST['l_column']) || empty($_REQUEST['l_column']))
			fatal_lang_error('ModSite_error_no_valid_id', false);

		$value = urlencode($pages->clean($_REQUEST['l_search_value']));
		$column = $pages->clean($_REQUEST['l_column']);

		/* Page stuff */
		$context['sub_template'] = 'ModSite_list';
		$context['page_title'] = $txt['ModSite_search_title'] . $value;
		$context['linktree'][] = array(
			'url' => $this->scriptUrl. '?action=modsite;sa=search',
			'name' => $txt['ModSite_list_title_by_letter'] . $value,
		);

		$context['modSite']['list'] = $pages->getBy($column, '%'. $value .'%');

		if (empty($context['modSite']['list']))
			fatal_lang_error('ModSite_no_modsite_with_letter', false);


		/* Pass the object to the template */
		$context['modSite']['object'] = $pages;
	}

	function download($pages)
	{
		global $context, $boarddir, $modSettings, $user_info;

		/* We need a valid ID and a valid downloads dir */
		if (!isset($_GET['mid']) || empty($modSettings['ModSite_download_path']))
			fatal_lang_error('ModSite_error_no_valid_id', false);

		/* You're not welcome here Mr bot... */
		if (true == $user_info['possibly_robot'])
			redirectexit();

		/* All good, get the file info */
		$mod = $pages->getSingle((int) $pages->clean($_GET['mid']));

		/* Build a correct path, the downloads dir ideally should be outside the web-accessible dir */
		$file_path = $boarddir .'/'. $modSettings['ModSite_download_path'] .'/'. $mod['name'] .'.zip';

		/* Oops! */
		if(!file_exists($file_path))
		{
			global $txt;

			loadLanguage('Errors');
			header('HTTP/1.0 404 ' . $txt['attachment_not_found']);
			header('Content-Type: text/plain; charset=' . (empty($context['character_set']) ? 'ISO-8859-1' : $context['character_set']));

			/* Nothing more to say really */
			die('404 - ' . $txt['attachment_not_found']);
		}

		else
		{
			/* Update the downloads stat */
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

	protected function render($type)
	{
		global $context;

		// Template stuff.
		$context['sub_template'] = $this->name .'_'. $type;
		$context['canonical_url'] = $this->scriptUrl . '?action=modsite';
		$context['page_title'] = $this->text('title_main') . ' - '. (!empty($type) ? $this->text('title_'. $type) : '') . (!empty($this->page) ? ' - '. $this->text('ui_page') .' '. $this->page : '');
	}
}
