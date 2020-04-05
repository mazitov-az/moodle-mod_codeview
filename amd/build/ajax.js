define(['jquery', 'core/notification', 'core/ajax'], function ($, Notification, ajax) {

	return {

		save: function(instance) {
			// body...
			var event = this;
			$( document ).ready(function() {
				//----------------------------------------------------------
				var editor = CodeMirror.fromTextArea(document.getElementById("code"), {
					lineNumbers: true,
					matchBrackets: true,
					mode: "application/x-httpd-php",

					keyMap: "sublime",
					theme: "monokai",

					indentUnit: 4,
					indentWithTabs: true
				});
				//----------------------------------------------------------
				editor.on("change", function(cm, change) {
					// body...
					var promises = ajax.call([{
						methodname: 'mod_codeview_save_code',
						args: { codeviewid: instance, code: cm.getValue() },
						fail: Notification.exception
					}]);

					promises[0].done(function(response) {
						console.log( response );
						console.log( response[0] );
						console.log( response[0].content);
					});
				});
				//----------------------------------------------------------
				$('#submit_for_verification').click(function() {
					// body...
					$('#submit_for_verification').slideUp();
					$('#control_panel_on_check').slideDown();

					$('#codemirror_pre').html( event.escapeHtml( editor.getValue() ) );
					$('.CodeMirror').slideUp();
					$('#codemirror_pre').slideDown();

					var verification = ajax.call([{
						methodname: 'mod_codeview_submit_for_verification',
						args: { codeviewid: instance },
						fail: Notification.exception
					}]);

					verification[0].done(function(response) {
						console.log( response );
						console.log( response[0] );
						console.log( response[0].content);
					});
				});
				//----------------------------------------------------------
			});
		},

		escapeHtml: function(text) {
			let map = {
				'&': '&amp;',
				'<': '&lt;',
				'>': '&gt;',
				'"': '&quot;',
				"'": '&#039;'
			};
			return text.replace(/[&<>"']/g, function(m) { return map[m]; });
		},

		teacher_check: function(instance) {
			$( document ).ready(function() {
				// $('#submit_for_verification').click(function() {
				// 	// body...
				// 	console.log(1);
				// });
			});
		}
	}
});