$(() => {
	setInterval(update_galleries, 500)
})


function update_galleries()
{
	$.each($(".gallery"), (index, item) => {
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
	})
}