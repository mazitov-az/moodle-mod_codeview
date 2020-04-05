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
 * Codeview module renderer
 *
 * @package		mod_codeview
 * @copyright	2020 Mazitov Artem https://github.com/mazitov-az/moodle-mod_codeview
 * @license		http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class mod_codeview_renderer extends plugin_renderer_base {

	public function load(){
		# code...
		$this->page->requires->js_call_amd('mod_codeview/ajax', 'save', [$this->page->cm->instance]);

	}

	public function intro(){
		# code...
		global $DB;
		return $DB->get_field('codeview', 'intro', ['id' => $this->page->cm->instance]);
	}

	// TODO add get file
	public function display_codeview(){
		# code...
		if ( has_capability('mod/codeview:addinstance', $this->page->context) ){
			// teacher
			return $this->teacher();
		} else {
			// student
			return $this->student();
		}
	}

	public function student(){
		# code...
		global $DB, $USER;
		$code = $DB->get_record('codeview_code', ['codeviewid' => $this->page->cm->instance, 'userid' => $USER->id], 'id, code, date, status');
		if ( !$code ) {
			# code...
			$insert = [
				'codeviewid' => $this->page->cm->instance,
				'userid' => $USER->id,
				'code' => '<?php',
				'date' => $_SERVER['REQUEST_TIME'],
				'status' => 0
			];
			$DB->insert_record('codeview_code', $insert);
			$user_code = $insert['code'];
			$status = 0;
		} else {
			$user_code = $code->code;
			$status = $code->status;
		}
		return $this->control_panel($status).'<hr>'.$this->codemirror($user_code, [], $status);
	}

	public function control_panel($status = 0){
		# code...
		$this->page->requires->js_call_amd('mod_codeview/ajax', 'teacher_check', [$this->page->cm->instance]);
		$none = ['style' => 'display: none;'];
		$_HTML = '';
		$_HTML .= html_writer::start_div('', ['id' => 'teacher_check']);
			$_HTML .= html_writer::link('#', 'Подать на проверку', (['id' => 'submit_for_verification', 'class' => 'btn'] + ($status == 0 ? [] : $none)) );
			$_HTML .= html_writer::div('На проверке', 'alert alert-info', (['id' => 'control_panel_on_check'] + ($status == 1 ? [] : $none)) );
			$_HTML .= html_writer::div('Зачтено', 'alert alert-success', ($status == 2 ? [] : $none));
		$_HTML .= html_writer::end_div();
		return $_HTML;
	}

	public function codemirror($code, array $option = [], $status){
		# code...
		// temp
		if ( $status > 0 ) {
			# code...
			$codemirror_box_attr = ['style' => 'display: none;'];
			$codemirror_pre_attr = ['id' => 'codemirror_pre'];
			// return html_writer::tag('pre', $code, ['id' => 'codemirror_pre']);
		} else {
			$codemirror_box_attr = [];
			$codemirror_pre_attr = ['id' => 'codemirror_pre', 'style' => 'display: none;'];
		}
		
		$attr = [
			'id' => 'code',
			'name' => 'code'
		] + $option;

		$script = '
			<script src="./codemirror-5.52.2/lib/codemirror.js"></script>
			<script src="./codemirror-5.52.2/addon/search/searchcursor.js"></script>
			<script src="./codemirror-5.52.2/addon/search/search.js"></script>
			<script src="./codemirror-5.52.2/addon/dialog/dialog.js"></script>
			<script src="./codemirror-5.52.2/addon/edit/matchbrackets.js"></script>
			<script src="./codemirror-5.52.2/addon/edit/closebrackets.js"></script>
			<script src="./codemirror-5.52.2/addon/comment/comment.js"></script>
			<script src="./codemirror-5.52.2/addon/wrap/hardwrap.js"></script>
			<script src="./codemirror-5.52.2/addon/fold/foldcode.js"></script>
			<script src="./codemirror-5.52.2/addon/fold/brace-fold.js"></script>

			<script src="./codemirror-5.52.2/mode/htmlmixed/htmlmixed.js"></script>
			<script src="./codemirror-5.52.2/mode/xml/xml.js"></script>
			<script src="./codemirror-5.52.2/mode/javascript/javascript.js"></script>
			<script src="./codemirror-5.52.2/mode/css/css.js"></script>
			<script src="./codemirror-5.52.2/mode/clike/clike.js"></script>
			<script src="./codemirror-5.52.2/mode/php/php.js"></script>

			<script src="./codemirror-5.52.2/keymap/sublime.js"></script>
		';
		$HTML = html_writer::div(
			html_writer::tag('textarea', $code, $attr).$script,
			'codemirror_box',
			$codemirror_box_attr
		).html_writer::tag('pre', $code, $codemirror_pre_attr);
		return $HTML;
	}

	// -----------------------------------

	public function teacher(){
		# code...
		$this->user_fields = user_picture::fields();
		$userid = optional_param('userid', false, PARAM_INT);
		if ($userid) {
			# code...
			return $this->get_user_code($userid);
		} else {
			return $this->get_users_table();
		}
	}

	public function get_users_table(){
		# code...
		global $DB;
		
		$table = new html_table();
		$table->head = ['#', 'user', 'update'];
		$i = 0;
		
		$rows = $DB->get_records('codeview_code', ['codeviewid' => $this->page->cm->instance], '' ,'id, userid, date');
		foreach ($rows as $row) {
			# code...
			$table->data[] = [
				++$i,
				$this->get_user($row->userid),
				date('d.m.Y H:i', $row->date)
			];
		}
		return html_writer::table($table);
	}

	public function get_user($userid){
		# code...
		global $DB;
		$user = $DB->get_record('user', ['id' => $userid], $this->user_fields);
		$url = $this->page->url->out(false, ['userid' => $userid]);
		return $this->user_picture($user, []).' '.html_writer::link($url, $user->lastname.' '.$user->firstname);
	}

	public function get_user_code(int $userid){
		# code...
		global $DB;
		$_HTML = html_writer::link($this->page->url, get_string('back'), ['class' => 'btn']).'<br><br>';
		$_HTML .= $this->get_user($userid).'<br><br>';
		$date = $DB->get_record('codeview_code', ['codeviewid' => $this->page->cm->instance, 'userid' => $userid]);
		// $_HTML .= '<pre>'.print_r($date->code, 1).'</pre>';
		$_HTML .= $this->codemirror($date->code, ['userid' => $userid]);
		return $_HTML;
	}
}