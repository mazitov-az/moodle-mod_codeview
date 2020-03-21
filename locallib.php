<?php

function codeview_get_editor_options($context) {
	global $CFG;
	return [
		'subdirs'		=> 1,
		'maxbytes'		=> $CFG->maxbytes,
		'maxfiles'		=> -1,
		'changeformat'	=> 1,
		'context'		=> $context,
		'noclean'		=> 1,
		'trusttext'		=> 0
	];
}