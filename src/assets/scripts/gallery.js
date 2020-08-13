$(() => {
	$.each($(".gallery"), (index, item) => {
		const items = $(item).find(".gallery-item")
		const marginLeft = items.css("margin-left").substring(0, items.css("margin-left").length-2)
		const marginRight = items.css("margin-right").substring(0, items.css("margin-right").length-2)
		const itemWidth = items.width() + marginLeft + marginRight
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
	})
})