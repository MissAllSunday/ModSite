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

	$areas['config']['areas']['modsettings']['subsections']['modsite'] = array($txt['ModSite_title_main']);
}

function modsite_actions(&$actions)
{
	$actions['modsite'] = array('ModSite/ModSite.php', 'modsite_dispatch');
}

function modsite_menu(&$menu_buttons)
{
		global $scripturl, $modSettings, $txt;

		loadLanguage('ModSite');

		$insert = !empty($modSettings['modsite_menu_position']) ? $modSettings['modsite_menu_position'] : 'home';
		$counter = 0;

		foreach ($menu_buttons as $area => $dummy)
			if (++$counter && $area == $insert )
				break;

		$menu_buttons = array_merge(
			array_slice($menu_buttons, 0, $counter),
			array('modsite' => array(
				'title' => $txt['ModSite_title_main'],
				'href' => $scripturl . '?action=modsite',
				'show' => empty($modSettings['ModSite_enable']) ? false : true,
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
		array('desc', 'ModSite_admin_desc'),
		array('check', 'ModSite_enable', 'subtext' => $txt['ModSite_enable_desc']),
		array('int', 'ModSite_latest_limit', 'subtext' => $txt['ModSite_latest_limit_desc'], 'size' => 3),
		array('int', 'ModSite_pag_limit', 'subtext' => $txt['ModSite_pag_limit_desc'], 'size' => 3),
		array(
			'select',
			'ModSite_menu_position',
			array(
				'home' => $txt['home'],
				'help' => $txt['help'],
				'search' => $txt['search'],
				'login' => $txt['login'],
				'register' => $txt['register']
			),
			'subtext' => $txt['ModSite_menu_position_desc']
		),
	);

	if ($return_config)
		return $config_vars;

	$context['post_url'] = $scripturl . '?action=admin;area=modsettings;save;sa=modsite';
	$context['settings_title'] = $txt['ModSite_title_main'];

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
	$permissionGroups['membergroup']['simple'] = array('modsiteMod_per_simple');
	$permissionGroups['membergroup']['classic'] = array('modsiteMod_per_classic');

	$permissionList['membergroup']['modsiteMod_viewmodsite'] = array(
		false,
		'modsiteMod_per_classic',
		'modsiteMod_per_simple');

	$permissionList['membergroup']['modsiteMod_deletemodsite'] = array(
		false,
		'modsiteMod_per_classic',
		'modsiteMod_per_simple');
	$permissionList['membergroup']['modsiteMod_addmodsite'] = array(
		false,
		'modsiteMod_per_classic',
		'modsiteMod_per_simple');
	$permissionList['membergroup']['modsiteMod_editmodsite'] = array(
		false,
		'modsiteMod_per_classic',
		'modsiteMod_per_simple');
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

		$call = 'modsite_' .(!empty($func) && in_array($func, array_values($subActions)) ?  $func : 'main');

		// Call the appropiate method
		$call($modsiteObject);
}

function modsite_main($modsiteObject)
{
	global $context, $scripturl, $txt, $user_info, $modSettings;

	/* Are you allowed to see this page? */
	/* $modsiteObject->permissions('view', true); */

	$context['sub_template'] = 'modsite_main';
	$context['canonical_url'] = $scripturl . '?action=modsite';
	$context['page_title'] = $txt['ModSite_title_main'];
	$context['linktree'][] = array(
		'url' => $scripturl. '?action=modsite',
		'name' => $context['page_title'],
	);

	/* Pass the object to the template */
	$context['modsite']['object'] = $modsiteObject;

	/* Get the latest modsite from DB */
	$context['modsite']['latest'] = $modsiteObject->getLatest(empty($modSettings['ModSite_latest_limit']) ? 10 : $modSettings['ModSite_latest_limit']);
}

function modsite_add($modsiteObject)
{
	global $context, $scripturl, $txt, $sourcedir;

	/* Check permissions */
	$modsiteObject->permissions('add', true);

	$context['sub_template'] = 'modsite_add';
	$context['page_title'] = $txt['modsite_post_title'];
	$context['linktree'][] = array(
		'url' => $scripturl. '?action=modsite;sa=add',
		'name' => $txt['modsite_post_title'],
	);

	/* Pass the object to the template */
	$context['modsite']['object'] = $modsiteObject;

	/* Tell the template we are adding, not editing */
	$context['modsite']['edit'] = false;

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

	/* Want to see your masterpiece before others? */
	if (isset($_REQUEST['preview']))
	{
		/* Set everything up to be displayed. */
		$context['preview_subject'] = $modsiteObject->clean($_REQUEST['title']);
		$context['preview_artist'] = $modsiteObject->clean($_REQUEST['artist']);
		$context['preview_message'] = $modsiteObject->clean($_REQUEST['body'], true);

		/* Parse out the BBC if it is enabled. */
		$context['preview_message'] = parse_bbc($context['preview_message']);

		/* We Censor for your protection... */
		censorText($context['preview_subject']);
		censorText($context['preview_artist']);
		censorText($context['preview_message']);

		/* Build the link tree.... */
		$context['linktree'][] = array(
			'url' => $scripturl . '?action=modsite;sa=add',
			'name' => $txt['modsite_preview_add'],
		);

		/* We need make sure we have this. */
		require_once($sourcedir . '/Subs-Editor.php');

		/* Create it... */
		$editorOptions = array(
			'id' => 'body',
			'value' => isset($_REQUEST['body']) ? str_replace(array('  '), array('&nbsp; '), $smcFunc['htmlspecialchars']($_REQUEST['body'])) : '',
			'width' => '90%',
		);

		create_control_richedit($editorOptions);

		/* ... and store the ID again for use in the form */
		$context['post_box_name'] = $editorOptions['id'];
		$context['sub_template'] = 'modsite_add';

		/* Set a descriptive title. */
		$context['page_title'] = $txt['preview'] .' - ' . $context['preview_subject'];
	}

	/* Editing */
	elseif (isset($_REQUEST['edit']))
	{
		if (!isset($_GET['lid']) || empty($_GET['lid']))
			redirectexit('action=modsite');

		$lid = (int) $modsiteObject->clean($_GET['lid']);

		/* Make usre it does exists... */
		$current = $modsiteObject->getBy('id', $lid, 1);

		/* Tell the user this entry doesn't exists anymore */
		if (empty($current))
			fatal_lang_error('modsite_no_valid_id', false);

		/* Let us continue... */
		$editData = array(
			'id' => $lid,
			'artist' => $modsiteObject->clean($_REQUEST['artist']),
			'title' => $modsiteObject->clean($_REQUEST['title']),
			'body' => $modsiteObject->clean($_REQUEST['body'], true),
		);

		/* Finally, store the data and tell the user */
		$modsiteObject->edit($editData);
		redirectexit('action=modsite;sa=success;pin=edit');
	}

	/* Lastly, Adding */
	else
	{
		/* Create the data */
		$data = array(
			'user' => $user_info['id'],
			'artist' => $modsiteObject->clean($_REQUEST['artist']),
			'title' => $modsiteObject->clean($_REQUEST['title']),
			'body' => $modsiteObject->clean($_REQUEST['body'], true),
		);

		$modsiteObject->add($data);
		redirectexit('action=modsite;sa=success;pin=add');
	}

}

function modsite_edit($modsiteObject)
{
	global $context, $scripturl, $modSettings, $sourcedir, $txt;

	$modsiteObject->permissions('edit', true);

	if (!isset($_GET['lid']) || empty($_GET['lid']))
		redirectexit('action=modsite');

	else
	{
		/* Pass the object to the template */
		$context['modsite']['object'] = $modsiteObject;

		if (isset($_REQUEST['body']) && !empty($_REQUEST['body_mode']))
		{
			$_REQUEST['body'] = html_to_bbc($_REQUEST['body']);
			$_REQUEST['body'] = un_htmlspecialchars($_REQUEST['body']);
			$_POST['body'] = $_REQUEST['body'];
		}

		$lid = (int) $modsiteObject->clean($_GET['lid']);

		$temp = $modsiteObject->getBy('id', $lid, 1);

		if (empty($temp))
			fatal_lang_error('modsite_no_valid_id', false);

		$context['modsite']['edit'] = $temp[$lid];
		$context['sub_template'] = 'modsite_add';
		$context['page_title'] = $txt['modsite_preview_edit'] .' - '. $context['modsite']['edit']['title'];
		$context['linktree'][] = array(
			'url' => $scripturl. '?action=modsite;sa=edit;lid='. $lid,
			'name' => $txt['modsite_preview_edit'] .' - '. $context['modsite']['edit']['title'],
		);

		require_once($sourcedir .'/Subs-Editor.php');
		/* Needed for the WYSIWYG editor, we all love the WYSIWYG editor... */
		$modSettings['disable_wysiwyg'] = !empty($modSettings['disable_wysiwyg']) || empty($modSettings['enableBBC']);

		$editorOptions = array(
			'id' => 'body',
			'value' => html_to_bbc(un_htmlspecialchars($context['modsite']['edit']['body'])),
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

	if (!isset($_GET['lid']) || empty($_GET['lid']))
		redirectexit('action=modsite');

	else
	{
		$lid = (int) $modsiteObject->clean($_GET['lid']);
		$modsiteObject->delete($lid);
		redirectexit('action=modsite;sa=success;pin=delete');
	}
}

function modsite_success($modsiteObject)
{
	global $context, $scripturl, $smcFunc, $txt;

	if (!isset($_GET['pin']) || empty($_GET['pin']))
		redirectexit('action=modsite');

	$context['modsite']['pin'] = trim($smcFunc['htmlspecialchars']($_GET['pin']));

		/* Build the link tree.... */
		$context['linktree'][] = array(
			'url' => $scripturl . '?action=modsite;sa=success',
			'name' => $txt['modsite_success_message_title'],
		);

		$context['sub_template'] = 'modsite_success';
		$context['modsite']['message'] = $txt['modsite_success_message_'. $context['modsite']['pin']];

		/* Set a descriptive title. */
		$context['page_title'] = $txt['modsite_success_title'];

	/* Pass the object to the template */
	$context['modsite']['object'] = $modsiteObject;
}

function modsite_single($modsiteObject)
{
	global $context, $scripturl, $txt, $user_info;

	/* Forget it... */
	if (!isset($_GET['lid']) || empty($_GET['lid']))
		fatal_lang_error('modsite_error_no_valid_action', false);

	/* Are you allowed to see this page? */
	$modsiteObject->permissions('view', true);

	/* Get a valid ID */
	$id = $modsiteObject->clean($_GET['lid']);

	if (empty($id))
		fatal_lang_error('modsite_error_no_valid_action', false);

	/* Does the data has been already loaded? */
	if (!empty($context['modsite_all'][$id]))
		$context['modsite']['single'] = $context['modsite_all'][$id];

	/* No? bugger.. well, get it from the DB */
	else
		$context['modsite']['single'] = $modsiteObject->getSingle($id);

	/* Set all we need */
	$context['sub_template'] = 'modsite_single';
	$context['canonical_url'] = $scripturl . '?action=modsite;sa=single;lid=' . $id;
	$context['page_title'] = $context['modsite']['single']['title'] .' - '. $context['modsite']['single']['artist'];
	$context['linktree'][] = array(
		'url' => $context['canonical_url'],
		'name' => $context['page_title'],
	);

	/* Pass the object to the template */
	$context['modsite']['object'] = $modsiteObject;
}

function modsite_artist($modsiteObject)
{
	global $context, $scripturl, $txt, $user_info;

	/* Forget it... */
	if (!isset($_GET['lid']) || empty($_GET['lid']))
		fatal_lang_error('modsite_error_no_valid_action', false);

	/* Are you allowed to see this page? */
	$modsiteObject->permissions('view', true);

	$lid = $modsiteObject->clean($_GET['lid']);

	$context['sub_template'] = 'modsite_artist';
	$context['canonical_url'] = $scripturl . '?action=modsite;sa=artist;lid='. $lid;
	$context['page_title'] = $txt['modsite_artist_title'] . $lid;
	$context['linktree'][] = array(
		'url' => $scripturl. '?action=modsite;sa=artist;lid='. $lid,
		'name' => $context['page_title'],
	);

	/* Get the latest modsite from DB */
	$context['modsite']['artist'] = $modsiteObject->getBy('artist', $lid , false);

	/* Pass the object to the template */
	$context['modsite']['object'] = $modsiteObject;
}

function modsite_list($modsiteObject)
{
	global $context, $txt, $scripturl;

	/* Are you allowed to see this page? */
	$modsiteObject->permissions('view', true);

	/* Page stuff */
	$context['sub_template'] = 'modsite_list';
	$context['page_title'] = $txt['modsite_list_title'];
	$context['linktree'][] = array(
		'url' => $scripturl. '?action=modsite;sa=list',
		'name' => $txt['modsite_list_title'],
	);

	/* No letter? then show the main page */
	if (!isset($_GET['lidletter']) || empty($_GET['lidletter']))
		$context['modsite']['list'] = $modsiteObject->getAll();

	/* Show a list of modsite starting with X letter */
	elseif (isset($_GET['lidletter']))
	{
		$lidletter = $modsiteObject->clean($_GET['lidletter']);

		/* Replace the linktree and title with something more specific */
		$context['page_title'] = $txt['modsite_list_title_by_letter'] . $lidletter;
		$context['linktree'][] = array(
			'url' => $scripturl. '?action=modsite;sa=list;lidletter='. $lidletter,
			'name' => $txt['modsite_list_title_by_letter'] . $lidletter,
		);

		$context['modsite']['list'] = $modsiteObject->getBy('title', $lidletter .'%');

		if (empty($context['modsite']['list']))
			fatal_lang_error('modsite_no_modsite_with_letter', false);
	}

	/* Pass the object to the template */
	$context['modsite']['object'] = $modsiteObject;
}

function modsite_manage($modsiteObject)
{
	global $context, $txt, $scripturl;

	/* Are you allowed to see this page? */
	$modsiteObject->permissions(array('edit', 'delete'), true);

	/* Page stuff */
	$context['sub_template'] = 'modsite_manage';
	$context['page_title'] = $txt['modsite_manage_title'];
	$context['linktree'][] = array(
		'url' => $scripturl. '?action=modsite;sa=manage',
		'name' => $context['page_title'],
	);

	/* No letter? then show the main page */
	if (!isset($_GET['lidletter']) || empty($_GET['lidletter']))
		$context['modsite']['list'] = $modsiteObject->getAll('manage');

	/* Show a list of modsite starting with X letter */
	elseif (isset($_GET['lidletter']))
	{
		$lidletter = $modsiteObject->clean($_GET['lidletter']);

		/* Replace the linktree and title with something more specific */
		$context['page_title'] = $txt['modsite_list_title_by_letter'] . $lidletter;
		$context['linktree'][] = array(
			'url' => $scripturl. '?action=modsite;sa=list;lidletter='. $lidletter,
			'name' => $txt['modsite_list_title_by_letter'] . $lidletter,
		);

		$context['modsite']['list'] = $modsiteObject->getBy('title', $lidletter .'%');

		if (empty($context['modsite']['list']))
			fatal_lang_error('modsite_no_modsite_with_letter', false);
	}

	/* Pass the object to the template */
	$context['modsite']['object'] = $modsiteObject;
}

function modsite_search($modsiteObject)
{
	global $context, $txt, $scripturl;

	/* Are you allowed to see this page? */
	$modsiteObject->permissions('view', true);

	/* We need a valur to serch and a column */
	if (!isset($_REQUEST['l_search_value']) || empty($_REQUEST['l_search_value']) || !isset($_REQUEST['l_column']) || empty($_REQUEST['l_column']))
		fatal_lang_error('modsite_error_no_valid_action', false);

	$value = urlencode($modsiteObject->clean($_REQUEST['l_search_value']));
	$column = $modsiteObject->clean($_REQUEST['l_column']);

	/* Page stuff */
	$context['sub_template'] = 'modsite_list';
	$context['page_title'] = $txt['modsite_search_title'] . $value;
	$context['linktree'][] = array(
		'url' => $scripturl. '?action=modsite;sa=search',
		'name' => $txt['modsite_list_title_by_letter'] . $value,
	);

	$context['modsite']['list'] = $modsiteObject->getBy($column, '%'. $value .'%');

	if (empty($context['modsite']['list']))
		fatal_lang_error('modsite_no_modsite_with_letter', false);


	/* Pass the object to the template */
	$context['modsite']['object'] = $modsiteObject;
}