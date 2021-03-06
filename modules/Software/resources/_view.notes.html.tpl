
<ul class="list-unstyled">

{foreach $notes as $note}
	<li>
		{$note->createdBy->fullName} ({$note->createdDate->format('Y-m-d h:i a')}): {$note->message}
		
		{if $note->oldValues}
		<a class="collapse-button collapsed" data-toggle="collapse" data-parent="#accordion" href="#noteHistory{$note->id}" aria-expanded="true" aria-controls="noteHistory{$note->id}" style="margin-left: 2em; font-weight: bold;">
			Show History
		</a>
		<div id="accordion">
			<div class="panel-collapse collapse" role="tabpanel" id="noteHistory{$note->id}">
				<table class="table table-bordered">
					<thead>
						<tr>
							<th scope="Field">Field</th>
							<th scope="Old">Old</th>
							<th scope="New">New</th>
						</tr>
					</thead>
					<tbody>
						{foreach $note->oldValues as $key => $value}
						<tr>
							<td scope="col">{$key|ucfirst}</td>
							<td>
								{if $value == 'checked'}
									<i class="glyphicon glyphicon-ok text-success"></i>
								{elseif $value == 'unchecked'}
									<i class="glyphicon glyphicon-remove text-danger"></i>
								{elseif is_array($value)}
									{foreach $value as $item}
										{$item}{if !$item@last}<br>{/if}
									{/foreach}
								{else}
									{if $value}{$value}{else}--{/if}
								{/if}
							</td>
							<td>
								{if $note->newValues[$key] == 'checked'}
									<i class="glyphicon glyphicon-ok text-success"></i>
								{elseif $note->newValues[$key] == 'unchecked'}
									<i class="glyphicon glyphicon-remove text-danger"></i>
								{elseif is_array($note->newValues[$key])}
									{foreach $note->newValues[$key] as $item}
										{$item}{if !$item@last}<br>{/if}
									{/foreach}
								{else}
									{$note->newValues[$key]}
								{/if}
							</td>
						</tr>
						{/foreach}
					</tbody>
				</table>
			</div>
		</div>
		{/if}
	</li>
{/foreach}

</ul>