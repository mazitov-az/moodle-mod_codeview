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
 *
 * @package		mod_codeview
 * @copyright	2020 Mazitov Artem https://github.com/mazitov-az/moodle-mod_codeview
 * @license		http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_codeview;
defined('MOODLE_INTERNAL') || die();

require_once $CFG->libdir.'/externallib.php';

use external_function_parameters;
use external_value;
use external_multiple_structure;
use external_single_structure;

class external extends \external_api {
	
	/**
	 * Expose to AJAX
	 * @return boolean
	 */
	public static function save_is_allowed_from_ajax() {
		return true;
	}

	/**
	 * Wrap the core function save.
	 *
	 * @return external_function_parameters
	 */
	public static function save_parameters() {
		return new external_function_parameters([
			'codeviewid' => new external_value(PARAM_INT, 'code'),
			'code'	=> new external_value(PARAM_RAW, 'code')
		]);
	}
	
	/**
	 * Wrap the core function save.
	 *
	 * @return external_description
	 */
	public static function save_returns() {
		return new external_multiple_structure(
			new external_single_structure([
				'content'	=> new external_value(PARAM_RAW, 'content text')
			])
		);
	}

	public static function save($codeviewid, $code = ''){
		# code...
		global $DB, $USER;
		$id = $DB->get_field('codeview_code', 'id', ['codeviewid' => $codeviewid, 'userid' => $USER->id]);
		if (!$id) {
			# code...
			// TODO error
			return [
				[
					'content' => 'fail'
				]
			];
		}
		$param = [
			'id' => $id,
			'code' => htmlspecialchars($code),
			'date' => $_SERVER['REQUEST_TIME']
		];
		$DB->update_record('codeview_code', $param);
		// mdl_codeview_code
		return [
			[
				'content' => 'save'
			]
		];
	}


	/**
	 * Expose to AJAX
	 * @return boolean
	 */
	public static function submit_for_verification_is_allowed_from_ajax() {
		return true;
	}

	/**
	 * Wrap the core function submit_for_verification.
	 *
	 * @return external_function_parameters
	 */
	public static function submit_for_verification_parameters() {
		return new external_function_parameters([
			'codeviewid' => new external_value(PARAM_INT, 'code')
		]);
	}
	
	/**
	 * Wrap the core function submit_for_verification.
	 *
	 * @return external_description
	 */
	public static function submit_for_verification_returns() {
		return new external_multiple_structure(
			new external_single_structure([
				'content'	=> new external_value(PARAM_RAW, 'content text')
			])
		);
	}

	public static function submit_for_verification($codeviewid){
		# code...
		global $DB, $USER;
		$id = $DB->get_field('codeview_code', 'id', ['codeviewid' => $codeviewid, 'userid' => $USER->id]);
		if (!$id) {
			# code...
			// TODO error
			return [
				[
					'content' => 'fail'
				]
			];
		}

		$DB->update_record('codeview_code', ['id' => $id, 'date' => $_SERVER['REQUEST_TIME'], 'status' => 1]);
		return [
			[
				'content' => 'submit_for_verification'
			]
		];
	}
}