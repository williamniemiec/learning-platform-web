$(() => {
	// Hides error message
	$("#includeModules_error").fadeToggle("fast")
})

/**
 * Displays include courses modal.
 */
function show_updateCourses(id_course)
{
	// Displays courses that bundle has
	let modules = new Map()
	
	// Gets all courses
	$.ajax({
		type:'GET',
		url:BASE_URL + "modules/getAll",
		async:false,
		success:(allModules) => {
			allModules = JSON.parse(allModules)
			
			for (let i in allModules) {
				modules.set(allModules[i].name, {first: false, second: allModules[i]})
			}
		}
	})
	
	// Gets modules that the course has
	$.ajax({
		type:'GET',
		url:BASE_URL + "courses/getModules",
		data:{id_course},
		async:false,
		success:(courseModules) => {
			if (courseModules == '')
				return
			
			courseModules = JSON.parse(courseModules)
			
			for (let i in courseModules) {
				modules.get(courseModules[i].name).first = true
				modules.get(courseModules[i].name).second.order = courseModules[i].order
			}
		}
	})
	
	let modulesHTML = ""

	for (const [key, value] of modules.entries()) {
		//const maxOrder = getMaxClassOrder(id_course, value.second.id_module)
		let selectOptions = ''

		
		for (let i = 1; i <= modules.size; i++) {
			selectOptions += `
				<option value='${i}' ${i == value.second.order ? "selected" : ""}>${i}</option>
			`
		}
		
		modulesHTML += `
			<li>
				<div class="form-group">
					<input type="checkbox" name="modules[]" value="${value.second.id_module}" ${value.first ? "checked" : ""} />
					${value.second.name}
				</div>
				<div class="form-group">
					<label>Order:</Label>
					<select>
						${selectOptions}
					</select>
				</div>
			</li>
		`
	}
	
	$("#include_modules").html(`
		<ul>
			${modulesHTML}
		</ul>
	`)
	// Displays courses
	$("#includeModules").modal("toggle")
}

/*function getMaxClassOrder(id_course, id_module)
{
	response = -1
	
	$.ajax({
		type:'GET',
		url:BASE_URL + "modules/getMaxOrderInCourse",
		data:{id_course, id_module},
		async:false,
		success:(maxOrder) => {
			response = maxOrder
		}
	})
	
	return response
}
*/
/**
 * Updates courses from a course.
 */
function update_course(id_course)
{
	let modules = []
	
	// Gets selected modules
	$("input:checkbox[name='modules[]']:checked").each((k, v) => {
		modules.push({id: v.value, order: $(v).closest("li").find("select").val()})
	})
	
	// Updated database
	$.ajax({
		type:'POST',
		url:BASE_URL + "courses/setModules",
		data:{id_course, modules},
		success:() => {
			window.location.reload()
			$("#includeModules").modal("toggle")
		},
		error:(msg) => { 
			includeModules_error(msg.responseText) }
	})
}

function includeModules_error(msg)
{
	$("#error-msg").html(msg)
	$("#includeModules_error").fadeToggle("fast")
}

