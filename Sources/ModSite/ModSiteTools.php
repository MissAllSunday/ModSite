<?php

/**
 * Mod Site (SMF)
 *
 * @package SMF
 * @author Suki <suki@missallsunday.com>
 * @copyright 2011 Suki http://missallsunday.com
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
	die('Hacking attempt...');

class ModSiteTools
{
	private static $_instance;
	protected $_settings;
	protected $_text;
	protected $_pattern;

	private function __construct()
	{
		$this->_pattern = '/'. ModSite::$name .'_/';
		$this->doExtract();
	}

	public static function getInstance()
	{
		if (!self::$_instance)
		{
			self::$_instance = new ModSiteTools();
		}
		return self::$_instance;
	}

	public function doExtract()
	{
		global $txt, $modSettings;

		loadLanguage(ModSite::$name);

		$this->matchesSettings = array();

		/* Get only the settings that we need */
		foreach ($modSettings as $km => $vm)
			if (preg_match($this->_pattern, $km))
			{
				$km = str_replace(''. ModSite::$name .'_', '', $km);

				/* Populate the new array */
				$this->matchesSettings[$km] = $vm;
			}

		$this->_settings = $this->matchesSettings;

		/* Again, this time for $txt. */
		foreach ($txt as $kt => $vt)
			if (preg_match($this->_pattern, $kt))
			{
				$kt = str_replace(''. ModSite::$name .'_', '', $kt);
				$this->matchesText[$kt] = $vt;
			}

		$this->_text = $this->matchesText;

		/* Done? then we don't need this anymore */
		if (!empty($this->_text) && !empty($this->_settings))
		{
			unset($this->matchesText);
			unset($this->matchesSettings);
		}
	}

	/* Return true if the value do exist, false otherwise, O RLY? */
	public function enable($var)
	{
		if (!empty($this->_settings[$var]))
			return true;
		else
			return false;
	}

	/* Get the requested setting  */
	public function getSetting($var)
	{
		if (!empty($this->_settings[$var]))
			return $this->_settings[$var];

		else
			return false;
	}

	public function getText($var)
	{
		if (!empty($this->_text[$var]))
			return $this->_text[$var];

		else
			return false;
	}

	public function __destruct() {}
}