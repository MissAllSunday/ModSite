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

/* Autoload */
function modsite_autoloader($class_name)
{
	global $sourcedir;

	$file_path = $sourcedir . ModSite::$folder . $class_name . '.php';

	if (file_exists($file_path))
		require_once ($file_path);

	else
		return false;
}

spl_autoload_register('modsite_autoloader');

class ModSite
{
	public static $name = 'ModSite';
	public static $folder = '/ModSite/';
	public static $api_folder = '/githubAPI/';
	public static $downloads_folder = '/Downloads/';
	protected $_tableName = 'mod_site';
	protected $_tools;

	public function __construct(){}

	/**
	 * ModsiteHooks::actions()
	 *
	 * Insert the actions needed by this mod
	 * @param array $actions An array containing all possible SMF actions.
	 * @return void
	 */
	public static function actions(&$actions)
	{
		$actions['mods'] = array(Modsite::$folder . 'ModsiteDispatcher.php', 'ModSiteDispatcher::dispatch');
	}
}