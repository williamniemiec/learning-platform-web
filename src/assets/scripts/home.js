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
		url:BASE_URL + "bundle/search",
		data:{
			name:text,
			filter:{type, order}
		},
		success:(bundles) => {
			bundles = JSON.parse(bundles)
			$("#bundle-search-results").fadeOut('fast')
			const resultArea = $("#bundle-search-results .gallery-items")
			let resultHTML = ""
			
			resultArea.html('').fadeOut('fast')
			
			for (let i in bundles) {
				resultHTML += `
					<button	class="gallery-item" 
        					onClick="window.location.href='${BASE_URL + "bundle/open/" + bundles[i].bundle.id}'"
        			>
        				<img	class="gallery-item-thumbnail" 
        						src="${bundles[i].bundle.logo.length == 0 ? 
									BASE_URL + "assets/img/noImage.png" : 
									BASE_URL + "assets/img/logos/bundles/" + bundles[i].bundle.logo}" 
						/>
        				<div class="gallery-item-content">
            				<div class="gallery-item-header">${bundles[i].bundle.name}</div>
            				<div class="gallery-item-body"><p>${bundles[i].bundle.description}</p></div>
            				<div class="gallery-item-footer">
								${bundles[i].has_bundle ? "Purchased" : bundles[i].bundle.price}
        					</div>
        				</div>
        			</button>
				`
			}
			
			resultArea.html(resultHTML).fadeIn('fast')
		}
	})
}