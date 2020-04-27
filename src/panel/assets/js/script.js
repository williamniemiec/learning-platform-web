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
		url:BASE_URL+"ajax/delete_module",
		data:{id_module:id_module}
	})
}

function deleteClass(obj,id_class)
{
	$(obj).closest(".class").fadeOut("fast")
	
	$.ajax({
		type:'POST',
		url:BASE_URL+"ajax/delete_class",
		data:{id_class:id_class}
	})
}

function addModule(obj)
{
	var name = $("#modalAdd").val()
	var id_course = $(obj).closest(".modules").attr("data-idModule")
	
	$.ajax({
		type:'POST',
		url:BASE_URL+"ajax/add_module",
		data:{id_course:id_course, name:name},
		success: function(id) {
			if (id == -1) {
				alert("Error!")
				return
			}
			var newModule = `
				<div class="module">
					<button class="btn btn-danger" onclick="deleteModule(this,${id})">Delete module</button>
					<h3>${name}</h3>
				</div>
			`
				
			$(obj).closest(".modal").modal("toggle")
			$(obj).closest(".modules").append(newModule)
		}
	})
}

var current_id_mod;
var current_id_course;

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
	current_id_course = $(obj).closest(".modules").attr("data-idCourse")
	$("#addClass").modal("toggle")
}

function show_editModule(obj, id_module)
{
	current_id_mod = id_module
	var modName = $(`div[data-moduleId=${current_id_mod}]`).find(".moduleName").html()
	
	$("#moduleName").val(modName)
	$("#editModule").modal("toggle")
}

function editModule(obj)
{
	var modName = $("#moduleName").val()
	
	$.ajax({
		type:'POST',
		url:BASE_URL+"ajax/edit_module",
		data:{
			id_module:current_id_mod,
			name: modName
		},
		success: function() {
			$(`div[data-moduleId=${current_id_mod}]`).find(".moduleName").html(modName)
			$("#editModule").modal("toggle")
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
			url:BASE_URL+"ajax/add_class_video",
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
						<h5>${title}</h5>
						<button class="btn btn-danger" onclick="deleteClass(this,${id})">Delete</button>
					</div>
				` 
				
				$(`div[data-moduleId=${current_id_mod}]`).find(".classes").append(newClass)
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
			url:BASE_URL+"ajax/add_class_quest",
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
						<h5>${quest_name}</h5>
						<button class="btn btn-danger" onclick="deleteClass(this,${id})">Delete</button>
					</div>
				` 
				
				$(obj).closest(".module").find(".classes").append(newClass)
			}
		})
	}
	
}