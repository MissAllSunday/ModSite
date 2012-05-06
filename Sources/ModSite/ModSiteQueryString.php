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

class ModSiteQueryString extends ModSite
{
	private $_request = array();
	private $_types;

	public function __construct($var)
	{
		if (empty($var))
			return false;

		$this->_types = array(
			'get' => $_GET,
			'post' => $_POST,
			'request' => $_REQUEST,
		);

		if (in_array($var, $this->_types))
			$this->_request = $this->_types[$var];

		else
			return false;
	}

	public function getVar($value)
	{
		if ($this->validate($value))
			return $this->sanitize($this->_request[$value]);

		else
			return false;
	}

	public function getRawValue($value)
	{
		if (isset($this->_request[$value]))
			return $this->_request[$value];

		else
			return false;
	}

	public function validate($value)
	{
		if (isset($this->_request[$value]) && !empty($this->_request[$value]))
			return true;

		else
			return false;
	}

	public function validateBody($var)
	{
		/* You cannot post just spaces */
		if(ctype_space($this->_request[$var]) || $this->_request[$var] == '')
			return false;

		elseif (isset($this->_request[$var]) && !empty($this->_request[$var]) && !ctype_space($this->_request[$var]))
			return true;

		else
			return false;
	}

	public function unsetVar($var)
	{
		unset($this->_request[$var]);
	}

	public function sanitize($var)
	{
		if (get_magic_quotes_gpc())
			$var = stripslashes($var);

		if (is_numeric($var))
			$var = (int) $var;

		elseif (is_string($var))
			$var = trim(strtr(htmlspecialchars($var, ENT_QUOTES), array("\r" => '<br />', "\n" => '<br />', "\t" => '&nbsp;&nbsp;&nbsp;&nbsp;')));

		else
			$var = 'error_' . $var;

		return $var;
	}
}