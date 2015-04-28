<div class="page-header"><h3>Add/Edit User Groups</h3></div>
<div class="row">
	<form class="form-horizontal" id="user-form" method="POST" enctype="multipart/form-data">
		<div class="form-group">
			<label class="col-sm-2 control-label" for="id">Id</label>
			<div class="col-sm-10">
				<p class="form-control-static"><?=(is_null($data['usergroup_data']->id) ? '&lt;Not Set&gt;' : $data['usergroup_data']->id); ?></p>
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-2 control-label" for="name">Name</label>
			<div class="col-sm-10">
				<input type="text" name="name" class="form-control" placeholder="Name" value="<?=(!is_null($data['usergroup_data']->name) ? $data['usergroup_data']->name : ''); ?>" required>
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-2 control-label" for="description">Description</label>
			<div class="col-sm-10">
				<input type="text" name="description" class="form-control" placeholder="Description" value="<?=(!is_null($data['user_data']->description) ? $data['user_data']->description : ''); ?>" required>
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-2 control-label" for="permissions">Users</label>
			<div class="col-sm-10">
				<select class="form-control" id="userSelect" multiple="multiple">

				</select>
			</div>
		</div>
		<div class="form-group">
			<div class="col-sm-10 col-sm-offset-2">
				<button type="submit" class="btn btn-primary">Save</button>
			</div>
		</div>
	</form>
</div>