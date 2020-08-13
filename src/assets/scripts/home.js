$(() => {
	const items = $(".gallery-item")
	const itemWidth = items.width()
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