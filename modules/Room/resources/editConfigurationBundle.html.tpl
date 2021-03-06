<h1>Edit Configuration Bundle <small>{$config->model}</small></h1>

<form action="" method="post">
<div class="form-horizontal">
	<div class="form-group">
		<label for="model" class="col-sm-2 control-label">Model</label>
		<div class="col-sm-10">
			<input type="text" class="form-control" name="model" value="{$config->model}" placeholder="">
		</div>
	</div>

	<div class="form-group">
		<label for="location" class="col-sm-2 control-label">Location</label>
		<div class="col-sm-10">
			<input type="text" class="form-control" name="location" value="{$config->location}" placeholder="">
		</div>
	</div>

	<div class="form-group">
		<label for="managementType" class="col-sm-2 control-label">Management Type</label>
		<div class="col-sm-10">
			<input type="text" class="form-control" name="managementType" value="{$config->managementType}" placeholder="">
		</div>
	</div>

	<div class="form-group">
		<label for="imageStatus" class="col-sm-2 control-label">Image Status</label>
		<div class="col-sm-10">
			<input type="text" class="form-control" name="imageStatus" value="{$config->imageStatus}" placeholder="">
		</div>
	</div>

	<div class="form-group">
		<label for="vintages" class="col-sm-2 control-label">Vintages</label>
		<div class="col-sm-10">
			<input type="text" class="form-control" name="vintages" value="{$config->vintages}" placeholder="">
		</div>
	</div>

	<div class="form-group">
		<label for="adBound" class="col-sm-2 control-label">AD Bound</label>
		<div class="col-sm-2">
			<input type="checkbox" class="checkbox" name="adBound" value="{$config->adBound}" {if $config->adBound}checked{/if}>
		</div>
	</div>
<hr>
	<h2>Software for this configuration {if $config->id}({$config->model}){/if}</h2>
	<div class="form-group">
		<label for="config" class="col-sm-2 control-label">Available Titles</label>
		<div class="col-sm-10">
			<table class="table table-bordered table-condensed software-table">
				<thead>
					<tr>
						<th>
							<input type="checkbox" id="select-all" data-target="software-titles">
							<label for="select-all" id="select-all-label">
								Check all
							</label>
						</th>
						<th>Title</th>
						<th>Version</th>
						<th>License #</th>
						<th>Expires</th>
						<th>Description</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
		{foreach $softwareLicenses as $titleKey => $licenses}
			{assign var="needsExpand" value=false}
			<div class="table-accordion" id="accordion{$titleKey}">
			{foreach $licenses as $license}
				{assign var=checked value=false}
				{foreach $config->softwareLicenses as $l}
					{if $l->id == $license->id}{assign var=checked value=true}{/if}
				{/foreach}

				<tr
					{if $needsExpand}
						class="collapse out" role="tabpanel" id="licenses{$titleKey}"
					{/if}
					data-index="{$licenses@index}"
					>
					<th class="text-center">
						<input type="checkbox" class="software-titles" name="licenses[{$license->id}]" id="licenses[{$license->id}]" {if $checked}checked{/if}>
					</th>
					<td>
						<label for="licenses[{$license->id}]">{$license->version->title->name}</label>
					</td>
					<td>{$license->version->number}</td>
					<td>{$license->number}</td>
					<td>{if $license->expirationDate}{$license->expirationDate->format('m/d/Y')}{else}N/A{/if}</td>
					<td>{$license->description|truncate:100}</td>
					<td>
						{if !$needsExpand && count($licenses) > 1}
							<a role="button" class="row-expand-btn btn btn-success btn-xs pull-right" data-toggle="collapse" data-parent="#accordion{$titleKey}" href="#licenses{$titleKey}" aria-expanded="true" aria-controls="licenses{$titleKey}">
								+ Show all versions
							</a>
							{assign var="needsExpand" value=true}
						{/if}			
					</td>
				</tr>

			{/foreach}
			</div>
		{/foreach}

				</tbody>
			</table>
		</div>
	</div>
</div>
<hr>
<div class="controls">
	{generate_form_post_key}
	<input type="hidden" name="configurationId" value="{$configuration->id}">
	<button type="submit" name="command[save]" class="btn btn-primary">Save Configuration</button>
	<button type="submit" name="command[delete]" class="btn btn-danger">Delete</button>
	<a href="software/{$title->id}" class="btn btn-link pull-right">Cancel</a>
</div>
</form>