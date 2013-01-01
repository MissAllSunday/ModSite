<?php

	function GetMods(){

		global $context, $GetMods, $GetCats, $smcFunc, $modSettings, $sourcedir, $scripturl, $settings, $txt;

		require_once($sourcedir . '/Subs.php');

		$num_mods = empty($modSettings['num_mods']) ? 100 : (int) $modSettings['num_mods'];
		$mod_sort = empty($modSettings['mod_sort_method']) ? '' : $modSettings['mod_sort_method'];
		$mod_sort = empty($modSettings['mod_sort_method']) ? 'id' : $modSettings['mod_sort_method'];
		$total_mods = CountMod();

		$query = $smcFunc['db_query']('', '
			SELECT m.id, m.description, m.category_id, m.approved, m.user, m.descargas, m.name, m.file_name, m.file_url, m.file_path, m.file_type, m.file_size, m.demo, m.version, m.id_topic, m.smf_version, m.smf_download, m.id_project, m.timestamp, cat.category_id, cat.category_name, mem.id_member, mem.real_name
			FROM {db_prefix}mod AS m
			LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = m.user)			
			LEFT JOIN {db_prefix}mod_categories AS cat ON (cat.category_id = m.category_id)
			ORDER BY {raw:sort}
			LIMIT {int:start}, {int:maxindex}',
			array(
				'start' => $_REQUEST['start'],
				'maxindex' => $num_mods,
				'sort' => $mod_sort,
			)
		);

		$GetMods = array();
		$context['GetMods'] = array();

		while($row = $smcFunc['db_fetch_assoc']($query)){

			$GetMods[$row['id']] = $row;
			$GetMods[$row['id']]['description'] = ModGetClean($GetMods[$row['id']]['description']);
			$context['GetMods'][] = array(
				'id' => $row['id'],
				'name' => $row['name'],
				'description' => ModGetClean($row['description']),
				'approved' => $row['approved'],				
				'category' => array(
					'name' => $row['category_name'],
					'href' => $scripturl.'?action=mods;sa=category;catid='.$row['category_id'].'',
					'link' => '<a href="'.$scripturl.'?action=mods;sa=category;catid='.$row['category_id'].'">'.$row['category_name'].'</a>',
					'smf_download' => !empty($row['smf_download']) ? '<a href="'.$row['smf_download'].'">'.$txt['mod_download_at_smf'].'</a>' : $txt['mod_download_at_smf'],	
				),
				'mod' => array (
					'demo_link' => !empty($row['demo']) ? '<a href="'.$row['demo'].'">'.$row['name'].'</a>' : $row['name'],
					'version' => $row['version'],
					'smf_version' => $row['smf_version'],
					'href' => $scripturl.'?topic='.$row['id_topic'],
					'topic_url' => empty($row['id_topic']) ? $row['name'] : '<a href="'.$scripturl.'?topic='.$row['id_topic'].'">'.$row['name'].'</a>',	
				),
				'file' => array (
					'descargas' => $row['descargas'],
					'name' => $row['file_name'],
					'size' => $row['file_size'],
					'type' => $row['file_type'],
					'path' => $row['file_path'],					
					'url' => $row['file_url'],
					'link' => '<a href="'.$row['file_url'].'">'.$row['file_name'].'</a>',	
				),
				'user' => array (
					'id' => $row['id_member'],
					'name' => $row['real_name'],
					'link' => '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '">' . $row['real_name'] . '</a>',
				),
				'tracker' => GetIssues($row['id_project']),

			);			
		}

		$smcFunc['db_free_result']($query);
		$context['page_index'] = constructPageIndex($scripturl . '?action=mods', $_REQUEST['start'], $total_mods,$num_mods, false);

	}

	// Count the FAQs
	function CountMod(){

		global $smcFunc;

		$request = $smcFunc['db_query']('', '
			SELECT COUNT(*)
			FROM {db_prefix}mod',
			array(
			)
		);

		list ($count_mod) = $smcFunc['db_fetch_row']($request);
		$smcFunc['db_free_result']($request);

		return $count_mod;

	}

	// Get the FAQs to edit/delete them... or to show them all
	function GetModsAdmin($where = ''){

		global $smcFunc, $context, $GetModsEdit, $scripturl, $txt;

			$query = $smcFunc['db_query']('', '
				SELECT m.id, m.description, m.category_id, m.approved, m.user, m.descargas, m.name, m.file_name, m.file_url, m.file_path, m.file_type, m.file_size, m.demo, m.version, m.id_topic, m.smf_version, m.smf_download, m.id_project, m.timestamp, cat.category_id, cat.category_name, mem.id_member, mem.real_name
				FROM {db_prefix}mod AS m
				LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = m.user)			
				LEFT JOIN {db_prefix}mod_categories AS cat ON (cat.category_id = m.category_id)
				'.(!empty($where) ? 'WHERE m.id ={int:id} LIMIT {int:limit}' : '').'
				',
				array(
					'limit' => 1,
					'id' => (int) $where,
				)
			);

			$GetModsEdit = array();
			$return = array();

			while($row = $smcFunc['db_fetch_assoc']($query)){

				$GetModsEdit[$row['id']] = $row;
				$GetModsEdit[$row['id']]['description'] = ModGetClean($GetModsEdit[$row['id']]['description']);	
				$return[] = array(
					'id' => $row['id'],
					'name' => $row['name'],
					'description' => ModGetClean($row['description']),
					'approved' => $row['approved'],				
					'category' => array(
						'name' => $row['category_name'],
						'href' => $scripturl.'?action=mods;sa=category;catid='.$row['category_id'].'',
						'link' => '<a href="'.$scripturl.'?action=mods;sa=category;catid='.$row['category_id'].'">'.$row['category_name'].'</a>',
						'smf_download' => !empty($row['smf_download']) ? '<a href="'.$row['smf_download'].'">'.$txt['mod_download_at_smf'].'</a>' : $txt['mod_download_at_smf'],	
					),
					'mod' => array (
						'demo_link' => !empty($row['demo']) ? '<a href="'.$row['demo'].'">'.$row['name'].'</a>' : $row['name'],
						'version' => $row['version'],
						'smf_version' => $row['smf_version'],
						'href' => $scripturl.'?topic='.$row['id_topic'],
						'topic_url' => empty($row['id_topic']) ? $row['name'] : '<a href="'.$scripturl.'?topic='.$row['id_topic'].'">'.$row['name'].'</a>',
					),
					'file' => array (
						'descargas' => $row['descargas'],
						'name' => $row['file_name'],
						'size' => $row['file_size'],
						'type' => $row['file_type'],
						'path' => $row['file_path'],					
						'url' => $row['file_url'],
						'link' => '<a href="'.$row['file_url'].'">'.$row['file_name'].'</a>',	
					),
					'user' => array (
						'id' => $row['id_member'],
						'name' => $row['real_name'],
						'link' => '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '">' . $row['real_name'] . '</a>',
					),
					'tracker' => GetIssues($row['id_project']),

				);
			}

		$smcFunc['db_free_result']($query);
		
		return $return;
	
	}
	
	// Get the FAQs by category,  
	function GetModsbyCat($CatID = ''){

		global $smcFunc, $context, $modsettings, $GetModsbyCat, $scripturl, $txt;
		
		$num_mods = empty($modSettings['num_mods']) ? 100 : (int) $modSettings['num_mods'];		

			$query = $smcFunc['db_query']('', '
				SELECT m.id, m.description, m.category_id, m.approved, m.user, m.descargas, m.name, m.file_name, m.file_url, m.file_path, m.file_type, m.file_size, m.demo, m.version, m.id_topic, m.smf_version, m.smf_download, m.id_project, m.timestamp, cat.category_id, cat.category_name, mem.id_member, mem.real_name
				FROM {db_prefix}mod AS m
				LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = m.user)			
				LEFT JOIN {db_prefix}mod_categories AS cat ON (cat.category_id = m.category_id)
				WHERE cat.category_id={int:category_id}',
				array(
				'category_id' => $CatID,				
				)
			);

			$GetModsbyCat = array();
			$return = array();

			while($row = $smcFunc['db_fetch_assoc']($query)){

				$GetModsbyCat[$row['id']] = $row;
				$GetModsbyCat[$row['id']]['description'] = ModGetClean($GetModsbyCat[$row['id']]['description']);
				$GetModsbyCat[$row['id']]['description'] = parse_bbc($GetModsbyCat[$row['id']]['description']);				
				$return[] = array(
					'id' => $row['id'],
					'name' => $row['name'],
					'description' => ModGetClean($row['description']),
					'approved' => $row['approved'],				
					'category' => array(
						'name' => $row['category_name'],
						'href' => $scripturl.'?action=mods;sa=category;catid='.$row['category_id'].'',
						'link' => '<a href="'.$scripturl.'?action=mods;sa=category;catid='.$row['category_id'].'">'.$row['category_name'].'</a>',
						'smf_download' => !empty($row['smf_download']) ? '<a href="'.$row['smf_download'].'">'.$txt['mod_download_at_smf'].'</a>' : $txt['mod_download_at_smf'],	
					),
					'mod' => array (
						'demo_link' => !empty($row['demo']) ? '<a href="'.$row['demo'].'">'.$row['name'].'</a>' : $row['name'],
						'version' => $row['version'],
						'smf_version' => $row['smf_version'],
						'href' => $scripturl.'?topic='.$row['id_topic'],
						'topic_url' => empty($row['id_topic']) ? $row['name'] : '<a href="'.$scripturl.'?topic='.$row['id_topic'].'">'.$row['name'].'</a>',
					),	
					'file' => array (
						'descargas' => $row['descargas'],
						'name' => $row['file_name'],
						'size' => $row['file_size'],
						'type' => $row['file_type'],
						'path' => $row['file_path'],					
						'url' => $row['file_url'],
						'link' => '<a href="'.$row['file_url'].'">'.$row['file_name'].'</a>',	
					),
					'user' => array (
						'id' => $row['id_member'],
						'name' => $row['real_name'],
						'link' => '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '">' . $row['real_name'] . '</a>',
					),
					'tracker' => GetIssues($row['id_project']),

				);
			}

		$smcFunc['db_free_result']($query);		
		
		return $return;
	
	}	

	// Editing.
	function editMod2($ModID = ''){

		global $smcFunc, $GetMods, $user_info, $txt, $scripturl, $modSettings, $boarddir, $boardurl;

		// No name/description/no category no cookie...
		if (empty($_POST['name']))
			fatal_lang_error('Mod_no_name', false);
			
		if (empty($_POST['description1']))
			fatal_lang_error('Mod_no_description', false);

		if (empty($_POST['category_id']))
			fatal_lang_error('Mod_no_category', false);

		if (empty($_POST['demo']))
			$demo = $txt['no_demo_url'];
			
		if (empty($_POST['version']))
			$version = '1.0';

		if (empty($_POST['smf_version']))
			$smf_version = '2.0';

		if (empty($_POST['smf_download']))
			$smf_download = '';

		if (empty($_POST['id_project']))
			$id_project = 0;

		// Get the file info only if we want to overwrite the file...
		if(isset($_FILES)){
		$file_name =  $_FILES['myfile']['name'];
		$file_size = round($_FILES['myfile']['size'] / 1024);
		$file_type = $_FILES['myfile']['type'];
		$file_url =  $boardurl.'/'.$modSettings['mod_folder'].'/'.$_FILES['myfile']['name'];
		$file_path = $boarddir.'/'.$modSettings['mod_folder'].'/'.$_FILES['myfile']['name'];
		}

		//Cleaning up the mess	
		$mod_description = ModClean($_POST['description1'], true);
		$mod_name = ModClean($_POST['name'], false);
		$mod_category = (int) $_POST['category_id'];
		$user = $user_info['id'];		
		$demo = ModClean($_POST['demo'], false);
		$version = ModClean($_POST['version'], false);
		$smf_version =ModClean( $_POST['smf_version'], false);
		$smf_download = ModClean($_POST['smf_download'], false);
		$id_project = (int) $_POST['id_project'];



		// Time to update the Mod.
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}mod
			SET name={string:name}, description={string:description}, category_id={int:category_id}, user={int:user}, demo={string:demo}, version={string:version}, smf_version={string:smf_version}, smf_download={string:smf_download}, id_project={int:id_project}'.(!isset($_FILES) ? ', file_name={string:file_name}, file_size={int:file_size}, $file_type={string:file_type}, file_url={string:file_url}, file_path={string:file_path}' : '').'
			WHERE id={int:id}',
			array(
				'name' => $mod_name,
				'description' => $mod_description,
				'category_id' => $mod_category,
				'user' => $user,
				'demo' => $demo,
				'version' => $version,
				'smf_version' => $smf_version,
				'smf_download' => $smf_download,
				'id_project' => $id_project,
				'file_name' => $file_name,
				'file_size' => $file_size,
				'file_type' => $file_type,
				'file_url' => $file_url,
				'file_path' => $file_path,
				'id' => $ModID
			)
		);
	}

	// Bye bye Mod...
	function delete2($ModID = ''){

		global $smcFunc;

		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}mod
			WHERE id={string:id}',
			array(
				'id' => $ModID,
			)
		);
	}

	function add2(){
	
		global $context, $smcFunc, $user_info, $boarddir, $modSettings, $scripturl, $boardurl, $txt;

		// No name/description/no category no cookie...
		if (empty($_POST['name']))
			fatal_lang_error('Mod_no_name', false);
			
		if (empty($_POST['description1']))
			fatal_lang_error('Mod_no_description', false);

		if (empty($_POST['category_id']))
			fatal_lang_error('Mod_no_category', false);

		if (empty($_POST['demo']))
			$demo = $txt['no_demo_url'];
			
		if (empty($_POST['version']))
			$version = '1.0';

		if (empty($_POST['smf_version']))
			$smf_version = '2.0';

		if (empty($_POST['smf_download']))
			$smf_download = '';

		if (empty($_POST['id_project']))
			$id_project = 0;

		else if(!isset($_FILES['myfile']))
			fatal_lang_error('need_file', false);			


		// Get the file info
		$file_name =  $_FILES['myfile']['name'];
		$file_size = round($_FILES['myfile']['size'] / 1024);
		$file_type = $_FILES['myfile']['type'];
		$file_url =  $boardurl.'/'.$modSettings['mod_folder'].'/'.$_FILES['myfile']['name'];
		$file_path = $boarddir.'/'.$modSettings['mod_folder'].'/'.$_FILES['myfile']['name'];

		//Cleaning up the mess	
		$mod_description = ModClean($_POST['description1'], true);
		$mod_name = ModClean($_POST['name'], false);
		$mod_category = (int) $_POST['category_id'];
		$user = $user_info['id'];		
		$demo = ModClean($_POST['demo'], false);
		$version = ModClean($_POST['version'], false);
		$smf_version =ModClean( $_POST['smf_version'], false);
		$smf_download = ModClean($_POST['smf_download'], false);
		$id_project = (int) $_POST['id_project'];
		$id_topic = 0;
		$approved = 0;

		if(move_uploaded_file($_FILES['myfile']['tmp_name'], $file_path)){	

			$smcFunc['db_insert']('replace',
				'{db_prefix}mod',
				array(
					'name' => 'string', 'description' => 'string', 'category_id' => 'int', 'user' => 'int', 'demo' => 'string', 'version' => 'string', 'smf_version' => 'string', 'smf_download' => 'string', 'id_project' => 'int', 'id_topic' => 'int', 'approved' => 'int', 'file_name' => 'string', 'file_size' => 'int', 'file_type' => 'string', 'file_url' => 'string', 'file_path' => 'string'
				),
				array(
					$mod_name, $mod_description, $mod_category, $user, $demo, $version, $smf_version, $smf_download, $id_project, $id_topic, $approved, $file_name, $file_size, $file_type, $file_url, $file_path
				),
				array('id')
			);
		}
		
	}

	// It's categories time!
	
	// Get all the Cats! just don't hurt them please :P
	function GetCats(){

		global $smcFunc, $context, $GetCats;

			$query = $smcFunc['db_query']('', '
				SELECT category_id, category_name 
				FROM {db_prefix}mod_categories',
				array(
				)
			);

			$GetCats = array();
			$return = array();

			while($row = $smcFunc['db_fetch_assoc']($query)){

				$GetCats[$row['category_id']] = $row;				
				$return[] = $GetCats[$row['category_id']];
			}

		$smcFunc['db_free_result']($query);
		
		return $return;
	
	}	
	
	// Edit a cat!
	function editCat2($CatID = ''){

		global $smcFunc, $user_info;

		// No name no cookie...
		if (empty($_POST['category_name']))
			fatal_lang_error('Mod_no_category_name', false);

		// Cleaning up the mess	
		$cat_name = ModClean($_POST['category_name'],false);		

		// Time to update the Cat...maybe into a Lion... wait, that's an upgrade!
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}mod_categories
			SET category_name={string:category_name}
			WHERE category_id={int:id}',
			array(
				'category_name' => $cat_name,
				'id' => (int) $CatID
			)
		);
	}

	// We never get enough cats...
	function addCat2(){
	
		global $context, $smcFunc, $user_info;

		// No name no cookie...
		if (empty($_POST['category_name']))
			fatal_lang_error('Mod_no_category_name', false);

		$cat_name = ModClean($_POST['category_name'],false);

		$smcFunc['db_insert']('replace',
            '{db_prefix}mod_categories',
            array(
                'category_name' => 'string'
            ),
            array(
                $cat_name
            ),
            array('category_id')
        );
	}	

	// No comments... you murderer...
	function deleteCat2($CatID = ''){

		global $smcFunc;

		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}mod_categories
			WHERE category_id={string:id}',
			array(
				'id' => $CatID,
			)
		);
	}

	// Get the last 5 issues for this mod
	function GetIssues($id_project){
		
	global $smcFunc, $scripturl, $settings, $context;
	
	$limit = 5;
	
		// Status
	$context['issue_status'] = array(
		1 => array(
			'id' => 1,
			'name' => 'new',
			'type' => 'open',
		),
		2 => array(
			'id' => 2,
			'name' => 'feedback',
			'type' => 'open',
		),
		3 => array(
			'id' => 3,
			'name' => 'confirmed',
			'type' => 'open',
		),
		4 => array(
			'id' => 4,
			'name' => 'assigned',
			'type' => 'open',
		),
		5 => array(
			'id' => 5,
			'name' => 'resolved',
			'type' => 'closed',
		),
		6 => array(
			'id' => 6,
			'name' => 'closed',
			'type' => 'closed',
		),
	);

	$request = $smcFunc['db_query']('', '
		SELECT
			i.id_project, i.id_issue, i.subject, i.priority, i.status, i.created, i.updated, i.id_tracker, i.status,
			mem.id_member, mem.real_name, cat.id_category, cat.category_name,
			p.id_tracker, p.short_name, p.tracker_name, p.plural_name
		FROM {db_prefix}issues AS i
			LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = i.id_assigned)
			LEFT JOIN {db_prefix}project_trackers AS p ON (p.id_tracker = i.id_tracker)
			LEFT JOIN {db_prefix}issue_category AS cat ON (cat.id_category = i.id_category)
		WHERE i.id_project={int:id_project}	
		ORDER BY i.id_issue DESC		
		LIMIT {int:limit}',
			array(
				'limit' => (int) $limit,
				'id_project' => (int) $id_project,
			)
	);


	$return = array();

	while($row = $smcFunc['db_fetch_assoc']($request)){
	
		// Prepare issue array
		$return[] = array(
			'id' => $row['id_issue'],
			'name' => $row['subject'],
			'href' => $scripturl .'?issue='.$row['id_issue'] . '.0',
			'link' => '<a href="'.$scripturl .'?issue='.$row['id_issue'] . '.0'.'">'.$row['subject'].'</a>',
			'category' => array(
				'id' => $row['id_category'],
				'name' => $row['category_name'],
				'link' => '<a href="'.$scripturl.'?project='.$row['id_project'].';area=issues;category='.$row['id_category'].'">'.$row['category_name'].'</a>',
			),
			'tracker' => array(
				'id' => $row['id_tracker'],
				'name' => $row['tracker_name'],
				'short' => $row['short_name'],
				'plural' => $row['plural_name'],
				'image' => '<img src="'. $settings['default_images_url']. '/'. $row['short_name']. '.png" />',
			),
			'status' => &$context['issue_status'][$row['status']],
			'assignee' => array(
				'id' => $row['id_member'],
				'name' => $row['real_name'],
				'link' => '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '">' . $row['real_name'] . '</a>',
			),
		);
	}
	
	$smcFunc['db_free_result']($request);
	
	return $return;

	}
	
	// We must approve this awesome mod...
	function ApproveMod($ModID = ''){
	
		global $smcFunc;
	
		$approved = 1;
		$last_topic = CountTopics();
		$id_topic = $last_topic + 1;
	
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}mod
			SET approved={string:approved}, id_topic={int:id_topic}
			WHERE id={int:id}',
			array(
				'approved' => $approved,
				'id_topic' => $id_topic,
				'id' => $ModID
			)
		);	
	}
	
	// Someone downloaded this mod?  MADNESSSSS!
	function DownMod($ModID = ''){
	
		global $smcFunc;
	
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}mod
			SET descargas = descargas + 1
			WHERE id={int:id}',
			array(
				'id' => $ModID
			)
		);	
	}	
	
	// Lets count all the topics and get the very last one...
	function CountTopics(){

		global $smcFunc;

		$request = $smcFunc['db_query']('', '
			SELECT id_topic 
			FROM {db_prefix}topics ORDER BY id_topic DESC 
			LIMIT 1',
			array(
			)
		);

		while($row = $smcFunc['db_fetch_assoc']($request)){
		
			$last_topic = $row['id_topic'];
		
		}
		$smcFunc['db_free_result']($request);

		return $last_topic;

	}	

	// Cleaning
	function ModClean($toclean, $description = false){
 
		global $smcFunc, $sourcedir;
 
		$toclean = $smcFunc['htmlspecialchars']($toclean, ENT_QUOTES);
		$toclean = $smcFunc['htmltrim']($toclean, ENT_QUOTES);
	
		if ($description){
		
			require_once($sourcedir . '/Subs-Post.php');
			preparsecode($toclean);	
		}
	
		return $toclean;
	}
	
	function ModGetClean($togetclean){
	
		global $sourcedir;
	
		require_once($sourcedir . '/Subs-Post.php');
	
		$togetclean = un_preparsecode($togetclean);
		
		return $togetclean;
	
	}

	// Show me that you care
	function ModCare(){

		$mod_care = '<div class="smalltext" style="text-align:center;">
<a href="http://missallsunday.com" target="_blank" name="Free SMF mods">Ohara Downloads &copy; Miss All Sunday</a>
</div>';

		echo $mod_care;

	}	
	
	// Believe in you.
?>