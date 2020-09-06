$(() => {
	// Hides error message
	$("#includeCourses_error").fadeToggle("fast")
})

/**
 * Displays include courses modal.
 */
function show_updateBundle(id_bundle)
{
	// Displays courses that bundle has
	let courses = new Map()
	
	// Gets all courses
	$.ajax({
		type:'GET',
		url:BASE_URL + "courses/getAll",
		async:false,
		success:(allCourses) => {
			allCourses = JSON.parse(allCourses)
			
			for (let i in allCourses) {
				courses.set(allCourses[i].name, {first: false, second: allCourses[i]})
			}
		}
	})
	
	// Gets courses that the bundle has
	$.ajax({
		type:'GET',
		url:BASE_URL + "bundles/getCourses",
		data:{id_bundle},
		async:false,
		success:(bundleCourses) => {
			if (bundleCourses == '')
				return
			
			bundleCourses = JSON.parse(bundleCourses)
			
			for (let i in bundleCourses) {
				courses.get(bundleCourses[i].name).first = true
			}
		}
	})
	
	let bundlesHTML = ""

	for (const [key, value] of courses.entries()) {
		bundlesHTML += `
			<li><input type="checkbox" name="courses[]" value="${value.second.id}" ${value.first ? "checked" : ""} />${value.second.name}</li>
		`
	}
	
	$("#include_courses").html(`
		<ul>
			${bundlesHTML}
		</ul>
	`)
	// Displays courses
	$("#includeCourses").modal("toggle")
}

/**
 * Updates courses from a bundle.
 */
function update_bundle(id_bundle)
{
	console.log($("input:checkbox[name='courses[]']:checked"))
	let courseIds = []
	
	// Gets selected courses
	$("input:checkbox[name='courses[]']:checked").each((k, v) => {
		courseIds.push(v.value)
	})
	
	// Updated database
	$.ajax({
		type:'POST',
		url:BASE_URL + "bundles/setCourses",
		data:{id_bundle, courseIds},
		success:() => {
			window.location.reload()
			$("#includeCourses").modal("toggle")
		},
		error:() => { $("#includeCourses_error").fadeToggle("fast") }
	})
}
