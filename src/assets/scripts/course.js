//-----------------------------------------------------------------------------
//        Methods
//-----------------------------------------------------------------------------
$(function(){
	updateCourseContent()
	$(window).resize(updateCourseContent)
	// Course menu button
	$(".course_menu_action").click(function() {
		$(".course_menu_action #mobile_menu_button").toggleClass("active")
		$(".course_menu").fadeToggle("fast")
	})
	
	// When the student answers a question, informs him if he answered the
	// question correctly and displays the answer
	$(".question").click(function() {
		const id_module = $(".questions").attr("data-id-module")
		const class_order = $(".questions").attr("data-class-order")
		const selectedQuestion = $(this).attr("data-index")
		
		// Shows answers
		$.ajax({
			type:"POST",
			url:BASE_URL+"class/getAnswer",
			data:{id_module, class_order},
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
			url:BASE_URL+"class/mark_watched",
			data:{id_module, class_order, type:1}
		})
	})
})

/**
 * Updates course content area.
 */
function updateCourseContent()
{
	// Updates video dimensions
	const ratio = 1920/1080
	const videoWidth = $("#class_video").width()
	const videoHeight = videoWidth/ratio
	
	
	$("#class_video").css("height", videoHeight+"px")
}

/**
 * Updates course menu height.
 */
function updateCourseMenu()
{
	const hCourseRight = $(".class_area").height()
	const hMain = $("main").height()

	
	// Updates course menu height
	if (hMain > hCourseRight) {
		$(".course_menu").css("height", hMain)
	} else {
		$(".course_menu").css("height", hCourseRight)
	}
}

/**
 * Opens replies from a comment.
 * 
 * @param		object obj Show replies button
 */
function open_reply(obj)
{
	$(obj).closest(".comment_info").find(".comment_reply").fadeIn("fast")
}

/**
 * Closes replies from a comment.
 * 
 * @param		object obj Show replies button
 */
function close_reply(obj)
{
	$(obj).closest(".comment_reply").fadeOut("fast")
}


//-----------------------------------------------------------------------------
//        Ajax
//-----------------------------------------------------------------------------
/**
 * Marks a class as watched.
 * 
 * @param       int id_module Module id to which the class to be added to logged 
 * student's watched class historic belongs
 * @param		int class_order Class order in module
 * 
 * @implSpec     It will make an ajax request using POST request method
 */
function markAsWatched(id_module, class_order)
{
	$.ajax({
		type:"POST",
		url:BASE_URL+"class/mark_watched",
		data:{id_module, class_order, type:0}
	})
	
	$(".content_info").hide().append(`<small class="class_watched">Watched</small>`).fadeIn("fast")
	$(`.module_class[data-class='${id_module}/${class_order}']`).hide().append(`<small class="class_watched">Watched</small>`).fadeIn("fast")
	$(".btn_mark_watch").attr("onclick", "removeWatched(" + id_module + "," + class_order + ")").html("Remove watched")
}


/**
 * Marks a class as unwatched.
 * 
 * @param       int id_module Module id to which the class to be removed to 
 * logged student's watched class historic belongs
 * @param		int class_order Class order in module
 * 
 * @implSpec     It will make an ajax request using POST request method
 */
function removeWatched(id_module, class_order)
{
	$.ajax({
		type:"POST",
		url:BASE_URL+"class/remove_watched",
		data:{id_module, class_order, type:0}
	})
	
	$(".content_info").find(".class_watched").fadeOut("fast")
	$(`.module_class[data-class='${id_module}/${class_order}']`).find(".class_watched").fadeOut("fast")
	$(".btn_mark_watch").attr("onclick", "markAsWatched(" + id_module + "," + class_order + ")").html("Mark as watched")
}

/**
 * Creates a new note.
 *
 * @param		object Send button
 */
function newNote(obj, id_module, class_order)
{
	//let form = $(obj).closest("form")
	const data = $(obj).closest(".class_content")
	const title = $("#note_title").val()
	const content = $("#note_content").val()
	
	$.ajax({
		type:"POST",
		url:BASE_URL+"notebook/new",
		data:{
			id_module:id_module,
			class_order:class_order,
			title:title,
			content:content
		},
		success:(id_note) => {
			const d = new Date()
			const year = new Intl.DateTimeFormat('en', { year: 'numeric' }).format(d)
			const month = new Intl.DateTimeFormat('en', { month: '2-digit' }).format(d)
			const day = new Intl.DateTimeFormat('en', { day: '2-digit' }).format(d)
			const newNote = `
				<li class="notebook-item">
					<div class="notebook-item-header">
						<a href="${BASE_URL}notebook/open/${id_note}">${title}</a>
					</div>
					<div class="notebook-item-footer">
						<div class="notebook-item-class">${title}</div>
						<div class="notebook-item-date">${month+'-'+day+"-"+year}</div>
					</div>
				</li>
			`
			
			$(obj).closest(".content_notes").find("ul.notebook").hide().prepend(newNote).fadeIn("fast")
			$("#note_title").val("")
			$("#note_content").val("")
		}
	})
}

/**
 * Removes a comment from a class.
 * 
 * @param		object obj Delete comment button
 * @param       int id_comment Comment id to be deleted
 * 
 * @apiNote     It will make an ajax request using POST request method
 */
function deleteComment(obj, id_comment)
{
	$.ajax({
		type:"POST",
		url:BASE_URL+"class/class_remove_comment",
		data:{id_comment:id_comment},
		success:function() {
			$(obj).closest(".comment").hide("slow")
		}
	})
}

/**
 * Adds a reply to a class comment.
 * 
 * @param       object obj Send reply button
 * @param       int id_doubt Doubt id to be replied
 * @param       int id_user User id that will reply the comment
 * 
 * @apiNote     It will make an ajax request using POST request method
 */
function send_reply(obj, id_doubt, id_user)
{
	var text = $(obj).closest(".comment_info").find("textarea").val()
	
	$.ajax({
		type:"POST",
		url:BASE_URL+"class/class_add_reply",
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

/**
 * Removes reply from a class comment.
 * 
 * @param		object obj Delete comment button
 * @param       int id_reply Reply id to be deleted
 * 
 * @apiNote     It will make an ajax request using POST request method
 */
function delete_reply(obj, id_reply)
{
	$.ajax({
		type:"POST",
		url:BASE_URL+"class/class_remove_reply",
		data:{id_reply:id_reply},
		success:function() {
			$(obj).closest(".comment_reply_content").hide("slow")
		}
	})
}