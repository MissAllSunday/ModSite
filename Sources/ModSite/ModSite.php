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

// Not that it matters where I require this since this very ow file is require EVERYWHERE!
require_once($sourcedir . '/Ohara.php');

class ModSite extends Ohara
{
	protected static $className = __CLASS__;
	protected $hooks = array();
	protected $subActions = array(
		'add',
		'add2',
		'delete',
		'edit',
		'list',
		'search',
		'single',
		'success',
		'download',
		'category',
	);

	/**
	 * Setup the object, gather all of the relevant settings
	 */
	protected function __construct()
	{
		$this->hooks = array(
			'integrate_menu_buttons' => 'menu',
			'integrate_actions' => 'actions',
			'integrate_load_permissions' => 'permissions',
			'integrate_admin_areas' => 'adminAreas',
			'integrate_modify_modifications' => 'modifications',
		);

		// Call the helper
		parent::__construct();
	}

	protected function adminAreas(&$areas)
	{
		$areas['config']['areas']['modsettings']['subsections']['modsite'] = array($this->text('title_main'));
	}

	function actions(&$actions)
	{
		$actions['modsite'] = array(self::$className .'/'. self::$className .'.php', self::$className .'::dispatch');
	}

	function menu(&$menu_buttons)
	{
			global $scripturl;

			$insert = $this->setting('menu_position') ? $this->setting('menu_position') : 'home';
			$counter = 0;

			foreach ($menu_buttons as $area => $dummy)
				if (++$counter && $area == $insert )
					break;

			$menu_buttons = array_merge(
				array_slice($menu_buttons, 0, $counter),
				array('modsite' => array(
					'title' => $this->text('title_main'),
					'href' => $scripturl . '?action=modsite',
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
		global $context, $scripturl, $txt;

		$config_vars = array(
			array('desc', self::$className .'_admin_sub'),
			array('check', self::$className .'_enable', 'subtext' => $this->text('enable_sub')),
			array('int', self::$className .'_latest_limit', 'subtext' => $this->text('latest_limit_sub'), 'size' => 3),
			array('int', self::$className .'_pag_limit', 'subtext' => $this->text('pag_limit_sub'), 'size' => 3),
			array('text', self::$className .'_json_dir', 'subtext' => $this->text('json_dir_sub')),
			array('text', self::$className .'_github_username', 'subtext' => $this->text('github_username_sub')),
			array(
				'select',
				self::$className .'_menu_position',
				array(
					'home' => $txt['home'],
					'help' => $txt['help'],
					'search' => $txt['search'],
					'login' => $txt['login'],
					'register' => $txt['register']
				),
				'subtext' => $this->text('menu_position_sub')
			),
			array('text', self::$className .'_download_path'),
		);

		if ($return_config)
			return $config_vars;

		$context['post_url'] = $scripturl . '?action=admin;area=modsettings;save;sa=modsite';
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

			// Should put some checks here but then again, this mod isn't intended for the regular "dumb" user...
			saveDBSettings($config_vars);
			redirectexit('action=admin;area=modsettings;sa=modsite');
		}
		prepareDBSettingContext($config_vars);
	}

	// Should really build a function on Ohara to handle adding permissions and avoid building redundant code...
	function permissions(&$permissionGroups, &$permissionList)
	{
		$permissionGroups['membergroup']['simple'] = array(self::$className .'_per_simple');
		$permissionGroups['membergroup']['classic'] = array(self::$className .'_per_classic');

		$permissionList['membergroup'][self::$className .'_view'] = array(
			false,
			self::$className .'_classic',
			self::$className .'_per_simple');

		$permissionList['membergroup'][self::$className .'_delete'] = array(
			false,
			self::$className .'_per_classic',
			self::$className .'_per_simple');
		$permissionList['membergroup'][self::$className .'_add'] = array(
			false,
			self::$className .'_per_classic',
			self::$className .'_simple');
		$permissionList['membergroup'][self::$className .'_edit'] = array(
			false,
			self::$className .'_per_classic',
			self::$className .'_per_simple');
	}

	function dispatch()
	{
		global $sourcedir, $context;

		/* Load both language and template files */
		loadLanguage('ModSite');
		loadtemplate('ModSite', 'gh-fork-ribbon');

		// This is a good time to stuff the memory with yet moar files!
		require_once($sourcedir . '/ModSite/ModSiteDB.php');
		require_once($sourcedir . '/ModSite/ModSiteParser.php');

		$this->db = new ModSiteDB();
		$this->parser = new ModSiteParser();

		$context['linktree'][] = array(
			'url' => $scripturl. '?action=modsite',
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

		/* It is faster to use $var() than use call_user_func_array */
		$func = $this->data('sa');

		$call = !empty($func) && isset($this->subActions[$func]) ?  $func : 'main';

		// Call the appropriate method if the mod is enable
		if (!empty($this->setting('enable')))
			$call();

		else
			fatal_lang_error(self::$className .'_error_enable', false);
	}

	function main()
	{
		global $context, $scripturl, $txt, $modSettings;

		/* Getting the current page. */
		$page = $this->data('page') ? $this->data('page') : 0;

		/* Are you allowed to see this page? */
		$this->permissions('view', true);

		// Template stuff.
		$context['sub_template'] = self::$className .'_main';
		$context['canonical_url'] = $scripturl . '?action=modsite';
		$context['page_title'] = $this->text('title_main') . (!empty($page) ? ' - '. $this->text('ui_page') .' '. $page : '');

		/* Set the pagination and send everything to the template */
		modsite_pagination($pages->getAll());
	}

	function modsite_category($pages)
	{
		global $context, $scripturl, $txt, $modSettings;

		/* Are you allowed to see this page? */
		$pages->permissions('view', true);

		/* Getting the current page. */
		$page = !empty($_GET['page']) ? ( int) trim($_GET['page']) : 1;

		/* Set some needed vars */
		$context['sub_template'] = 'ModSite_main';
		$context['canonical_url'] = $scripturl . '?action=modsite;sa=category';

		/* We need a valid ID */
		if (!isset($_GET['mid']) || empty($_GET['mid']))
			fatal_lang_error('ModSite_error_no_valid_id', false);

		$catID = (int) $pages->clean($_GET['mid']);

		/* Get the cat name */
		$cat = $pages->getSingleCat($catID);

		/* Get all mods within category X, we are gonna reuse the main template ^-^ */
		modsite_pagination($pages->getBy('cat', $catID));

		/* We got what we need, pass it to the template */
		$context['page_title'] = $txt['ModSite_ui_cat'] .' - '. $cat['name'] .' - '. $txt['ModSite_ui_page'] .' '. $page ;;
		$context['linktree'][] = array(
			'url' => $scripturl. '?action=modsite;sa=category;mid='. $catID,
			'name' => $context['page_title'],
		);

		/* Pass the object to the template */
		$context['modSite']['object'] = $pages;
	}

	function modsite_add($pages)
	{
		global $context, $scripturl, $txt, $sourcedir;

		/* Check permissions */
		$pages->permissions('add', true);

		$context['sub_template'] = 'ModSite_add';
		$context['page_title'] = $txt['ModSite_edit_creating'];
		$context['linktree'][] = array(
			'url' => $scripturl. '?action=modsite;sa=add',
			'name' => $context['page_title'],
		);

		/* Pass the object to the template */
		$context['modSite']['object'] = $pages;

		/* Tell the template we are adding, not editing */
		$context['modSite']['edit'] = false;
	}

	function modsite_add2($pages)
	{
		checkSession('post', '', true);

		/* Check permissions */
		$pages->permissions(isset($_REQUEST['edit']) ? 'edit' : 'add', true);

		/* Gotta send the user back to the form and tell them theres a missing field */
		if (empty($_REQUEST['name']))
			redirectexit('action=modsite;sa=add;missing=name');

		/* Set the method name */
		$method = 'add';

		/* Let us continue... */
		$data = array(
			$pages->clean($_REQUEST['name']),
		);

		/* Are we editing */
		if(isset($_REQUEST['edit']))
		{
			/* If editing, we need the ID */
			if (!isset($_GET['mid']) || empty($_GET['mid']))
				redirectexit('action=modsite;sa=add;missing=ID');

			/* Make some checks if we are editing */
			else
			{
				$mid = (int) $pages->clean($_GET['mid']);

				/* Make sure it does exists... */
				$current = $pages->doesExists($mid);

				/* Tell the user this entry doesn't exists anymore */
				if (empty($current))
					fatal_lang_error('ModSite_error_no_valid_id', false);
			}

			/* All good, append the ID */
			$data += array(
				!empty($mid) ? $mid : 0,
			);

			/* And finally, change the method */
			$method = 'edit';
		}

		/* Call the DB */
		$pages->$method($data);

		/* All done, show a nice page */
		redirectexit('action=modsite;sa=success;pin='. $method);
	}

	function modsite_edit($pages)
	{
		global $context, $scripturl, $modSettings, $sourcedir, $txt;

		$pages->permissions('edit', true);

		if (!isset($_GET['mid']) || empty($_GET['mid']))
			redirectexit('action=modsite');

		/* Pass the object to the template */
		$context['modSite']['object'] = $pages;

		$mid = (int) $pages->clean($_GET['mid']);

		/* Get the mod */
		$temp = $pages->getSingle($mid);

		if (empty($temp))
			fatal_lang_error('ModSite_no_valid_id', false);

		$context['modSite']['edit'] = $temp;
		$context['sub_template'] = 'ModSite_add';
		$context['page_title'] = $txt['ModSite_preview_edit'] .' - '. $context['modSite']['edit']['title'];
		$context['linktree'][] = array(
			'url' => $scripturl. '?action=modsite;sa=edit;mid='. $mid,
			'name' => $txt['ModSite_preview_edit'] .' - '. $context['modSite']['edit']['title'],
		);
	}

	function modsite_delete($pages)
	{
		global $context, $txt;

		$pages->permissions('delete', true);

		/* Gotta have an ID to work with */
		if (!isset($_GET['mid']) || empty($_GET['mid']))
			redirectexit('action=modsite');

		else
		{
			$mid = (int) $pages->clean($_GET['mid']);
			$pages->delete($mid);
			redirectexit('action=modsite;sa=success;pin=delete');
		}
	}

	function modsite_success($pages)
	{
		global $context, $scripturl, $txt;

		/* No direct access */
		if (!isset($_GET['pin']) || empty($_GET['pin']))
			redirectexit('action=modsite');

		$context['modSite']['pin'] = $pages->clean($_GET['pin']);

		/* Build the link tree.... */
		$context['page_title'] = $txt['ModSite_success_message_title'];
		$context['linktree'][] = array(
			'url' => $scripturl . '?action=modsite;sa=success',
			'name' => $context['page_title'],
		);

		$context['sub_template'] = 'ModSite_success';
		$context['modSite']['message'] = $txt['ModSite_success_message_'. $context['modSite']['pin']];

		/* Pass the object to the template */
		$context['modSite']['object'] = $pages;
	}

	function modsite_single($pages)
	{
		global $context, $scripturl, $txt, $user_info;

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
		$context['canonical_url'] = $scripturl . '?action=modsite;sa=single;mid=' . $id;
		$context['page_title'] = $context['modSite']['single']['info']['publicName'];
		$context['linktree'][] = array(
			'url' => $context['canonical_url'],
			'name' => $context['page_title'],
		);

		/* Pass the object to the template */
		$context['modSite']['object'] = $pages;
	}

	function modsite_list($pages)
	{
		global $context, $txt, $scripturl;

		/* Are you allowed to see this page? */
		$pages->permissions('view', true);

		/* Page stuff */
		$context['sub_template'] = 'ModSite_list';
		$context['page_title'] = $txt['ModSite_list_title'];
		$context['linktree'][] = array(
			'url' => $scripturl. '?action=modsite;sa=list',
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
				'url' => $scripturl. '?action=modsite;sa=list;lidletter='. $midletter,
				'name' => $txt['ModSite_list_title_by_letter'] . $midletter,
			);

			$context['modSite']['list'] = $pages->getBy('title', $midletter .'%');

			if (empty($context['modSite']['list']))
				fatal_lang_error('ModSite_no_modsite_with_letter', false);
		}

		/* Pass the object to the template */
		$context['modSite']['object'] = $pages;
	}

	function modsite_search($pages)
	{
		global $context, $txt, $scripturl;

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
			'url' => $scripturl. '?action=modsite;sa=search',
			'name' => $txt['ModSite_list_title_by_letter'] . $value,
		);

		$context['modSite']['list'] = $pages->getBy($column, '%'. $value .'%');

		if (empty($context['modSite']['list']))
			fatal_lang_error('ModSite_no_modsite_with_letter', false);


		/* Pass the object to the template */
		$context['modSite']['object'] = $pages;
	}

	function modsite_download($pages)
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

	function modsite_pagination($array)
	{
		global $sourcedir, $context, $scripturl;

		if (empty($array) || !is_array($array))
			return false;

		/* Get the pagination class */
		require_once($sourcedir . '/OharaPagination.php');

		/* Getting the current page. */
		$page = !empty($_GET['page']) ? ( int) trim($_GET['page']) : 1;

		/* Applying pagination. */
		$pagination = new OharaPagination($array, $page,'?action=modsite;page=', '', 10, 3);
		$pagination->PaginationArray();
		$pagtrue = $pagination->PagTrue();

		/* Send the array to the template if there is pagination */
		if ($pagtrue)
		{
			$context['modSite']['all'] = $pagination->OutputArray();
			$context['modSite']['panel'] = $pagination->OutputPanel();
		}

		/* If not, then let's use the default array */
		else
		{
			$context['modSite']['all'] = $array;
			$context['modSite']['panel'] = '';
		}
	}

	protected function permissions($type, $fatal_error = false)
	{
		global $modSettings;

		$type = is_array($type) ? array_unique($type) : array($type);
		$allowed = array();

		if (empty($type))
			return false;

		/* The mod must be enable */
		if (empty($this->setting('enable')))
			fatal_lang_error('ModSite_error_enable', false);

		/* collect the permissions */
		foreach ($type as $t)
				$allowed[] = (allowedTo('ModSite_'. $t) == true ? 1 : 0);

		/* You need at least 1 permission to be true */
		if ($fatal_error == true && !in_array(1, $allowed))
			isAllowedTo('ModSite_'. $t);

		elseif ($fatal_error == false && !in_array(1, $allowed))
			return false;

		elseif ($fatal_error == false && in_array(1, $allowed))
			return true;
	}
}
