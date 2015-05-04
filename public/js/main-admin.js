$(document).ready(function() {
	var table = $('#list-table').DataTable({
		"processing": true,
		"serverSide": true,
		"ajax":{
			"url":"/brm/list",
			"type":"POST"
		},
		"columns": [
			{"data":"id"},
			{"data":"title"},
			{"data":"state"},
			{"data":"createdby_name"},
			{"data":"launchdate"},
			{"data":"current_version"},
			{"data":"approval_needed"},
			{"data":"approved"},
			{"data":"denied"},
			{"data":"view",
			 "orderable":false},
			{"data":null,
			 "defaultContent":'<button class="btn btn-danger del-btn">Delete</button>',
			 "orderable":false}
		],
		"bFilter": false,
		"dom": 'rt<"bottom col-sm-12"<"row"<"col-sm-2"l><"col-sm-2"i><"col-sm-8 pull-right"p>>><"clear">'
	});

	$('#list-table tbody').on('click', 'button.del-btn', function() {
		var data = table.row($(this).parents('tr')).data();
		var modal = $('#deleteModal');

		// Creating the delete options for the endpoint.
		$('<input type="hidden" name="deleteId">').val(data["id"]).appendTo(modal);

		modal.modal('show');
	});

});

$('#closeDelete').click(function() {
	$('input[name=deleteId]').remove();
});