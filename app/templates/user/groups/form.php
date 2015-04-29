<div class="page-header"><h3>Add/Edit User Groups</h3></div>
<div class="row">
	<form class="form-horizontal" id="user-form" method="POST" enctype="multipart/form-data">
		<div class="form-group">
			<label class="col-sm-2 control-label" for="id">Id</label>
			<div class="col-sm-10">
				<p class="form-control-static"><?=(is_null($data['usergroup_info']->id) ? '&lt;Not Set&gt;' : $data['usergroup_info']->id); ?></p>
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-2 control-label" for="name">Name</label>
			<div class="col-sm-10">
				<input type="text" name="name" class="form-control" placeholder="Name" value="<?=(!is_null($data['usergroup_info']->name) ? $data['usergroup_info']->name : ''); ?>" required>
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-2 control-label" for="description">Description</label>
			<div class="col-sm-10">
				<input type="text" name="description" class="form-control" placeholder="Description" value="<?=(!is_null($data['usergroup_info']->description) ? $data['usergroup_info']->description : ''); ?>" required>
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-2 control-label" for="permissions">Users</label>
			<div class="col-sm-10">
				<select class="form-control" id="userSelect" name="users[]" multiple="multiple">
					<?php foreach($data['users'] as $user) {
						// See if they are already in the group ...
						if(!is_null($data['usergroup_info']->id)) {
							$selected = $data['usergroup_info']->members()->where('userid', $user->id)->find_one();
						}

						if(!$selected) {
							$selected = '';
						} else {
							$selected = ' selected="selected"';
						}

						printf('<option value="%s"%s>%s %s &lt;%s&gt;</option>', $user->id, $selected, $user->firstname, $user->lastname, $user->email);

					} ?>
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