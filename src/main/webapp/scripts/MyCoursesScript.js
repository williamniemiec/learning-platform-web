import NotebookNavigator from './NotebookNavigator.js'


/**
 * Searches courses that a student has.
 *
 * @param		object obj Search button 
 */
function search(obj)
{
	const text = $(obj).closest(".search-bar").find("input[type='text']").val()
	
	$.ajax({
		type:"POST",
		url:BASE_URL + "courses/search",
		data:{text},
		success:(json) => {
			const courses = JSON.parse(json)
			let coursesHTML = ""
			console.log(courses)
			
			for (let index in courses) {
				coursesHTML += `
					<button	class="course" 
        					onClick="window.location.href='${BASE_URL + "courses/open/" + courses[index].course.id}'"
        			>
            			<!-- Course information -->
        				<img	class="img img-responsive" 
        						src="${courses[index].course.logo == "" ? 
									BASE_URL + "src/main/webapp/images/default/noImage.png" : 
									BASE_URL + "src/main/webapp/images/logos/courses/" + courses[index].course.logo}" 
        				/>
            			<h2>${courses[index].course.name}</h2>
            			<p>${courses[index].course.description}</p>                			
            			
            			<div class="course_info">
            				<span class="course_watchedClasses">
            					&#128249;
            					${courses[index].total_classes_watched} / 
            					${courses[index].course.total_classes}
            				</span>
            				<span class="course_length">
            					&#128337;
								${courses[index].course.total_length == 0 ? "0/0" :
				               		(courses[index].total_length_watched / 60).toFixed(2) 
        					        + "h / " 
                                    + (courses[index].course.total_length / 60).toFixed(2) + "h"} 
    					    </span>
            			</div>
            			
            			<!-- Course progress -->
            			<div class="progress position-relative">
				`
				
				if (courses[index].course.total_classes == 0) {
					coursesHTML += `
						<div class="progress-bar bg-success" style="width:0%"></div>
    					<small class="justify-content-center d-flex position-absolute w-100">0%</small>
					`
				}
				else {
					coursesHTML += `
						<div	class="progress-bar bg-success" 
        						style="width:${Math.floor(courses[index].total_classes_watched / courses[index].course.total_classes * 100)}%"
						>
        				</div>
        				<small class="justify-content-center d-flex position-absolute w-100">
        			 		${Math.floor(courses[index].total_classes_watched / courses[index].course.total_classes * 100)}%
    				    </small>
					`
				}
				
				coursesHTML += `
						</div>
            		</button>
				`
			}
			
			$("#courses").fadeOut('fast').html(coursesHTML).fadeIn('fast')
		}
	})
}

/**
 * Responsible for handling notebook pagination.
 *
 * @param		String action 'af' for go forward, 'bef' to move backward and 
 * 'go' to go to a specific page
 * @param		int goto Page to go
 */
function navigate(action, goto = -1)
{
	let nav = new NotebookNavigator(4, "notebook/get_all")
	nav.navigate(action, goto)
}


// Exports
window.search = search
window.navigate = navigate
