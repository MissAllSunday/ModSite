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

class ModsiteGlobals
{
	protected $_request;

	/**
	 * ModsiteGlobals::__construct()
	 * 
	 * @param mixed $var
	 * @return
	 */
	public function __construct($var = 'request')
	{
		switch ($var)
		{
			case 'get':
				$this->_request = $_GET;
				break;
			case 'post':
				$this->_request = $_POST;
				break;
			case 'request':
				$this->_request = $_REQUEST;
				break;
			default:
				$this->_request = $_REQUEST;
		}
	}

	/**
	 * ModsiteGlobals::getValue()
	 * 
	 * @param mixed $value
	 * @return
	 */
	public function getValue($value)
	{
		if ($this->validate($value))
			return $this->sanitize($this->_request[$value]);
		else
			return false;
	}

	/**
	 * ModsiteGlobals::getRaw()
	 * 
	 * @param mixed $value
	 * @return
	 */
	public function getRaw($value)
	{
		if (isset($this->_request[$value]))
			return $this->_request[$value];

		else
			return false;
	}

	/**
	 * ModsiteGlobals::validate()
	 * 
	 * @param mixed $var
	 * @return
	 */
	public function validate($var)
	{
		if (isset($this->_request[$var]))
			return true;
		else
			return false;
	}

	/**
	 * ModsiteGlobals::validateBody()
	 * 
	 * @param mixed $var
	 * @return
	 */
	public function validateBody($var)
	{
		if (!isset($this->_request[$var]) || empty($this->_request[$var]))
			return false;

		/* You cannot post just spaces */
		if (ctype_space($this->_request[$var]) || $this->_request[$var] == '')
			return false;

		elseif (isset($this->_request[$var]) && !empty($this->_request[$var]) && !
			ctype_space($this->_request[$var]))
			return true;

		else
			return false;
	}

	/**
	 * ModsiteGlobals::unsetVar()
	 * 
	 * @param mixed $var
	 * @return
	 */
	public function unsetVar($var)
	{
		unset($this->_request[$var]);
	}

	/**
	 * ModsiteGlobals::sanitize()
	 * 
	 * @param mixed $var
	 * @return
	 */
	public function sanitize($var)
	{
		if (get_magic_quotes_gpc())
			$var = stripslashes($var);

		if (is_numeric($var))
			$var = (int)trim($var);

		elseif (is_string($var))
			$var = trim(strtr(htmlspecialchars($var, ENT_QUOTES), array(
				"\r" => '<br />',
				"\n" => '<br />',
				"\t" => '&nbsp;&nbsp;&nbsp;&nbsp;')));

		else
			$var = 'error_' . $var;

		return $var;
	}
}