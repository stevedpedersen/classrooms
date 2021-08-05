{if $location->id}
	<h1>Edit Room <small><a href="{$location->roomUrl}">{$location->building->name} {$location->number}</a></small></h1>
{else}
	<h1>New Room</h1>
{/if}

<div class="row pull-right" style="margin-bottom: 2em;">
	<div class="col-sm-12">
	{if $location->inDatasource}
		{if $location->tutorial}
			<a href="rooms/{$location->id}/tutorials/{$location->tutorial->id}/edit" class="btn btn-info">Edit Tutorial</a>
		{else}
			<a href="rooms/{$location->id}/tutorials/new/edit" class="btn btn-success">Add New Tutorial</a>
		{/if}
	{/if}
	</div>
</div>


{if $customConfigurations && count($customConfigurations) > 0}
<br><br><br>
<form action="" method="get" id="selectConfiguration">
	<div class="container">
		<hr>
		<div class="row">
			<div class="col-sm-12">
				<div class="form-horizontal">

					<div class="form-group existing-items">
						<label for="configuration" class="col-sm-2 control-label">Selected Configuration</label>
						<div class="col-sm-8">
							<select name="configuration" id="configuration" class="form-control">
								<option value="">Choose Configuration</option>
							{foreach $customConfigurations as $configuration}
								{if !$configuration->deleted}
								<option value="{$configuration->id}" {if $selectedConfiguration->id == $configuration->id}selected{/if}>{$configuration->model}{if $configuration->location} - {$configuration->location}{/if}</option>
								{/if}
							{/foreach}
							</select>
						</div>
						<div class="controls col-sm-2 edit">
							<button class="btn btn-primary pull-right">Go</button>
							<a href="rooms/{$location->id}/configurations/{$selectedConfiguration->id}/edit" data-baseurl="rooms/{$location->id}/configurations/" class="btn btn-info pull-right">Edit</a>
						</div>
					</div>

				</div>
			</div>
		</div>
	</div>
</form>
{/if}

<form action="" method="post">
<div class="container"> 
<div class="row">
	<div class="col-xs-12 edit-details">
				
		<div class="panel panel-default details">
			<div class="panel-heading"><h2>Room Details</h2></div>
			<div class="panel-body">
				<div class="form-horizontal">
					<div class="form-group">
						<label for="building" class="col-sm-2 control-label">Building</label>
						<div class="col-sm-10">
							<select name="room[building]" id="building" class="form-control" required {if $location->building_id}readonly{/if}>
								<option value="">Choose Building</option>
							{foreach $buildings as $building}
								<option value="{$building->id}"{if $location->building_id == $building->id} selected{/if}>{$building->code} - {$building->name}</option>
							{/foreach}
							</select>
						</div>
					</div>

					<div class="form-group">
						<label for="type" class="col-sm-2 control-label">Type</label>
						<div class="col-sm-10">
							<select name="room[type]" id="type" class="form-control" required>
								<option value="">Choose Room Type</option>
							{foreach $types as $type}
								<option value="{$type->id}"{if $location->type_id == $type->id} selected{/if}>{$type->name}</option>
							{/foreach}
							</select>
						</div>
					</div>

					<div class="form-group">
						<label for="number" class="col-sm-2 control-label">Room #</label>
						<div class="col-sm-2">
							<input type="text" class="form-control" name="room[number]" value="{$location->number}" placeholder="#" {if $location->number}readonly{/if}>
						</div>
					</div>

					<div class="form-group">
						<label for="description" class="col-sm-2 control-label">Description</label>
						<div class="col-sm-10">
							<input type="text" class="form-control" name="room[description]" value="{$location->description}" placeholder="Brief description of room">
						</div>
					</div>

					<div class="form-group">
						<label for="capacity" class="col-sm-2 control-label">Capacity</label>
						<div class="col-sm-2">
							<input type="text" class="form-control" name="room[capacity]" value="{$location->capacity}" placeholder="">
						</div>
					</div>

					<div class="form-group">
						<label for="scheduledBy" class="col-sm-2 control-label">Scheduled By</label>
						<div class="col-sm-10">
							<select class="form-control" name="room[scheduledBy]">
								<option value="">Select...</option>
							{foreach $scheduledBy as $sb}
								<option value="{$sb}" {if $location->scheduledBy == $sb}selected{/if}>
									{$sb}
								</option>
							{/foreach}
							</select>
							<p class="help-block"><a href="rooms/metadata" class="text-muted">Edit these options here</a></p>
						</div>
					</div>

					<div class="form-group">
						<label for="supportedBy" class="col-sm-2 control-label">Supported By</label>
						<div class="col-sm-10">
							<select class="form-control" name="room[supportedBy]">
								<option value="">Select...</option>
							{foreach $supportedBy as $sb}
								<option value="{$sb}" {if $location->supportedBy == $sb}selected{/if}>
									{$sb}
								</option>
							{/foreach}
							</select>
							<p class="help-block"><a href="rooms/metadata" class="text-muted">Edit these options here</a></p>
						</div>
					</div>

<!-- 					<div class="form-group">
						<label for="url" class="col-sm-2 control-label">URL</label>
						<div class="col-sm-10">
							<input type="text" class="form-control" name="room[url]" value="{$location->url}" placeholder="Website URL if applicable">
						</div>
					</div> -->

					<div class="form-group">
						<label for="avEquipment" class="col-sm-2 control-label">A/V Equipment</label>
						<div class="col-sm-10">
							<table class="table table-bordered" id="avEquipment">
								<thead>
									<tr>
									{foreach $allAvEquipment as $key => $equipment}
										<th scope="{$equipment}">{$equipment}</th>
									{/foreach}
									</tr>
								</thead>
								<tbody>
									<tr>
									{foreach $allAvEquipment as $key => $equipment}
										<td class="text-centers">
											<input type="checkbox" name="room[avEquipment][{$key}]" {if $roomAvEquipment[$key]}checked{/if}>
										</td>
									{/foreach}
									</tr>
								</tbody>
							</table>
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
			<input type="text" class="form-control" name="room[uniprintQueue]" value="{$room->uniprintQueue}" placeholder="">
		</div>
	</div>

	<div class="form-group">
		<label for="releaseStationIp" class="col-sm-2 control-label">Release Station IP</label>
		<div class="col-sm-10">
			<input type="text" class="form-control" name="room[releaseStationIp]" value="{$room->releaseStationIp}" placeholder="">
		</div>
	</div>

	<div class="form-group">
		<label for="printerModel" class="col-sm-2 control-label">Printer Model</label>
		<div class="col-sm-10">
			<input type="text" class="form-control" name="room[printerModel]" value="{$room->printerModel}" placeholder="">
		</div>
	</div>

	<div class="form-group">
		<label for="printerIp" class="col-sm-2 control-label">Printer IP</label>
		<div class="col-sm-10">
			<input type="text" class="form-control" name="room[printerIp]" value="{$room->printerIp}" placeholder="">
		</div>
	</div>

	<div class="form-group">
		<label for="printerServer" class="col-sm-2 control-label">Printer Server</label>
		<div class="col-sm-10">
			<input type="text" class="form-control" name="room[printerServer]" value="{$room->printerServer}" placeholder="">
		</div>
	</div>

	</div>
</div>

				</div>
				
			</div>
		</div>

		<div class="panel panel-default bundles">
			<div class="panel-heading"><h2>Tutorial</h2></div>
			<div class="panel-body">
				
				<div class="form-horizontal">
					<div class="form-group">
						<label for="room[tutorial]" class="col-sm-2 control-label">Attach a tutorial to this room</label>
						<div class="col-sm-10">
						<select name="room[tutorial]" class="form-control">
							<option value="">Choose tutorial...</option>
						{foreach $tutorials as $tutorial}
							<option value="{$tutorial->id}" {if $location->tutorial_id == $tutorial->id}selected{/if}>{$tutorial->name}</option>
						{/foreach}
						</select>
						</div>
					</div>
				</div>
			</div>
		</div>

	{if $bundles}
		<div class="panel panel-default bundles">
			<div class="panel-heading"><h2>Configuration Bundles</h2></div>
			<div class="panel-body">
				
				<div class="form-horizontal">
					<div class="form-group">
						<label for="bundles" class="col-sm-2 control-label">Available Bundles</label>
						<div class="col-sm-10">
						
							<table class="table table-bordered table-condensed table-striped">
								<thead>
									<tr>
										<th scope="col"></th>
										<th scope="col">Model</th>
										<th scope="col"># of software licenses</th>
									</tr>
								</thead>
								<tbody>
							{foreach $bundles as $bundle}
								<tr>
									<td class="text-center">
										<input type="checkbox" name="bundles[{$bundle->id}]" id="bundles[{$bundle->id}]" {if $location->configurations->has($bundle)}checked{/if}>
									</td>
									<td><label for="bundles[{$bundle->id}]">{$bundle->model}</label></td>
									<td>{$bundle->softwareLicenses->count()}</td>
								</tr>
							{/foreach}
								</tbody>
							</table>
						
						</div>
					</div>
				</div>
			</div>
		</div>
	{/if}

		{include file="partial:_configurations.html.tpl"}

		<div class="panel panel-default notes">
			<div class="panel-heading"><h2>Internal Notes</h2></div>
			<div class="panel-body">
				<div class="form-group">
					<label for="internalNote" class="col-sm-2 control-label">Add Internal Note</label>
					<div class="col-sm-10">
						<input type="text" class="form-control" name="internalNote" value="" placeholder="">
					</div>
				</div>

				{if count($location->internalNotes)}
				<div class="form-group">
					<label for="internalNote" class="col-sm-2 control-label">
						<a class="collapse-button collapsed" data-toggle="collapse" data-parent="#accordion1" href="#showNotes" aria-expanded="true" aria-controls="showNotes" style="margin-bottom: 1em;">
							Past Notes &nbsp;
						</a>
					</label>
					<div class="col-sm-10">
					<div id="accordion1">
						<div class="panel-collapse collapse" role="tabpanel" id="showNotes">
							<ul class="">
							{foreach $location->internalNotes as $note}
								<li>
									<strong>{$note->addedBy->fullname} on {$note->createdDate->format('Y/m/d')}:</strong> {$note->message}
								</li>
							{/foreach}
							</ul>
						</div>
					</div>
					</div>
				</div>
				{/if}

			</div>
		</div>


		<div class="controls">
			{generate_form_post_key}
			<input type="hidden" name="locationId" value="{$location->id}">
			<button type="submit" name="command[save]" class="btn btn-primary">Save Room</button>
			<a href="{$location->roomUrl}" class="btn btn-default">Cancel</a>
			<button type="submit" name="command[delete]" class="btn btn-danger pull-right">Delete</button>
		</div>

	</div>
</div>
</div>
</form>

{if $notes}
	<hr>
	<h2>Notes</h2>
	{include file="partial:_view.notes.html.tpl"}
{/if}
