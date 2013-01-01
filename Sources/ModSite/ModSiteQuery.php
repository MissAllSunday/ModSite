<?php

/**
 * Mod Site (SMF)
 *
 * @package SMF
 * @author Suki <suki@missallsunday.com>
 * @copyright 2012 Jessica Gonz�lez
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

class ModSiteQuery extends ModSite
{
	private static $_instance;
	protected $_rows = array();
	protected $_params = array();
	protected $_data = array();
	protected $_db;
	protected $_values;


	public function __construct()
	{
		$this->setDB();

		$this->_rows = array(
			'id' => 'id',
			'name' => 'name',
			'github' => 'github',
			'user' => 'id_user',
			'topic' => 'id_topic',
			'down' => 'downloads',
			'info' => 'info',
			'desc' => 'description',
		);

		foreach ($this->_rows as $k => $v)
			$this->_values[$k] = '';
	}

	public static function getInstance()
	{
		if (!self::$_instance)
		{
			self::$_instance = new ModSiteQuery();
		}
		return self::$_instance;
	}

	public function killCache($type)
	{
		cache_put_data(Modsite::$name, null);
	}

	protected function setDB()
	{
		$this->_db = new ModSiteDB($this->_tableName);
	}

	protected function db()
	{
		return $this->_db;
	}

	protected function getAll()
	{
		if (($this->_values[$row] = cache_get_data(''. Modsite::$name .'', 120)) == null)
		{
			$this->_params['rows'] = implode(',', $this->_rows);
			$this->db()->params($this->_params, $this->_data);
			$this->db()->getData(null, false);

			$return = $this->_db->dataResult();

			cache_put_data(''. Modsite::$name .'', $return, 120);
		}

		if (!empty($return))
			return $return;

		else
			return false;
	}

}