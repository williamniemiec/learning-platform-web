$(function() {
	$(".notification_icon").click(function() {
		$(".notifications_area").toggle("fast")
		$(".notification_icon").focus()
	})
	
	$(".notification_icon").blur(function() {
		alert("b")
	})
	
	$(".notifications_area").hover(function() {

	}, function() {
		$(".notifications_area").toggle("fast")
	})
})
