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

class ModSitePage
{
	public function __construct($query, $settings, $text)
	{
		global $scripturl;

		/* Load stuff */
		loadtemplate(ModSite::$name);

		$this->text = $text;
		$this->settings = $settings;
		$this->query = $query;
		$this->scripturl = $scripturl;

		/* We need a brand new globals object */
		$this->globals = new ModSiteGlobals('get');
	}

	public function call()
	{
		$subActions = array(
			'post' => 'doPost',
			'post2' => 'doPost2',
			'single' => 'doSingle',
			'download' => 'doDownload',
			'delete' => 'doDelete',
			'all' => 'doAll',
			'categories' => 'doCategories',
		);

		/* Does the subaction even exist? */
		if (in_array($this->globals->getValue('sa'), array_keys($subActions)))
			$this->$subActions[$this->globals->getValue('sa')]();

		/* No?  redirect them to the main page */
		else
			$this->main();
	}

	public function main()
	{
		global $context;

		/* meh... I haz all tha powerz */
		if (!$context['user']['is_admin'])
			redirectexit();

		/* Set all the page stuff */
		$context['sub_template'] = ModSite::$name.'_main';
		$context['page_title'] = $this->text->getText('title_main');
		$context['canonical_url'] = $this->scripturl . '?action=mods';

		/* Set the pagination stuff */

		/* Get all the mods */

	}

	public function doPost()
	{
		global $context;

		/* meh... I haz all tha powerz */
		if (!$context['user']['is_admin'])
			redirectexit();

		/* Set all the page stuff */
		$context['sub_template'] = ModSite::$name.'_post';
		$context['page_title'] = $this->text->getText('title_post');
		$context['canonical_url'] = $this->scripturl . '?action=mods;sa=post';

		/* Build the form */
		$form = $this->buildForm();

		/* Pass it to the template */
		$context['ModSite']['Form'] = $form;
		$context['page_desc'] = 'Some description here...';
	}

	public function doDownload()
	{
		global $context;

	}

	public function doSingle()
	{
		/* Set all the page stuff */
		$context['sub_template'] = 'single';
		$context['page_title'] = $this->text->getText('title_single');
		$context['canonical_url'] = $this->scripturl . '?action=mods;sa=single;mid=';
	}

	protected function buildForm($editing = false)
	{
		/* Get all categories */
		$categories = $this->query->getAllCategories();

		/* The form loves multidimensional arrays... */
		foreach($categories as $k => $v)
			$categories[$k] = array(
				$v,
				!empty($editing['cat']) && $editing['cat'] == $v ? true : false,
			);

		/* Build the form */
		$form = new ModSiteForm($this->text);

		/* Common text entries */
		$commonText = array('title', 'file', 'demo', 'version', 'topic', 'github', 'smf', 'smfd');

		/* CommonTextArea */
		$commonTextArea = array('desc', 'info',);

		foreach ($commonText as $c)
			$form->addText(
				$c,
				$c,
				$editing ? $editing[$c] : '',
				55,55
			);

		if (!empty($categories) && is_array($categories))
			$form->addSelect(
				'category',
				'mod_category',
				$categories
			);

		foreach ($commonTextArea  as $c)
			$form->addTextArea(
				$c,
				$c,
				$editing ? $editing[$c] : ''
			);

		return $form->display();
	}
}