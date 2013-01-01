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

abstract class ModsiteDispatcher
{
	/**
	 * ModSiteDispatcher::__construct()
	 *
	 * @return
	 */
	private function __construct(){}

	static function dispatch()
	{
		$container = new BreezeContainer();

		/* Globals */
		$container->globals = $container->asShared(function ($c)
		{
			return new ModSiteGlobals();
		});

		/* Settings */
		$container->settings = $container->asShared(function ($c)
		{
			return new ModSiteSettings();
		});

		/* Text */
		$container->text = $container->asShared(function ($c)
		{
			return new ModSiteText();
		});

		/* Query */
		$container->query = $container->asShared(function ($c)
		{
			return new ModSiteQuery($c->settings, $c->globals, $c->text);
		});

		$actions = array(
			'mods' => array(ModSite::$name .'Page' , 'call'),
		);

		if (in_array($container->globals->getValue('action'), array_keys($actions)))
		{
			$controller_name = $actions[$container->globals->getValue('action')][0];
			$controller = new $controller_name();

			/* Lets call the method */
			$method_name = $actions[$sglobals->getValue('action')][1];
			call_user_func_array(array($controller, $method_name), array());
		}
	}
}