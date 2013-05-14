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
	global $txt, $context, $scripturl, $modSettings, $settings;

	modsite_header();

	/* The main div */
	echo '
	<div class="floatright nopadding" style="width:99%;">';

	/* There is no mods or this thing is disable :( */
	if (empty($context['modSite']['all']) || empty($modSettings['modSite_enable']))
		echo '
		<div class="cat_bar">
			<h3 class="catbg">
				<span class="ie6_header floatleft">', $txt['modSite_error_message'] ,'</span>
			</h3>
		</div>
		<div class="windowbg">
			<span class="topslice"><span></span></span>
			<div class="content">
			', $txt['modSite_error_enable'] ,'
			</div>
			<span class="botslice"><span></span></span>
		</div>';

	/* Show the goodies */
	else
		foreach ($context['modSite']['all'] as $mod)
		{
			echo '
			<div class="cat_bar">
				<h3 class="catbg">
					<span class="ie6_header floatleft">', $mod['info']['publicName'] ,'</span>
					<span class="floatright"><img src="', $settings['default_theme_url'] ,'/images/disk.png" style="vertical-align:middle" /> ', $txt['modSite_ui_download'] ,'</span>
				</h3>
			</div>
			<div class="windowbg">
				<span class="topslice"><span></span></span>
				<div class="content">';

			/* The inner sidebar for each mod */
			if (!empty($mod['info']))
			{
				echo '
					<div class="description" style="width:25%; float:left; margin:0 1em 1em 0; display: block;">
						<ul class="reset modsite_info">
							<li class="li_cat"><a href="', $scripturl ,'?action=modsite;sa=categories;mid=', $mod['category']['key'] ,'" title="', $txt['modSite_ui_cat'] ,'">', $mod['category']['name'] ,'</a></li>
							<li class="li_support"><a href="', $scripturl ,'?topic=', $mod['info']['supportID'] ,'" title="', $txt['modSite_ui_support'] ,'">', $txt['modSite_ui_support'] ,'</a></li>
							<li class="li_supported_versions">', $txt['modSite_ui_smf_versions'] . $mod['info']['versionSMF'] ,'</li>
							<li class="li_license">', $txt['modSite_ui_license'] ,'<a href="', $mod['info']['license']['link'] ,'">', $mod['info']['license']['name'] ,'</a></li>';


			/* These values depend on github so lets check em first */
				if (!empty($mod['info']['html_url']))
					echo '
							<li class="li_github"><a href="', $mod['info']['html_url'] ,'" title="', $txt['modSite_ui_github'] ,'">', $txt['modSite_ui_github'] ,'</a></li>';

			/* End the list */
				echo '
						</ul>
					</div>';
			}

			/* Description */
			if (!empty($mod['info']['desc']))
				echo '
					<div style="padding:10px;">', $mod['info']['desc'] ,'</div>';

				/* End block */
			echo
				'<div class="clear"></div>
				</div>
				<span class="botslice"><span></span></span>
			</div>';
		}

	/* End of main div */
	echo
	'</div>';

	echo '
	<div class="clear">';

	/* Button for adding a new entry */
	if ($context['modSite']['object']->permissions('add') == true)
		echo '
			<div id="confirm_buttons">
				<form action="', $scripturl, '?action=modsite;sa=add" method="post" target="_self">
					<input type="submit" name="send" class="input_text" value="Create a new entry" />
				</form>
			</div>';

	echo '
		</div>
		<br />';
}

function template_modSite_add()
{
	global $context, $settings, $options, $scripturl, $modSettings, $txt;

	modsite_header();

	/* The main div */
	echo '
	<div class="floatright nopadding" style="width:99%;">';

	/* You didn't fill the field... */
	if (isset($_GET['missing']))
		echo '
	<div class="errorbox">
<p class="alert">
	<h3>', $txt['modSite_error_message'] ,'</h3>
	<p>
		', $_GET['missing'] . $txt['modSite_error_empty_field'] ,'
	</p>
</div>';
	/* A nice form for adding a new cat */
	// if ($context['modsite']['object']->permissions('add') == true)
		echo '
		<span class="clear upperframe">
			<span></span>
		</span>
		<div class="roundframe rfix">
			<div class="innerframe">
				<form action="', $scripturl, '?action='. modsite::$name .';sa=add2', !empty($context['modSite']['edit']) ? ';edit;mid='. $context['modSite']['id'] : '' ,'" method="post" target="_self">
					<dl id="post_header">
						<dt>
							<span id="caption_subject">', $txt['modSite_edit_name'] ,'</span>
						</dt>
						<dd>
							<input type="hidden" id="', $context['session_var'], '" name="', $context['session_var'], '" value="', $context['session_id'], '" />
							<input type="text" name="name" size="55" tabindex="1" maxlength="255" value="', !empty($context['modSite']['edit']) ? $context['modSite']['edit']['name'] : '' ,'" class="input_text" /> <input type="submit" name="send" class="sbtn" value="', !empty($context['modSite']['edit']) ? $txt['modSite_edit_edit'] : $txt['modSite_edit_add'] ,'" />
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

function template_modSite_success()
{
	global $txt, $context, $scripturl, $modSettings;

	modsite_header();

	/* The main div */
	echo '
	<div class="floatright" style="width:99%;">';

	/* No direct access */
	if (!empty($context['modSite']['pin']))
		echo '
		<div class="cat_bar">
			<h3 class="catbg">
				<span class="ie6_header floatleft">', $context['modSite']['message'] ,'</span>
			</h3>
		</div>

		<div class="windowbg nopadding">
			<span class="topslice"><span></span></span>
			<div class="content">
				', $context['modSite']['message'] ,'<p />
			</div>
			<span class="botslice"><span></span></span>
		</div>';

	echo '
	</div>
	<div class="clear"></div>';
}

function modsite_header()
{

}
