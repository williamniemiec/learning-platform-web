$(function() {
	// Fix footer
	updateFooter()
	$(window).resize(updateFooter)
	
	// Activates bootstrap tooltips
	$("[data-toggle='tooltip']").tooltip()
})

/**
 * Places the footer at the bottom
 */
function updateFooter()
{
	var hWindow = $("#mCSB_1").height()
	var hasViewPanel = $("main").find(".view_panel").length
	var x = hWindow - 50 - 50;
	
	
	if (hasViewPanel)
		x -= 30		// View panel has 30px margin top
	
	$("main").css("min-height", x+"px")
}