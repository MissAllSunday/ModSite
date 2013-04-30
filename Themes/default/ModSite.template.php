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

function template_modSite_main()
{
	global $txt, $context, $scripturl, $modSettings;

	modsite_header();

	/* Sidebar */
	modsite_sideBar();

	/* Show a nice message if no FAQs are avaliable */
	echo '
	<div class="floatright nopadding" style="width:80%;">
		<div class="cat_bar">
			<h3 class="catbg">
				<span class="ie6_header floatleft">Some title here</span>
			</h3>
		</div>
		<div class="windowbg">
			<span class="topslice"><span></span></span>
			<div class="content">
			some text here
			</div>
			<span class="botslice"><span></span></span>
		</div>
	</div>';

	echo '
		<div class="clear">';

	/* Button for adding a new entry */
	// if ($context['Modsite']['object']->permissions('add') == true)
		echo '
			<div id="confirm_buttons">
				<form action="', $scripturl, '?action=modsite;sa=add" method="post" target="_self">
					<input type="submit" name="send" class="input_text" value="', $txt['send'] ,'" />
				</form>
			</div>';

	echo '
		</div>';
}

function template_modSite_add()
{
	global $context, $settings, $options, $scripturl, $modSettings, $txt;

	/* The main div */
	echo '
	<div class="floatright nopadding" style="width:80%">';

		echo '
		<form action="', $scripturl, '?action=modsite;sa=add2;', (!empty($context['modSite']['edit']) ? 'fid='.  $context['modSite']['edit']['id'] .';edit' : ''),'" method="post" target="_self" id="postmodify" class="flow_hidden" onsubmit="submitonce(this);smc_saveEntities(\'postmodify\', [\'name\', \'description\']);" >
			<div class="cat_bar">
				<h3 class="catbg">
					',(!empty($context['modSite']['edit']) ?  $txt['modSite_edit_editing'] .' - '. $context['modSite']['edit']['name'] : $txt['modSite_edit_creating']),'
				</h3>
			</div>
			<span class="clear upperframe">
				<span></span>
			</span>
			<div class="roundframe rfix">
				<div class="innerframe">
					<dl id="post_header">';

			/* Name */
			echo '
						<dt>
							<span id="caption_subject">', $txt['modSite_edit_name'] ,'</span>
						</dt>
						<dd>
							<input type="text" name="name" size="55" tabindex="1" maxlength="255" value="', (!empty($context['modSite']['edit']) ? $context['modSite']['edit']['name'] : '') ,'" class="input_text" />
						</dd>';

			/* File */
			echo '
						<dt>
							<span id="caption_subject">', $txt['modSite_edit_file'] ,'</span>
						</dt>
						<dd>
							<input type="text" name="file" size="55" tabindex="1" maxlength="255" value="', (!empty($context['modSite']['edit']) ? $context['modSite']['edit']['file'] : '') ,'" class="input_text" />
						</dd>';

			/* Demo link */
			echo '
						<dt>
							<span id="caption_subject">', $txt['modSite_edit_demo'] ,'</span>
						</dt>
						<dd>
							<input type="text" name="demo" size="55" tabindex="1" maxlength="255" value="', (!empty($context['modSite']['edit']) ? $context['modSite']['edit']['demo'] : '') ,'" class="input_text" />
						</dd>';

			/* Mod version */
			echo '
						<dt>
							<span id="caption_subject">', $txt['modSite_edit_version'] ,'</span>
						</dt>
						<dd>
							<input type="text" name="version" size="55" tabindex="1" maxlength="255" value="', (!empty($context['modSite']['edit']) ? $context['modSite']['edit']['version'] : '') ,'" class="input_text" />
						</dd>';

			/* ID topic for support */
			echo '
						<dt>
							<span id="caption_subject">', $txt['modSite_edit_topic'] ,'</span>
						</dt>
						<dd>
							<input type="text" name="topic" size="55" tabindex="1" maxlength="255" value="', (!empty($context['modSite']['edit']) ? $context['modSite']['edit']['topic'] : '') ,'" class="input_text" />
						</dd>';

			/* SMF version */
			echo '
						<dt>
							<span id="caption_subject">', $txt['modSite_edit_smf'] ,'</span>
						</dt>
						<dd>
							<input type="text" name="smf" size="55" tabindex="1" maxlength="255" value="', (!empty($context['modSite']['edit']) ? $context['modSite']['edit']['smf_version'] : '') ,'" class="input_text" />
						</dd>';

			/* SMF download */
			echo '
						<dt>
							<span id="caption_subject">', $txt['modSite_edit_smfd'] ,'</span>
						</dt>
						<dd>
							<input type="text" name="smfd" size="55" tabindex="1" maxlength="255" value="', (!empty($context['modSite']['edit']) ? $context['modSite']['edit']['smf_download'] : '') ,'" class="input_text" />
						</dd>';

			/* Github repo name */
			echo '
						<dt>
							<span id="caption_subject">', $txt['modSite_edit_github'] ,'</span>
						</dt>
						<dd>
							<input type="text" name="github" size="55" tabindex="1" maxlength="255" value="', (!empty($context['modSite']['edit']) ? $context['modSite']['edit']['github'] : '') ,'" class="input_text" />
						</dd>';

			/* Category select field */
			echo'
						<dt>
							<span id="caption_subject">', $txt['modSite_edit_category'] ,'</span>
						</dt>
						<dd>';

			/* Show the category select field */
			if (!empty($context['modSite']['cats']))
			{
				echo '
							<select name="id_category">';

				foreach($context['modSite']['cats'] as $cats)
					echo '
								<option value="', $cats['id'] ,'" ', (isset($context['modSite']['edit']['cat']['id']) && $cats['id'] == $context['modSite']['edit']['cat']['id'] ? 'selected="selected"' : '') ,'>', $cats['name'] ,'</option>';

				echo '
							</select>';
			}

			else
				echo '
							<div class="faqmod_warning">
								', $txt['modSite_edit_category_no'] ,'
							</div>';

			echo'
						</dd>
					</dl>';

			if ($context['show_bbc'])
				echo '
						<div id="bbcBox_message"></div>';

			if (!empty($context['smileys']['postform']) || !empty($context['smileys']['popup']))
				echo '
						<div id="smileyBox_message"></div>';

			echo template_control_richedit($context['post_box_name'], 'smileyBox_message', 'bbcBox_message');

			echo '
						<div id="confirm_buttons">
							<input type="hidden" id="', $context['session_var'], '" name="', $context['session_var'], '" value="', $context['session_id'], '" />
							<input type="submit" name="send" class="sbtn" value="',(!empty($context['modSite']['edit']) || !empty($_REQUEST['previewEdit']) ? $txt['modSite_edit_edit'] : $txt['modSite_edit_add']),'" />
							<input type="submit" name="preview" class="sbtn" value="', $txt['preview'], '" />
						</div>
					</div>
			</div>
			<span class="lowerframe">
				<span></span>
			</span><br />
		</form>';

	echo '
	</div>
	<div class="clear"></div>';
}

function modsite_header()
{

}

function modsite_sideBar()
{

}