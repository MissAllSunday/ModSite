<?php


	function template_main(){

		/* mod_sidebar(); */

		// No FAQs ? :(
			echo '
				<span class="clear upperframe">
					<span></span>
				</span>
				<div class="roundframe rfix">
					<div class="innerframe">
						<div class="content">
							LOL
						</div>
					</div>
				</div>
				<span class="lowerframe">
					<span></span>
				</span><br />';
	}

	function template_delete(){

		global $txt, $context, $scripturl;

		if(empty($context['delete']['current']) && !empty($context['deletecat']['current'])){

			$sub_delete = 'deletecat2';
			$modid = 'catid='.$context['deletecat']['current']['category_id'];
			$name_delete = $context['deletecat']['current']['category_name'];
		}

		elseif(!empty($context['delete']['current']) && empty($context['deletecat']['current'])){

			$sub_delete = 'delete2';
			$modid = 'modid='.$context['delete']['current']['id'];
			$name_delete = $context['delete']['current']['name'];
		}

		echo '
			<div class="cat_bar">
				<h3 class="catbg">
					<span class="ie6_header floatleft">
						',$txt['Mod_delete'],'
					</span>
				</h3>
			</div>
			<span class="clear upperframe">
				<span></span>
			</span>
			<div class="roundframe rfix">
				<div class="innerframe">
					<div class="content">
						 ',$txt['Mod_delete_con'],' ',$name_delete,'
					</div>
					<div id="confirm_buttons">
						<form action="', $scripturl, '?action=mods;sa=',$sub_delete,';',$modid,'" method="post" target="_self">
							<input type="hidden" id="', $context['session_var'], '" name="', $context['session_var'], '" value="', $context['session_id'], '" />
							<input type="submit" name="send" class="sbtn" value="',$txt['Mod_delete_send'],'" />
						</form>
					</div>
				</div>
			</div>
			<span class="lowerframe">
				<span></span>
			</span><br />';

	}

	function template_add(){

	global $context, $scripturl, $txt;

		$mod_edit = 0;

		if(!empty($context['edit']['current'])){
			$mod_edit = 1;
			$mod_edit_id = 'edit2;modid='.$context['edit']['current']['id'].'';
		}

		echo '
		<form action="', $scripturl, '?action=mods;sa=',$mod_edit == 1 ? $mod_edit_id : 'add2','" method="post" target="_self" id="postmodify" class="flow_hidden" enctype="multipart/form-data" target="_self">
			<div class="cat_bar">
				<h3 class="catbg">',($mod_edit == 1 ?  $txt['Mod_editing'] : $txt['Mod_adding']),'</h3>
			</div>
			<span class="clear upperframe">
					<span></span>
				</span>
				<div class="roundframe rfix">
					<div class="innerframe">
					<dl id="post_header">
						<dt>
							<span id="caption_subject">',$txt['Mod_name_edit'],'</span>
						</dt>
						<dd>
							<input type="text" name="name" size="45" tabindex="1" maxlength="55" value="',($mod_edit == 1 ? $context['edit']['current']['name'] : ''),'" class="input_text" />
						</dd>

						<dt>
							<span id="caption_demo">',$txt['Mod_demo_edit'],'</span>
						</dt>
						<dd>
							<input type="text" name="demo" size="70" tabindex="1" maxlength="70" value="',($mod_edit == 1 ? $context['edit']['current']['demo'] : ''),'" class="input_text" />
						</dd>
						<dt>
							<span id="caption_smf_download">',$txt['Mod_smf_download_edit'],'</span>
						</dt>
						<dd>
							<input type="text" name="smf_download" size="70" tabindex="1" maxlength="70" value="',($mod_edit == 1 ? $context['edit']['current']['smf_download'] : ''),'" class="input_text" />
						</dd>
						<dt>
							<span id="caption_smf_version">',$txt['Mod_smf_version_edit'],'</span>
						</dt>
						<dd>
							<input type="text" name="smf_version" size="15" tabindex="1" maxlength="15" value="',($mod_edit == 1 ? $context['edit']['current']['smf_version'] : ''),'" class="input_text" />
						</dd>


						<dt>
							<span id="caption_version">',$txt['Mod_version_edit'],'</span>
						</dt>
						<dd>
							<input type="text" name="version" size="10" tabindex="1" maxlength="10" value="',($mod_edit == 1 ? $context['edit']['current']['version'] : ''),'" class="input_text" />
						</dd>


						<dt>
							<span id="caption_id_project">',$txt['Mod_id_project_edit'],'</span>
						</dt>
						<dd>
							<input type="text" name="id_project" size="10" tabindex="1" maxlength="10" value="',($mod_edit == 1 ? $context['edit']['current']['id_project'] : ''),'" class="input_text" />
						</dd>



						<dt>
							<span id="caption_category">',$txt['mod_edit_category'],'</span>
						</dt>
						<dd>';

						if($context['mod_getcats']){

							echo'<select name="category_id">';
							foreach($context['mod_getcats'] as $cats)
								echo '<option value="',$cats['category_id'],'">',$cats['category_name'],'</option>';

								echo '</select>';
						}

						else {

						echo '<div class="mod_warning">
								',$txt['Mod_no_cat_admin'],'
								</div>';

						}

					echo'</dd></dl>';

						if ($context['show_bbc'])
							echo '<div id="bbcBox_message"></div>';

						if (!empty($context['smileys']['postform']) || !empty($context['smileys']['popup']))
							echo '<div id="smileyBox_message"></div>';

						echo template_control_richedit($context['post_box_name'], 'smileyBox_message', 'bbcBox_message');


					// lets show the upload thingy
						echo '
						<p /><input id="myfile" name="myfile" type="file" size="30" />';

						echo '
				<div id="confirm_buttons">
					<input type="hidden" id="', $context['session_var'], '" name="', $context['session_var'], '" value="', $context['session_id'], '" />
					<input type="submit" name="send" class="sbtn" value="',($mod_edit == 1 ? $txt['Mod_edit_send'] : $txt['Mod_add_send']),'" />
				</div>
				</div>
				</div>
				<span class="lowerframe">
					<span></span>
				</span><br />
		</form>';

	}

	function template_show_edit(){

		global $context, $txt, $scripturl, $GetCats;

		echo '<div class="cat_bar">
				<h3 class="catbg">',$txt['mod_manage'],'</h3>
			</div>
			<div class="windowbg description">
				',$txt['mod_manage_desc'],'
			</div>';

		// No FAQs ? :(
		if (empty($context['GetModsEdit']))
			echo '
				<span class="clear upperframe">
					<span></span>
				</span>
				<div class="roundframe rfix">
					<div class="innerframe">
						<div class="content">
							',$txt['Mod_no_mod'],'
						</div>
					</div>
				</div>
				<span class="lowerframe">
					<span></span>
				</span><br />';

		else{

		echo '
			<table class="table_grid" cellspacing="0" width="100%">
				<thead>
					<tr class="catbg">
						<th scope="col" class="first_th">',$txt['mod_edit_id'],'</th>
						<th scope="col">',$txt['mod_edit_name'],'</th>
						<th scope="col">',$txt['mod_edit_category'],'</th>
						<th scope="col">',$txt['mod_smf_download'],'</th>
						<th scope="col">',$txt['mod_edit_last_edit_by'],'</th>
						<th scope="col">',$txt['mod_edit_edit'],'</th>
						<th scope="col">',$txt['mod_approve_edit'],'</th>
						<th scope="col" class="last_th">',$txt['mod_edit_delete'],'</th>
					</tr>
				</thead>
			<tdescription>';

			foreach($context['GetModsEdit'] as $mod_edit){

				echo '
						<tr class="windowbg" style="text-align: center">
							<td>
							',$mod_edit['id'],'
							</td>
							<td>
							',$mod_edit['name'],'
							</td>
							<td>
							',$mod_edit['category']['name'],'
							</td>
							<td>
							',$mod_edit['file']['descargas'],'
							</td>
							<td>
							',$mod_edit['user']['link'],'
							</td>
							<td>
							<a href="',$scripturl,'?action=mods;sa=edit;modid=',$mod_edit['id'],'">',$txt['mod_edit_edit'],'</a>
							</td>
							<td>
							',($mod_edit['approved'] == 0 ? '<a href="'.$scripturl.'?action=mods;sa=approve;modid='.$mod_edit['id'].'">'.$txt['mod_approve_edit'].'</a>' : 'approved'),'
							</td>
							<td>
							<a href="',$scripturl,'?action=mods;sa=delete;modid=',$mod_edit['id'],'">',$txt['mod_edit_delete'],'</a>
							</td>
						</tr>';
			}

			echo '</tdescription>
			</table><br />';
		}

		// Add a  new FAQ
		if($context['modperedit'])
		echo '
			<div id="confirm_buttons">
				<form action="', $scripturl, '?action=mods;sa=add" method="post" target="_self">
					<input type="submit" name="send" class="sbtn" value="',$txt['Mod_add_send'],'" />
				</form>
			</div>';
	}

	function template_show_edit_cat(){

		global $context, $txt, $scripturl;

		echo '<div class="cat_bar">
				<h3 class="catbg">',$txt['mod_manage_category'],'</h3>
			</div>
			<div class="windowbg description">
				',$txt['mod_manage_category_desc'],'
			</div>';

		// No Cats ? :(
		if (empty($context['GetCats']))
			echo '
				<span class="clear upperframe">
					<span></span>
				</span>
				<div class="roundframe rfix">
					<div class="innerframe">
						<div class="content">
							',$txt['Mod_no_cat_admin'],'
						</div>
					</div>
				</div>
				<span class="lowerframe">
					<span></span>
				</span><br />';

		else{

		echo '
			<table class="table_grid" cellspacing="0" width="100%">
				<thead>
					<tr class="catbg">
						<th scope="col" class="first_th">',$txt['mod_edit_id'],'</th>
						<th scope="col">',$txt['mod_edit_name'],'</th>
						<th scope="col">',$txt['mod_edit_edit'],'</th>
						<th scope="col" class="last_th">',$txt['mod_edit_delete'],'</th>
					</tr>
				</thead>
			<tdescription>';

			foreach($context['mod_getcats'] as $cat_edit){

				echo '
						<tr class="windowbg" style="text-align: center">
							<td>
							',$cat_edit['category_id'],'
							</td>
							<td>
							',$cat_edit['category_name'],'
							</td>
							<td>
							<a href="',$scripturl,'?action=mods;sa=editcat;catid=',$cat_edit['category_id'],'">',$txt['mod_edit_edit'],'</a>
							</td>
							<td>
							<a href="',$scripturl,'?action=mods;sa=deletecat;catid=',$cat_edit['category_id'],'">',$txt['mod_edit_delete'],'</a>
							</td>
						</tr>';
			}

			echo '</tdescription>
			</table><br />';
		}

		// Add a  new Category
		if($context['modperedit'])
		echo '
			<div id="confirm_buttons">
				<form action="', $scripturl, '?action=mods;sa=addcat" method="post" target="_self">
					<input type="submit" name="send" class="sbtn" value="',$txt['Mod_addcat_send'],'" />
				</form>
			</div>';

	}

	function template_addcat(){

		global $scripturl, $txt, $context;

		$edit_catid = '';

		if(!empty($context['editcat']['current']))
			$edit_catid = ';catid='.$context['editcat']['current']['category_id'];

				echo '
		<form action="', $scripturl, '?action=mods;sa=',(empty($context['editcat']['current']) ? 'addcat2' : 'editcat2'),'',$edit_catid,'" method="post" target="_self" id="postmodify" class="flow_hidden">
			<div class="cat_bar">
				<h3 class="catbg">',(empty($context['editcat']['current']) ? $txt['Mod_adding_cat'] : $txt['Mod_editing_cat']),'</h3>
			</div>
			<span class="clear upperframe">
					<span></span>
				</span>
				<div class="roundframe rfix">
					<div class="innerframe">
					<dl id="post_header">
						<dt>
							<span id="caption_category">',$txt['mod_edit_name'],'</span>
						</dt>
						<dd>
							<input type="text" name="category_name" size="45" tabindex="1" maxlength="55" value="',(empty($context['editcat']['current']) ? '' : $context['editcat']['current']['category_name']),'" class="input_text" />
						</dd>
					</dl>
				<div id="confirm_buttons">
					<input type="hidden" id="', $context['session_var'], '" name="', $context['session_var'], '" value="', $context['session_id'], '" />
					<input type="submit" name="send" class="sbtn" value="',(empty($context['editcat']['current']) ? $txt['Mod_addcat_send'] : $txt['Mod_editcat_send']),'" />
				</div>
				</div>
				</div>
				<span class="lowerframe">
					<span></span>
				</span><br />
		</form>';


	}

	function template_show(){

		global $txt, $context, $scripturl, $modSettings;

		mod_sidebar();

		echo '<div class="mods">';

		// No FAQs ? :(
		if (empty($context['show']))
			echo '
				<span class="clear upperframe">
					<span></span>
				</span>
				<div class="roundframe rfix">
					<div class="innerframe">
						<div class="content">
							',$txt['Mod_no_mod'],'
						</div>
					</div>
				</div>
				<span class="lowerframe">
					<span></span>
				</span><br />';

		else {

			//So... we need to define some variables first...
			$show_javascript = '';
			$show_display = '';

			// Lets show the FAQs...
			foreach($context['show'] as $show){

				if (!empty($showSettings['mod_use_javascript'])){
					$show_javascript = 'onmousedown="toggleDiv(\'content'.$show['id'].'\');"';
					$show_display = 'style="display:none;"';
				}

				if($show['approved'] || $context['modperedit']){

					echo '
				<div class="topleftpost">
					<div class="topright">
						<div class="bottomleft">
							<div class="bottomright">
								<div class="hpost">
									<h2 class="center"><a href="',(!empty($showSettings['mod_use_javascript']) ? 'javascript:void(0)' : $scripturl.'?action=mods;sa=show;modid='.$show['id']),'" ',$show_javascript,' > ',$show['name'],'</a>';

								echo '<span style="float:right;">
										<a href="',$scripturl,'?action=mods;sa=download;modid=',$show['id'],'">',$txt['mod_download'],'</a>';

									if($context['modperedit'])
									echo' |<a href="',$scripturl,'?action=mods;sa=edit;modid=',$show['id'],'">',$txt['mod_edit_edit'],'</a> | <a href="',$scripturl,'?action=mods;sa=delete;modid=',$show['id'],'">',$txt['mod_edit_delete'],'</a> ',($show['approved'] == 0 ? ' | <a href="'.$scripturl.'?action=mods;sa=approve;modid='.$show['id']
										.'">'.$txt['mod_approve_edit'].'</a>' : ''),'';

						echo '</span></h2>
									<div class="headerlist">
										<span class="editlink"> ', $show['user']['link'], '</span>
										<div class="entry content',$show['id'],'" id="content',$show['id'],'" ',$show_display,'>
											<div class="mod_des">
												<ul>
													<li>',$show['category']['smf_download'],'</li>
													<li>',$txt['mod_edit_category'],': ',$show['category']['link'],' </li>
													<li>',$txt['mod_demo'],': ',$show['mod']['demo_link'],'</li>
													<li>',$txt['Mod_version_edit'],' : ',$show['mod']['version'],'</li>
													<li>',$txt['Mod_for_smf_version'],' : ',$show['mod']['smf_version'],'</li>
													<li>',$txt['Mod_support_topic'],' : ',$show['mod']['topic_url'],'</li>
													<li>',$txt['mod_smf_download'],' : ',$show['file']['descargas'],'</li>
													<li>',$txt['mod_file_type'],' : ',$show['file']['type'],'</li>
													<li>',$txt['mod_file_size'],' : ',$show['file']['size'],'',$txt['mod_file_kb'],'</li>
													<li>',$txt['mod_issues'],':
														<ul>';
														if(!empty($show['tracker']))
															foreach($show['tracker'] as $issue)
																echo '<li class="issue_', $issue['status']['name'], '">',$issue['tracker']['image'],' ', !empty($issue['category']['link']) ? '[' . $issue['category']['link'] . '] ' : '', '<a href="',$issue['href'],'" title="', $issue['status']['name'], '">',$issue['name'],'</a></li>';

													echo'</ul></li>
												</ul>
											</div>
											',parse_bbc($show['description']),'
										</div>
										<div class="clear"></div>
											<p class="tags">
											<g:plusone size="small" href="', $show['mod']['href'], '"></g:plusone>
											</p>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>';
				}
			}
		}

		echo'</div><div class="clear"></div>';

		if(!empty($modSettings['mod_care']))
			ModCare();


	}

	function template_categoryshow(){

		global $txt, $context, $scripturl, $modSettings;

		mod_sidebar();

		echo '<div class="mods">';

		// No FAQs ? :(
		if (empty($context['mod_category']))
			echo '
				<span class="clear upperframe">
					<span></span>
				</span>
				<div class="roundframe rfix">
					<div class="innerframe">
						<div class="content">
							',$txt['Mod_no_mod'],'
						</div>
					</div>
				</div>
				<span class="lowerframe">
					<span></span>
				</span><br />';

		else {

			//So... we need to define some variables first...
			$mod_category_javascript = '';
			$mod_category_display = '';

			// Lets show the FAQs...
			foreach($context['mod_category'] as $mod_category){

				if (!empty($mod_categorySettings['mod_use_javascript'])){
					$mod_category_javascript = 'onmousedown="toggleDiv(\'content'.$mod_category['id'].'\');"';
					$mod_category_display = 'style="display:none;"';
				}

				if($mod_category['approved'] || $context['modperedit']){

					echo '
				<div class="topleftpost">
					<div class="topright">
						<div class="bottomleft">
							<div class="bottomright">
								<div class="hpost">
									<h2 class="center"><a href="',(!empty($mod_categorySettings['mod_use_javascript']) ? 'javascript:void(0)' : $scripturl.'?action=mods;sa=show;modid='.$mod_category['id']),'" ',$mod_category_javascript,' > ',$mod_category['name'],'</a>';

								echo '<span style="float:right;">
										<a href="',$scripturl,'?action=mods;sa=download;modid=',$mod_category['id'],'">',$txt['mod_download'],'</a>';

									if($context['modperedit'])
									echo' |<a href="',$scripturl,'?action=mods;sa=edit;modid=',$mod_category['id'],'">',$txt['mod_edit_edit'],'</a> | <a href="',$scripturl,'?action=mods;sa=delete;modid=',$mod_category['id'],'">',$txt['mod_edit_delete'],'</a> ',($mod_category['approved'] == 0 ? ' | <a href="'.$scripturl.'?action=mods;sa=approve;modid='.$mod_category['id']
										.'">'.$txt['mod_approve_edit'].'</a>' : ''),'';

						echo '</span></h2>
									<div class="headerlist">
										<span class="editlink"> ', $mod_category['user']['link'], '</span>
										<div class="entry content',$mod_category['id'],'" id="content',$mod_category['id'],'" ',$mod_category_display,'>
											<div class="mod_des">
												<ul>
													<li>',$mod_category['category']['smf_download'],'</li>
													<li>',$txt['mod_edit_category'],': ',$mod_category['category']['link'],' </li>
													<li>',$txt['mod_demo'],': ',$mod_category['mod']['demo_link'],'</li>
													<li>',$txt['Mod_version_edit'],' : ',$mod_category['mod']['version'],'</li>
													<li>',$txt['Mod_for_smf_version'],' : ',$mod_category['mod']['smf_version'],'</li>
													<li>',$txt['Mod_support_topic'],' : ',$mod_category['mod']['topic_url'],'</li>
													<li>',$txt['mod_smf_download'],' : ',$mod_category['file']['descargas'],'</li>
													<li>',$txt['mod_file_type'],' : ',$mod_category['file']['type'],'</li>
													<li>',$txt['mod_file_size'],' : ',$mod_category['file']['size'],'',$txt['mod_file_kb'],'</li>
													<li>',$txt['mod_issues'],':
														<ul>';
														if(!empty($mod_category['tracker']))
															foreach($mod_category['tracker'] as $issue)
																echo '<li class="issue_', $issue['status']['name'], '">',$issue['tracker']['image'],' ', !empty($issue['category']['link']) ? '[' . $issue['category']['link'] . '] ' : '', '<a href="',$issue['href'],'" title="', $issue['status']['name'], '">',$issue['name'],'</a></li>';

													echo'</ul></li>
												</ul>
											</div>
											',parse_bbc($mod_category['description']),'
										</div>
										<div class="clear"></div>
											<p class="tags">
											<g:plusone size="small" href="', $mod_category['mod']['href'], '"></g:plusone>
											</p>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>';
				}
			}
		}

		echo'</div><div class="clear"></div>';

	}

	function mod_sidebar(){

		global $context, $scripturl, $modSettings, $txt;

			echo '<div id="sidebar">';

			echo'<div class="sidebar-top">
					<div class="sidebar-bottom">
						<h2 class="widgettitle">title</h2>';

			if(empty($context['mod_getcats']))
				echo '<div class="mod_categories mod_warning">
						no categories
					</div>';
			else{

				echo '<ul>';

				/* foreach($context['mod_getcats'] as $category) */
					echo '<li>lol</li>';

				echo'</ul></div></div>';
			}

		// side bar end
		echo '</div>';
	}