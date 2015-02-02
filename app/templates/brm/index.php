<?php if(isset($data['active'])): ?>
	<div class="row page-header">
		<h3>Your Active BRMs</h3>
	</div>

<?php endif; ?>
<?php if(isset($data['created'])): ?>
	<div class="row page-header">
		<h3>Your Created BRMs</h3>
	</div>
	<div class="row">
		<table class="table" id="created-table">
			<thead>
				<tr>
					<td>BRM ID</td>
					<td>Title</td>
					<td>Description</td>
					<td># of Versions</td>
					<td>Current Version</td>
					<td>View</td>
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