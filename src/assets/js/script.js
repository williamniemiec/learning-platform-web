$(function() {
	$(".notification_icon").click(function() {
		$(".notifications_area").toggle("fast")
		$(".notification_icon").focus()
	})
	
	
	$(".scrollbar_light").mCustomScrollbar({
		theme:"inset-3-dark"
	})
	
	$(".notification_icon").blur(function() {
		alert("b")
	})
	
	$(".notifications_area").hover(function() {

	}, function() {
		$(".notifications_area").toggle("fast")
	})
	
	setInterval(function(){
		var hMain = $("main").css("height")
		
		if ($("#mCSB_1_container").hasClass("mCS_no_scrollbar_y") && $("#mCSB_1").height() > hMain) {
			var h = $("body").css("height")
			var x = $("#mCSB_1").height() - 100
			$("main").css("height", x+"px")
		}
	},200)
})
