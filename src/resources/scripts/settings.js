function update_profilePhoto(obj)
{
	var file = $("#profile_photo")[0].files
	
	var data = new FormData()
	data.append("photo", file[0])
	
	$.ajax({
		type:'POST',
		url:BASE_URL+"settings/update_profile_photo",
		data: data,
		contentType: false,
		processData: false,
		success: function() {
			$(obj).closest(".modal").modal("toggle")
			document.location.reload()
		}
	})
}

function update_password(obj)
{
	var newPassword = $("#new_password").val()
	var currentPassword = $("#current_password").val()
	
	$.ajax({
		type:"POST",
		url:BASE_URL+"settings/update_password",
		data: {
			new_password: newPassword,
			current_password: currentPassword
		},
		success: function(response) {
			if (response == 1) {
				$(obj).closest(".modal").modal("toggle")
				document.location.reload()
			} else {
				$("#changePassword_error").show("fast")
			}
		}
	})
}

function changePassword_error()
{
	$("#changePassword_error").fadeToggle("fast")
}