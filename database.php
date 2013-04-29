<?php

	if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
		require_once(dirname(__FILE__) . '/SSI.php');

	elseif (!defined('SMF'))
		exit('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');

	global $smcFunc, $context, $db_prefix;

	db_extend('packages');

	if (empty($context['uninstalling'])){

		$tables[] = array(
			'table_name' => 'mod_site',
			'columns' => array(
				array(
					'name' => 'id',
					'type' => 'int',
					'size' => 5,
					'null' => false,
					'auto' => true
				),
				array(
					'name' => 'id_category',
					'type' => 'int',
					'size' => 5,
					'null' => false
				),
				array(
					'name' => 'id_user',
					'type' => 'int',
					'size' => 10,
				),
				array(
					'name' => 'downloads',
					'type' => 'int',
					'size' => 10,
				),
				array(
					'name' => 'name',
					'type' => 'varchar',
					'size' => 255,
					'default' => '',
				),
				array(
					'name' => 'file',
					'type' => 'text',
					'size' => '',
					'default' => '',
				),
				array(
					'name' => 'demo',
					'type' => 'varchar',
					'size' => 255,
					'default' => '',
				),
				array(
					'name' => 'version',
					'type' => 'varchar',
					'size' => 255,
					'default' => '',
				),
				array(
					'name' => 'id_topic',
					'type' => 'int',
					'size' => 10,
				),
				array(
					'name' => 'smf_version',
					'type' => 'varchar',
					'size' => 255,
					'default' => '',
				),
				array(
					'name' => 'smf_download',
					'type' => 'varchar',
					'size' => 255,
					'default' => '',
				),
				array(
					'name' => 'github',
					'type' => 'varchar',
					'size' => 255,
					'default' => '',
				),
				array(
					'name' => 'description',
					'type' => 'text',
					'size' => '',
					'default' => '',
				),
				array(
					'name' => 'time',
					'type' => 'int',
					'size' => 10,
					'default' => null,
				),
			),
			'indexes' => array(
				array(
					'type' => 'primary',
					'columns' => array('id')
				),
			),
			'if_exists' => 'ignore',
			'error' => 'fatal',
			'parameters' => array(),
		);
		$tables[] = array (
			'table_name' => 'mod_categories',
			'columns' => array(
				array(
					'name' => 'cat_id',
					'type' => 'int',
					'size' => 10,
					'auto' => true,
				),
				array(
					'name' => 'cat_name',
					'type' => 'varchar',
					'size' => 255,
					'default' => '',
				),
			),
			'indexes' => array(
				array(
					'type' => 'primary',
					'columns' => array(
						'cat_id'
					),
				),
			),
			'if_exists' => 'ignore',
			'error' => 'fatal',
			'parameters' => array(),
		);

		// Loop through each defined table and do whats needed, update existing or add as new
		foreach ($tables as $table)
			$smcFunc['db_create_table']($db_prefix . $table['table_name'], $table['columns'], $table['indexes'], $table['parameters'], $table['if_exists'], $table['error']);
	}