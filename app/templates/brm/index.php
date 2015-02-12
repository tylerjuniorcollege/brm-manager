<?php if(isset($data['approval'])): ?>
	<div class="row page-header">
		<h3>BRMs Waiting Your Approval</h3>
	</div>
	<div class="row">
		<table class="table table-striped table-hover" id="approval-table">
			<colgroup>
				<col class="col-md-1">
				<col class="col-md-3">
				<col class="col-md-3">
				<col class="col-md-1">
				<col class="col-md-1">
				<col class="col-md-1">
				<col class="col-md-1">
				<col class="col-md-1">
				<col class="col-md-1">
			</colgroup>
			<thead>
				<tr>
					<th>BRM ID</th>
					<th>Title</th>
					<th>Description</th>
					<th># of Versions</th>
					<th>Current Version</th>
					<th>View</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach($data['created'] as $row) {
					printf('<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td><a href="/brm/view/%s">View</a></td></tr>', 
						$row->id,
						$row->title,
						$row->description,
						$row->version_count,
						$row->current_version,
						$row->id);
				} ?>
			</tbody>
		</table>
	</div>
<?php endif; ?>

<?php if(isset($data['created'])): ?>
	<div class="row page-header">
		<h3>Your Created BRMs</h3>
	</div>
	<div class="row">
		<table class="table table-striped table-hover" id="created-table">
			<colgroup>
				<col class="col-md-1">
				<col class="col-md-3">
				<col class="col-md-3">
				<col class="col-md-1">
				<col class="col-md-1">
				<col class="col-md-1">
				<col class="col-md-1">
				<col class="col-md-1">
			</colgroup>
			<thead>
				<tr>
					<th rowspan="2">BRM ID</th>
					<th rowspan="2">Title</th>
					<th rowspan="2">Description</th>
					<th rowspan="2">Current Version</th>
					<th colspan="3">Approval Stats</th>
					<th rowspan="2">View</th>
				</tr>
				<tr>
					<th>Approval Needed</th>
					<th>Approved</th>
					<th>Denied</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach($data['created'] as $row) {
					printf('<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td><a href="/brm/view/%s">View</a></td></tr>', 
						$row->id,
						$row->title,
						$row->description,
						$row->current_version,
						$row->approval_needed,
						$row->approved,
						$row->denied,
						$row->id);
				} ?>
			</tbody>
		</table>
	</div>
<?php endif; ?>