<?php

	if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF')) 
		require_once(dirname(__FILE__) . '/SSI.php');
		
	elseif (!defined('SMF'))
		exit('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');

	global $smcFunc, $context, $db_prefix;

	db_extend('packages'); 

	if (empty($context['uninstalling'])){
	
		$tables[] = array(
			'table_name' => 'mod',
			'columns' => array(
				array(
					'name' => 'id', 
					'type' => 'int', 
					'size' => 5, 
					'null' => false, 
					'auto' => true
				),
				array(
					'name' => 'category_id', 
					'type' => 'int', 
					'size' => 5, 
					'null' => false
				),
				array(
					'name' => 'approved', 
					'type' => 'int', 
					'size' => 5, 
					'null' => false
				),				
				array(
					'name' => 'user', 
					'type' => 'int', 
					'size' => 11, 
				),
				array(
					'name' => 'descargas', 
					'type' => 'int', 
					'size' => 11, 
				),				
				array(
					'name' => 'name', 
					'type' => 'varchar', 
					'size' => 255, 
					'default' => '',
				),
				array(
					'name' => 'file_name', 
					'type' => 'varchar', 
					'size' => 255, 
					'default' => '',
				),
				array(
					'name' => 'file_url', 
					'type' => 'varchar', 
					'size' => 255, 
					'default' => '',
				),
				array(
					'name' => 'file_path', 
					'type' => 'varchar', 
					'size' => 255, 
					'default' => '',
				),				
				array(
					'name' => 'file_type', 
					'type' => 'varchar', 
					'size' => 255, 
					'default' => '',
				),
				array(
					'name' => 'file_size', 
					'type' => 'int', 
					'size' => 11, 
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
					'type' => 'varchar', 
					'size' => 255, 
					'default' => '',
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
					'name' => 'id_project', 
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
					'name' => 'timestamp',
					'type' => 'timestamp',
					'size' => '',
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
					'name' => 'category_id', 
					'type' => 'int', 
					'size' => 11, 
					'auto' => true,
				),					
				array(
					'name' => 'category_name', 
					'type' => 'varchar', 
					'size' => 255,  
					'default' => '',
				),
			),
			'indexes' => array(
				array(
					'type' => 'primary', 
					'columns' => array(
						'category_id'
					),
				),
			),
			'if_exists' => 'ignore',
			'error' => 'fatal',
			'parameters' => array(),
		); 
		
		// Now the tables ... if they don't exist create them and if they do exist update them if required.
		$current_tables = $smcFunc['db_list_tables'](false, '%mod%');
		$real_prefix = preg_match('~^(`?)(.+?)\\1\\.(.*?)$~', $db_prefix, $match) === 1 ? $match[3] : $db_prefix;

		// Loop through each defined table and do whats needed, update existing or add as new
		foreach ($tables as $table)
		{
			// Does the table exist?
			if (in_array($real_prefix . $table['table_name'], $current_tables)){
		
				foreach ($table['columns'] as $column)
					if ($column['type'] != 'timestamp')
						$smcFunc['db_add_column']($db_prefix . $table['table_name'], $column);

				foreach ($table['indexes'] as $index)
					$smcFunc['db_add_index']($db_prefix . $table['table_name'], $index, array(), 'ignore');
			}
			else
				$smcFunc['db_create_table']($db_prefix . $table['table_name'], $table['columns'], $table['indexes'], $table['parameters'], $table['if_exists'], $table['error']);
		}

		// Give them a default category for good measure
		$rows = array();
		$rows[] = array(
			'method' => 'ignore',
			'table_name' => '{db_prefix}mod_categories',
			'columns' => array(
				'category_name' => 'string',
				'category_id' => 'int'
			),
			'data' => array(
				'New Feature',
				1
			),
			'keys' => array(
				'id_category'
			)
		);

		// Add rows to any existing tables
		foreach ($rows as $row)
			$smcFunc['db_insert']($row['method'], $row['table_name'], $row['columns'], $row['data'], $row['keys']);
 
	}	
	
	// You can't go fishing without hooks
	$hooks = array(
		'integrate_pre_include' => '$boarddir/Sources/Mod.php',
		'integrate_menu_buttons' => 'ModMenu',
		'integrate_actions' => 'ModAction',
		'integrate_load_permissions' =>	'ModPermissions',
		'integrate_admin_areas' => 'ModAdmin',
	);

	if (!empty($context['uninstalling']))
		$call = 'remove_integration_function';
		
	else
		$call = 'add_integration_function';

	foreach ($hooks as $hook => $function)
		$call($hook, $function);

?>