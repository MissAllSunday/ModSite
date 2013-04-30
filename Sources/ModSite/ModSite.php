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
	$permissionList['membergroup']['modsite_deleteOwn'] = array(
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
	$permissionList['membergroup']['modsite_editOwn'] = array(
		false,
		'modsite_per_classic',
		'modsite_per_simple');
}

function modsite_dispatch()
{
	global $txt, $sourcedir, $modSettings, $context;
	static $modsiteObject;

		/* Safety first, hardcode the actions */
		$subActions = array(
			'add',
			'add2',
			'delete',
			'edit',
			'list',
			'search',
			'artist',
			'single',
			'success',
			'manage',
		);

		if (empty($modsiteObject))
		{
			require_once($sourcedir .'/ModSite/Subs-Modsite.php');
			$modsiteObject = new modsite();
		}

		/* Load both language and template files */
		loadLanguage('modsite');
		loadtemplate('modsite', 'admin');

		/* It is faster to use $var() than use call_user_func_array */
		if (isset($_GET['sa']))
			$func = $modsiteObject->clean($_GET['sa']);

		$call = 'modSite_' .(!empty($func) && in_array($func, array_values($subActions)) ?  $func : 'main');

		// Call the appropiate method
		$call($modsiteObject);
}

function modsite_main($modsiteObject)
{
	global $context, $scripturl, $txt, $user_info, $modSettings;

	/* Are you allowed to see this page? */
	/* $modsiteObject->permissions('view', true); */

	$context['sub_template'] = 'modSite_main';
	$context['canonical_url'] = $scripturl . '?action=modsite';
	$context['page_title'] = $txt['modSite_title_main'];
	$context['linktree'][] = array(
		'url' => $scripturl. '?action=modsite',
		'name' => $context['page_title'],
	);

	/* Pass the object to the template */
	$context['modSite']['object'] = $modsiteObject;

	/* Get the latest modsite from DB */
	$context['modSite']['latest'] = $modsiteObject->getLatest(empty($modSettings['modSite_latest_limit']) ? 10 : $modSettings['modSite_latest_limit']);
}

function modsite_add($modsiteObject)
{
	global $context, $scripturl, $txt, $sourcedir;

	/* Check permissions */
	$modsiteObject->permissions('add', true);

	$context['sub_template'] = 'modSite_add';
	$context['page_title'] = $txt['modSite_edit_creating'];
	$context['linktree'][] = array(
		'url' => $scripturl. '?action=modsite;sa=add',
		'name' => $context['page_title'],
	);

	/* Pass the object to the template */
	$context['modSite']['object'] = $modsiteObject;

	/* Tell the template we are adding, not editing */
	$context['modSite']['edit'] = false;

	/* We need make sure we have this. */
	require_once($sourcedir . '/Subs-Editor.php');

	/* Create it... */
	$editorOptions = array(
		'id' => 'body',
		'value' => '',
		'width' => '90%',
	);

	create_control_richedit($editorOptions);

	/* ... and store the ID again for use in the form */
	$context['post_box_name'] = $editorOptions['id'];
}

function modsite_add2($modsiteObject)
{
	global $context, $scripturl, $user_info, $sourcedir, $txt, $smcFunc;

	checkSession('post', '', true);

	/* Check permissions */
	$modsiteObject->permissions('add', true);

	/* Long, long check */
	if (empty($_REQUEST['id_category']) || empty($_REQUEST['name']) || empty($_REQUEST['file']) || empty($_REQUEST['demo']) || empty($_REQUEST['version']) || empty($_REQUEST['id_topic']) || empty($_REQUEST['smf_version']) || empty($_REQUEST['smf_download']) || empty($_REQUEST['github']) || empty($_REQUEST['description']))
		redirectexit('action=modsite'); // Gotta send the user back to the form but I'm lazy...

	/* If editing, we need the ID */
	if (isset($_REQUEST['edit']) && !isset($_GET['mid']) || empty($_GET['mid']))
		redirectexit('action=modsite');

	else
	{
		$mid = (int) $modsiteObject->clean($_GET['mid']);

		/* Make sure it does exists... */
		$current = $modsiteObject->doesExists($mid);

		/* Tell the user this entry doesn't exists anymore */
		if (empty($current))
			fatal_lang_error('modSite_error_no_valid_id', false);
	}

	/* Let us continue... */
	$data = array(
		'id' => $mid,
		'id_category' => $modsiteObject->clean($_REQUEST['id_category']),
		'name' => $modsiteObject->clean($_REQUEST['name']),
		'file' => $modsiteObject->clean($_REQUEST['file']),
		'demo' => $modsiteObject->clean($_REQUEST['demo']),
		'version' => $modsiteObject->clean($_REQUEST['version']),
		'id_topic' => $modsiteObject->clean($_REQUEST['id_topic']),
		'smf_version' => $modsiteObject->clean($_REQUEST['smf_version']),
		'smf_download' => $modsiteObject->clean($_REQUEST['smf_download']),
		'github' => $modsiteObject->clean($_REQUEST['github']),
		'description' => $modsiteObject->clean($_REQUEST['description']),
		'id_user' => $user_info['id'],
		'downloads' => 0,
		'time' => time(),
	);

	/* Finally, store the data and tell the user */
	$method = isset($_REQUEST['edit']) ? 'edit' : 'add';

	$modsiteObject->$method($data);
	redirectexit('action=modsite;sa=success;pin='. $method);
}

function modsite_edit($modsiteObject)
{
	global $context, $scripturl, $modSettings, $sourcedir, $txt;

	$modsiteObject->permissions('edit', true);

	if (!isset($_GET['mid']) || empty($_GET['mid']))
		redirectexit('action=modsite');

	else
	{
		/* Pass the object to the template */
		$context['modSite']['object'] = $modsiteObject;

		if (isset($_REQUEST['body']) && !empty($_REQUEST['body_mode']))
		{
			$_REQUEST['body'] = html_to_bbc($_REQUEST['body']);
			$_REQUEST['body'] = un_htmlspecialchars($_REQUEST['body']);
			$_POST['body'] = $_REQUEST['body'];
		}

		$mid = (int) $modsiteObject->clean($_GET['mid']);

		$temp = $modsiteObject->getBy('id', $mid, 1);

		if (empty($temp))
			fatal_lang_error('modSite_no_valid_id', false);

		$context['modSite']['edit'] = $temp[$mid];
		$context['sub_template'] = 'modSite_add';
		$context['page_title'] = $txt['modSite_preview_edit'] .' - '. $context['modSite']['edit']['title'];
		$context['linktree'][] = array(
			'url' => $scripturl. '?action=modsite;sa=edit;mid='. $mid,
			'name' => $txt['modSite_preview_edit'] .' - '. $context['modSite']['edit']['title'],
		);

		require_once($sourcedir .'/Subs-Editor.php');
		/* Needed for the WYSIWYG editor, we all love the WYSIWYG editor... */
		$modSettings['disable_wysiwyg'] = !empty($modSettings['disable_wysiwyg']) || empty($modSettings['enableBBC']);

		$editorOptions = array(
			'id' => 'body',
			'value' => html_to_bbc(un_htmlspecialchars($context['modSite']['edit']['body'])),
			'width' => '90%',
		);

		create_control_richedit($editorOptions);
		$context['post_box_name'] = $editorOptions['id'];
	}
}

function modsite_delete($modsiteObject)
{
	global $context, $txt;

	$modsiteObject->permissions('delete', true);

	if (!isset($_GET['mid']) || empty($_GET['mid']))
		redirectexit('action=modsite');

	else
	{
		$mid = (int) $modsiteObject->clean($_GET['mid']);
		$modsiteObject->delete($mid);
		redirectexit('action=modsite;sa=success;pin=delete');
	}
}

function modsite_success($modsiteObject)
{
	global $context, $scripturl, $smcFunc, $txt;

	if (!isset($_GET['pin']) || empty($_GET['pin']))
		redirectexit('action=modsite');

	$context['modSite']['pin'] = trim($smcFunc['htmlspecialchars']($_GET['pin']));

		/* Build the link tree.... */
		$context['linktree'][] = array(
			'url' => $scripturl . '?action=modsite;sa=success',
			'name' => $txt['modSite_success_message_title'],
		);

		$context['sub_template'] = 'modSite_success';
		$context['modSite']['message'] = $txt['modSite_success_message_'. $context['modSite']['pin']];

		/* Set a descriptive title. */
		$context['page_title'] = $txt['modSite_success_title'];

	/* Pass the object to the template */
	$context['modSite']['object'] = $modsiteObject;
}

function modsite_single($modsiteObject)
{
	global $context, $scripturl, $txt, $user_info;

	/* Forget it... */
	if (!isset($_GET['mid']) || empty($_GET['mid']))
		fatal_lang_error('modSite_error_no_valid_action', false);

	/* Are you allowed to see this page? */
	$modsiteObject->permissions('view', true);

	/* Get a valid ID */
	$id = $modsiteObject->clean($_GET['mid']);

	if (empty($id))
		fatal_lang_error('modSite_error_no_valid_action', false);

	/* Does the data has been already loaded? */
	if (!empty($context['modSite_all'][$id]))
		$context['modSite']['single'] = $context['modSite_all'][$id];

	/* No? bugger.. well, get it from the DB */
	else
		$context['modSite']['single'] = $modsiteObject->getSingle($id);

	/* Set all we need */
	$context['sub_template'] = 'modSite_single';
	$context['canonical_url'] = $scripturl . '?action=modsite;sa=single;mid=' . $id;
	$context['page_title'] = $context['modSite']['single']['title'] .' - '. $context['modSite']['single']['artist'];
	$context['linktree'][] = array(
		'url' => $context['canonical_url'],
		'name' => $context['page_title'],
	);

	/* Pass the object to the template */
	$context['modSite']['object'] = $modsiteObject;
}

function modsite_artist($modsiteObject)
{
	global $context, $scripturl, $txt, $user_info;

	/* Forget it... */
	if (!isset($_GET['mid']) || empty($_GET['mid']))
		fatal_lang_error('modSite_error_no_valid_action', false);

	/* Are you allowed to see this page? */
	$modsiteObject->permissions('view', true);

	$mid = $modsiteObject->clean($_GET['mid']);

	$context['sub_template'] = 'modSite_artist';
	$context['canonical_url'] = $scripturl . '?action=modsite;sa=artist;mid='. $mid;
	$context['page_title'] = $txt['modSite_artist_title'] . $mid;
	$context['linktree'][] = array(
		'url' => $scripturl. '?action=modsite;sa=artist;mid='. $mid,
		'name' => $context['page_title'],
	);

	/* Get the latest modsite from DB */
	$context['modSite']['artist'] = $modsiteObject->getBy('artist', $mid , false);

	/* Pass the object to the template */
	$context['modSite']['object'] = $modsiteObject;
}

function modsite_list($modsiteObject)
{
	global $context, $txt, $scripturl;

	/* Are you allowed to see this page? */
	$modsiteObject->permissions('view', true);

	/* Page stuff */
	$context['sub_template'] = 'modSite_list';
	$context['page_title'] = $txt['modSite_list_title'];
	$context['linktree'][] = array(
		'url' => $scripturl. '?action=modsite;sa=list',
		'name' => $txt['modSite_list_title'],
	);

	/* No letter? then show the main page */
	if (!isset($_GET['lidletter']) || empty($_GET['lidletter']))
		$context['modSite']['list'] = $modsiteObject->getAll();

	/* Show a list of modsite starting with X letter */
	elseif (isset($_GET['lidletter']))
	{
		$midletter = $modsiteObject->clean($_GET['lidletter']);

		/* Replace the linktree and title with something more specific */
		$context['page_title'] = $txt['modSite_list_title_by_letter'] . $midletter;
		$context['linktree'][] = array(
			'url' => $scripturl. '?action=modsite;sa=list;lidletter='. $midletter,
			'name' => $txt['modSite_list_title_by_letter'] . $midletter,
		);

		$context['modSite']['list'] = $modsiteObject->getBy('title', $midletter .'%');

		if (empty($context['modSite']['list']))
			fatal_lang_error('modSite_no_modsite_with_letter', false);
	}

	/* Pass the object to the template */
	$context['modSite']['object'] = $modsiteObject;
}

function modsite_manage($modsiteObject)
{
	global $context, $txt, $scripturl;

	/* Are you allowed to see this page? */
	$modsiteObject->permissions(array('edit', 'delete'), true);

	/* Page stuff */
	$context['sub_template'] = 'modSite_manage';
	$context['page_title'] = $txt['modSite_manage_title'];
	$context['linktree'][] = array(
		'url' => $scripturl. '?action=modsite;sa=manage',
		'name' => $context['page_title'],
	);

	/* No letter? then show the main page */
	if (!isset($_GET['lidletter']) || empty($_GET['lidletter']))
		$context['modSite']['list'] = $modsiteObject->getAll('manage');

	/* Show a list of modsite starting with X letter */
	elseif (isset($_GET['lidletter']))
	{
		$midletter = $modsiteObject->clean($_GET['lidletter']);

		/* Replace the linktree and title with something more specific */
		$context['page_title'] = $txt['modSite_list_title_by_letter'] . $midletter;
		$context['linktree'][] = array(
			'url' => $scripturl. '?action=modsite;sa=list;lidletter='. $midletter,
			'name' => $txt['modSite_list_title_by_letter'] . $midletter,
		);

		$context['modSite']['list'] = $modsiteObject->getBy('title', $midletter .'%');

		if (empty($context['modSite']['list']))
			fatal_lang_error('modSite_no_modsite_with_letter', false);
	}

	/* Pass the object to the template */
	$context['modSite']['object'] = $modsiteObject;
}

function modsite_search($modsiteObject)
{
	global $context, $txt, $scripturl;

	/* Are you allowed to see this page? */
	$modsiteObject->permissions('view', true);

	/* We need a valur to serch and a column */
	if (!isset($_REQUEST['l_search_value']) || empty($_REQUEST['l_search_value']) || !isset($_REQUEST['l_column']) || empty($_REQUEST['l_column']))
		fatal_lang_error('modSite_error_no_valid_action', false);

	$value = urlencode($modsiteObject->clean($_REQUEST['l_search_value']));
	$column = $modsiteObject->clean($_REQUEST['l_column']);

	/* Page stuff */
	$context['sub_template'] = 'modSite_list';
	$context['page_title'] = $txt['modSite_search_title'] . $value;
	$context['linktree'][] = array(
		'url' => $scripturl. '?action=modsite;sa=search',
		'name' => $txt['modSite_list_title_by_letter'] . $value,
	);

	$context['modSite']['list'] = $modsiteObject->getBy($column, '%'. $value .'%');

	if (empty($context['modSite']['list']))
		fatal_lang_error('modSite_no_modsite_with_letter', false);


	/* Pass the object to the template */
	$context['modSite']['object'] = $modsiteObject;
}