<div class="page-header"><h3>User Group Admin<small class="pull-right"><a href="/user/groups/add" class="btn btn-primary">Add New Group</a></small></h3></div>
<div class="row">
	<div class="col-sm-12">
		<table class="table table-striped table-hover" id="userTable">
			<colgroup>
				<col class="col-sm-1">
				<col class="col-sm-3">
				<col class="col-sm-6">
				<col class="col-sm-1">
				<col class="col-sm-1">
			</colgroup>
			<thead>
				<tr>
					<th>Id</th>
					<th>Name</th>
					<th>Description</th>
					<th>Number of Users</th>
					<th>Edit</th>
				</tr>
			</thead>
			<tbody>
			<?php foreach ($data['usergroups'] as $usergroup) {
				printf("\n\t\t\t\t<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td><a href=\"%s\">Edit</a></td></tr>",
					$usergroup->id,
					$usergroup->name,
					$usergroup->description,
					$usergroup->members()->count(),
					$usergroup->editLink()
				);
			} ?>
			</tbody>
		</table>
	</div>
</div>