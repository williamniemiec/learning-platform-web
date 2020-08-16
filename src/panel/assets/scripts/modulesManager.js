$(() => {
	// Hides error message
	$("#includeClasses_error").fadeToggle("fast")
})

/**
 * Displays include courses modal.
 */
function show_updateModules(id_module)
{
	// Displays classes that module has
	let classesVideo = new Map()
	let classesQuestionnaire = new Map()
	
	// Gets all courses
	$.ajax({
		type:'GET',
		url:BASE_URL + "classes/getAll",
		async:false,
		success:(allClasses) => {
			allClasses = JSON.parse(allClasses)
			
			// Parses video classes
			for (let i in allClasses.videos) {
				classesVideo.set(allClasses.videos[i].title, {first: false, second: allClasses.videos[i]})
			}
			
			// Parses questionnaire classes
			for (let i in allClasses.questionnaires) {
				classesQuestionnaire.set(allClasses.questionnaires[i].question, {first: false, second: allClasses.questionnaires[i]})
			}
		}
	})

	// Gets classes that the module has
	$.ajax({
		type:'GET',
		url:BASE_URL + "modules/getClasses",
		data:{id_module},
		async:false,
		success:(moduleClasses) => {
			if (moduleClasses == '')
				return
			
			moduleClasses = JSON.parse(moduleClasses)
			
			for (let i in moduleClasses) {
				if (moduleClasses[i].type == 'video') {
					classesVideo.get(moduleClasses[i].title).first = true
					classesVideo.get(moduleClasses[i].title).second.class_order = moduleClasses[i].class_order
				}
				else {
					classesQuestionnaire.get(moduleClasses[i].question).first = true
					classesQuestionnaire.get(moduleClasses[i].question).second.class_order = moduleClasses[i].class_order
				}
			}
		}
	})
	
	let videosHTML = ""
	let questionnairesHTML = ""
	const totClasses = classesVideo.size + classesQuestionnaire.size

	
	// Builds list of video classes
	for (const [key, value] of classesVideo.entries()) {
		let selectOptions = ''
		let oldIndex = -1
		
		
		for (let i = 1; i <= totClasses; i++) {
			selectOptions += `
				<option value='${i}' ${i == value.second.class_order ? "selected" : ""}>${i}</option>
			`
			
			if (i == value.second.class_order)
				oldIndex = i
		}
		
		videosHTML += `
			<tr data-old-order="${oldIndex}" data-id_module="${value.second.module.id}">
				<td class="name">
					<input type="checkbox" name="classes[]" value="video" ${value.first ? "checked disabled" : ""} />
					${value.second.title}
				</td>
				<td><span class="class-type">${value.second.module.id == id_module ? "<b>" + value.second.module.name + "</b>" : value.second.module.name}</span></td>
				<td>
					<select>
						${selectOptions}
					</select>
				</td>
			</tr>
		`
	}
	
	// Builds list of questionnaire classes
	for (const [key, value] of classesQuestionnaire.entries()) {
		let selectOptions = ''
		let oldIndex = -1
		
		
		for (let i = 1; i <= totClasses; i++) {
			selectOptions += `
				<option value='${i}' ${i == value.second.class_order ? "selected" : ""}>${i}</option>
			`
			
			if (i == value.second.class_order)
				oldIndex = i
		}
		
		questionnairesHTML += `
			<tr data-old-order="${oldIndex}" data-id_module="${value.second.module.id}">
				<td class="name">
					<input type="checkbox" name="classes[]" value="questionnaire" ${value.first ? "checked disabled" : ""} />
					${value.second.question}
				</td>
				<td><span class="class-type">${value.second.module.id == id_module ? "<b>" + value.second.module.name + "</b>" : value.second.module.name}</span></td>
				<td>
					<select>
						${selectOptions}
					</select>
				</td>
			</tr>
		`
	}
	
	$("#include_classes").html(`
		<div id="classes-video">
			<h2>Video classes</h2>
			<table>
				<thead>
					<th>Name</th>
					<th>Module</th>
					<th>Order</th>
				</thead>
				<tbody>
					${videosHTML}
				</tbody>
					
			</table>
		</div>
		<div id="classes-questionnaire">
			<h2>Questionnaire classes</h2>
			<table>
				<thead>
					<th>Name</th>
					<th>Module</th>
					<th>Order</th>
				</thead>
				<tbody>
					${questionnairesHTML}
				</tbody>
			</table>
		</div>
	`)
	
	// Displays classes
	$("#includeClasses").modal("toggle")
}


/**
 * Updates courses from a course.
 */
function update_module(id_module)
{
	let classes = []
	
	// Gets selected classes
	$("input:checkbox[name='classes[]']:checked").each((k, v) => {
		classes.push({
			type: v.value, 
			id_module:$(v).closest("tr").attr("data-id_module"),
			order_old:$(v).closest("tr").attr("data-old-order"), 
			order_new: $(v).closest("tr").find("select").val()
		})
	})
	// Updated database
	$.ajax({
		type:'POST',
		url:BASE_URL + "modules/setClasses",
		data:{id_module, classes},
		success:() => {
			window.location.reload()
			$("#includeClasses").modal("toggle")
		},
		error:(msg) => { 
			includeClasses_error(msg.responseText) }
	})
}

function includeClasses_error(msg)
{
	$("#error-msg").html(msg)
	$("#includeClasses_error").fadeToggle("fast")
}

