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
 * The mod_codeview course module viewed event.
 *
 * @package		mod_codeview
 * @copyright	2020 Mazitov Artem https://github.com/mazitov-az/moodle-mod_codeview
 * @license		http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_codeview\event;
defined('MOODLE_INTERNAL') || die();
use stdClass;

/**
 * The mod_codeview course module viewed event class.
 */
class course_module_viewed extends \core\event\course_module_viewed {

	/**
	 * Init method.
	 */
	protected function init() {
		$this->data['crud'] = 'r';
		$this->data['edulevel'] = self::LEVEL_PARTICIPATING;
		$this->data['objecttable'] = 'codeview';
	}

	public static function get_objectid_mapping() {
		return ['db' => 'codeview', 'restore' => 'codeview'];
	}

	public static function view(stdClass $codeview, stdClass $course, stdClass $cm, \context_module $context){
		// Trigger course_module_viewed event.
		$params = [
			'context'	=> $context,
			'objectid'	=> $codeview->id
		];

		$event = self::create($params);
		$event->add_record_snapshot('course_modules', $cm);
		$event->add_record_snapshot('course', $course);
		$event->add_record_snapshot('codeview', $codeview);
		$event->trigger();

		// Completion.
		$completion = new \completion_info($course);
		$completion->set_module_viewed($cm);
	}
}