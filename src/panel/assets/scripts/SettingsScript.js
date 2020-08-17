$(() => {
	// Hides error msg
	$("#changePassword_error").fadeToggle("fast")
})

function update_password(obj)
{
	const newPassword = $("#new_password").val()
	
	$.ajax({
		type:"POST",
		url:BASE_URL+"settings/update_password",
		data: { new_password: newPassword },
		success: function(response) {
			if (response == 1) {
				$(obj).closest(".modal").modal("toggle")
				document.location.reload()
			} else {
				$("#changePassword_error").show("fast")
			}
		},
		error: (e) => { changePassword_error(e.responseText) }
	})
}

function changePassword_error(msg = '')
{
	$("#changePassword_error").fadeToggle("fast")
	
	if (msg != '')
		$("#error-msg").html(msg)
}