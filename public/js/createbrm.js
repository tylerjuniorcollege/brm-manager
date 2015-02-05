var resultUsersId = '#user-';

$('#searchUsers').keyup(function() {
	$.getJSON('/user/search?q=' + $(this).val(), function(data) {
		if(data.data.length > 0) {
			$("#searchResults").empty();
			$('#searchResHeading').html('Search Results');
			$.each(data.data, function() {
				if($(resultUsersId + this.id).length == 0) { // This doesn't exist
					$('<a>').attr({
						id: 'user-'+ this.id,
						class: 'user_action list-group-item',
						href: '#'
					}).html(this.firstname + " " + this.lastname + " &lt;" + this.email + "&gt;").appendTo($('#searchResults'));
				}
			});
		} else {
			// This is a new user ... show new user form and clear the data.

		}
	});
});

$('#searchResults').on('click', '.user_action', function() {
	var username = $(this).html();
	var userid = this.id.replace(/user-/g, '');
	
	$('#commonUsers').hide();
	$('#searchUsers').val('');
	$('#permUserSelect').html(username);
	$('#selectUserPermissions').show();
	$('#currUser').html(userid);

	// add the user to list, but hide them.
	$('<li>').attr({
		id: this.id,
		class: 'list-group-item'
	}).html(username + '<span class="pull-right" id="userPerm-' + userid + '"></span>').appendTo($('#currentUsers')).hide();

	// add hidden user to the form.
	$('<input type="hidden">').attr({
		name: 'users[]',
		value: userid
	}).appendTo('form');

	return false;
});

$('#userPermissions').on('click', function() {

});