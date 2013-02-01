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
</span>
<br />';
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