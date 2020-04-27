function deleteModule(obj,id_module)
{
	//alert(id_module)
	$(obj).closest(".module").fadeOut("fast")
	
	$.ajax({
		type:'POST',
		url:BASE_URL+"ajax/delete_module",
		data:{id_module:id_module}
	})
}

function deleteClass(obj,id_class)
{
	$(obj).closest(".class").fadeOut("fast")
	
	$.ajax({
		type:'POST',
		url:BASE_URL+"ajax/delete_class",
		data:{id_class:id_class}
	})
}