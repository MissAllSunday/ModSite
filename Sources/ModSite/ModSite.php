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

/* Autoload */
function __autoload($class_name)
{
	global $sourcedir;

	if(file_exists($sourcedir.ModSite::$folder.$class_name . '.php'))
		require_once($file_path);

	elseif(file_exists($sourcedir.ModSite::$api_folder.$class_name . '.php'))
		require_once($file_path);

	else
		return false;
}

class ModSite
{
	public static $name = 'ModSite';
	public static $folder = '/ModSite/';
	public static $api_folder = '/githubAPI/';
	protected $_tableName = 'mod_site';
	protected $_globals;

	public function __construct()
	{
		$this->_tools = ModSiteTools::getInstance();
	}

	public function tools()
	{
		return $this->_tools;
	}

	public static function globals($var, $value = false)
	{
		$_globals = ModSiteTools::getInstance();

		if (!$value)
			return $_globals->getGlobal[$var];

		else
			return $_globals->getGlobal[$var][$value];
	}
}