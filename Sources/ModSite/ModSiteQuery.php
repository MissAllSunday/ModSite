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

class ModSiteQuery
{

	public function __construct()
	{
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

	public function killCache($type)
	{
		cache_put_data(Modsite::$name . $type, null);
	}
}