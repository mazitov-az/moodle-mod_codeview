define(['jquery', 'core/notification', 'core/ajax'], function ($, Notification, ajax) {
	//возвращаем объект, методы которых будут вызываться в PHP  $PAGE->requires->js_call_amd()
	//пример
	//$PAGE->requires->js_call_amd('local_example/ajax', 'test_ajax', [$argument]);
	return {
		//method
		save: function(instance) {
			// body...
			$( document ).ready(function() {
				console.log( "ready!" );
				var editor = CodeMirror.fromTextArea(document.getElementById("code"), {
					lineNumbers: true,
					matchBrackets: true,
					mode: "application/x-httpd-php",

					keyMap: "sublime",
					theme: "monokai",

					indentUnit: 4,
					indentWithTabs: true
				});
				editor.on("change", function(cm, change) {
					// body...
					console.log( cm.getValue());
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
			});
			/*$('.test_ajax').click(function() {
				console.log('test_ajax');
				// body...

				promises[0].done(function(response) {
					console.log( response );
					console.log( response[0] );
					console.log( response[0].content);
					$('#ajax_summ').val(response[0].content);
				}).fail(function(ex) {
					// do something with the exception
				});
			});*/
		}
	}
});