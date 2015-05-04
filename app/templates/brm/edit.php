<?php $brm = $data['brm']; ?>
<div id="currUser" style="display: none;"></div>
<div class="row page-header">
	<h3>Editing BRM Email</h3>
</div>
<div class="row">
	<form class="form-horizontal" id="create-brm" method="POST" action="<?=$data['save']; ?>" enctype="multipart/form-data">
		<div class="form-group">
			<label for="name" class="col-sm-2 control-label">Name for BRM</label>
			<div class="col-sm-10">
				<input type="text" name="name" placeholder="Name ..." class="form-control" value="<?=$brm->title;?>">
			</div>
		</div>
		<div class="form-group">
			<label for="description" class="col-sm-2 control-label">Description</label>
			<div class="col-sm-10">
				<input type="text" name="description" placeholder="Description ..." class="form-control" value="<?=$brm->description;?>">
			</div>
		</div>
		<div class="row">
			<div class="form-group col-sm-6">
				<label for="description" class="col-sm-4 control-label">Template Id</label>
				<div class="col-sm-8">
					<input type="text" name="templateid" placeholder="Template ID ..." class="form-control" value="<?=$brm->templateid;?>">
				</div>
			</div>
			<div class="form-group col-sm-6">
				<label for="campaigns" class="col-sm-4 control-label">Campaign</label>
				<div class="col-sm-8" id="campaign-select">
					<select name="campaigns" class="form-control" id="campaign-list" placeholder="Campaign">
						<option></option>
						<option value="new">New Campaign</option>
						<option value="none">No Assigned Campaign</option>
						<optgroup label="Existing Campaigns">
						<?php foreach($data['campaigns'] as $campaign): ?>
							<option value="<?=$campaign->id;?>"<?=($campaign->id == $brm->campaignid ? ' selected' : '');?>><?=$campaign->name; ?></option>
						<?php endforeach; ?>
						</optgroup>
					</select>
				</div>
			</div>
		</div>
		<div class="col-sm-10 col-sm-offset-2" style="padding: 0">
			<div class="panel panel-primary collapse" data-toggle="collapse" id="campaignCreateForm">
				<div class="panel-heading"><h3 class="panel-title">New Campaign</h3></div>
				<div class="panel-body">
					<div class="form-group">
						<label for="newCampaignName" class="col-sm-2 control-label">Campaign Name</label>
						<div class="col-sm-10">
							<input type="text" name="campaign-name" placeholder="Name" class="form-control">
						</div>
					</div>
					<div class="form-group">
						<label for="newCampaignName" class="col-sm-2 control-label">Campaign Description</label>
						<div class="col-sm-10">
							<input type="text" name="campaign-description" placeholder="Description" class="form-control">
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="form-group">
			<label for="launchdate" class="col-sm-2 control-label">Launch Date</label>
			<div class="col-sm-10">
				<div class="input-group date" id="launchdate">
					<input type="text" class="form-control" name="launchdate" placeholder="Date String (e.g. <?=date('m/d/Y g:i A'); ?>)" value="<?=(!is_null($brm->launchdate) ? date('m/d/Y g:i A', strtotime($brm->launchdate)) : ''); ?>">
					<span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
				</div>
			</div>
		</div>
		<?php
			// Expand if data is present.
			$request_expand = FALSE;
			$request = \Model::factory('BRM\Request')->create();
			$launch_expand = FALSE;

			if(!is_null($brm->requestid)) {
				$request_expand = TRUE;
				$request = $brm->request();
			}

			if(!is_null($brm->launchdate) || !is_null($brm->population) || !is_null($brm->listname)) {
				$launch_expand = TRUE;
			}
		?>
		<div class="col-sm-10 col-sm-offset-2 panel-group" id="aOpts" style="padding:0">
			<div class="panel panel-default" id="requestDetails">
				<div class="panel-heading" id="reqHeading">
					<h3 class="panel-title">
						<a data-toggle="collapse" href="#reqCollapse" aria-expanded="<?=($request_expand ? 'true' : 'false'); ?>" aria-controls="reqCollapse">
							Request Details
						</a>
					</h3>
				</div>
				<div class="panel-collapse collapse<?=($request_expand ? ' in' : ''); ?>" id="reqCollapse" aria-labelledby="reqHeading">
					<div class="panel-body">
						<div class="form-group">
							<label for="requestdate" class="col-sm-2 control-label">Requested Date</label>
							<div class="col-sm-10">
								<div class="input-group date" id="requesteddate">
									<input type="text" class="form-control" name="requestdate" placeholder="Date String (e.g. <?=date('m/d/Y g:i A'); ?>)" value="<?=(!is_null($request->timestamp) ? date('m/d/Y g:i A', strtotime($request->timestamp)) : ''); ?>">
									<span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
								</div>
							</div>
						</div>
						<div class="form-group">
							<label for="requestuser" class="col-sm-2 control-label">Requesting User</label>
							<div class="col-sm-10">
								<input type="text" name="requestuser" class="form-control" id="requestuser" data-provide="typeahead" autocomplete="off" placeholder="Email Address" value="<?=(!is_null($request->userid) ? $request->user()->email : '');?>">
							</div>
						</div>
						<div class="form-group">
							<label for="requestdepartment" class="col-sm-2 control-label">Requesting Department</label>
							<div class="col-sm-10">
								<select name="department" class="form-control" id="departmentSelect" placeholder="Department">
									<option></option>
									<?php foreach($data['departments'] as $dept): ?>
									<option value="<?=$dept->id; ?>"<?=($dept->id == $request->departmentid ? ' selected' : '');?>><?=$dept->name; ?></option>
									<?php endforeach; ?>
								</select>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="panel panel-default" id="launchSettings">
				<div class="panel-heading" id="launchHeading">
					<h3 class="panel-title">
						<a data-toggle="collapse" href="#launchCollapse" aria-expanded="<?=($launch_expand ? 'true' : 'false'); ?>" aria-controls="launchCollapse">
							Population Details
						</a>
					</h3>
				</div>
				<div class="panel-collapse collapse<?=($launch_expand ? ' in' : ''); ?>" id="launchCollapse" aria-labelledby="launchHeading">
					<div class="panel-body">
						<div class="form-group">
							<label for="population" class="col-sm-2 control-label">Population</label>
							<div class="col-sm-10">
								<input type="number" name="population" class="form-control" id="population" placeholder="Population" value="<?=$brm->population;?>">
							</div>
						</div>
						<div class="form-group">
							<label for="requestdepartment" class="col-sm-2 control-label">Email List Name</label>
							<div class="col-sm-10">
								<input type="text" name="emaillistname" class="form-control" placeholder="List Name" value="<?=$brm->listname;?>">
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="form-group">
			<label for="content-subject" class="col-sm-2 control-label">Email Subject</label>
			<div class="col-sm-10">
				<input type="text" name="contentsubject" placeholder="Email Subject Line" class="form-control" id="contentSubject" value="<?=$brm->currentVersion()->subject; ?>">
			</div>
		</div>
		<div class="form-group">
			<label for="content" class="col-sm-2 control-label">Email Content</label>
			<div class="col-sm-10">
				<textarea name="content" placeholder="BRM Content Here ..." rows="25" class="form-control" id="contentInput"><?=$brm->currentVersion()->content; ?></textarea>
				<iframe id="previewContent" style="display:none; height: 300px;" class="col-sm-12"></iframe>
				<span class="help-block"><button class="btn btn-primary" id="previewBtn" type="button">Render Preview</button><button class="btn btn-primary" id="editBtn" type="button" style="display:none; margin-top: 5px;">Show Edit</button></span>
			</div>
		</div>
		<div class="form-group">
			<label for="approval_list" class="col-sm-2 control-label">Approval/View List</label>
			<div class="col-sm-5">
				<div class="panel panel-success">
					<div class="panel-heading"><h3 class="panel-title">Current Users Tied To The BRM</h3></div>
					<ul class="list-group" id="currentUsers">
						<?php foreach($brm->authorizedUsers()->find_many() as $user_a): 
							$user = $user_a->user();
							$userPerms = \BRMManager\Permissions::userCan((int) $user_a->permission);
							printf('<input type="hidden" name="users[]" value="%s" id="input-user-%s">', $user->id, $user->id);
							printf('<input type="hidden" name="permissions[%s]" value="%s" id="input-perm-user-%s">', $user->id, $user_a->permission, $user->id);
						?>
						<li id="userList-<?=$user->id;?>" class="list-group-item" style="display:block;">
							<?=$user->firstname; ?> <?=$user->lastname; ?> &lt;<?=$user->email; ?>&gt;
							<button type="button" class="btn btn-danger btn-xs pull-right remove-user" id="removeUser-<?=$user->id;?>"><i class="fa fa-times"></i></button>
							<span class="pull-right" id="userPerm-<?=$user->id;?>">
								<?php foreach($userPerms as $k => $perm) {
									switch($perm) {
										case 'approve':
											print '<span class="label label-primary">Approve</span>';
											break;

										case 'edit':
											print '<span class="label label-warning">Edit</span>';
											break;
									}
								} ?>
							</span>
						</li>
						<?php endforeach; ?>
					</ul>
				</div>
			</div>
			<div class="col-sm-5">
				<input type="text" id="searchUsers" placeholder="Search Or Input Email Address ..." class="form-control" autocomplete="off">
				<br />
				<div class="panel panel-default" id="userGroups">
					<div class="panel-heading"><h3 class="panel-title" id="searchResHeading">User Groups In The System</h3></div>
					<div id="userGroupResults" class="list-group">
						<?php foreach($data['user_groups'] as $ug) {
							
							$popover = array();
							foreach($ug->members()->find_many() as $member) {
								// The user:
								$user = $member->user()->find_one();
								printf('<input type="hidden" class="ugMember-%s-id" value="%s">', $ug->id, $user->id);
								printf('<input type="hidden" id="ugMember-%s-name-%s" value="%s %s">', $ug->id, $user->id, $user->firstname, $user->lastname);
								printf('<input type="hidden" id="ugMember-%s-email-%s" value="%s">', $ug->id, $user->id, $user->email);
								$popover[] = sprintf('%s %s &lt;%s&gt;', $user->firstname, $user->lastname, $user->email);
							}

							printf('<a id="userGroup-%s" tabindex="0" class="user_group list-group-item" role="button" data-toggle="popover" data-trigger="hover" title="%s" data-content="%s">%s <i class="fa fa-users pull-right"></i></a>', 
									$ug->id, 
									$ug->description, // Using the description as the title.
									implode("\n", $popover),
									$ug->name);
						} ?>
					</div>
				</div>
				<div class="panel panel-default" id="searchUsersResults" style="display:none;">
					<div class="panel-heading"><h3 class="panel-title">Search Results</h3></div>
					<div id="searchResults" class="list-group"></div>
				</div>
				<div class="panel panel-default" id="selectUserPermissions" style="display:none;">
					<div class="panel-heading"><h3 class="panel-title">Permissions for User: <span id="permUserSelect"></span></h3></div>
					<div class="panel-body">
						<label class="checkbox-custom checkbox-inline" data-initialize="checkbox">
							<input type="checkbox" id="permApprove" class="selectUserPermCheck" value="2">
							<span id="permLabelApprove" class="label label-primary">Approve</span>
						</label>
						<label class="checkbox-custom checkbox-inline" data-initialize="checkbox">
							<input type="checkbox" id="permEdit" class="selectUserPermCheck" value="4">
							<span id="permLabelEdit" class="label label-warning">Edit</span>
						</label>
					</div>
					<div class="panel-footer">
						<button type="button" id="cancelUserPerm" class="btn btn-danger pull-left">Cancel</button>
						<button type="button" id="userPermissions" class="btn btn-default pull-right">Add User to List</button>
						<div class="clearfix"></div>
					</div>
				</div>
				<div class="panel panel-default" id="selectGroupPermissions" style="display:none;">
					<div class="panel-heading"><h3 class="panel-title">Permissions for Group: <span id="permGroupSelect"></span></h3></div>
					<div class="panel-body">
						<label class="checkbox-custom checkbox-inline" data-initialize="checkbox">
							<input type="checkbox" id="permGroupApprove" class="selectGroupPermCheck" value="2">
							<span id="permGroupLabelApprove" class="label label-primary">Approve</span>
						</label>
						<label class="checkbox-custom checkbox-inline" data-initialize="checkbox">
							<input type="checkbox" id="permGroupEdit" class="selectGroupPermCheck" value="4">
							<span id="permGroupLabelEdit" class="label label-warning">Edit</span>
						</label>
					</div>
					<div class="panel-footer">
						<button type="button" id="cancelGroupPerm" class="btn btn-danger pull-left">Cancel</button>
						<button type="button" id="groupPermissions" class="btn btn-default pull-right">Add Group Members to List</button>
						<div class="clearfix"></div>
					</div>
				</div>
				<div class="panel panel-default" id="addNewUser" style="display:none;">
					<div class="panel-heading"><h3 class="panel-title">Add A New User</h3></div>
					<div class="panel-body">
						<div class="form-group">
							<label for="firstName">First Name</label>
							<input type="text" id="newFirst" class="form-control" placeholder="First Name ...">
						</div>
						<div class="form-group">
							<label for="lastName">Last Name</label>
							<input type="text" id="newLast" class="form-control" placeholder="Last Name ...">
						</div>
						<div class="form-group">
							<label for="newPerm">Permissions</label>
							<div>
								<label class="checkbox-custom checkbox-inline" data-initialize="checkbox">
									<input type="checkbox" id="newPermApprove" class="selectUserPermCheck" value="2">
									<span class="label label-primary">Approve</span>
								</label>
								<label class="checkbox-custom checkbox-inline" data-initialize="checkbox">
									<input type="checkbox" id="newPermEdit" class="selectUserPermCheck" value="4">
									<span class="label label-warning">Edit</span>
								</label>
							</div>
						</div>
					</div>
					<div class="panel-footer">
						<button type="button" id="cancelAddUser" class="btn btn-danger pull-left">Cancel</button>
						<button type="button" id="addUserSubmit" class="btn btn-default pull-right">Add User</button>
						<div class="clearfix"></div>
					</div>
				</div>
			</div>
		</div>
		<hr />
		<div class="form-group">
			<div class="col-sm-3 col-sm-offset-9">
				<?php if($brm->stateid == 0): ?>
				<button type="submit" name="submit" value="save" class="btn btn-default">Save BRM</button>
				<button type="submit" name="submit" value="send" class="btn btn-primary">Submit for Approval</button>
				<?php else: ?>
				<button type="submit" name="submit" value="send" class="btn btn-primary">Re-submit For Approval</button>
				<?php endif; ?>
			</div>
		</div>
		<hr />
	</form>
</div>