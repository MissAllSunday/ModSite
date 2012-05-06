<?php

/**
 * Mod Site (SMF)
 *
 * @package SMF
 * @author Suki <suki@missallsunday.com>
 * @copyright 2012 Jessica González
 * @license http://www.mozilla.org/MPL/ MPL 2.0
 *
 * @version 1.0 Alpha 1 Beta
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
	die('Hacking attempt...');


abstract class ModSiteHooks
{
	protected static $_tools;

	/* Permissions */
	public static function permissions(&$permissionGroups, &$permissionList)
	{
		$permissionList['membergroup'][''. ModSite::$name .'_can_see'] = array(false, ''. ModSite::$name .'_per_classic', ''. ModSite::$name .'_per_simple');
		$permissionList['membergroup'][''. ModSite::$name .'_can_add'] = array(false, ''. ModSite::$name .'_per_classic', ''. ModSite::$name .'_per_simple');
		$permissionList['membergroup'][''. ModSite::$name .'_can_delete'] = array(false, ''. ModSite::$name .'_per_classic', ''. ModSite::$name .'_per_simple');
		$permissionGroups['membergroup']['simple'] = array(''. ModSite::$name .'_per_simple');
		$permissionGroups['membergroup']['classic'] = array(''. ModSite::$name .'_per_classic');
	}

	/* Admin menu hook */
	public static function admin(&$admin_areas)
	{
		if (!$_tools)
			$_tools = ModSiteTools::getInstance();

		$admin_areas['config']['areas'][ModSite::$name] = array(
			'label' => $_tools->getText('admin_panel'),
			'file' => ModSite::$name.'.php',
			'function' => 'wrapper_admin_dispatch',
			'icon' => 'posts.gif',
			'subsections' => array(
				'general' => array($_tools->getText('admin_panel_settings')),
			),
		);
	}

	/* The settings hook */
	public static function settingsDispatch($return_config = false)
	{
		global $sourcedir;

		require_once($sourcedir.'/ManageSettings.php');

		if (!$_tools)
			$_tools = ModSiteTools::getInstance();

		$subActions = array(
			'general' => 'wrapper_admin_settings',
		);

		loadGeneralSettingParameters($subActions, 'general');

		// Load up all the tabs...
		$context[$context['admin_menu_name']]['tab_data'] = array(
			'title' => $_tools->->getText('admin_panel'),
			'description' => $_tools->->getText('admin_panel_desc'),
			'tabs' => array(
				'general' => array(),
			),
		);

		$subActions[$_REQUEST['sa']]();
	}

	/* Settings */
	static function settings()
	{
		global $scripturl, $context, $sourcedir;

		require_once($sourcedir.'/ManageServer.php');

		if (!$_tools)
			$_tools = ModSiteTools::getInstance();

		$config_vars = array(
			array('check', ''. ModSite::$name .'_enable','subtext' => $_tools()->getText('enable_sub')),
		);

		$context['post_url'] = $scripturl . '?action=admin;area='. ModSite::$name .';sa=general;save';

		/* Saving? */
		if (isset($_GET['save']))
		{
			checkSession();

			/* Force a new instance */
			$_tools->__destruct();

			saveDBSettings($config_vars);
			redirectexit('action=admin;area='. ModSite::$name .';sa=general');
		}

		prepareDBSettingContext($config_vars);
	}
}