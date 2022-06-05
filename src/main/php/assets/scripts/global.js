$(function() {
	// Fix footer
	fixFooter()
	setInterval(fixFooter, 500)
	
	// Enables tooltip
	$("[data-toggle='tooltip']").tooltip()
})

/**
 * If there is no scroll bar, sets the footer as fixed at the bottom.
 */
function fixFooter()
{
	if ($("body").hasClass("mCS_no_scrollbar") || $("#mCSB_1_container").hasClass("mCS_no_scrollbar_y")) {
		$("footer").css("position", "fixed").css("bottom", 0)
	}
	else {
		$("footer").css("position", "")
	}
}