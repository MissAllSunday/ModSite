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
					'name' => 'name',
					'type' => 'varchar',
					'size' => 255,
					'default' => '',
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
			'table_name' => 'mod_cat',
			'columns' => array(
				array(
					'name' => 'id_cat',
					'type' => 'int',
					'size' => 10,
					'auto' => true,
				),
				array(
					'name' => 'name_cat',
					'type' => 'varchar',
					'size' => 255,
					'default' => '',
				),
			),
			'indexes' => array(
				array(
					'type' => 'primary',
					'columns' => array(
						'id_cat'
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