<div class="row page-header">
	<h3>Viewing <?=$data['brm_data']->title; ?> <small>Version Created on <span id="curr_ver_date"><?=date('l, F j, Y g:i:s', $data['current_version']->created); ?></span></small></h3>
</div>
<div class="row">
	<form class="form-horizontal" id="view-brm" method="POST">
		<div class="form-group">
			<label for="description" class="col-sm-2 control-label">Description</label>
			<div class="col-sm-10">
				<p class="form-control-static"><?=$data['brm_data']->description; ?></p>
			</div>
		</div>
		<div class="form-group">
			<label for="description" class="col-sm-2 control-label">Template Id</label>
			<div class="col-sm-10">
				<p class="form-control-static"><?=(!is_null($data['brm_data']->templateid) ? $data['brm_data']->templateid : '<i>&lt;Not Set&gt;</i>'); ?></p>
			</div>
		</div>
		<div class="form-group">
			<label for="content" class="col-sm-2 control-label">Content</label>
			<div class="col-sm-10">
				<iframe id="contentframe" src="data:text/html;charset=utf-8,<?=(rawurlencode($data['current_version']->content)); ?>" class="col-sm-12" height="300"></iframe>
				<?php if(!empty($data['previous_versions'])): ?>
				<div class="row">
					<label for="previousversions" class="col-sm-2 control-label" style="padding-top: 13px;">Previous Versions</label>
					<div class="col-sm-10" style="padding-top: 5px;">
						<select class="form-control" id="prev_version">
							<option value="<?=$data['brm_data']->current_version; ?>">Current Version</option>
						<?php foreach($data['previous_versions'] as $row) {
							printf('<option value="%s">%s</option>', $row['id'], $row['brmversionid'] . " - " . date('l, F j, Y g:i:s', (int)$row['created']));
							} ?>
						</select>
					</div>
				</div>
				<?php endif; ?>
			</div>
		</div>
		<div class="form-group">
			<label for="header_imgs" class="col-sm-2 control-label">Header Images</label>
			<div class="col-sm-10">

			</div>
		</div>
		<?php if($data['owner'] === TRUE): ?>
			<div class="form-group">
				<label for="approval_list" class="col-sm-2 control-label">Approval/View List</label>
				<div class="col-sm-6">
					<ul class="list-group">
						<?php foreach($data['auth_users'] as $authuser) {
							switch ($authuser->approved) {
								case 1:
									$li_css = ' list-group-item-success';
									$icon = 'fa-check-circle-o';
									break;

								case -1:
									$li_css = ' list-group-item-danger';
									$icon = 'fa-times-circle-o';
									break;

								default:
									$li_css = null;
									$icon = 'fa-circle-o';
									if(!is_null($authuser->viewedtime)) {
										$icon = 'fa-dot-circle-o';
									}
									break;
							}

							// Building links ...
							$user_str = sprintf('%s %s &lt;%s&gt;', $authuser->user()->firstname, $authuser->user()->lastname, $authuser->user()->email);
							printf('<li class="list-group-item%s">%s <i class="fa %s pull-right"></i></li>', $li_css, $user_str, $icon);
						} ?>
					</ul>
				</div>
				<div class="col-sm-4">
					<ul class="list-group">
						<li class="list-group-item list-group-item-success">Approvals for this version <span class="badge"><?=$data['current_version']->countApproved(); ?></span></li>
						<li class="list-group-item list-group-item-danger">Denials for this version <span class="badge"><?=$data['current_version']->countDenied(); ?></span></li>
						<li class="list-group-item">Approvals needed for this version <span class="badge"><?=$data['current_version']->countAwaiting(); ?></span></li>
					</ul>
				</div>
			</div>
		<?php endif; ?>
		<div class="form-group">
			<label for="comments" class="col-sm-2 control-label">Comments</label>
			<div class="col-sm-10">
				<textarea class="form-control col-sm-12" rows="3" placeholder="Add A Comment ..." name="comment"></textarea>
				<div class="row">
					<div class="col-sm-5 col-sm-offset-7" style="padding-top: 5px;">
						<input type="hidden" name="brmid" value="<?= $data['brm_data']->id; ?>">
						<input type="hidden" name="versionid" value="<?= $data['current_version']->id; ?>">
						<button type="submit" name="action" value="addcomment" class="btn btn-default">Add Comment</button>
						<button type="submit" name="action" value="approve" class="btn btn-success">Approve Version</button>
						<button type="submit" name="action" value="deny" class="btn btn-danger">Deny Version</button>
					</div>
				</div>
				<hr />
				<div class="row" id="comments">
				<?php foreach($data['comments'] as $c_row): ?>
					<div class="media">
						<div class="media-left">
							<img src="<?= \BRMManager\Gravatar\genUrl($c_row->useremail); ?>" class="media-object"/>
						</div>
						<div class="media-body">
							<?= $c_row->comment; ?> 
							<h4 class="media-heading"><?= $c_row->userfirstname; ?> <?=$c_row->userlastname; ?> - Posted on <?=date('l, F j, Y g:i:s', $c_row->timestamp); ?> For Version #<?=$c_row->brmversionid; ?></h4>
						</div>
					</div>
				<?php endforeach; ?>
				</div>
			</div>
		</div>
		<hr />
	</form>
</div>