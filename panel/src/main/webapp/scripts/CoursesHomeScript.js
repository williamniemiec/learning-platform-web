//-------------------------------------------------------------------------
//        Ajax
//-------------------------------------------------------------------------
/**
 * Searches bundles.
 *
 * @param		object obj Search button 
 */
function search(obj)
{
	const text = $(obj).closest(".search-bar").find("input[type='text']").val()
	const type = $("input[name='filter']:checked").val()
	const order = $("#order").val()
	
	$.ajax({
		type:'POST',
		url:BASE_URL + "courses/search",
		data:{
			name:text,
			filter:{type, order}
		},
		success:(courses) => {
			courses = JSON.parse(courses)
			const resultArea = $("#courses")
			
			resultArea.fadeOut('fast')
			let resultHTML = ""
			
			for (let course of courses) {
				resultHTML += `
            		<tr>
						<td class="manager-table-logo"><img class="img img-responsive" src="${course.logo == '' ? BASE_URL + "../assets/img/default/noImage" : BASE_URL + "../assets/img/logos/courses/" + course.logo}" /></td>
            			<td><a href="${BASE_URL + "courses/edit/" + course.id}">${course.name}</a></td>
            			<td>${course.description}</td>
            			<td>${course.totalStudents}</td>
            			<td>${course.totalClasses}</td>
            			<td>${(course.totalLength / 60).toFixed(2)}h</td>
            			<td class="actions">
            				<a class="btn_theme" href="${BASE_URL + "courses/edit/" + course.id}">Edit</a>
            				<a class="btn_theme btn_theme_danger" href="${BASE_URL + "courses/delete/" + course.id}">Delete</a>
        				</td>
            		</tr>
				`
			}
			
			resultArea.html(resultHTML).fadeIn('fast')
		}
	})
}