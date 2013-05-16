<?php

/**
 * @package ModSite mod
 * @version 1.0
 * @author Suki <missallsunday@simplemachines.org>
 * @copyright 2013 Suki
 */

if (!defined('SMF'))
	die('Hacking attempt...');

function modsite_admin_areas(&$areas)
{
	global $txt;

	loadLanguage('ModSite');

	$areas['config']['areas']['modsettings']['subsections']['modsite'] = array($txt['modSite_title_main']);
}

function modsite_actions(&$actions)
{
	$actions['modsite'] = array('ModSite/ModSite.php', 'modSite_dispatch');
}

function modsite_menu(&$menu_buttons)
{
		global $scripturl, $modSettings, $txt;

		loadLanguage('ModSite');

		$insert = !empty($modSettings['modSite_menu_position']) ? $modSettings['modSite_menu_position'] : 'home';
		$counter = 0;

		foreach ($menu_buttons as $area => $dummy)
			if (++$counter && $area == $insert )
				break;

		$menu_buttons = array_merge(
			array_slice($menu_buttons, 0, $counter),
			array('modsite' => array(
				'title' => $txt['modSite_title_main'],
				'href' => $scripturl . '?action=modsite',
				'show' => empty($modSettings['modSite_enable']) ? false : true,
			)),
			array_slice($menu_buttons, $counter)
		);
}

function modsite_modify_modifications(&$sub_actions)
{
	global $context;

	$sub_actions['modsite'] = 'modify_modsite_post_settings';
	$context[$context['admin_menu_name']]['tab_data']['tabs']['modsite'] = array();
}

function modify_modsite_post_settings(&$return_config = false)
{
	global $context, $scripturl, $txt;

	$config_vars = array(
		array('desc', 'modSite_admin_desc'),
		array('check', 'modSite_enable', 'subtext' => $txt['modSite_enable_desc']),
		array('int', 'modSite_latest_limit', 'subtext' => $txt['modSite_latest_limit_desc'], 'size' => 3),
		array('int', 'modSite_pag_limit', 'subtext' => $txt['modSite_pag_limit_desc'], 'size' => 3),
		array('text', 'modsite_json_dir', 'subtext' => $txt['modsite_json_dir_desc']),
		array('text', 'modsite_github_username', 'subtext' => $txt['modsite_github_username_desc']),
		array(
			'select',
			'modSite_menu_position',
			array(
				'home' => $txt['home'],
				'help' => $txt['help'],
				'search' => $txt['search'],
				'login' => $txt['login'],
				'register' => $txt['register']
			),
			'subtext' => $txt['modSite_menu_position_desc']
		),
		array('text', 'modSite_download_path'),
	);

	if ($return_config)
		return $config_vars;

	$context['post_url'] = $scripturl . '?action=admin;area=modsettings;save;sa=modsite';
	$context['settings_title'] = $txt['modSite_title_main'];

	if (empty($config_vars))
	{
		$context['settings_save_dont_show'] = true;
		$context['settings_message'] = '<div align="center">' . $txt['modification_no_misc_settings'] . '</div>';

		return prepareDBSettingContext($config_vars);
	}

	if (isset($_GET['save']))
	{
		checkSession();
		$save_vars = $config_vars;
		saveDBSettings($save_vars);
		redirectexit('action=admin;area=modsettings;sa=modsite');
	}
	prepareDBSettingContext($config_vars);
}

function modsite_permissions(&$permissionGroups, &$permissionList)
{
	$permissionGroups['membergroup']['simple'] = array('modsite_per_simple');
	$permissionGroups['membergroup']['classic'] = array('modsite_per_classic');

	$permissionList['membergroup']['modsite_view'] = array(
		false,
		'modsite_per_classic',
		'modsite_per_simple');

	$permissionList['membergroup']['modsite_delete'] = array(
		false,
		'modsite_per_classic',
		'modsite_per_simple');
	$permissionList['membergroup']['modsite_add'] = array(
		false,
		'modsite_per_classic',
		'modsite_per_simple');
	$permissionList['membergroup']['modsite_edit'] = array(
		false,
		'modsite_per_classic',
		'modsite_per_simple');
}

function modsite_dispatch()
{
	global $txt, $sourcedir, $modSettings, $context, $scripturl, $settings;
	static $mainObj;

		/* Safety first, hardcode the actions */
		$subActions = array(
			'add',
			'add2',
			'delete',
			'edit',
			'list',
			'search',
			'single',
			'success',
			'download',
		);

		if (empty($mainObj))
		{
			require_once($sourcedir .'/ModSite/ModSiteParser.php');
			require_once($sourcedir .'/ModSite/Subs-ModSite.php');

			$mainObj = new modsite();
		}

		/* Load both language and template files */
		loadLanguage('ModSite');
		loadtemplate('ModSite', 'gh-fork-ribbon');

		$context['linktree'][] = array(
			'url' => $scripturl. '?action=modsite',
			'name' => $txt['modSite_title_main'],
		);

		/* Set some JavaScript to hide blocks */
		$context['html_headers'] .= '
	<script language="JavaScript"  type="text/javascript">
	<!--
	function toggleDiv(divid){
		if(document.getElementById(divid).style.display == \'none\'){
			document.getElementById(divid).style.display = \'block\';
		}
		else{
			document.getElementById(divid).style.display = \'none\';
		}
	}
	//-->
	</script>';

		/* It is faster to use $var() than use call_user_func_array */
		if (isset($_GET['sa']))
			$func = $mainObj->clean($_GET['sa']);

		$call = 'modSite_' .(!empty($func) && in_array($func, array_values($subActions)) ?  $func : 'main');

		// Call the appropiate method if the mod is enable
		if (!empty($modSettings['modSite_enable']))
			$call($mainObj);

		else
		fatal_lang_error('modSite_error_enable', false);
}

function modsite_main($mainObj)
{
	global $context, $scripturl, $txt, $modSettings;

	/* Are you allowed to see this page? */
	$mainObj->permissions('view', true);
	$context['sub_template'] = 'modSite_main';
	$context['canonical_url'] = $scripturl . '?action=modsite';
	$context['page_title'] = $txt['modSite_title_main'];

	/* Pass the object to the template */
	$context['modSite']['object'] = $mainObj;

	/* Get the latest modsite from DB */
	$context['modSite']['all'] = $mainObj->getAll();
}

function modsite_add($mainObj)
{
	global $context, $scripturl, $txt, $sourcedir;

	/* Check permissions */
	$mainObj->permissions('add', true);

	$context['sub_template'] = 'modSite_add';
	$context['page_title'] = $txt['modSite_edit_creating'];
	$context['linktree'][] = array(
		'url' => $scripturl. '?action=modsite;sa=add',
		'name' => $context['page_title'],
	);

	/* Pass the object to the template */
	$context['modSite']['object'] = $mainObj;

	/* Tell the template we are adding, not editing */
	$context['modSite']['edit'] = false;
}

function modsite_add2($mainObj)
{
	checkSession('post', '', true);

	/* Check permissions */
	$mainObj->permissions(isset($_REQUEST['edit']) ? 'edit' : 'add', true);

	/* Gotta send the user back to the form and tell them theres a missing field */
	if (empty($_REQUEST['name']))
		redirectexit('action=modsite;sa=add;missing=name');

	/* Set the method name */
	$method = 'add';

	/* Let us continue... */
	$data = array(
		$mainObj->clean($_REQUEST['name']),
	);

	/* Are we editing */
	if($_REQUEST['edit'])
	{
		/* If editing, we need the ID */
		if (!isset($_GET['mid']) || empty($_GET['mid']))
			redirectexit('action=modsite;sa=add;missing=ID');

		/* Make some checks if we are editing */
		else
		{
			$mid = (int) $mainObj->clean($_GET['mid']);

			/* Make sure it does exists... */
			$current = $mainObj->doesExists($mid);

			/* Tell the user this entry doesn't exists anymore */
			if (empty($current))
				fatal_lang_error('modSite_error_no_valid_id', false);
		}

		/* All good, append the ID */
		$data += array(
			!empty($mid) ? $mid : 0,
		);

		/* And finally, change the method */
		$method = 'edit';
	}

	/* Call the DB */
	$mainObj->$method($data);

	/* All done, show a nice page */
	redirectexit('action=modsite;sa=success;pin='. $method);
}

function modsite_edit($mainObj)
{
	global $context, $scripturl, $modSettings, $sourcedir, $txt;

	$mainObj->permissions('edit', true);

	if (!isset($_GET['mid']) || empty($_GET['mid']))
		redirectexit('action=modsite');

	/* Pass the object to the template */
	$context['modSite']['object'] = $mainObj;

	$mid = (int) $mainObj->clean($_GET['mid']);

	/* Get the mod */
	$temp = $mainObj->getSingle($mid);

	if (empty($temp))
		fatal_lang_error('modSite_no_valid_id', false);

	$context['modSite']['edit'] = $temp[$mid];
	$context['sub_template'] = 'modSite_add';
	$context['page_title'] = $txt['modSite_preview_edit'] .' - '. $context['modSite']['edit']['title'];
	$context['linktree'][] = array(
		'url' => $scripturl. '?action=modsite;sa=edit;mid='. $mid,
		'name' => $txt['modSite_preview_edit'] .' - '. $context['modSite']['edit']['title'],
	);
}

function modsite_delete($mainObj)
{
	global $context, $txt;

	$mainObj->permissions('delete', true);

	/* Gotta have an ID to work with */
	if (!isset($_GET['mid']) || empty($_GET['mid']))
		redirectexit('action=modsite');

	else
	{
		$mid = (int) $mainObj->clean($_GET['mid']);
		$mainObj->delete($mid);
		redirectexit('action=modsite;sa=success;pin=delete');
	}
}

function modsite_success($mainObj)
{
	global $context, $scripturl, $txt;

	/* No direct access */
	if (!isset($_GET['pin']) || empty($_GET['pin']))
		redirectexit('action=modsite');

	$context['modSite']['pin'] = $mainObj->clean($_GET['pin']);

	/* Build the link tree.... */
	$context['page_title'] = $txt['modSite_success_message_title'];
	$context['linktree'][] = array(
		'url' => $scripturl . '?action=modsite;sa=success',
		'name' => $context['page_title'],
	);

	$context['sub_template'] = 'modSite_success';
	$context['modSite']['message'] = $txt['modSite_success_message_'. $context['modSite']['pin']];

	/* Pass the object to the template */
	$context['modSite']['object'] = $mainObj;
}

function modsite_single($mainObj)
{
	global $context, $scripturl, $txt, $user_info;

	/* Forget it... */
	if (!isset($_GET['mid']) || empty($_GET['mid']))
		fatal_lang_error('modSite_error_no_valid_id', false);

	/* Are you allowed to see this page? */
	$mainObj->permissions('view', true);

	/* Get a valid ID */
	$id = $mainObj->clean($_GET['mid']);

	if (empty($id))
		fatal_lang_error('modSite_error_no_valid_id', false);

	/* Does the data has been already loaded? */
	if (!empty($context['modSite_all'][$id]))
		$context['modSite']['single'] = $context['modSite_all'][$id];

	/* No? bugger.. well, get it from the DB */
	else
		$context['modSite']['single'] = $mainObj->getSingle($id);

	/* Set all we need */
	$context['sub_template'] = 'modSite_single';
	$context['canonical_url'] = $scripturl . '?action=modsite;sa=single;mid=' . $id;
	$context['page_title'] = $context['modSite']['single']['title'] .' - '. $context['modSite']['single']['artist'];
	$context['linktree'][] = array(
		'url' => $context['canonical_url'],
		'name' => $context['page_title'],
	);

	/* Pass the object to the template */
	$context['modSite']['object'] = $mainObj;
}

function modsite_list($mainObj)
{
	global $context, $txt, $scripturl;

	/* Are you allowed to see this page? */
	$mainObj->permissions('view', true);

	/* Page stuff */
	$context['sub_template'] = 'modSite_list';
	$context['page_title'] = $txt['modSite_list_title'];
	$context['linktree'][] = array(
		'url' => $scripturl. '?action=modsite;sa=list',
		'name' => $txt['modSite_list_title'],
	);

	/* No letter? then show the main page */
	if (!isset($_GET['lidletter']) || empty($_GET['lidletter']))
		$context['modSite']['list'] = $mainObj->getAll();

	/* Show a list of modsite starting with X letter */
	elseif (isset($_GET['lidletter']))
	{
		$midletter = $mainObj->clean($_GET['lidletter']);

		/* Replace the linktree and title with something more specific */
		$context['page_title'] = $txt['modSite_list_title_by_letter'] . $midletter;
		$context['linktree'][] = array(
			'url' => $scripturl. '?action=modsite;sa=list;lidletter='. $midletter,
			'name' => $txt['modSite_list_title_by_letter'] . $midletter,
		);

		$context['modSite']['list'] = $mainObj->getBy('title', $midletter .'%');

		if (empty($context['modSite']['list']))
			fatal_lang_error('modSite_no_modsite_with_letter', false);
	}

	/* Pass the object to the template */
	$context['modSite']['object'] = $mainObj;
}

function modsite_search($mainObj)
{
	global $context, $txt, $scripturl;

	/* Are you allowed to see this page? */
	$mainObj->permissions('view', true);

	/* We need a valur to serch and a column */
	if (!isset($_REQUEST['l_search_value']) || empty($_REQUEST['l_search_value']) || !isset($_REQUEST['l_column']) || empty($_REQUEST['l_column']))
		fatal_lang_error('modSite_error_no_valid_id', false);

	$value = urlencode($mainObj->clean($_REQUEST['l_search_value']));
	$column = $mainObj->clean($_REQUEST['l_column']);

	/* Page stuff */
	$context['sub_template'] = 'modSite_list';
	$context['page_title'] = $txt['modSite_search_title'] . $value;
	$context['linktree'][] = array(
		'url' => $scripturl. '?action=modsite;sa=search',
		'name' => $txt['modSite_list_title_by_letter'] . $value,
	);

	$context['modSite']['list'] = $mainObj->getBy($column, '%'. $value .'%');

	if (empty($context['modSite']['list']))
		fatal_lang_error('modSite_no_modsite_with_letter', false);


	/* Pass the object to the template */
	$context['modSite']['object'] = $mainObj;
}

function modsite_download($mainObj)
{
	global $context, $boarddir, $modSettings, $user_info;

	/* We need a valid ID and a valid downloads dir */
	if (!isset($_GET['mid']) || empty($modSettings['modSite_download_path']))
		fatal_lang_error('modSite_error_no_valid_id', false);

	/* You're not welcome here Mr bot... */
	if (true == $user_info['possibly_robot'])
		redirectexit();

	/* All good, get the file info */
	$mod = $mainObj->getSingle((int) $mainObj->clean($_GET['mid']));

	/* Build a correct path, the downloads dir ideally should be outside the web-accessible dir */
	$file_path = $boarddir .'/'. $modSettings['modSite_download_path'] .'/'. $mod['name'] .'.zip';

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
		$mainObj->updateCount($mod['id']);

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