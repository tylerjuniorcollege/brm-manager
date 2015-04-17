$('#user-form').submit(function() {
	var userPerm = 0;
	$('.userPermission:checked').each(function() {
		userPerm += parseInt($(this).val());
	});

	$('#userPermissions').val(userPerm);
	return;
});