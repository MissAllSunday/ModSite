<?php

/**
 * Mod Site (SMF)
 *
 * @package SMF
 * @author Suki <suki@missallsunday.com>
 * @copyright 2011 Suki http://missallsunday.com
 * @license http://www.mozilla.org/MPL/ MPL 2.0
 *
 * @version 1.0 Beta
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

	if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
		require_once(dirname(__FILE__) . '/SSI.php');

	elseif (!defined('SMF'))
		exit('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');

	global $smcFunc, $context, $db_prefix;

	db_extend('packages');

	if (empty($context['uninstalling'])){

		$table = array(
			'table_name' => 'post_limit',
			'columns' => array(
				array(
					'name' => 'id_user',
					'type' => 'int',
					'size' => 5,
					'null' => false,
				),
				array(
					'name' => 'id_boards',
					'type' => 'varchar',
					'size' => 255,
					'default' => '',
				),
				array(
					'name' => 'post_limit',
					'type' => 'int',
					'size' => 5,
					'null' => false,
				),
				array(
					'name' => 'post_count',
					'type' => 'int',
					'size' => 5,
					'null' => false,
				),
			),
			'indexes' => array(
				array(
					'type' => 'primary',
					'columns' => array('id_user')
				),
			),
			'if_exists' => 'ignore',
			'error' => 'fatal',
			'parameters' => array(),
		);

		$smcFunc['db_create_table']($db_prefix . $table['table_name'], $table['columns'], $table['indexes'], $table['parameters'], $table['if_exists'], $table['error']);

		/* Add the Scheduled Task */
		$smcFunc['db_insert']('ignore',
			'{db_prefix}scheduled_tasks',
			array(
				'id_task' => 'int',
				'next_time' => 'int',
				'time_offset' => 'int',
				'time_regularity' => 'int',
				'time_unit' => 'string',
				'disabled' => 'int',
				'task' => 'string',
			),
			array(0,0,0,1,'d',0,'postLimit'
			),
			array('task')
		);
	}
 