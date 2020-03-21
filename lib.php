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
 * @package		mod_codeview
 * @copyright	2020 Mazitov Artem https://github.com/mazitov-az/moodle-mod_codeview
 * @license		http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * List of features supported in Code view module
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed True if module supports feature, false if not, null if doesn't know
 */
function codeview_supports($feature) {
	switch($feature) {
		case FEATURE_MOD_ARCHETYPE:			return MOD_ARCHETYPE_RESOURCE;
		case FEATURE_GROUPS:				return false;
		case FEATURE_GROUPINGS:				return false;
		case FEATURE_MOD_INTRO:				return true;
		case FEATURE_COMPLETION_TRACKS_VIEWS:return true;
		case FEATURE_GRADE_HAS_GRADE:		return false;
		case FEATURE_GRADE_OUTCOMES:		return false;
		case FEATURE_BACKUP_MOODLE2:		return true;
		case FEATURE_SHOW_DESCRIPTION:		return true;

		default: return null;
	}
}

/**
 * Add codeview instance.
 * @param stdClass $data
 * @param mod_codeview_mod_form $mform
 * @return int new codeview instance id
 */
 function codeview_add_instance($data, $mform = null) {
 	global $DB;
	$data->timemodified = time();
	$data->id = $DB->insert_record('codeview', $data);

	$completiontimeexpected = !empty($data->completionexpected) ? $data->completionexpected : null;
	\core_completion\api::update_completion_date_event($cmid, 'codeview', $data->id, $completiontimeexpected);

	return $data->id;
 }

 /**
 * Update codeview instance.
 * @param object $data
 * @param object $mform
 * @return bool true
 */
function codeview_update_instance($data, $mform) {
	global $DB;
	// dpr($data);
    $cmid = $data->coursemodule;
	$codeview = [
		'id'			=> $data->instance,
		'name'			=> $data->name,
		'intro'			=> $data->intro,
		'introformat'	=> $data->introformat,
		'timemodified'	=> time()
	];

	$completiontimeexpected = !empty($data->completionexpected) ? $data->completionexpected : null;
	\core_completion\api::update_completion_date_event($cmid, 'codeview', $data->instance, $completiontimeexpected);

	return $DB->update_record('codeview', $codeview);
}

 /**
 * Delete codeview instance.
 * @param int $id
 * @return bool true
 */
function codeview_delete_instance($id) {
	global $DB;

	if (!$codeview = $DB->get_record('codeview', ['id' => $id])) {
		return false;
	}

	$cm = get_coursemodule_from_instance('codeview', $id);
	\core_completion\api::update_completion_date_event($cm->id, 'codeview', $id, null);

	// note: all context files are deleted automatically

	$DB->delete_records('codeview', ['id' => $codeview->id]);

	return true;
}

/**
 * Serves the codeview files.
 *
 * @package  mod_codeview
 * @category files
 * @param stdClass $course course object
 * @param stdClass $cm course module object
 * @param stdClass $context context object
 * @param string $filearea file area
 * @param array $args extra arguments
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 * @return bool false if file not found, does not return if found - just send the file
 */
function codeview_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = []) {
	global $CFG, $DB;
	require_once("$CFG->libdir/resourcelib.php");

	if ($context->contextlevel != CONTEXT_MODULE) {
		return false;
	}

	require_course_login($course, true, $cm);
	if (!has_capability('mod/codeview:view', $context)) {
		return false;
	}

	if ($filearea !== 'content') {
		// intro is handled automatically in pluginfile.php
		return false;
	}

	$arg = array_shift($args);
	$fs = get_file_storage();
	$relativepath = implode('/', $args);
	$fullpath = "/$context->id/mod_codeview/$filearea/0/$relativepath";
	if (!$file = $fs->get_file_by_hash(sha1($fullpath)) || $file->is_directory()) {
		$codeview = $DB->get_record('codeview', ['id' => $cm->instance], 'id, legacyfiles', MUST_EXIST);
		if ($codeview->legacyfiles != RESOURCELIB_LEGACYFILES_ACTIVE) {
			return false;
		}
		if (!$file = resourcelib_try_file_migration('/'.$relativepath, $cm->id, $cm->course, 'mod_codeview', 'content', 0)) {
			return false;
		}
	}

	// finally send the file
	send_stored_file($file, null, 0, $forcedownload, $options);
}