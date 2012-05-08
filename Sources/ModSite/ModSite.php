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
	public static $downloads_folder = '/Downloads/';
	protected $_tableName = 'mod_site';
	protected $_tools;

	public function __construct(){}

	public function tools()
	{
		return ModSiteTools::getInstance();
	}

	public function db()
	{
		return new ModSiteDB(self::$_tableName);
	}

	public function download()
	{

	}

	public function queryString($var)
	{
		return new ModSiteQuery($var);
	}

	public function __destruct() {}
}