//-------------------------------------------------------------------------
//        Global variables
//-------------------------------------------------------------------------
var current_id_student = -1


//-------------------------------------------------------------------------
//        Methods
//-------------------------------------------------------------------------
function show_editStudent(id_student)
{
	const div_bundles = $("#edit_bundles")
	let bundles = new Map()
	let cbx_bundles = ''
	current_id_student = id_student
	
	// Gets student info
	$.ajax({
		type:'POST',
		url:BASE_URL+"students/getStudent",
		data:{id_student: current_id_student},
		dataType:'json',
		success: function(student) {
			$("#edit_student_name").val(student.name)
			if (student.genre == 0)
				$("#edit_student_male").attr("checked", 'checked')
			else
				$("#edit_student_female").attr("checked", 'checked')
			$("#edit_student_birthdate").val(student.birthdate.split("/").join("-"))
			$("#edit_student_email").val(student.email)
			$("#edit_student_pass").val("")

		}
	})
	
	// Gets all bundles
	$.ajax({
		type:'GET',
		url:BASE_URL + "bundles/getAll",
		async:false,
		success: (allBundles) => {
			allBundles = JSON.parse(allBundles)
			
			for (let bundle of allBundles) {
				bundles.set(bundle.name, {first: false, second: bundle})
			}
		}
	})

	// Gets bundles that a student has
	$.ajax({
		type:'POST',
		url:BASE_URL+"students/getBundles",
		data: {id_student: id_student},
		async:false,
		success: function(studentBundles) {
			studentBundles = JSON.parse(studentBundles)
			div_bundles.html("")

			for (let bundle of studentBundles) {
				bundles.get(bundle.name).first = true
			}
		}
	})
	
	// Displays bundles
	for (const [key, value] of bundles.entries()) {
		cbx_bundles = `
			<input id="edit_b${value.second.id}" type="checkbox" ${value.first ? "checked disabled" : "name='bundles[]' value='"+value.second.id+"' data-name='"+value.second.name+"'"} />
    		<label for="edit_b${value.second.id}">${value.second.name}</label><br />
		`
		
		div_bundles.append(cbx_bundles)
	}
	
	$("#editStudent").modal("toggle")
}

function editStudent(obj)
{
	const name = $("#edit_student_name").val()
	const pass = $("#edit_student_pass").val()
	
	if (pass == "") {
		$.ajax({
			type:'POST',
			url:BASE_URL+"students/editStudent",
			data: {
				id_student:current_id_student,
				name: name,
				genre: $(obj).closest(".modal-content").find("input[name='genre']:checked").val(),
				birthdate: $("#edit_student_birthdate").val(),
				email: $("#edit_student_email").val()
			},
			success: function() {
				edit_student_success(obj)
			}
		})
	} 
	else {
		$.ajax({
			type:'POST',
			url:BASE_URL+"students/editStudent",
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
	const student_td = $(`tr[data-id_student=${current_id_student}]`)
	const bundles = $(obj).closest(".modal-content").find("input:checkbox[name='bundles[]']:checked")
	const tr_student = $(`tr[data-id_student='${current_id_student}']`)
	const student_bundles = tr_student.find(".student_bundles")
	
	
	for (let bundle of bundles) {
		// Add bundle in student purchases
		$.ajax({
			type:'POST',
			url:BASE_URL+"students/addStudentBundle",
			data:{
				id_student:current_id_student,
				id_bundle:bundle.value
			}
		})
	console.log(bundle)
		if (isBlank(student_bundles.html()))
			student_bundles.html(" | " + $(bundle).attr('data-name') + " | ")
		else
			student_bundles.html(student_bundles.html() + $(bundle).attr('data-name') + " | ")
	}
	
	// Updates bundles counter
	const td_totalBundles = tr_student.find(".student_totalBundles")
	td_totalBundles.html(bundles.length)

	student_td.find(".student_name").val(name)
	student_td.find(".student_bundles").val(bundles)
}

function deleteStudent(obj, id)
{	
	$.ajax({
		type:'POST',
		url:BASE_URL+"students/deleteStudent",
		data:{id_student:id},
		success: function() {
			$(obj).closest("tr").fadeOut("fast");
		}
	})
}

function isBlank(str) {
    return (!str || /^\s*$/.test(str));
}