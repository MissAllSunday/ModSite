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
		global $smcFunc;

		$this->_rows = array(
			'id' => 'id',
			'cat' => 'id_category',
			'user' => 'id_user',
			'down' => 'downloads',
			'name' => 'name',
			'file' => 'file',
			'demo' => 'demo',
			'version' => 'version',
			'topic' => 'id_topic',
			'smf' => 'smf_version',
			'smfd' => 'smf_download',
			'desc' => 'description',
			'github' => 'github',
			'info' => 'info',
			'time' => 'time',
		);

		$this->smcFunc = $smcFunc;
	}

	public function killCache($type)
	{
		cache_put_data(Modsite::$name . $type, null);
	}

	public function getAllCategories()
	{
		if (($return = cache_get_data(ModSite::$name .'-cats', 120)) == null)
		{
			$query = $this->smcFunc['db_query']('', '
				SELECT id, name
				FROM {db_prefix}mod_categories',
				array(
				)
			);

			while($row = $this->smcFunc['db_fetch_assoc']($query))
				$return[$row['id']] = $row['name'];

			$this->smcFunc['db_free_result']($query);

			cache_put_data(ModSite::$name .'-cats', $return, 120);
		}

		return $return;
	}

	public function getAllMods()
	{
		if (($return = cache_get_data(ModSite::$name .'-mods', 120)) == null)
		{
			$query = $this->smcFunc['db_query']('', '
				SELECT c.id, c.name, m.id, m.id_category, m.id_user, m.downloads, m.name, m.file, m.demo, m.version, m.id_topic, m.smf_version, m.smf_download, m.github, m.description, m.info, m.time
				FROM {db_prefix}mod_site
					LEFT JOIN {db_prefix}mod_categories AS c ON (c.id = m.id_category)
				ORDER BY m.time DESC',
				array(
				)
			);

			while($row = $this->smcFunc['db_fetch_assoc']($query))
				$return = $row;

			$this->smcFunc['db_free_result']($query);

			cache_put_data(ModSite::$name .'-mods', $return, 120);
		}

		return $return;
	}

	public function insertMod($array = array())
	{
		if (empty($array) || !is_array($array))
			return false;

		/* No logic, just action! */
		$this->_smcFunc['db_insert']('replace', '{db_prefix}'. ModSite::$_tableName['mod'], array(
			'id_category' => 'int',
			'id_user' => 'int',
			'downloads' => 'int',
			'name' => 'string',
			'file' => 'string',
			'demo' => 'string',
			'version' => 'string',
			'id_topic' => 'int',
			'smf_version' => 'string',
			'smf_download' => 'string',
			'description' => 'string',
			'github' => 'string',
			'info' => 'string',
			'time' => 'int',
			), $array, array('id')
		);
	}
}