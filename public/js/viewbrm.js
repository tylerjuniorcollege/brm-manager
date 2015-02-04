$("#prev_version").change(function() {
	var versionid = $(this).val();
	$.getJSON("/brm/view/version/" + versionid, function(data) {
		$("#curr_ver_date").html(data.created);

		$("#contentframe").attr('src', 'data:text/html;charset=uft-8,' + encodeURIComponent(data.content));
	});
});
