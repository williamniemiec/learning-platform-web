var current_id_student = -1


function show_addStudent(obj)
{
	$("#addStudent").modal("toggle")
}

function addStudent(obj)
{
	var name = $("#add_student_name").val()
	
	$.ajax({
		type:"POST",
		url:BASE_URL+"students/add_student",
		data:{
			name: name,
			genre: $("input[name='genre']:checked").val(),
			birthdate: $("#add_student_birthdate").val(),
			email: $("#add_student_email").val(),
			password:$("#add_student_pass").val() 
		},
		success: function(id) {
			$("#addStudent").modal("toggle")
			
			var newStudent = `
				<tr data-id_student="${id}">
					<td class="student_name">${name}</td>
					<td class="student_courses"></td>
					<td class="student_totalCourses">0</td>
					<td class="actions">
						<button class="btn btn-warning" onclick="show_editStudent(this,${id})">Edit</button>
    					<button class="btn btn-danger" onclick="deleteStudent(this,${id})">Delete</button>
					</td>
				</tr>
			`
			
			// Clears fields of modal
			$("#add_student_name").val("")
			$("input[name='genre']").eq(0).attr("checked", "checked")
			$("#add_student_birthdate").val("")
			$("#add_student_email").val("")
			$("#add_student_pass").val("") 
			
			$("tbody").append(newStudent)
		}
	})
}

function show_editStudent(obj, id_student)
{
	current_id_student = id_student
	
	// Gets student info
	$.ajax({
		type:'POST',
		url:BASE_URL+"students/get_student",
		data:{id_student: current_id_student},
		dataType:'json',
		success: function(student) {
			$("#edit_student_name").val(student.name)
			if (student.genre == 0)
				$("#edit_student_male").attr("checked", 'checked')
			else
				$("#edit_student_female").attr("checked", 'checked')
			$("#edit_student_birthdate").val(student.birthdate.split(" ")[0])
			$("#edit_student_email").val(student.email)
			$("#edit_student_pass").val("")
		}
	})
	
	// Gets course info
	$.ajax({
		type:'POST',
		url:BASE_URL+"students/get_courses",
		dataType:'json',
		data: {id_student: id_student},
		success: function(courses) {
			var div_courses = $("#edit_courses")
			div_courses.html("")

			for (var course of courses) {
				if (course.hasCourse > 0) {
					var cbx_course = `
						<input id="edit_c${course.id}" type="checkbox" name="${course.name}" value="${course.id}" checked />
	            		<label for="edit_c${course.id}">${course.name}</label><br />
					`
				} else {
					var cbx_course = `
						<input id="edit_c${course.id}" type="checkbox" name="${course.name}" value="${course.id}" />
	            		<label for="edit_c${course.id}">${course.name}</label><br />
					`
				}
				
				div_courses.append(cbx_course)
			}
		}
	})
	
	$("#editStudent").modal("toggle")
}

function editStudent(obj)
{
	var name = $("#edit_student_name").val()
	var pass = $("#edit_student_pass").val()
	
	if (pass == "") {
		$.ajax({
			type:'POST',
			url:BASE_URL+"students/edit_student",
			data: {
				name: name,
				genre: $(obj).closest(".modal-content").find("input[name='genre']:checked").val(),
				birthdate: $("#edit_student_birthdate").val(),
				email: $("#edit_student_email").val()
			},
			success: function() {
				edit_student_success(obj)
			}
		})
	} else {
		$.ajax({
			type:'POST',
			url:BASE_URL+"students/edit_student",
			data: {
				name: name,
				genre: $(".modal-body").closest("input[name='genre']:selected").val(),
				birthdate: $("#edit_student_birthdate").val(),
				email: $("#edit_student_email").val(),
				password: $("#edit_student_pass").val()
			},
			success: function() {
				edit_student_success(obj)
			}
		})
	}
	
	$("#editStudent").modal("toggle")
}

function edit_student_success(obj) 
{
	var student_td = $(`tr[data-id_student=${current_id_student}]`)
	var courses = $(obj).closest(".modal-content").find("input[type='checkbox']:checked")
	var tr_student = $(`tr[data-id_student='${current_id_student}']`)
	var student_courses = tr_student.find(".student_courses")
	student_courses.html("")
	
	// Removes all relationships
	$.ajax({
		type:'POST',
		url:BASE_URL+"students/clear_student_course",
		data:{
			id_student:current_id_student
		}
	})
	
	for (var course of courses) {
		// Add course in student_course
		$.ajax({
			type:'POST',
			url:BASE_URL+"students/add_student_course",
			data:{
				id_student:current_id_student,
				id_course:course.value
			}
		})

		if (student_courses.html() == "")
			student_courses.html(student_courses.html() + course.name)
		else
			student_courses.html(student_courses.html() + ", " + course.name)
	}
	
	// Updates courses counter
	var td_totalCourses = tr_student.find(".student_totalCourses")
	td_totalCourses.html(courses.length)

	student_td.find(".student_name").val(name)
	student_td.find(".student_courses").val(courses)
}

function deleteStudent(obj,id)
{	
	$.ajax({
		type:'POST',
		url:BASE_URL+"students/delete_student",
		data:{id_student:id},
		success: function() {
			$(obj).closest("tr").fadeOut("fast");
		}
	})
}