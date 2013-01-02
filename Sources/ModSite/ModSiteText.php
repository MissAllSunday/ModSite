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

class ModSiteText
{
	protected $_text;
	protected $_pattern;

	public function __construct()
	{
		$this->_pattern = '/'. ModSite::$name .'_/';
		$this->doExtract();
	}

	public function doExtract()
	{
		global $txt;

		loadLanguage(ModSite::$name);

		$this->_text = $txt;
	}

	/**
	 * Get the requested array element.
	 *
	 * @param string the key name for the requested element
	 * @access public
	 * @return mixed
	 */
	public function getText($var)
	{
		if (empty($var))
			return false;

		if (empty($this->_text))
			$this->extract();

		if (!empty($this->_text[$this->_pattern . $var]))
			return $this->_text[$this->_pattern . $var];

		else
			return false;
	}
}