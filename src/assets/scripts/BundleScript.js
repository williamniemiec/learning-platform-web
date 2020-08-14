function buy(id_bundle)
{
	$.ajax({
		type:"POST",
		url:BASE_URL + "bundle/buy",
		data:{id_bundle},
		success:(link) => {
			window.location.href=link
		}
	})
}