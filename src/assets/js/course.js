$(function(){
	updateScreen()
	setInterval(updateScreen, 200)
	
	$("#course_left_button").click(function() {
		$("#course_left_button").toggleClass("active")
		$(".course_left").fadeToggle("fast")
	})
	
	$(".question").click(function() {
		var id_quest = $(".questions").attr("data-quest")
		var selectedQuestion = $(this).attr("data-index")
		
		// Shows answers
		$.ajax({
			type:"POST",
			url:BASE_URL+"ajax/quests",
			data:{id_quest:id_quest},
			success:function(ans) {
				if (ans == selectedQuestion) {
					alert("Correct!")
				} else {
					alert("Incorrect!")
				}
				
				$(".question").each(function(index) {
					$(this).addClass(index+1 == ans ? "question_correct" : "question_wrong")
					
					if (index+1 == selectedQuestion) {
						$(this).addClass("question_selected")
					}
				})
				
				$(".question").unbind()
			}
		})
		
		// Marks class as watched
		$.ajax({
			type:"POST",
			url:BASE_URL+"ajax/mark_class_watched",
			data:{id_class:$(".questions").attr("data-class")}
		})
	})
})

function updateScreen()
{
	var h = $(".course_right").height()
	var padding = $(".course_right").css("padding")
	
	$(".course_left").css("height", h+padding+"px")
	
	var ratio = 1920/1080
	var videoWidth = $("#class_video").width()
	var videoHeight = videoWidth/ratio
	
	$("#class_video").css("height", videoHeight+"px")
}

function markAsWatched(id_class)
{
	$.ajax({
		type:"POST",
		url:BASE_URL+"ajax/mark_class_watched",
		data:{id_class:id_class}
	})
	
	$(".content_info").hide().append(`<small class="class_watched">Watched</small>`).fadeIn("fast")
	$(".btn_mark_watch").attr("onclick", "removeWatched("+id_class+")")
}

function removeWatched(id_class)
{
	$.ajax({
		type:"POST",
		url:BASE_URL+"ajax/remove_watched_class",
		data:{id_class:id_class}
	})
	
	$(".class_watched").fadeOut("fast")
	$(".btn_mark_watch").attr("onclick", "markAsWatched("+id_class+")")
}

function deleteComment(obj, id_comment)
{
	$.ajax({
		type:"POST",
		url:BASE_URL+"ajax/remove_comment",
		data:{id_comment:id_comment},
		success:function() {
			$(obj).closest(".comment").hide("slow")
		}
	})
}

function open_reply(obj)
{
	$(obj).closest(".comment_info").find(".comment_reply").fadeIn("fast")
}

function close_reply(obj)
{
	$(obj).closest(".comment_reply").fadeOut("fast")
}

function send_reply(obj, id_doubt, id_user)
{
	var text = $(obj).closest(".comment_info").find("textarea").val()
	
	$.ajax({
		type:"POST",
		url:BASE_URL+"ajax/add_reply",
		data:{
			id_doubt:id_doubt,
			id_user:id_user,
			text:text
		},
		success: function(id_reply) {
			$.ajax({
				type:"POST",
				url:BASE_URL+"ajax/get_student_name",
				data:{id_student:id_user},
				success:function (studentName) {
					var name = studentName
					
					var html_comment = `
						<div class="comment comment_reply_content">
								<img class="img img-thumbnail" src="https://media.gettyimages.com/photos/colorful-powder-explosion-in-all-directions-in-a-nice-composition-picture-id890147976?s=612x612" />
								<div class="comment_content">
		        					<div class="comment_info">
		            					<h5>${name}</h5>
		            					<p>${text}</p>
		        					</div>
		            					<div class="comment_action">
		            						<button class="btn btn-danger" onclick="delete_reply(this,${id_reply})">&times;</button>
		            					</div>
		        				</div>
							</div>
					`
					$(obj).closest(".comment_info").find(".comment_replies").fadeOut("fast").prepend(html_comment).fadeIn("fast")
				}
			})
		}
	})
}

function delete_reply(obj, id_reply)
{
	$.ajax({
		type:"POST",
		url:BASE_URL+"ajax/remove_reply",
		data:{id_reply:id_reply},
		success:function() {
			$(obj).closest(".comment_reply_content").hide("slow")
		}
	})
}

function update_profilePhoto(obj)
{
	var file = $("#profile_photo")[0].files
	
	if (file.length > 0) {
		var data = new FormData()
		data.append("photo", file[0])
		
		$.ajax({
			type:'POST',
			url:BASE_URL+"ajax/update_profile_photo",
			data: data,
			contentType: false,
			processData: false,
			success: function() {
				document.location.reload()
				$(obj).modal("toggle")
			}
		})
	}
}