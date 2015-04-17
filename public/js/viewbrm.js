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

$('#view-brm').submit(function() {
	var commentHtml = commentEdit.cleanHtml();

	$('#commentContent').val(commentHtml);
});