<div class="media <?=$data['background']; ?>" id="comment<?=$data['commentid']; ?>">
	<div class="media-left">
		<img src="<?= $data['avatar']; ?>" class="media-object"/>
	</div>
	<div class="media-body">
		<div class="media-comment">
			<?= $data['comment']; ?>
			<h4 class="media-heading"><?= $data['name']; ?> - Posted on <?= $data['posted']; ?> For Version #<?=$data['versionid']; ?> - <a href="#" class="comment-reply" id="comment-<?= $data['commentid']; ?>">Reply</a></h4>
		</div> 
		<div class="media bg-info collapse" id="comment-<?=$data['commentid']; ?>-reply"><div class="media-body">
		<div class="btn-toolbar" id="comment-<?= $data['commentid']; ?>-toolbar" data-role="editor-toolbar" data-target="#comment-<?= $data['commentid']; ?>-editor">
			<div class="btn-group">
			  <a class="btn btn-default" data-edit="bold" title="Bold (Ctrl/Cmd+B)"><i class="fa fa-bold"></i></a>
			  <a class="btn btn-default" data-edit="italic" title="Italic (Ctrl/Cmd+I)"><i class="fa fa-italic"></i></a>
			  <a class="btn btn-default" data-edit="strikethrough" title="Strikethrough"><i class="fa fa-strikethrough"></i></a>
			  <a class="btn btn-default" data-edit="underline" title="Underline (Ctrl/Cmd+U)"><i class="fa fa-underline"></i></a>
			</div>
			<div class="btn-group">
			  <a class="btn btn-default" data-edit="insertunorderedlist" title="Bullet list"><i class="fa fa-list-ul"></i></a>
			  <a class="btn btn-default" data-edit="insertorderedlist" title="Number list"><i class="fa fa-list-ol"></i></a>
			  <a class="btn btn-default" data-edit="outdent" title="Reduce indent (Shift+Tab)"><i class="fa fa-indent"></i></a>
			  <a class="btn btn-default" data-edit="indent" title="Indent (Tab)"><i class="fa fa-outdent"></i></a>
			</div>
			<div class="btn-group">
			  <a class="btn btn-default" data-edit="justifyleft" title="Align Left (Ctrl/Cmd+L)"><i class="fa fa-align-left"></i></a>
			  <a class="btn btn-default" data-edit="justifycenter" title="Center (Ctrl/Cmd+E)"><i class="fa fa-align-center"></i></a>
			  <a class="btn btn-default" data-edit="justifyright" title="Align Right (Ctrl/Cmd+R)"><i class="fa fa-align-right"></i></a>
			  <a class="btn btn-default" data-edit="justifyfull" title="Justify (Ctrl/Cmd+J)"><i class="fa fa-align-justify"></i></a>
			</div>
			<div class="btn-group">
			  <a class="btn btn-default" data-edit="undo" title="Undo (Ctrl/Cmd+Z)"><i class="fa fa-undo"></i></a>
			  <a class="btn btn-default" data-edit="redo" title="Redo (Ctrl/Cmd+Y)"><i class="fa fa-repeat"></i></a>
			</div>
		</div>
		<div class="comment-reply-editor" id="comment-<?=$data['commentid'];?>-editor"></div>
		<div class="comment-reply-btn row" id="comment-<?=$data['commentid'];?>-reply-btn">
			<div class="col-sm-5 col-sm-offset-7" style="padding-top: 5px;">
				<button type="submit" name="action" value="addcommentreply" class="btn btn-primary pull-right">Add Comment</button>
				<button type="button" id="comment-<?=$data['commentid']; ?>-cancel" class="comment-reply-cancel btn btn-default pull-right">Cancel</button>
			</div>
		</div></div></div>
		<?= $data['child_comments']; ?>
	</div>
</div>