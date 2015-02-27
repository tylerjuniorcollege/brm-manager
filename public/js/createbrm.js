var resultUsersId = '#user-';
var currentUserId = 0;
var userSearch = '/user/search?q=';

var userSearchSource = new Bloodhound({
	datumTokenizer: Bloodhound.tokenizers.obj.whitespace('email'),
 	queryTokenizer: Bloodhound.tokenizers.whitespace,
 	remote: {
 		url: userSearch + '%QUERY'
 	}
});

userSearchSource.initialize();
$('#requestuser').typeahead({
  hint: true,
  highlight: true,
  minLength: 1
},
{
	displayKey: 'email',
	source: userSearchSource.ttAdapter()
});

$('#searchUsers').keyup(function() {
	var query = $(this).val();
	$.getJSON(userSearch + $(this).val(), function(data) {
		if(data.length > 0 && query.length > 0) {
			$("#commonUsers").hide();
			$("#addNewUser").hide();
			$("#searchUsersResults").show();
			$.each(data, function() {
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
	}).html(username + '<button type="button" class="btn btn-danger btn-xs pull-right remove-user" id="removeUser-' + userid + '"><i class="fa fa-times"></i></button><span class="pull-right" id="userPerm-' + userid + '"></span>').appendTo($('#currentUsers')).hide();

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

$('#addUserSubmit').on('click', function() {
	var postData = {
		firstname: '',
		lastname: '',
		email: '',
		permissions: 0
	};
	postData.firstname = $('#newFirst').val();
	postData.lastname = $('#newLast').val();
	postData.email = $('#searchUsers').val();

	if(postData.email.length === 0) {
		// Stop processing and throw an error.
	}

	if(postData.firstname.length === 0) {
		postData.firstname = null;
	}

	if(postData.lastname.length === 0) {
		postData.lastname = null;
	}

	var username = postData.firstname + " " + postData.lastname + " &lt;" + postData.email + "&gt;";

	var permDisplay = [];
	$('.selectUserPermCheck:checked').each(function() {
		postData.permissions = postData.permissions + parseInt($(this).val());
		permDisplay.push($(this.id.replace(/newPerm/g, '#permLabel')).clone().removeAttr('id'));
	});

	// Now we submit the data and wait for an ID# to return.
	$.post('/user/add', postData, function(retData) {
		var userid = retData.userid;

			// add the user to list, but hide them.
		$('<li>').attr({
			id: 'userList-' + userid,
			class: 'list-group-item'
		}).html(username + '<span class="pull-right" id="userPerm-' + userid + '"></span>').appendTo($('#currentUsers'));

		$.each(permDisplay, function() {
			$(this).appendTo('#userPerm-' + userid);
		});
	
		// add hidden user to the form.
		$('<input type="hidden">').attr({
			name: 'users[]',
			id: 'input-user-' + userid,
			value: userid
		}).appendTo('form');

		var permname = 'permissions[' + userid + ']';
		$('<input type="hidden">').attr({
			id: 'input-perm-user-' + userid,
			name: permname,
			value: retData.permissions
		}).appendTo('form');

		// Clear the data from the form.
		$('#searchUsers, #newFirst, #newLast').val('');
		$('.selectUserPermCheck').attr('checked', false);

		$("#addNewUser").hide();
		$("#commonUsers").show();
	});
});

$(document).on('click', '.remove-user', function() {
	var userid = this.id.replace(/removeUser-/g, '');
	removeUser(userid);
});

$('#previewBtn').on('click', function() {
	var content = $('#contentInput').val();
	if(content.length > 0) {
		$('#previewContent').attr('src', 'data:text/html;charset=utf-8,' + encodeURIComponent(content));
		$('#previewContent').show();
		$('#editBtn').show();
		$(this).hide();
		$('#contentInput').hide();
	}
});

$('#editBtn').on('click', function() {
	$('#contentInput').show();
	$('#previewBtn').show();
	$(this).hide();
	$('#previewContent').hide();
});

$('#requesteddate').datetimepicker();

function removeUser(id) {
	//$('#user-' + id).remove();
	$('#userList-' + id).remove();
	$('form #input-user-' + id).remove();
	$('form #input-perm-user-' + id).remove();
}