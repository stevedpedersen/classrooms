{assign var="config" value=$selectedConfiguration}


<h3>Custom Configurations 
	<small>
	{if $config->id && $location->configurations && $location->configurations->count() > 1}
		({$config->model} {$model->location})
	{else}
		(default)
	{/if}
	</small>
</h3>

{if $config->id}
<div class="form-horizontal">
	<div class="form-group">
		<label for="model" class="col-sm-2 control-label">Model</label>
		<div class="col-sm-10">
			<input type="text" class="form-control" name="config[existing][model]" value="{$config->model}" placeholder="">
		</div>
	</div>

	<div class="form-group">
		<label for="deviceType" class="col-sm-2 control-label">Type of Units/Devices</label>
		<div class="col-sm-10">
			<input type="text" class="form-control" name="config[existing][deviceType]" value="{$config->deviceType}" placeholder="">
		</div>
	</div>

	<div class="form-group">
		<label for="deviceQuantity" class="col-sm-2 control-label">Quantity of Units/Devices</label>
		<div class="col-sm-10">
			<input type="text" class="form-control" name="config[existing][deviceQuantity]" value="{$config->deviceQuantity}" placeholder="">
		</div>
	</div>

	<div class="form-group">
		<label for="location" class="col-sm-2 control-label">Location</label>
		<div class="col-sm-10">
			<input type="text" class="form-control" name="config[existing][location]" value="{$config->location}" placeholder="">
		</div>
	</div>

	<div class="form-group">
		<label for="managementType" class="col-sm-2 control-label">Management Type</label>
		<div class="col-sm-10">
			<input type="text" class="form-control" name="config[existing][managementType]" value="{$config->managementType}" placeholder="">
		</div>
	</div>

	<div class="form-group">
		<label for="imageStatus" class="col-sm-2 control-label">Image Status</label>
		<div class="col-sm-10">
			<input type="text" class="form-control" name="config[existing][imageStatus]" value="{$config->imageStatus}" placeholder="">
		</div>
	</div>

	<div class="form-group">
		<label for="vintages" class="col-sm-2 control-label">Vintages</label>
		<div class="col-sm-10">
			<input type="text" class="form-control" name="config[existing][vintages]" value="{$config->vintages}" placeholder="">
		</div>
	</div>

<a class="btn btn-info collapse-button collapsed" data-toggle="collapse" data-parent="#accordion1" href="#advancedExisting" aria-expanded="true" aria-controls="advancedExisting" style="margin-bottom: 1em;">
	Show/Hide Advanced Fields
</a><br>
<div id="accordion1">
	<div class="panel-collapse collapse" role="tabpanel" id="advancedExisting">

	<div class="form-group">
		<label for="uniprintQueue" class="col-sm-2 control-label">Uniprint Queue</label>
		<div class="col-sm-10">
			<input type="text" class="form-control" name="config[existing][uniprintQueue]" value="{$config->uniprintQueue}" placeholder="">
		</div>
	</div>

	<div class="form-group">
		<label for="releaseStationIp" class="col-sm-2 control-label">Release Station IP</label>
		<div class="col-sm-10">
			<input type="text" class="form-control" name="config[existing][releaseStationIp]" value="{$config->releaseStationIp}" placeholder="">
		</div>
	</div>

	<div class="form-group">
		<label for="adBound" class="col-sm-2 control-label">AD Bound</label>
		<div class="col-sm-2">
			<input type="checkbox" class="checkbox" name="config[existing][adBound]" value="{$config->adBound}" {if $config->adBound}checked{/if}>
		</div>
	</div>

	</div>
</div>

	<br>
	<h5>Software for this configuration {if $selectedConfiguration->id}({$selectedConfiguration->model}){/if}</h5>
	<div class="form-group">
		<label for="config" class="col-sm-2 control-label">Available Titles</label>
		<div class="col-sm-10">
			<table class="table table-condensed">
				<thead>
					<tr>
						<th></th>
						<th>Title</th>
						<th>Version</th>
						<th>License</th>
						<th>Expires</th>
					</tr>
				</thead>
				<tbody>
		{foreach $softwareLicenses as $licenses}
			{foreach $licenses as $license}
				<tr>
				{assign var=checked value=false}
				{foreach $selectedConfiguration->softwareLicenses as $l}
					{if $l->id == $license->id}{assign var=checked value=true}{/if}
				{/foreach}
					<td>
						<input type="checkbox" name="config[existing][licenses][{$license->id}]" {if $checked}checked{/if} id="config[existing][licenses][{$license->id}]">
					</td>
					<td>
						<label for="config[existing][licenses][{$license->id}]">
							{$license->version->title->name}
						</label>
					</td>
					<td>{$license->version->number}</td>
					<td>{$license->number}</td>
					<td>{$license->expirationDate->format('m/d/Y')}</td>
				</tr>
			{/foreach}
		{/foreach}
				</tbody>
			</table>
		</div>
	</div>
</div>
{/if}


<div id="accordion">

	{if count($customConfigurations) > 0}
	<div class="container-fluid">
		<div class="row">
			<a role="button" class="btn btn-default pull-right" data-toggle="collapse" data-parent="#accordion" href="#newConfig" aria-expanded="true" aria-controls="newConfig">
				+ Add New Configuration
			</a>
		</div>	
	</div>
	{/if}

	<div class="panel-collapse collapse {if count($customConfigurations) == 0}in{/if}" role="tabpanel" id="newConfig">
		<div class="form-horizontal">
			{if count($customConfigurations) > 0}
				<h4 class="">Add new configuration</h4>
			{/if}
			<div class="form-group">
				<label for="model" class="col-sm-2 control-label">Model</label>
				<div class="col-sm-10">
					<input type="text" class="form-control" name="config[new][model]" value="" placeholder="">
				</div>
			</div>

			<div class="form-group">
				<label for="deviceType" class="col-sm-2 control-label">Type of Units/Devices</label>
				<div class="col-sm-10">
					<input type="text" class="form-control" name="config[new][deviceType]" value="" placeholder="">
				</div>
			</div>

			<div class="form-group">
				<label for="deviceQuantity" class="col-sm-2 control-label">Quantity of Units/Devices</label>
				<div class="col-sm-10">
					<input type="text" class="form-control" name="config[new][deviceQuantity]" value="" placeholder="">
				</div>
			</div>

			<div class="form-group">
				<label for="location" class="col-sm-2 control-label">Location</label>
				<div class="col-sm-10">
					<input type="text" class="form-control" name="config[new][location]" value="" placeholder="">
				</div>
			</div>

			<div class="form-group">
				<label for="managementType" class="col-sm-2 control-label">Management Type</label>
				<div class="col-sm-10">
					<input type="text" class="form-control" name="config[new][managementType]" value="" placeholder="">
				</div>
			</div>

			<div class="form-group">
				<label for="imageStatus" class="col-sm-2 control-label">Image Status</label>
				<div class="col-sm-10">
					<input type="text" class="form-control" name="config[new][imageStatus]" value="" placeholder="">
				</div>
			</div>

			<div class="form-group">
				<label for="vintages" class="col-sm-2 control-label">Vintages</label>
				<div class="col-sm-10">
					<input type="text" class="form-control" name="config[new][vintages]" value="" placeholder="">
				</div>
			</div>

	<a class="btn btn-info collapse-button collapsed" data-toggle="collapse" data-parent="#accordion2" href="#advancedNew" aria-expanded="true" aria-controls="advancedNew" style="margin-bottom: 1em;">
		Show/Hide Advanced Fields
	</a><br>
	<div id="accordion2">
		<div class="panel-collapse collapse" role="tabpanel" id="advancedNew">

			<div class="form-group">
				<label for="uniprintQueue" class="col-sm-2 control-label">Uniprint Queue</label>
				<div class="col-sm-10">
					<input type="text" class="form-control" name="config[new][uniprintQueue]" value="" placeholder="">
				</div>
			</div>

			<div class="form-group">
				<label for="releaseStationIp" class="col-sm-2 control-label">Release Station IP</label>
				<div class="col-sm-10">
					<input type="text" class="form-control" name="config[new][releaseStationIp]" value="" placeholder="">
				</div>
			</div>

			<div class="form-group">
				<label for="adBound" class="col-sm-2 control-label">AD Bound</label>
				<div class="col-sm-2">
					<input type="checkbox" class="checkbox" name="config[new][adBound]" value="">
				</div>
			</div>

		</div>
	</div>
			<br>
			<h5>Software for this configuration (new)</h5>
			<div class="form-group">
				<label for="config" class="col-sm-2 control-label">Available Titles</label>
				<div class="col-sm-10">
					<table class="table table-condensed">
						<thead>
							<tr>
								<th></th>
								<th>Title</th>
								<th>Version</th>
								<th>License</th>
								<th>Expires</th>
							</tr>
						</thead>
						<tbody>
				{foreach $softwareLicenses as $licenses}
					{foreach $licenses as $license}
						<tr>
							<td class="text-center">
								<input type="checkbox" name="config[new][licenses][{$license->id}]" id="config[new][licenses][{$license->id}]">
							</td>
							<td>
								<label for="config[new][licenses][{$license->id}]">
									{$license->version->title->name}
								</label>
							</td>
							<td>{$license->version->number}</td>
							<td>{$license->number}</td>
							<td>{$license->expirationDate->format('m/d/Y')}</td>
						</tr>
					{/foreach}
				{/foreach}
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>

</div>