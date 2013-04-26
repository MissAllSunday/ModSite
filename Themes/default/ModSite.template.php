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

function template_ModSite_main()
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
	if ($context['Modsite']['object']->permissions('add') == true)
		echo '
			<div id="confirm_buttons">
				<form action="', $scripturl, '?action=modsite;sa=add" method="post" target="_self">
					<input type="submit" name="send" class="input_text" value="', $txt['send'] ,'" />
				</form>
			</div>';

	echo '
		</div>';
}

function template_ModSite_add()
{
	global $context, $settings, $options, $scripturl, $modSettings, $txt;

	// The main containing header.
	echo '
		<form action="', $scripturl, '?action=mods;sa=add2" method="post" accept-charset="', $context['character_set'], '" name="creator" id="creator" enctype="multipart/form-data">
			<h3 class="catbg">
				<span class="left"></span>
				<img src="', $settings['images_url'], '/icons/profile_sm.gif" alt="" class="icon" />
				', $context['page_desc'] , '
			</h3>
			<p class="windowbg description">
				', $context['page_desc'] , '
			</p>
			<div class="windowbg2">
				<span class="topslice"><span></span></span>
					<div class="content">';

		/* Print the form */
		echo $context['ModSite']['Form'];

	echo '<input type="hidden" id="', $context['session_var'], '" name="', $context['session_var'], '" value="', $context['session_id'], '" />
						<input type="submit" name="send" class="sbtn" value="Create" />
					</div>
				<span class="botslice"><span></span></span>
			</div>
			<br />
		</form>';
}

function modsite_header()
{

}

function modsite_sideBar()
{

}