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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.	See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.	If not, see <http://www.gnu.org/licenses/>.

/**
 * Page module version information
 *
 * @package		mod_codeview
 * @copyright	2020 Mazitov Artem https://github.com/mazitov-az/moodle-mod_codeview
 * @license		http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require __DIR__ . '/../../config.php';

$id	= optional_param('id', 0, PARAM_INT); // Course Module ID

if (!$cm = get_coursemodule_from_id('codeview', $id)) {
	print_error('invalidcoursemodule');
}
$codeview = $DB->get_record('codeview', ['id' => $cm->instance], '*', MUST_EXIST);
$course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);

require_course_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/codeview:view', $context);

// Completion and trigger events.
\mod_codeview\event\course_module_viewed::view($codeview, $course, $cm, $context);

$PAGE->set_url('/mod/codeview/view.php', ['id' => $cm->id]);
$PAGE->set_title($course->shortname.': '.$codeview->name);
$PAGE->set_heading($course->fullname);

$PAGE->requires->jQuery();

$PAGE->requires->css('/mod/codeview/codemirror-5.52.2/lib/codemirror.css');
$PAGE->requires->css('/mod/codeview/codemirror-5.52.2/theme/monokai.css');
$PAGE->requires->css('/mod/codeview/codemirror-5.52.2/addon/fold/foldgutter.css');
$PAGE->requires->css('/mod/codeview/codemirror-5.52.2/addon/dialog/dialog.css');

$output = $PAGE->get_renderer('mod_codeview');
$output->load();

echo $output->header();
echo $output->heading(format_string($codeview->name), 2);

echo $output->intro();
echo $output->display_codeview();

echo $output->footer();