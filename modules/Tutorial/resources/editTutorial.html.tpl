<div class="container">
	<div class="row">
		<div class="col-xs-12">
			<h1>
			{if $tutorial->inDatasource}
				Edit Tutorial <small>{$tutorial->name}</small>
			{else}
				New Tutorial
			{/if}
			</h1>
		</div>
	</div>
</div>

<div class="well">
	<form
		id="fileupload"
		action="tutorials/upload"
		method="POST"
		enctype="multipart/form-data"
		>
		<input type="hidden" name="imageCopy" id="imageCopy">
	<!-- The fileupload-buttonbar contains buttons to add/delete files and start/cancel the upload -->
	<div class="row fileupload-buttonbar">
	  <div class="col-lg-7">
	    <!-- The fileinput-button span is used to style the file input field as button -->
	    <span class="btn btn-success fileinput-button">
	      <i class="glyphicon glyphicon-plus"></i>
	      <span>Add image...</span>
	      <input type="file" name="file" />
	    </span>

	    <!-- The global file processing state -->
	    <span class="fileupload-process"></span>
	  </div>
	</div>
	<!-- The table listing the files available for upload/download -->
	<table role="presentation" class="table table-striped">
	  <tbody class="files"></tbody>
	</table>
	{generate_form_post_key}
	</form>

	<div id="image-gallery" class="container" style="display:none">
		<h4>Click image to copy it's URL <small id="copy-message" style="opacity:0;margin-left:3em" class="text-success">COPIED</small></h4>
		<div class="row" >
		{foreach $images as $image}

			<div class="col-xs-3">
				<div data-src="{$image->getTutorialImageSrc($tutorial->id)}" class="copy-image-btn">
					<img src="{$image->getTutorialImageSrc($tutorial->id)}" class="img-responsive">
				</div>
			</div>
		{/foreach}		
		</div>
	</div>

</div>


<form action="" method="post" id="detailsForm">
	<div class="container"> 
		<div class="row">
			<div class="col-xs-12 edit-details">
				<div class="form-horizontals">
					<div class="form-group">
						<label for="name" class="control-label">Tutorial Title</label>
						<div class="">
							<input type="text" class="form-control" name="name" value="{$tutorial->name}">
						</div>
					</div>
					<div class="form-group">
						<label for="name" class="control-label">Header Image URL</label>
						<div class="">
							<input type="text" class="form-control" name="headerImageUrl" value="{$tutorial->headerImageUrl}">
							{if $tutorial->headerImageUrl}
								<div class="row">
									<div class="col-sm-3">
										<img src="{$tutorial->headerImageUrl}" class="img-responsive">
									</div>
								</div>
							{/if}
						</div>
					</div>
					<div class="form-group">
						<label for="youtubeEmbedCode">YouTube Embed Code</label>
						<textarea rows="3" name="youtubeEmbedCode" class="form-control">
							{$tutorial->youtubeEmbedCode|trim}
						</textarea>
					</div>
					<div class="form-group">
						<label for="description" class="control-label">Description</label>
			            <div class="form-control-wrapper textarea">
			                <textarea class="text-field form-control wysiwyg" name="description" rows="15">{$tutorial->description}</textarea>
			            </div>
					</div>
				</div>
			</div>
		</div>

		<div class="controls">
			{generate_form_post_key}
			<input type="hidden" name="tutorialId" value="{$tutorial->id}">
			<button type="submit" name="command[save]" class="btn btn-primary">Save Tutorial</button>
			<button type="submit" name="command[delete]" class="btn btn-danger">Delete</button>
			<a href="tutorials" class="btn btn-link pull-right">Cancel</a>
		</div>
	</div>
</form>