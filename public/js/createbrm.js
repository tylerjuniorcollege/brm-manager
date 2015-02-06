var resultUsersId = '#user-';
var currentUserId = 0;

$('#searchUsers').keyup(function() {
	var query = $(this).val();
	$.getJSON('/user/search?q=' + $(this).val(), function(data) {
		if(data.data.length > 0 && query.length > 0) {
			$("#commonUsers").hide();
			$("#addNewUser").hide();
			$("#searchUsersResults").show();
			$.each(data.data, function() {
				if($(resultUsersId + this.id).length === 0) { // This doesn't exist
					$('<a>').attr({
						id: 'user-'+ this.id,
						class: 'user_action list-group-item',
						href: '#'
					}).html(this.firstname + " " + this.lastname + " &lt;" + this.email + "&gt;").appendTo($('#searchResults'));
				}
			});
		} else if(query.length > 0) {
			// This is a new user ... show new user form and clear the data.
			$("#commonUsers").hide();
			$("#searchUsersResults").hide();
			$("#addNewUser").show();
		} else if(query.length === 0) {
			$("#addNewUser").hide();
			$("#searchUsersResults").hide();
			$("#commonUsers").show();
		}
	});
});

$(document).on('click', '#commonUserResults .user_action, #searchResults .user_action', function() {
	var username = $(this).html();
	var userid = this.id.replace(/user-/g, '');
	
	$('#commonUsers').hide();
	$("#searchUsersResults").hide();
	$('#searchUsers').val('');
	$('#permUserSelect').html(username);
	$('#selectUserPermissions').show();
	currentUserId = userid;

	// add the user to list, but hide them.
	$('<li>').attr({
		id: 'userList-' + userid,
		class: 'list-group-item'
	}).html(username + '<span class="pull-right" id="userPerm-' + userid + '"></span>').appendTo($('#currentUsers')).hide();

	// add hidden user to the form.
	$('<input type="hidden">').attr({
		name: 'users[]',
		id: 'input-' + this.id,
		value: userid
	}).appendTo('form');

	return false;
});

$('#userPermissions').on('click', function() {
	// Calculate Permissions.
	var permInt = 0;

	$('.selectUserPermCheck:checked').each(function() {
		var permSpan = this.id.replace(/perm/g, '#permLabel');
		permInt = permInt + parseInt($(this).val());
		$(permSpan).clone().removeAttr('id').appendTo($('#userPerm-' + currentUserId));
	});

	// Now I Need to Assign The Hidden Element for the User.
	var permname = 'permissions[' + currentUserId + ']';
	$('<input type="hidden">').attr({
		id: 'input-perm-user-' + currentUserId,
		name: permname,
		value: permInt
	}).appendTo('form');

	$('#userList-' + currentUserId).show();
	$('.selectUserPermCheck').attr('checked', false);
	currentUserId = 0;
	$('#selectUserPermissions').hide();
	$('#commonUsers').show();
});

$('#cancelUserPerm').on('click', function() {
	$('.selectUserPermCheck').attr('checked', false);

	// Remove user items from being submitted.
	removeUser(currentUserId);

	currentUserId = 0;
	$('#selectUserPermissions').hide();
	$('#commonUsers').show();
});

$('#cancelAddUser').on('click', function() {
	$('.selectUserPermCheck').attr('checked', false);

	$('#searchUsers').val('');
	$('#newFirst').val('');
	$('#newLast').val('');

	$("#addNewUser").hide();
	$("#searchUsersResults").hide();
	$("#commonUsers").show();
});

function removeUser(id) {
	$('user-' + id).remove();
	$('input-user-' + id).remove();
	$('input-perm-user-' + id).remove();
}