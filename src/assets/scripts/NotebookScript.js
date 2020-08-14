function navigation(obj, action, goto = -1, limit = 4)
{
	//const currentIndex = $(".pagination .active").attr("data-index")
	let totPages = $(".page-item").length - 2
	let oldIndex = parseInt($(".pagination .active").attr("data-index")) 
	let index = oldIndex

	switch (action) {
		case 'bef':
			if (index - 1 >= 1) {
				index -= 1
			}
			break
		case 'af':
			if (index + 1 <= totPages) {
				index += 1
			}
			break
		case 'go':
			if (goto >= 1 && goto <= totPages) {
				index = parseInt(goto)
			}
			break
	}
	
	$.ajax({
		type:"GET",
		url:BASE_URL + "notebook/getAll",
		data:{index, limit},
		success:(notes) => {
			notes = JSON.parse(notes)
			notesHTML = ''
			
			$(".notebook").html('').fadeOut('fast')
			
			for (let i in notes) {
				notesHTML += `
					<li class="notebook-item">
						<div class="notebook-item-header">
							<a href="${BASE_URL + "notebook/open/" + notes[i].id}">${notes[i].title}</a>
						</div>
						<div class="notebook-item-footer">
							<div class="notebook-item-class">${notes[i].class.title}</div>
							<div class="notebook-item-date">${formatDate(new Date(notes[i].date))}</div>
						</div>
    				</li>
				`
			}
			
			// Updates pagination buttons
			const paginationBefore = $(".page-item:first-of-type")
			const paginationAfter = $(".page-item:last-of-type")
			
			paginationBefore.removeClass("disabled")
			paginationAfter.removeClass("disabled")

			if (oldIndex != index) {
				$(`.page-item[data-index='${oldIndex}']`).removeClass('active')
			
				$(`.page-item[data-index='${index}']`).addClass('active')
			}
			
			// Updates before and after buttons
			if (index == totPages) {
				paginationAfter.addClass("disabled")
			}
			
			if (index == 1) {
				paginationBefore.addClass("disabled")
			}
			
			// Displays results
			$(".notebook").html(notesHTML).fadeIn('fast')
		}
	})
}

function formatDate(date)
{
    let dd = date.getDate()
    let mm = date.getMonth()+1
    const yyyy = date.getFullYear()

    if(dd<10) {dd='0'+dd}
    if(mm<10) {mm='0'+mm}
    
	date = mm+'/'+dd+'/'+yyyy+' '+date.getHours()+':'+date.getMinutes()+':'+date.getSeconds()
    
	return date
 }
