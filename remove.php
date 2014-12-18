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

	if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
		require_once(dirname(__FILE__) . '/SSI.php');

	elseif (!defined('SMF'))
		exit('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');

	$hooks = array(
		'integrate_menu_buttons' => 'ModSite.php|ModSite::menu#',
		'integrate_actions' => 'ModSite.php|ModSite::actions',
		'integrate_load_permissions' => 'ModSite.php|ModSite::permissions',
		'integrate_admin_areas' => 'ModSite.php|ModSite::adminAreas',
		'integrate_modify_modifications' => 'ModSite.php|ModSite::modifications',
	);

		$call = 'remove_integration_function';

	foreach ($hooks as $hook => $function)
		$call($hook, $function);