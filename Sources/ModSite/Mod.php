<?php


	// If this code works, it was written by Miss All Sunday. If not, I don't know who wrote it.

	if (!defined('SMF'))
		die('Hacking attempt...');
		
	loadLanguage('Mod');

	// Action hook
	function ModAction(&$actions){

		$actions['mods'] = array('Mod.php', 'mods');

	}

	// Permission hook
	function ModPermissions(&$permissionGroups, &$permissionList){

		$permissionGroups['membergroup']['simple'] = array('modper');
		$permissionGroups['membergroup']['classic'] = array('modper');
		$permissionList['membergroup']['modperview'] = array(false, 'modper', 'modper');
		$permissionList['membergroup']['modperedit'] = array(false, 'modperedit', 'modper');

	}

	// Button menu hook
	function ModMenu(&$menu_buttons){

		global $scripturl, $txt, $modSettings;
		
		$mod_insert = empty($modSettings['mod_menu_position']) ? 'home' : $modSettings['mod_menu_position'];

		// Lets add our button next to the ModifyModsSettings's selection... 
		// Thanks to SlammedDime (http://mattzuba.com) for the example
		$counter = 0;
		foreach ($menu_buttons as $area => $dummy)
			if (++$counter && $area == $mod_insert)
				break;

		$menu_buttons = array_merge(
			array_slice($menu_buttons, 0, $counter),
			array('mod' => array(
			'title' => $txt['Mod_name'],
			'href' => $scripturl . '?action=mods',
			'show' => allowedTo('modperview'),
			'sub_buttons' => array(
				'mod_admin' => array(
					'title' => $txt['mod_manage'],
					'href' => $scripturl . '?action=mods;sa=manage',
					'show' => allowedTo('modperedit'),
					'sub_buttons' => array(
						'mod_add' => array(
						'title' => $txt['Mod_add_send'],
						'href' => $scripturl . '?action=mods;sa=add',
						'show' => allowedTo('modperedit'),
						),
					),
				),
				'mod_category' => array(
					'title' => $txt['mod_manage_category'],
					'href' => $scripturl . '?action=mods;sa=managecat',
					'show' => allowedTo('modperedit'),
					'sub_buttons' => array(
						'mod_add' => array(
						'title' => $txt['Mod_addcat_send'],
						'href' => $scripturl . '?action=mods;sa=addcat',
						'show' => allowedTo('modperedit'),
						),
					),
				),				
			),
		)),
			array_slice($menu_buttons, $counter)
		);
	}

	// Admin menu hook
	function ModAdmin(&$admin_areas){

		global $txt;

		$admin_areas['config']['areas']['moddmin'] = array(	
					'label' => $txt['mod_admin_panel'],
					'file' => 'Mod.php',
					'function' => 'ModifyMasModsSettings',
					'icon' => 'posts.gif',
					'subsections' => array(
						'basic' => array($txt['mod_basic_settings']),
						'files' => array($txt['mod_file_settings']),
						'edit' => array($txt['edit_mod_page']),
						'add' => array($txt['Mod_add_send']),
				),
		);
	}

	// The settings hook
	function ModifyMasModsSettings($return_config = false){
	
		global $txt, $scripturl, $context, $sourcedir;

		require_once($sourcedir . '/ManageSettings.php');

		$context['page_title'] = $txt['mod_admin_panel'];

		$subActions = array(
			'basic' => 'BasicModSettings',
			'files' => 'FilesModSettings',
			'edit' => 'EditModAdminPage',
			'add' => 'AddModAdminPage',
		);

		loadGeneralSettingParameters($subActions, 'basic');

		// Load up all the tabs...
		$context[$context['admin_menu_name']]['tab_data'] = array(
			'title' => $txt['mod_admin_panel'],
			'description' => $txt['mod_admin_panel_desc'],
			'tabs' => array(
				'basic' => array(
				),
				'files' => array(
				),
				'edit' => array(
				),
				'add' => array(
				),
			),
		);

		call_user_func($subActions[$_REQUEST['sa']]);

	}

	// Settings
	function BasicModSettings(){

		global $scripturl, $txt, $context, $boarddir, $modSettings, $sourcedir;
		
		require_once($sourcedir . '/ManageServer.php');

		$config_vars = array(
							array('int', 'num_mods', 'size' => 3, 'subtext' => $txt['num_mods_sub']),
							array('select', 'mod_sort_method', array('id' => $txt['mod_id'], 'name' => $txt['mod_name'], 'timestamp' => $txt['mod_date']), 'subtext' => $txt['mod_sort_method_sub']),
							array('select', 'mod_menu_position', array('home' => $txt['mod_menu_home'], 'help' => $txt['mod_menu_help'], 'search' => $txt['mod_menu_search'], 'login' => $txt['mod_menu_login'], 'register' => $txt['mod_menu_register']), 'subtext' => $txt['mod_menu_position_sub']),
							array('check', 'mod_use_javascript', 'subtext' => $txt['mod_use_javascript_sub']),
							array('check', 'mod_show_all', 'subtext' => $txt['mod_show_all_sub']),					
							array('check', 'mod_search_engines', 'subtext' => $txt['mod_search_engines_sub']),
							array('check', 'mod_care', 'subtext' => $txt['mod_care_sub']),
		);

		$context['post_url'] = $scripturl . '?action=admin;area=moddmin;sa=basic;save';

		// Saving?
		if (isset($_GET['save'])){

			checkSession();

			saveDBSettings($config_vars);

			redirectexit('action=admin;area=moddmin;sa=basic');
		}

		prepareDBSettingContext($config_vars);
	}
	
	// File Settings, paths/urls and such...
	function FilesModSettings(){

		global  $txt, $scripturl, $context, $sourcedir;
		
		require_once($sourcedir . '/ManageServer.php');

		$config_vars = array(
							array('int', 'mod_board', 'subtext' => $txt['mod_board_sub']),
							array('text', 'mod_folder', 'subtext' => $txt['mod_folder_sub']),					
							array('int', 'mod_max_size', 'subtext' => $txt['mod_max_size_sub']),
		);

		$context['post_url'] = $scripturl . '?action=admin;area=moddmin;sa=files;save';

		// Saving?
		if (isset($_GET['save'])){

			checkSession();

			saveDBSettings($config_vars);

			redirectexit('action=admin;area=moddmin;sa=files');
		}

		prepareDBSettingContext($config_vars);
	}	
	

	// A whole function for a redirect... yep, sounds about right :P
	function EditModAdminPage(){
			
		redirectexit('action=mods;sa=manage');
			
	}

	// Again?  this is MADNESS!	
	function AddModAdminPage(){
	
		redirectexit('action=mods;sa=add');
	}

	// Main function	
	function Mods(){

		global $sourcedir, $txt, $context,$scripturl, $GetMods, $GetModsEdit, $GetCats, $GetModsbyCat, $modSettings;

		require_once($sourcedir . '/Subs-Mod.php');
		require_once($sourcedir . '/Subs-Editor.php');

        if (!empty($_REQUEST['description_mode']) && isset($_REQUEST['description']))
        {
            $_REQUEST['description'] = html_to_bbc($_REQUEST['description']);
            $_REQUEST['description'] = un_htmlspecialchars($_REQUEST['description']);
            $_POST['description'] = $_REQUEST['description'];
        }
		
		$subActions = array(
			'manage' => array('ModManage'),		
			'edit' => array('ModEdit'),
			'edit2' => array('ModEdit2'),
			'delete' => array('ModDelete'),
			'delete2' => array('ModDelete2'),
			'add' => array('ModAdd'),
			'add2' => array('ModAdd2'),
			'managecat' => array('ModManageCat'),
			'editcat' => array('ModEditCat'),
			'editcat2' => array('ModEditCat2'),
			'deletecat' => array('ModDeleteCat'),
			'deletecat2' => array('ModDeleteCat2'),
			'addcat' => array('ModAddCat'),
			'addcat2' => array('ModAddCat2'),
			'category' => array('ModCategory'),
			'show' => array('ModShow'),
			'approve' => array('ModApprove'),
			'download' => array('ModDownload'),
		);

		// Default to no sub action if nothing was provided or we don't know what they want ;)
		$_REQUEST['sa'] = isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]) ? $_REQUEST['sa'] : '';

		// Do the permission check, you might not be allowed here.
		isAllowedTo('modperview');

		// Language and template stuff, the usual suspects
		loadLanguage('Mod');
		GetMods(); 
		GetModsAdmin();
		loadtemplate('Mod');
		writeLog(true);

		$context['page_title'] =  $txt['Mod_name'];
		$context['linktree'][] = array(
			'url' => $scripturl . '?action=mods',
			'name' => $txt['Mod_name']
		);
		$context['canonical_url'] = $scripturl . '?action=mods';
		$context['modperview'] = allowedTo('modperview');
		$context['modperedit'] = allowedTo('modperedit');
		$context['mod_getcats'] = GetCats();
		$context['getallmods'] = GetModsAdmin();
		$context['robot_no_index'] = false;

		// Call the right subaction, assuming there is one.
		if (!empty($_REQUEST['sa']))
			$subActions[$_REQUEST['sa']][0]();


			// Needed for the WYSIWYG editor, we all love the WYSIWYG editor...
			$modSettings['disable_wysiwyg'] = true;

			$editorOptions = array(
				'id' => 'description1',
				'value' => !empty($context['edit']['current']['description']) ? $context['edit']['current']['description'] : '',
				'width' => '90%',
			);

			create_control_richedit($editorOptions);
			$context['post_box_name'] = $editorOptions['id'];
			
			// Echo the javascript and css bits. 
			mod_headers();	
		
	}

	// Manage the FAQs, show a nice table please...	
	function ModManage() {
		global $txt, $context, $scripturl, $GetModsEdit;

		isAllowedTo('modperedit');
		$context['GetModsEdit'] = GetModsAdmin();
		$context['sub_template'] = 'show_edit';
		$context['page_title'] = $txt['mod_manage'];
		$context['linktree'][] = array(
			'url' => $scripturl. '?action=mods;sa=manage',
			'name' => $txt['mod_manage'],
		);			
	}

	// Editing, get the info and show a nice editing form
	function ModEdit() {
		global $txt, $context, $scripturl, $GetModsEdit;

		if (isset($_REQUEST['modid']) && in_array($_REQUEST['modid'], array_keys($GetModsEdit))) {

			isAllowedTo('modperedit');
			$context['edit']['current'] = $GetModsEdit[$_GET['modid']];
			$context['sub_template'] = 'add';
			$context['page_title'] = $txt['Mod_editing']. ' - '. $context['edit']['current']['name'];
			$context['linktree'][] = array(
				'url' => $scripturl. '?action=mods;sa=edit;modid='.$_GET['modid'] ,
				'name' => $txt['Mod_edit']. ' '. $context['edit']['current']['name'],
			);
		}
	}

	// Got the data?  let's update the FAQ ^o^
	function ModEdit2() {

		global $GetModsEdit, $txt, $context;

		if (isset($_GET['modid']) && in_array($_GET['modid'], array_keys($GetModsEdit))){

			checkSession('post', '', true);
			isAllowedTo('modperedit');
			editMod2($_GET['modid']);
			redirectexit('action=mods;sa=manage');		
			
		}
	}

	// Lets ask the ModifyModsSettings if (s)he really want to do this...
	function ModDelete() {
		global $txt, $context, $scripturl, $GetModsEdit;

		if (isset($_GET['modid']) && in_array($_GET['modid'], array_keys($GetModsEdit))){

			isAllowedTo('modperedit');
			$context['delete']['current'] = $GetModsEdit[$_GET['modid']];
			$context['sub_template'] = 'delete';
			$context['page_title'] = $txt['Mod_deleting']. ' - '. $context['delete']['current']['name'];
			$context['linktree'][] = array(
				'url' => $scripturl. '?action=mods;sa=edit;modid='.$_GET['modid'] ,
				'name' => $txt['Mod_deleting']. ' '. $context['delete']['current']['name'],
			);
		}
	}

	// Deleting...
	function ModDelete2() {

		global $GetModsEdit;
		
		if (isset($_GET['modid']) && in_array($_GET['modid'], array_keys($GetModsEdit))){

			checkSession('post', '', true);
			isAllowedTo('modperedit');
			delete2($_GET['modid']);
			redirectexit('action=mods;sa=manage');

		}
	}

	// Fill out the form to get a nice brand new FAQ...	
	function ModAdd() {

		global $txt, $context, $scripturl;

		isAllowedTo('modperedit');
		$context['sub_template'] = 'add';
		$context['page_title'] = $txt['Mod_adding'];
		$context['linktree'][] = array(
			'url' => $scripturl. '?action=mods;sa=add',
			'name' => $txt['Mod_adding'],
		);

	}
		
	// Adding...
	function ModAdd2() {
		global $txt, $context, $scripturl;

		checkSession('post', '', true);
		isAllowedTo('modperedit');
		add2();
		redirectexit('action=mods;sa=manage');
	}

	// Manage the categoriess, show a nice table ...again
	function ModManageCat() {
		global $txt, $context, $scripturl, $GetCats;

		isAllowedTo('modperedit');
		$context['GetCats'] = GetCats();
		$context['sub_template'] = 'show_edit_cat';
		$context['page_title'] = $txt['mod_manage_category'];
		$context['linktree'][] = array(
			'url' => $scripturl. '?action=mods;sa=managecat',
			'name' => $txt['mod_manage_category'],
		);

	}

	// Editing, get the info and show a nice editing form
	function ModEditCat() {
		global $txt, $context, $scripturl, $GetCats;

		if (isset($_REQUEST['catid']) && in_array($_REQUEST['catid'], array_keys($GetCats))){
			
			isAllowedTo('modperedit');
			$context['editcat']['current'] = $GetCats[$_GET['catid']];
			$context['sub_template'] = 'addcat';
			$context['page_title'] = $txt['Mod_editing_cat']. ' - '. $context['editcat']['current']['category_name'];
			$context['linktree'][] = array(
				'url' => $scripturl. '?action=mods;sa=edit;catid='.$_GET['catid'] ,
				'name' => $txt['Mod_edit']. ' '. $context['editcat']['current']['category_name'],
			);
		}
	}

	// So, it appears the ModifyModsSettings has finished with the edits already, fair enough, give her/him some kudos...
	function ModEditCat2() {

		if (isset($_GET['catid']) && in_array($_GET['catid'], array_keys($GetCats))){

			checkSession('post', '', true);
			isAllowedTo('modperedit');
			editCat2($_GET['catid']);
			redirectexit('action=mods;sa=managecat');

		}
	}

	// This time the ModifyModsSettings will try to delete a category, lets see if (s)he succeeded...
	function ModDeleteCat() {
		global $txt, $context, $GetCats;

		if (isset($_GET['catid']) && in_array($_GET['catid'], array_keys($GetCats))){

			isAllowedTo('modperedit');
			$context['deletecat']['current'] = $GetCats[$_GET['catid']];
			$context['sub_template'] = 'delete';  // No point in having yet another subtemplate for this.
			$context['page_title'] = $txt['Mod_deleting']. ' - '. $context['deletecat']['current']['category_name'];

		}
	}

	// OMG!  (s)he did it!!
	function ModDeleteCat2() {
		
		if (isset($_GET['catid']) && in_array($_GET['catid'], array_keys($GetCats))){

			checkSession('post', '', true);
			isAllowedTo('modperedit');
			deleteCat2($_GET['catid']);
			redirectexit('action=mods;sa=managecat');

		}
	}

	// Fill out the form and you will get yourself a nice brand new category ready to be used...
	function ModAddCat() {
		global $txt, $context;

		isAllowedTo('modperedit');
		$context['sub_template'] = 'addcat';
		$context['page_title'] = $txt['Mod_adding_cat'];
	}

	// Adding the category   ...finally!
	function ModAddCat2() {
		
		checkSession('post', '', true);
		isAllowedTo('modperedit');
		addCat2();
		redirectexit('action=mods;sa=managecat');

	}

	// Show the FAQs within a category
	function ModCategory() {

		global $context, $scripturl, $GetCats, $GetModsbyCat;

		if (isset($_GET['catid'])){

			isAllowedTo('modperview');
			
			$context['mod_category'] = GetModsbyCat($_GET['catid']);
			$context['sub_template'] = 'categoryshow';
			$context['page_title'] = $GetCats[$_GET['catid']]['category_name'];
			$context['linktree'][] = array(
				'url' => $scripturl. '?action=mods;sa=category;catid='.$_GET['catid'] ,
				'name' => $GetCats[$_GET['catid']]['category_name'],
			);
		}
	}

	// Last but not least, let's show a single mod...
	function ModShow() {
		global $context, $scripturl, $GetModsEdit;


		if (isset($_GET['modid']) && in_array($_GET['modid'], array_keys($GetModsEdit))){

			isAllowedTo('modperview');
			$context['show'] = GetModsAdmin($_GET['modid']);
			$context['sub_template'] = 'show';
			$context['page_title'] =  $GetModsEdit[$_GET['modid']]['name'];
			$context['linktree'][] = array(
				'url' => $scripturl. '?action=mods;sa=show;modid='.$_GET['modid'] ,
				'name' => $GetModsEdit[$_GET['modid']]['name'],
			);
		}
	}
	
	// Approve a mod and create a new topic for it
	function ModApprove(){
	
		global $GetModsEdit, $sourcedir, $modSettings;
		
		require_once($sourcedir . '/Subs-Post.php');
		
		if (isset($_GET['modid']) && in_array($_GET['modid'], array_keys($GetModsEdit))){

			isAllowedTo('modperedit');
			ApproveMod($_GET['modid']);

			$msgOptions = array(
				'body' => $GetModsEdit[$_GET['modid']]['description'],
				'id' => 0,
				'subject' => $GetModsEdit[$_GET['modid']]['name'],
				'approved' => 1
			);
			$topicOptions = array(
				'id' => 0,
				'board' => $modSettings['mod_board'],
				'mark_as_read' => '0',
				'lock_mode' => 0,
			);
			$posterOptions = array(
				'id' => $GetModsEdit[$_GET['modid']]['user'],
				'update_post_count' => '1',
			);
			createPost($msgOptions, $topicOptions, $posterOptions);
			
			
			redirectexit('action=mods');
		}
	}
	
	// Download!
	function ModDownload(){
	
		global $GetModsEdit, $context;
		
		if (isset($_GET['modid']) && in_array($_GET['modid'], array_keys($GetModsEdit))){
		
			$file_path = $GetModsEdit[$_GET['modid']]['file_path'];
			
			if(!file_exists($file_path)) {
				global $txt, $context;
			
				loadLanguage('Errors');

				header('HTTP/1.0 404 ' . $txt['attachment_not_found']);
				header('Content-Type: text/plain; charset=' . (empty($context['character_set']) ? 'ISO-8859-1' : $context['character_set']));

				// Nothing more to say really
				die('404 - ' . $txt['attachment_not_found']);
			}
			else {
			
				// Update the database
				DownMod($_GET['modid']);
				
				// Get the file's extension
				$ext = substr($file_path, strrpos($file_path, '.') + 1);	
				
				// Turn off gzip for IE browsers
				if(ini_get('zlib.output_compression'))
					ini_set('zlib.output_compression', 'Off');
				
				// clear anything that is in the buffers
				while (@ob_get_level() > 0)
					@ob_end_clean();
	  				
				// Set headers to force file download
				header('Pragma: ');
				if (!$context['browser']['is_gecko'])
					header('Content-Transfer-Encoding: binary');
				header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($file_path)) . ' GMT');
				header('Accept-Ranges: bytes');
				header('Content-Length: ' . filesize($file_path));
				header('Content-Encoding: none');
				header('Connection: close');
				header('ETag: ' . md5_file($file_path));
				header('Content-Type: application/octet-stream');
				header('Content-Disposition: attachment; filename="' . basename($file_path) .'"');

				// Read the file and write it to the output buffer
				readfile($file_path);
				
				// done so we need to end
				exit;
			}
		}	
	} 

	// All the template headers, css, js, etc
	function mod_headers(){

		global $context, $modSettings, $scripturl, $settings;

		// Does the ModifyModsSettings want to use javascript to show/hide the FAQs?
		if(!empty($modSettings['mod_use_javascript']) && $context['current_action'] == 'mods')
			$context['html_headers'] .= '
				<script language="JavaScript"  type="text/javascript">
				<!--
				function toggleDiv(divid){
					if(document.getElementById(divid).style.display == \'none\'){
						document.getElementById(divid).style.display = \'block\';
						document.pageLoading.TCallLabel(\'/\',\'restart_function\');
					}
					else{
						document.getElementById(divid).style.display = \'none\';
					}
				}
				//-->
				</script>';
				
		// CSS!
		if($context['current_action'] == 'mods')	
			$context['html_headers'] .=	'

<style type="text/css">

div.mod_des a {

color: rgb(128, 0, 128)!important;

}

.mod_list ul, .mod_categories ul, .mod_des ul {
list-style-image: none;
list-style-position: outside;
list-style-type: none;
padding-left:10px;
}


.mod_des {

border: 1px solid rgb(141, 182, 212);
background-color:#fff;
width: 180px;
margin:10px;
padding:10px;
float:left;
}



</style>';

	}

?>