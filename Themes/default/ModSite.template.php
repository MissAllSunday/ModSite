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

	/* Show a nice message if no mods are avaliable */
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

	modsite_header();

	/* Sidebar */
	modsite_sideBar();

	/* The main div */
	echo '
	<div class="floatright nopadding" style="width:80%;">';

	/* A nice form for adding a new cat */
	// if ($context['faq']['object']->permissions('add') == true)Q
		echo '
		<span class="clear upperframe">
			<span></span>
		</span>
		<div class="roundframe rfix">
			<div class="innerframe">
				<form action="', $scripturl, '?action='. modsite::$name .';sa=add2" method="post" target="_self">
					<dl id="post_header">
						<dt>
							<span id="caption_subject">', $txt['modSite_edit_name'] ,'</span>
						</dt>
						<dd>
							<input type="hidden" id="', $context['session_var'], '" name="', $context['session_var'], '" value="', $context['session_id'], '" />
							<input type="text" name="title" size="55" tabindex="1" maxlength="255" value="" class="input_text" /> <input type="submit" name="send" class="sbtn" value="', $txt['modSite_edit_add'] ,'" />
						</dd>
					</dl>
				</form>
			</div>
		</div>
		<span class="clear lowerframe">
			<span></span>
		</span><br />';

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