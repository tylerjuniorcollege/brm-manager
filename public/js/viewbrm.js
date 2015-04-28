$("#prev_version").change(function() {
	var versionid = $(this).val();
	$.getJSON("/brm/view/version/" + versionid, function(data) {
		$("#curr_ver_date").html(data.created);

		$("#contentframe").attr('src', 'data:text/html;charset=utf-8,' + encodeURIComponent(data.content));
		$("#emailsubject").html(data.subject);
	});
});

var commentEdit = $('#commentEditor');

commentEdit.wysiwyg();

$('#view-brm').submit(function(e) {
	var commentHtml = commentEdit.cleanHtml();

	$('#commentContent').val(commentHtml);

	var submitbtn = $(this).find("button[type=submit]:focus");
	//console.log(submitbtn);
	//e.preventDefault();
	if(submitbtn.val() == 'deny-version' && $.trim(commentHtml).length < 4) {
		$('#confirmDeny').modal('toggle');
		e.preventDefault();
	}
	
	// If submit button has been hit and the change state action requires a popup.
	if(submitbtn.val() == 'statechange') {
		//console.log($("select[name=changestate]").val());
		//e.preventDefault();
		switch($("select[name=changestate]").val()) {
			case '4':
				$('#publishedModal').modal('show');
				e.preventDefault();
				break;

			case '5':

				e.preventDefault();
				break;
		}
	}
});

$('#publishedNotify').select2();

$('#publishedSubmit').click(function() {
	$('#view-brm').submit();
});