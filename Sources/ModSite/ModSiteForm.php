<?php

/**
 * Mod Site (SMF)
 *
 * @package SMF
 * @author Suki <suki@missallsunday.com>
 * @copyright 2013 Jessica Gonz�lez
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

	class ModSiteForm
	{
		public $method;
		public $action;
		public $name;
		public $id_css;
		public $class;
		public $elements = array();
		public $status = 0;
		public $buffer = '';
		public $onsubmit;
		public $text;

		function __construct($text)
		{
			$this->text = $text;
		}

		public function returnElementNames()
		{
			$this->returnelementsnames = array();

			if (!empty($this->elements))
				foreach ($this->elements as $e)
					if (isset($e['name']) && !empty($e['name']))
						$this->returnelementsnames[$e['name']] = $e['name'];

			return $this->returnelementsnames;
		}

		private function addElement($element)
		{
			$plus = $this->countElements();
			$element['id'] = $this->countElements();
			$this->elements[$element['id']] = $element;
		}

		private function countElements()
		{
			return count($this->elements);
		}

		private function getElement($id)
		{
			return $this->elements[$id];
		}

		private function getNextElement()
		{
			if( $this->status == $this->countElements())
				$this->status = 0;

			$element = $this->getElement($this->status);
			$this->status++;
		}

		function addSelect($name, $text, $values = array())
		{
			$element['type'] = 'select';
			$element['name'] = $name;
			$element['values'] = $values;
			$element['text']  = $text;
			$element['html_start'] = '<'. $element['type'] .' name="default_options['. $element['name'] .']">';
			$element['html_end'] = '</'. $element['type'] .'>';

			foreach($values as $k => $v)
				$element['values'][$k] = '<option value="' .$k. '" '. (isset($v[1]) && $v[1] == 'selected' ? 'selected="selected"' : '') .'>'. $this->text->getText('user_settings_'. $v[0]) .'</option>';

			return $this->addElement($element);
		}

		function addCheckBox($name, $text, $checked = false)
		{
			$element['type'] = 'checkbox';
			$element['name'] = $name;
			$element['value'] = 1;
			$element['checked'] = empty($checked) ? '' : 'checked="checked"';
			$element['text'] = $text;
			$element['html'] = '<input type="'. $element['type'] .'" name="default_options['. $element['name'] .']" id="default_options['. $element['name'] .']" value="'. (int)$element['value'] .'" '. $element['checked'] .' class="input_check" />';

			return $this->addElement($element);
		}

		function addText($name, $text, $value, $size = false, $maxlength = false)
		{
			$element['type'] = 'text';
			$element['name'] = $name;
			$element['value'] = $value;
			$element['text'] = $text;
			$element['size'] = empty($size) ? 'size="20"' : 'size="' .$size. '"';
			$element['maxlength'] = empty($maxlength) ? 'maxlength="20"' : 'maxlength="' .$maxlength. '"';
			$element['html'] = '<input type="'. $element['type'] .'" name="default_options['. $element['name'] .']" id="'. $element['name'] .'" value="'. $element['value'] .'" '. $element['size'] .' '. $element['maxlength'] .' class="input_text" />';

			return $this->addElement($element);
		}

		function addTextArea($name, $text, $value)
		{
			$element['type'] = 'textarea';
			$element['name'] = $name;
			$element['value'] = empty($value) ? '' : $value;
			$element['text'] = $text;
			$element['html'] = '<'. $element['type'] .' name="default_options['. $element['name'] .']" id="'. $element['name'] .'">'. $element['value'] .'</'. $element['type'] .'>';

			return $this->addElement($element);
		}

		function addHiddenField($name, $value)
		{
			$element['type'] = 'hidden';
			$element['name'] = $name;
			$element['value'] = $value;
			$element['html'] = '<input type="'. $element['type'] .'" name="default_options['. $element['name'] .']" id="'. $element['name'] .'" value="'. $element['value'] .'" />';

			return $this->addElement($element);
		}

		function addHr()
		{
			$element['type'] = 'hr';
			$element['html'] = '<hr />';

			return $this->addElement($element);
		}

		function display()
		{print_r($this->text);die;
			$this->buffer .= '<dl class="settings">';
			$element = $this->getNextElement();

			foreach($this->elements as $el)
			{
				switch($el['type'])
				{
					case 'textarea':
					case 'checkbox':
					case 'text':
						$this->buffer .= '<dt>
							<span style="font-weight:bold;">'. $this->text->getText($el['text']) .'</span>
							<br /><span class="smalltext">'. $this->text->getText($el['text'] .'_sub') .'</span>
						</dt>
						<dd>
							<input type="hidden" name="default_options['. $el['name'] .']" value="0" />'. $el['html'] .'
						</dd>';
						break;
					case 'select':
						$this->buffer .= '<dt>
							<span style="font-weight:bold;">'. $this->text->getText($el['text']) .'</span>
							<br /><span class="smalltext">'. $this->text->getText('user_settings_'.$el['text'] .'_sub') .'</span>
						</dt>
						<dd>
							<input type="hidden" name="default_options['. $el['name'] .']" value="0" />'. $el['html_start'] .'';

						foreach($el['values'] as $k => $v)
							$this->buffer .= $v .'';

						$this->buffer .= $el['html_end'] .'
						</dd>';
						break;
					case 'hidden':
					case 'submit':
						$this->buffer .= '<dt></dt>
						<dd>
							'. $el['html'] .'
						</dd>';
						break;
					case 'hr':
						$this->buffer .= '</dl>
							'. $el['html'] .'
						<dl class="settings">';
						break;
				}
			}

			$this->buffer .= '</dl>';

			return $this->buffer;
		}
	}