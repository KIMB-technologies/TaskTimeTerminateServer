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

function checkForLoginCode(){
	var url = window.location.href;
	if(	localStorage.hasOwnProperty("loginToken") &&
		( !sessionStorage.hasOwnProperty("tokenUsed") || parseInt(sessionStorage.getItem('tokenUsed')) + 10000 < Date.now() ) &&
		url.substring(url.length - 6) !== 'logout'
	){
		sessionStorage.setItem("tokenUsed", Date.now());
		let data = localStorage.getItem("loginToken").split(',');
		$.post(url, { "group": data[1], "token": data[0]}, () => {
			window.location.reload();
		});
	}
}