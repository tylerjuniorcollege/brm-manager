$(document).ready(function() {
	$('#list-table').DataTable({
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
			{"data":null,
			 "visible":false}
		],
		"bFilter": false
	});
});