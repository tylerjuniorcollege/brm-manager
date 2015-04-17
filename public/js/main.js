$(document).ready(function() {
	$('#list-table').dataTable({
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
			{"data":"launchdate"},
			{"data":"current_version"},
			{"data":"approval_needed"},
			{"data":"approved"},
			{"data":"denied"},
			{"data":"view"}
		],
		"bFilter": false
	});
});