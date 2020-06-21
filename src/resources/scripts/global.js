$(function() {
	// Places the footer at the bottom
	setInterval(function(){
		var hMain = $("main").height()
		
		if ($("#mCSB_1_container").hasClass("mCS_no_scrollbar_y") && $("#mCSB_1").height() > hMain) {
			var h = $("body").css("height")
			var x = $("#mCSB_1").height() - 130
			$("main").css("height", x+"px")
		}
	},200)
	
	
	// Activates bootstrap tooltips
	$("[data-toggle='tooltip']").tooltip()
})