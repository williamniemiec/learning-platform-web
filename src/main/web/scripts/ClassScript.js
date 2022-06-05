//-----------------------------------------------------------------------------
//        Methods
//-----------------------------------------------------------------------------
$(function(){
	updateScreen()
	$(window).resize(updateScreen)
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
 * Updates course content area and video dimensions.
 */
function updateScreen()
{
	updateVideo()
	updateCourseContent()
}

/**
 * Updates course content area.
 */
function updateVideo()
{
	// Updates video dimensions
	const ratio = 1920/1080
	const videoWidth = $("#class_video").width()
	const videoHeight = videoWidth/ratio
	
	
	$("#class_video").css("height", videoHeight+"px")
}

/**
 * Updates course content height.
 */
function updateCourseContent()
{
	if ($("body").hasClass("mCS_no_scrollbar") || $("#mCSB_1_container").hasClass("mCS_no_scrollbar_y"))
		$(".course_content").height($('footer').offset().top-50)
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
		url:BASE_URL + "class/mark_watched",
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
		url:BASE_URL + "class/remove_watched",
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
	const title = $("#note_title").val()
	const content = $("#note_content").val()
	
	$.ajax({
		type:"POST",
		url:BASE_URL + "notebook/new",
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
			const notebook = $(obj).closest(".content_notes").find("ul.notebook")
			let newNote = ''
			
			notebook.hide()
			
			newNote = `
				<li class="notebook-item">
					<div class="notebook-item-header">
						<a href="${BASE_URL}notebook/open/${id_note}">${title}</a>
					</div>
					<div class="notebook-item-footer">
						<div class="notebook-item-class">${title}</div>
						<div class="notebook-item-date">${month+'-'+day+"-"+year+' ' + date.getHours() + ':' + date.getMinutes() + ':' + date.getSeconds()}</div>
					</div>
				</li>
			`
			
			if (notebook.find(".notebook-item").length == 2)
				notebook.find(".notebook-item:last-of-type").remove()
			
			notebook.prepend(newNote).fadeIn("fast")
			$("#note_title").val("")
			$("#note_content").val("")
		}
	})
}

/**
 * Creates a new comment in a class.
 * 
 * @param		int id_course Course id to which the class belongs
 * @param       int id_module Module id to which the class belongs
 * @param		int class_order Class order in module
 */
function newComment(id_course, id_module, class_order)
{
	const commentArea = $("#question")
	const content = commentArea.val()
	
	$.ajax({
		type:'POST',
		url:BASE_URL + "class/new_comment",
		data:{id_course, id_module, class_order, content},
		success:(id) => {
			const creator = getStudentLoggedIn()
			commentArea.val('')
			$('.comments').prepend(`
				<div class="comment">
    				<img	class="img img-thumbnail" 
    						src="${creator.photo == "" ? 
								BASE_URL + "src/main/web/images/default/noImage.png" : 
								BASE_URL + "src/main/web/images/profile_photos/" + creator.photo}" 
					/>
    				<div class="comment_content">
    					<div class="comment_info">
    						<!-- Comment info -->
        					<h5>${creator.name}</h5>
        					<p>${content}</p>
        					<button class="btn btn-small" onclick="open_reply(this)">&ldca; Reply</button>
        					<div class="comment_reply">
        						<textarea class="form-control"></textarea>
        						<div class="comment_reply_actions">
            						<button class="btn btn-primary" onclick="close_reply(this)">Cancel</button>
            						<button	class="btn btn-primary" 
            								onclick="send_reply(this,${id},${creator.id})"
    								>
    									Send
									</button>
        						</div>
        					</div>
        					
        					<!-- Comment replies -->
        					<div class="comment_replies">
            				</div>
    					</div>
    					<div class="comment_action">
    						<button	class="btn btn-danger" 
									onclick="deleteComment(this,${id})"
							>
								&times;
							</button>
    					</div>  					
    				</div>
    			</div>
			`)
		}
	})
}

/**
 * Gets logged in student.
 *
 * @return		JSON Student logged in
 */
function getStudentLoggedIn()
{
	let student = null
	
	$.ajax({
		type:'POST',
		url:BASE_URL + "home/get_student_logged_in",
		async:false,
		datatype:'json',
		success:(s) => {
			student = JSON.parse(s)
		}
	})
	
	return student
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
		url:BASE_URL + "class/delete_comment",
		data:{id_comment},
		success:function() {
			$(obj).closest(".comment").hide("slow")
		}
	})
}

/**
 * Adds a reply to a class comment.
 * 
 * @param       object obj Send reply button
 * @param       int id_comment Comment id to be replied
 * 
 * @apiNote     It will make an ajax request using POST request method
 */
function new_reply(obj, id_comment)
{
	const content = $(obj).closest(".comment_info").find("textarea").val()
	
	$.ajax({
		type:"POST",
		url:BASE_URL+"class/add_reply",
		data:{id_comment, content},
		success: (id) => {
			const creator = getStudentLoggedIn()
			const html_comment = `
				<div class="comment comment_reply_content">
					<img 	class="img img-thumbnail" 
							src="${creator.photo == "" ? 
								BASE_URL + "src/main/web/images/default/noImage.png" : 
								BASE_URL + "src/main/web/images/profile_photos/" + creator.photo}" 
					/>
					<div class="comment_content">
    					<div class="comment_info">
        					<h5>${creator.name}</h5>
        					<p>${content}</p>
    					</div>
    					<div class="comment_action">
    						<button class="btn btn-danger" 
    								onclick="delete_reply(this,${id})"
							>
								&times;
							</button>
    					</div>
    				</div>
				</div>
			`
			$(obj).closest(".comment_info")
				.find(".comment_replies")
				.fadeOut("fast")
				.prepend(html_comment)
				.fadeIn("fast")
				
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
		url:BASE_URL+"class/remove_reply",
		data:{id_reply:id_reply},
		success:function() {
			$(obj).closest(".comment_reply_content").hide("slow")
		}
	})
}