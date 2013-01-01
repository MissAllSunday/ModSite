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

	class ModSiteForm
	{
		public $method;
		public $action;
		public $name;
		public $id_css;
		public $class;
		public $elements;
		public $status;
		public $buffer;
		public $onsubmit;
		public $text;

		function __construct($form = array())
		{
			global $scripturl, $txt;

			LoadLanguage(ModSite::$name);

			if (empty($form) || !is_array($form))
				return;

			/* Load the text strings */
			$this->text = $txt;

			$this->action = $scripturl . '?action=' . $form['action'];
			$this->method = $form['method'];
			$this->id_css= $form['id_css'];
			$this->name = $form['name'];
			$this->onsubmit = empty($form['onsubmit']) ? '' : 'onsubmit="'. $form['onsubmit'] .'"';
			$this->class_css = $form['class_css'];
			$elements = array();
			$this->status = 0;
			$this->buffer = '';
		}

		public function returnElementNames()
		{
			$this->returnElementsNames = array();

			if (!empty($this->elements))
				foreach ($this->elements as $e)
					if (isset($e['name']) && !empty($e['name']))
						$this->returnElementsNames[$e['name']] = $e['name'];

			return $this->returnElementsNames;
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
			$element['html_start'] = '<'. $element['type'] .' name="' .$element['name']. '">';
			$element['html_end'] = '</'. $element['type'] .'>';

			foreach($values as $k => $v)
				$element['values'][$k] = '<option value="' .$k. '" '. (isset($v[1]) && $v[1] == 'selected' ? 'selected="selected"' : '') .'>'. $this->text[ModSite::$name.'form_'.$v[0]] .'</option>';

			return $this->addElement($element);
		}

		function addCheckBox($name,$value, $text, $checked = false)
		{
			$element['type'] = 'checkbox';
			$element['name'] = $name;
			$element['value'] = $value;
			$element['checked'] = empty($checked) ? '' : 'checked="checked"';
			$element['text'] = $text;
			$element['html'] = '<input type="'. $element['type'] .'" name="'. $element['name'] .'" id="'. $element['name'] .'" value="'. (int)$element['value'] .'" '. $element['checked'] .' class="input_check" />';

			return $this->addElement($element);
		}

		function addText($name,$value, $text, $size = false, $maxlength = false)
		{
			$element['type'] = 'text';
			$element['name'] = $name;
			$element['value'] = $value;
			$element['text'] = $text;
			$element['size'] = empty($size) ? 'size="20"' : 'size="' .$size. '"';
			$element['maxlength'] = empty($maxlength) ? 'maxlength="20"' : 'maxlength="' .$maxlength. '"';
			$element['html'] = '<input type="'. $element['type'] .'" name="'. $element['name'] .'" id="'. $element['name'] .'" value="'. $element['value'] .'" '. $element['size'] .' '. $element['maxlength'] .' class="input_text" />';

			return $this->addElement($element);
		}

		function addTextArea($name,$value, $text)
		{
			$element['type'] = 'textarea';
			$element['name'] = $name;
			$element['value'] = empty($value) ? '' : $value;
			$element['text'] = $text;
			$element['html'] = '<'. $element['type'] .' name="'. $element['name'] .'" id="'. $element['name'] .'">'. $element['value'] .'</'. $element['type'] .'>';

			return $this->addElement($element);
		}

		function addHiddenField($name,$value)
		{
			$element['type'] = 'hidden';
			$element['name'] = $name;
			$element['value'] = $value;
			$element['html'] = '<input type="'. $element['type'] .'" name="'. $element['name'] .'" id="'. $element['name'] .'" value="'. $element['value'] .'" />';

			return $this->addElement($element);
		}

		function addSubmitButton($value)
		{
			$element['type'] = 'submit';
			$element['value']= $this->text[$value];
			$element['html'] = '<input class="button_submit" type="'. $element['type'] .'"  value="'. $element['value'] .'" />';

			return $this->addElement($element);
		}

		function addHr()
		{
			$element['type'] = 'hr';
			$element['html'] = '<hr />';

			return $this->addElement($element);
		}

		function display()
		{
			$this->buffer = '<form action="'. $this->action .'" method="'. $this->method .'" id="'. $this->id_css .'" class="'. $this->class_css .'"  '. $this->onsubmit .' >';
			$this->buffer .= '<dl class="settings">';
			$element = $this->GetNextElement();

			foreach($this->elements as $el)
			{
				switch($el['type'])
				{
					case 'textarea':
					case 'checkbox':
					case 'text':
						$this->buffer .= '<dt>
							<span style="font-weight:bold;">'. $this->text[ModSite::$name.'form_'. $el['text'][0]] .'</span>
							<br /><span class="smalltext">'. $this->text[ModSite::$name.'form_'.$el['text'][1]] .'</span>
						</dt>
						<dd>
							'. $el['html'] .'
						</dd>';
						break;
					case 'select':
						$this->buffer .= '<dt>
							<span style="font-weight:bold;">'. $this->text[ModSite::$name.'form_'.$el['text'][0]] .'</span>
							<br /><span class="smalltext">'. $this->text[ModSite::$name.'form_'.$el['text'][1]] .'</span>
						</dt>
						<dd>
							'. $el['html_start'] .'';

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

			$this->buffer .= '</dl></form>';

			return $this->buffer;
		}
	}