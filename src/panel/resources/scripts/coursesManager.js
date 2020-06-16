var current_id_mod = -1
var current_id_course = -1
var current_id_video = -1
var current_id_quest = -1


$(function() {
	$("input[name='classType']").change(function() {
		var type = $("input[name='classType']:checked").val()
		
		if (type === "video") {
			addClass_showVideo()
		} else {
			addClass_showQuest()
		}
	})
})

function deleteModule(obj,id_module)
{
	$(obj).closest(".module").fadeOut("fast")
	
	$.ajax({
		type:'POST',
		url:BASE_URL+"courses/delete_module",
		data:{id_module:id_module}
	})
}

function deleteClass(obj,id_class)
{
	$(obj).closest(".class").fadeOut("fast")
	
	$.ajax({
		type:'POST',
		url:BASE_URL+"courses/delete_class",
		data:{id_class:id_class}
	})
}

function addModule(obj)
{
	var name = $("#modalAdd").val()
	var id_course = $(".modules").attr("data-id_course")

	$.ajax({
		type:'POST',
		url:BASE_URL+"courses/add_module",
		data:{id_course:id_course, name:name},
		success: function(id) {
			if (id == -1) {
				alert("Error!")
				return
			}
			var newModule = `
				<div class="module" data-id_module="${id}">
					<button class="btn btn-primary" onclick="show_addClass(this,${id})">Add class</button>
    				<button class="btn btn-warning" onclick="show_editModule(this,${id})">Edit Module</button>
					<button class="btn btn-danger" onclick="deleteModule(this,${id})">Delete module</button>
					<h3 class="moduleName">${name}</h3>
				</div>
			`
				
			$(obj).closest(".modal").modal("toggle")
			$(".modules").append(newModule)
		}
	})
}


function addClass_showQuest()
{
	$(".classType_content_video").hide()
	$(".classType_content_quest").show()
}

function addClass_showVideo()
{
	$(".classType_content_video").show()
	$(".classType_content_quest").hide()
}

function show_addClass(obj,id_module)
{
	current_id_mod = id_module
	current_id_course = $(".modules").attr("data-id_course")
	$("#addClass").modal("toggle")
}

function show_editModule(obj, id_module)
{
	current_id_mod = id_module
	var modName = $(`div[data-id_module=${current_id_mod}]`).find(".moduleName").html()
	
	$("#moduleName").val(modName)
	$("#editModule").modal("toggle")
}

function show_editVideo(obj, id_video)
{
	$.ajax({
		type:'POST',
		url:BASE_URL+"courses/get_video",
		data:{id_video:id_video},
		dataType:'json',
		success: function(json) {
			$("#modal_editVideo").find("#edit_classType_video_title").val(json.title)
			$("#modal_editVideo").find("#edit_classType_video_description").val(json.description)
			$("#modal_editVideo").find("#edit_classType_video_url").val(json.url)
			
			$("#modal_editVideo").modal("toggle")
			
			current_id_video = id_video
		}
	})
}

function show_editQuest(obj, id_quest)
{
	$.ajax({
		type:'POST',
		url:BASE_URL+"courses/get_quest",
		data:{id_quest:id_quest},
		dataType:'json',
		success: function(json) {
			$("#modal_editQuest").find("#edit_classType_quest_name").val(json.question)
			$("#modal_editQuest").find("#edit_classType_quest_q1").val(json.op1)
			$("#modal_editQuest").find("#edit_classType_quest_q2").val(json.op2)
			$("#modal_editQuest").find("#edit_classType_quest_q3").val(json.op3)
			$("#modal_editQuest").find("#edit_classType_quest_q4").val(json.op4)
			$("#modal_editQuest").find("#edit_answer").val(json.answer)
			
			$("#modal_editQuest").modal("toggle")
			
			current_id_quest = id_quest
		}
	})
}

function editModule(obj)
{
	var modName = $("#moduleName").val()
	
	$.ajax({
		type:'POST',
		url:BASE_URL+"courses/edit_module",
		data:{
			id_module:current_id_mod,
			name: modName
		},
		success: function() {
			$(`div[data-id_module=${current_id_mod}]`).find(".moduleName").html(modName)
			$("#editModule").modal("toggle")
		}
	})
}

function editVideo(obj)
{
	var title = $("#edit_classType_video_title").val()
	var desc = $("#edit_classType_video_description").val()
	var url = $("#edit_classType_video_url").val()
	
	$.ajax({
		type:'POST',
		url:BASE_URL+"courses/edit_video",
		data:{
			id_video:current_id_video,
			title:title,
			description:desc,
			url:url
		},
		success: function() {
			// Updates new class title
			$(`.class_title[data-id_video=${current_id_video}]`).html(title)
			
			$("#modal_editVideo").modal("toggle")
		}
	})
}

function editQuest(obj)
{
	var question = $("#edit_classType_quest_name").val()
	
	$.ajax({
		type:'POST',
		url:BASE_URL+"courses/edit_quest",
		data:{
			id_quest:current_id_quest,
			question:question,
			op1:$("#edit_classType_quest_q1").val(),
			op2:$("#edit_classType_quest_q2").val(),
			op3:$("#edit_classType_quest_q3").val(),
			op4:$("#edit_classType_quest_q4").val(),
			answer:$("#edit_answer").val()
		},
		success: function() {
			// Updates new class title
			$(`.class_title[data-id_quest=${current_id_quest}]`).html(question)
			
			$("#modal_editQuest").modal("toggle")
		}
	})
}

function addClass(obj)
{
	var option = $("input[name='classType']:checked").val()
	var type = $("input[name='classType']:checked").val()
	
	if (type === "video") {
		var title = $("#classType_video_title").val()
		var desc = $("#classType_video_description").val()
		var video_url = $("#classType_video_url").val()

		$.ajax({
			type:'POST',
			url:BASE_URL+"courses/add_class_video",
			data:{
				id_module:current_id_mod,
				id_course:current_id_course,
				title:title, 
				description:desc,
				url:video_url
			},
			success: function(id) {
				$("#addClass").modal("toggle")
				
				var newClass = `
					<div class="class">
						<h5 class="class_title" data-id_video="${id}">${title}</h5>
						<div class="class_actions">
							<button class="btn btn-warning" onclick="show_editQuest(this,${id})">Edit</button>
							<button class="btn btn-danger" onclick="deleteClass(this,${id})">Delete</button>
						</div>
					</div>
				` 
				
				$(`div[data-id_module=${current_id_mod}]`).find(".classes").append(newClass)
			}
		})
	} else {
		var quest_name = $("#classType_quest_name").val()
		var q1 = $("#classType_quest_q1").val()
		var q2 = $("#classType_quest_q2").val()
		var q3 = $("#classType_quest_q3").val()
		var q4 = $("#classType_quest_q4").val()
		var ans = $("#answer").val()
		
		$.ajax({
			type:'POST',
			url:BASE_URL+"courses/add_class_quest",
			data:{
				id_module:id_mod,
				id_course:id_course,
				question:quest_name, 
				q1:q1,
				q2:q2,
				q3:q3,
				q4:q4,
				answer:ans
			},
			success: function(id) {
				$("#addClass").modal("toggle")
				
				var newClass = `
					<div class="class">
						<h5 class="class_title" data-id_quest="${id}">${quest_name}</h5>
						<div class="class_actions">
							<button class="btn btn-warning" onclick="show_editQuest(this,${id})">Edit</button>
							<button class="btn btn-danger" onclick="deleteClass(this,${id})">Delete</button>
						</div>
					</div>
				` 
				
				$(obj).closest(".module").find(".classes").append(newClass)
			}
		})
	}
}