$(function() {
	setInterval(() => {
		if ($("body").hasClass("mCS_no_scrollbar")) {
			$("footer").css("position", "fixed").css("bottom", 0)
		}
		else {
			$("footer").css("position", "")
		}
	}, 500)
})