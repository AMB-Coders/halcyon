@extends('layouts.master')

@php
app('request')->merge(['hidemainmenu' => 1]);

app('pathway')
	->append(
		trans('queues::queues.module name'),
		route('admin.queues.index')
	)
	->append(
		trans('queues::queues.schedulers'),
		route('admin.queues.schedulers')
	)
	->append(
		($row->id ? trans('global.edit') . ' #' . $row->id : trans('global.create'))
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('edit queues.schedulers'))
		{!! Toolbar::save(route('admin.queues.schedulers.store')) !!}
	@endif

	{!!
		Toolbar::spacer();
		Toolbar::cancel(route('admin.queues.schedulers.cancel'));
	!!}

	{!! Toolbar::render() !!}
@stop

@php
	app('request')->merge(['hidemainmenu' => 1]);
@endphp

@section('title')
{!! config('queues.name') !!}: {{ trans('queues::queues.schedulers') }}: {{ $row->id ? trans('queues::queues.edit') . ': #' . $row->id : trans('queues::queues.create') }}
@stop

@section('content')
<form action="{{ route('admin.queues.schedulers.store') }}" method="post" name="adminForm" id="item-form" class="editform form-validate">
	<div class="grid row">
		<div class="col col-md-7 span7">
			<fieldset class="adminform">
				<legend>{{ trans('global.details') }}</legend>

				<div class="form-group">
					<label for="field-hostname">{{ trans('queues::queues.hostname') }}: <span class="required">{{ trans('global.required') }}</span></label>
					<input type="text" name="fields[hostname]" id="field-hostname" class="form-control required" value="{{ $row->hostname }}" />
				</div>

				<div class="form-group">
					<?php
					$day = 60 * 60 * 24;
					$hour = 60 * 60;
					$min = 60;

					if ($row->defaultmaxwalltime > $day)
					{
						$sel = 'days';
						$val = $row->defaultmaxwalltime / 60 / 60 / 24;
					}
					elseif ($row->defaultmaxwalltime > $hour)
					{
						$sel = 'hours';
						$val = $row->defaultmaxwalltime / 60 / 60;
					}
					elseif ($row->defaultmaxwalltime > $min)
					{
						$sel = 'minutes';
						$val = $row->defaultmaxwalltime / 60;
					}
					else
					{
						$sel = 'seconds';
						$val = $row->defaultmaxwalltime;
					}
					?>
					<label for="field-defaultmaxwalltime">{{ trans('queues::queues.default max walltime') }}:</label>
					<div class="row">
						<div class="col-md-8">
							<input type="number" name="maxwalltime" id="field-maxwalltime" class="form-control" value="{{ $val }}" />
						</div>
						<div class="col-md-4">
							<select class="form-control" name="unit">
								<option value="seconds"<?php if ($sel == 'seconds') { echo ' selected="selected"'; } ?>>{{ trans_choice('global.time.seconds', 2) }}</option>
								<option value="minutes"<?php if ($sel == 'minutes') { echo ' selected="selected"'; } ?>>{{ trans_choice('global.time.minutes', 2) }}</option>
								<option value="hours"<?php if ($sel == 'hours') { echo ' selected="selected"'; } ?>>{{ trans_choice('global.time.hours', 2) }}</option>
								<option value="days"<?php if ($sel == 'days') { echo ' selected="selected"'; } ?>>{{ trans_choice('global.time.days', 2) }}</option>
							</select>
						</div>
					</div>
					<input type="hidden" name="fields[defaultmaxwalltime]" value="{{ $row->defaultmaxwalltime }}" />
				</div>

				<div class="form-group">
					<label for="field-queuesubresourceid">{{ trans('queues::queues.resource') }}:</label>
					<select name="fields[queuesubresourceid]" id="field-queuesubresourceid" class="form-control">
						<option value="0">{{ trans('global.none') }}</option>
						<?php foreach ($resources as $resource): ?>
							<?php $children = $resource->children()->get();
							if (count($children)) { ?>
								<optgroup label="{{ $resource->name }}">
									<?php foreach ($children as $child): ?>
										<option value="{{ $child->subresourceid }}"<?php if ($row->queuesubresourceid == $child->subresourceid): echo ' selected="selected"'; endif;?>>{{ $child->subresource ? $child->subresource->name : trans('global.unknown') }}</option>
									<?php endforeach; ?>
								</optgroup>
							<?php } ?>
						<?php endforeach; ?>
					</select>
				</div>

				<div class="form-group">
					<label for="field-batchsystem">{{ trans('queues::queues.batch system') }}:</label>
					<select name="fields[batchsystem]" id="field-batchsystem" class="form-control">
						<option value="0">{{ trans('queues::queues.all batch systems') }}</option>
						<?php foreach ($batchsystems as $batchsystem): ?>
							<?php $selected = ($batchsystem->id == $row->batchsystem ? ' selected="selected"' : ''); ?>
							<option value="{{ $batchsystem->id }}"<?php echo $selected; ?>>{{ $batchsystem->name }}</option>
						<?php endforeach; ?>
					</select>
				</div>

				<div class="form-group">
					<label for="field-schedulerpolicyid">{{ trans('queues::queues.scheduler policy') }}:</label>
					<select name="fields[schedulerpolicyid]" id="field-schedulerpolicyid" class="form-control">
						<option value="0">{{ trans('queues::queues.all scheduler policies') }}</option>
						<?php foreach ($policies as $policy): ?>
							<?php $selected = ($policy->id == $row->schedulerpolicyid ? ' selected="selected"' : ''); ?>
							<option value="{{ $policy->id }}"<?php echo $selected; ?>>{{ $policy->name }}</option>
						<?php endforeach; ?>
					</select>
				</div>
			</fieldset>
		</div>
		<div class="col col-md-5 span5">
			@include('history::admin.history')
		</div>
	</div>

	<input type="hidden" name="id" id="field-id" value="{{ $row->id }}" />

	@csrf
</form>
@stop