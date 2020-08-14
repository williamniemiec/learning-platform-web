import formatDate, { americanFormatter } from './DataUtil'


/**
 * Responsible for handling notebook pagination.
 */
class NotebookNavigator
{
	//-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
	/**
	 * @param		int limit Maximum results that are displayed
	 * @param		string requestURL URL that will be used in ajax request
	 * @param		object data [Optional] Ajax request data
	 */
	constructor(limit, requestURL, data = {})
	{
		this.limit = limit
		this.requestURL = requestURL
		this.data = data
	}
	
	
	//-------------------------------------------------------------------------
    //        Methods
    //-------------------------------------------------------------------------
	/**
	 * Responsible for handling notebook pagination.
	 *
	 * @param		String action 'af' for go forward, 'bef' to move backward and 
	 * 'go' to go to a specific page
	 * @param		int goto Page to go
	 */
	navigate(action, goto = -1)
	{
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
			url:BASE_URL + this.requestURL,
			data:{index, limit:this.limit, ...this.data},
			success:(notes) => {
				notes = JSON.parse(notes)
				let notesHTML = ''
				
				$(".notebook").html('').fadeOut('fast')
				
				for (let i in notes) {
					notesHTML += `
						<li class="notebook-item">
							<div class="notebook-item-header">
								<a href="${BASE_URL + "notebook/open/" + notes[i].id}">${notes[i].title}</a>
							</div>
							<div class="notebook-item-footer">
								<div class="notebook-item-class">${notes[i].class.title}</div>
								<div class="notebook-item-date">${formatDate(new Date(notes[i].date), americanFormatter)}</div>
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
}


export default NotebookNavigator
