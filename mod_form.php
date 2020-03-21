<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Page configuration form
 *
 * @package		mod_codeview
 * @copyright	2020 Mazitov Artem https://github.com/mazitov-az/moodle-mod_codeview
 * @license		http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once $CFG->dirroot . '/course/moodleform_mod.php';
require_once $CFG->dirroot . '/mod/codeview/locallib.php';
require_once $CFG->libdir . '/filelib.php';

class mod_codeview_mod_form extends moodleform_mod {
	function definition() {
		global $CFG;
		$mform = $this->_form;

		//-------------------------------------------------------
		$mform->addElement('header', 'general', get_string('general', 'form'));
		$mform->addElement('text', 'name', get_string('name'), ['size'=>'48']);
		if (!empty($CFG->formatstringstriptags)) {
			$mform->setType('name', PARAM_TEXT);
		} else {
			$mform->setType('name', PARAM_CLEANHTML);
		}
		$mform->addRule('name', null, 'required', null, 'client');
		$mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
		$this->standard_intro_elements();
		//-------------------------------------------------------
		$this->standard_coursemodule_elements();

		//-------------------------------------------------------
		$this->add_action_buttons();

		//-------------------------------------------------------
	}

	function data_preprocessing(&$default_values) {
		if ($this->current->instance) {
			$draftitemid = file_get_submitted_draft_itemid('codeview');
			$default_values['codeview']['format'] = $default_values['contentformat'];
			$default_values['codeview']['text']   = file_prepare_draft_area(
				$draftitemid,
				$this->context->id,
				'mod_codeview',
				'content',
				0,
				codeview_get_editor_options($this->context),
				$default_values['content']
			);
			$default_values['codeview']['itemid'] = $draftitemid;
		}
	}
}
