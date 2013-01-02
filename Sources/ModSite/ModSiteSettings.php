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

class ModSiteSettings
{
	protected $_settings;
	protected $_pattern;

	public function __construct()
	{
		$this->_pattern = '/'. ModSite::$name .'_/';
		$this->doExtract();
	}

	public function doExtract()
	{
		global $modSettings;

		$this->_settings = $modSettings;
	}

	/* Return true if the value do exist, false otherwise, O RLY? */
	public function enable($var)
	{
		if (empty($this->_settings))
			$this->extract();

		if (!empty($this->_settings[$this->_pattern . $var]))
			return true;

		else
			return false;
	}

	/* Get the requested setting  */
	public function getSetting($var)
	{
		if (empty($this->_settings))
			$this->extract();

		if (!empty($this->_settings[$this->_pattern . $var]))
			return $this->_settings[$this->_pattern . $var];

		else
			return false;
	}
}