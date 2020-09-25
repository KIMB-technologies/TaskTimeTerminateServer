$(() => {
	function confirmDialog(title, text, ok) {
		$("body").append('<div id="confirmDialog"></div>');
		$("div#confirmDialog").text(text);
		$("div#confirmDialog").dialog({
			resizable: false,
			height: "auto",
			width: "350",
			modal: true,
			title: title,
			close : () => {
				$("div#confirmDialog").remove();
			},
			buttons: {
				"Ok" : () => {
					$("div#confirmDialog").dialog('close');
					ok();
				},
				"Abbrechen" : () => {
					$("div#confirmDialog").dialog('close');
				}
			}
		});
	}

	$(".confirm").click((e) => {
		e.preventDefault();
		confirmDialog(
			$(e.currentTarget).attr('title'),
			$(e.currentTarget).attr('content'),
			() => {
				if($(e.currentTarget).hasClass('confirmLink')){
					window.location = $(e.currentTarget).attr('href');
				}
		});
	});
});