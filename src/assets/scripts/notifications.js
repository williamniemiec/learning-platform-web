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


/**
 * Marks a notification as read.
 *
 * @param		mixed obj Button object
 * @param		int id_notification Notification id
 */
function read(obj, id_notification)
{
	$.ajax({
		type:"POST",
		url:BASE_URL+"notification/read",
		data:{id_notification},
		success:() => {
			let badge = $("#notifications_new")
			
			$(obj).attr("onClick", `unread(this, ${id_notification})`)
			$(obj).html("Mark as unread")
			$(obj).closest(".notification").removeClass("new")
			$("#notifications_new").html(badge.html() - 1)
			
			// If total unread notifications is zero, hide badge
			if ($("#notifications_new").html() == 0)
				$("#notifications_new").addClass("no_notifications")
		}
	})
}

/**
 * Marks a notification as unread.
 *
 * @param		mixed obj Button object
 * @param		int id_notification Notification id
 */
function unread(obj, id_notification)
{
	$.ajax({
		type:'POST',
		url:BASE_URL+"notification/unread",
		data:{id_notification},
		success:()=>{
			let badge = $("#notifications_new")
			
			$(obj).attr("onClick", `read(this, ${id_notification})`)
			$(obj).html("Mark as read")
			$(obj).closest(".notification").addClass("new")
			$("#notifications_new").html(parseInt(badge.html()) + 1)
			
			// If badge is hidden, display it
			if ($("#notifications_new").hasClass("no_notifications"))
				$("#notifications_new").removeClass("no_notifications")
		}
	})
}

/**
 * Removes a notification.
 *
 * @param		mixed obj Button object
 * @param		int id_notification Notification id
 */
function remove(obj, id_notification)
{
	$.ajax({
		type:"POST",
		url:BASE_URL+"notification/delete",
		data:{id_notification},
		success:() => {
			$(obj).closest(".notification").fadeOut("slow")
		}
	})
}