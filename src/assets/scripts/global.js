$(function() {
	// Fix footer
	setInterval(() => {
		if ($("body").hasClass("mCS_no_scrollbar") || $("#mCSB_1_container").hasClass("mCS_no_scrollbar_y")) {
			$("footer").css("position", "fixed").css("bottom", 0)
		}
		else {
			$("footer").css("position", "")
		}
	}, 500)
	
	// Enables tooltip
	$("[data-toggle='tooltip']").tooltip()
})