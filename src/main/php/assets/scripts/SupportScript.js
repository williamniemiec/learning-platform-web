/**
 * Searches support topics that a student has.
 *
 * @param		object obj Search button 
 */
function search(obj)
{
	const text = $(obj).closest(".search-bar").find("input[type='text']").val()
	const type = $("input[name='rdo-type']:checked").val()
	const id_category = $("#sel-category").val()

	$.ajax({
		type:"POST",
		url:BASE_URL + "support/search",
		data:{
			name:text,
			filter:{type, id_category}
		},
		success:(topics) => {
			topics = JSON.parse(topics)
			const topicsTable = $("#topics")
			let newTopicsTable = ''
			
			// Hides pagination
			$(".pagination").fadeOut('fast')
			
			for (let i in topics) {
				newTopicsTable += `
					<tr>
            			<td><a href="${BASE_URL + "support/open/" + topics[i].id}">${topics[i].title}</a></td>
            			<td>${topics[i].category.name[0].toUpperCase() + topics[i].category.name.substring(1).toLowerCase()}</td>
            			<td>${topics[i].date}</td>
            			<td>${topics[i].closed ? "Closed" : "Open"}</td>
            		</tr>
				`
			}
			
			topicsTable.html('').fadeOut('fast')
			topicsTable.html(newTopicsTable).fadeIn('fast')
		}
	})
}