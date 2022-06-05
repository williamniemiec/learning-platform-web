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
			$("#bundle-search-results").fadeIn('fast')
			const resultArea = $("#bundle-search-results .gallery-items-area")
			let resultHTML = ""
			resultArea.html('').fadeOut('fast')
			
			for (let i in bundles) {
				resultHTML += `
					<button	class="gallery-item" 
        					onClick="window.location.href='${BASE_URL + "bundle/open/" + bundles[i].bundle.id}'"
        			>
        				<img	class="gallery-item-thumbnail" 
        						src="${bundles[i].bundle.logo.length == 0 ? 
									BASE_URL + "assets/img/default/noImage.png" : 
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
			
			update_gallery($("#bundle-search-results").find(".gallery"))
			
			resultArea.html(resultHTML).fadeIn('fast')
		}
	})
}


function update_gallery(gallery)
{
	const item = gallery
	const items = $(item).find(".gallery-item")
		
	if (items.length > 0) {
		const item = $(items[0])
		const marginLeft = item.css("margin-left").substring(0, item.css("margin-left").length-2)
		const marginRight = item.css("margin-right").substring(0, item.css("margin-right").length-2)
		const paddingLeft = item.css("padding-left").substring(0, item.css("padding-left").length-2)
		const paddingRight = item.css("padding-right").substring(0, item.css("padding-right").length-2)
		const itemWidth = item.width() + parseInt(marginLeft) + parseInt(marginRight) + parseInt(paddingLeft) + parseInt(paddingRight)
		const totItems = items.length

		$(".gallery-items-area").css("width", itemWidth * totItems)
		$(".gallery-control-right").click((e) => {
			const galleryArea = $(e.target).closest(".gallery").find(".gallery-items-area")
			const currentMargin = galleryArea.css("margin-left")
		
			if (parseInt(currentMargin) - itemWidth > - (totItems * itemWidth) / 2)
				galleryArea.css("margin-left", (parseInt(currentMargin) - itemWidth) + "px")
		})
		
		$(".gallery-control-left").click((e) => {
			const galleryArea = $(e.target).closest(".gallery").find(".gallery-items-area")
			const currentMargin = galleryArea.css("margin-left")
			
			if (parseInt(currentMargin) + itemWidth > 0)
				galleryArea.css("margin-left", "0px")
			else
				galleryArea.css("margin-left", (parseInt(currentMargin) + itemWidth) + "px")
		})
	}
}