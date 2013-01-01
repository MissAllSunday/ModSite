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

if (!defined('SMF'))
	die('No direct access...');

abstract class ModSitePage
{
	protected static $_modsite;

	public static function function call()
	{
		/* Load stuff */
		loadtemplate(ModSite::$name);

		self::$_modSite = new Modsite();

		$subActions = array(
			'post',
			'single',
			'download'
		);

		/* Does the subaction even exist? */
		if (in_array(self::_modSite->queryString()->getRawValue('sa'), array_keys($subActions)))
			$subActions[self::_modSite->queryString()->getRawValue('sa')]();

		/* No?  redirect them to the main page */
		else
			self::main();
	}

	public static function main()
	{
		global $context, $scripturl;

		/* meh... I haz all tha powerz */
		if (!$context['user']['is_admin'])
			redirectexit();

		/* Set all the page stuff */
		$context['sub_template'] = ModSite::$name.'_main';
		$context['page_title'] = self::_modSite->tools->getText('title_main');
		$context['canonical_url'] = $scripturl . '?action='. ModSite::$name;

		/* Set the pagination stuff */

		/* Get all the mods */

	}

	public static function post()
	{
		global $context, $scripturl;

		/* meh... I haz all tha powerz */
		if (!$context['user']['is_admin'])
			redirectexit();

		/* Set all the page stuff */
		$context['sub_template'] = ModSite::$name.'_post';
		$context['page_title'] = self::_modSite->tools->getText('title_post');
		$context['canonical_url'] = $scripturl . '?action='. ModSite::$name . ';sa=post';

		/* Build the form */

		/* Pass it to the template */


	}

	public static function download()
	{
		global $context;




	}

	public static function single()
	{
		/* meh... I haz all tha powerz */
		if (!$context['user']['is_admin'])
			redirectexit();

		/* Set all the page stuff */
		$context['sub_template'] = ModSite::$name.'_single';
		$context['page_title'] = self::_modSite->tools->getText('title_single');
		$context['canonical_url'] = $scripturl . '?action='. ModSite::$name . ';sa=single;mid=';


	}
}