@extends('layouts.master')

@section('toolbar')
	@if (auth()->user()->can('edit groups'))
		{!! Toolbar::save(route('admin.groups.members.store')) !!}
	@endif

	{!!
		Toolbar::spacer();
		Toolbar::cancel(route('admin.groups.members.cancel'));
	!!}

	{!! Toolbar::render() !!}
@stop

@section('title')
{!! config('groups.name') !!}: {{ $row->id ? 'Edit: #' . $row->id : 'Create' }}
@stop

@section('content')
<form action="{{ route('admin.groups.members.store') }}" method="post" name="adminForm" id="item-form" class="editform form-validate">

	@if ($errors->any())
		<div class="alert alert-danger">
			<ul>
				@foreach ($errors->all() as $error)
					<li>{{ $error }}</li>
				@endforeach
			</ul>
		</div>
	@endif

	<fieldset class="adminform">
		<legend><span>{{ trans('global.details') }}</span></legend>

		<div class="form-group" data-hint="{{ trans('courses::courses.name hint') }}">
			<label for="field-name">{{ trans('courses::courses.name') }}:</label>
			<input type="text" name="fields[name]" id="field-user" class="form-control disabled" disabled="disabled" readonly="readonly" value="{{ $row->user->name }}" />
		</div>
	</fieldset>

	<fieldset class="adminform">
		<legend>{{ trans('courses::courses.unix groups') }}</legend>

		<table>
			<thead>
				<tr>
					<th scope="col">{{ trans('courses::courses.unix group') }}</th>
					<th scope="col" class="text-center">{{ trans('courses::courses.member') }}</th>
					<th scope="col" class="text-right">Added</th>
				</tr>
			</thead>
			<tbody>
			@foreach ($row->group->unixGroups as $u)
				<tr id="unixgroup-{{ $u->id }}" data-id="{{ $u->id }}">
					<td>{{ $u->longname }}</td>
					<td class="text-center">
						@php
						$has = $u->members->search(function ($item, $key) use ($u)
						{
							return $item->unixgroupid == $u->id;
						});
						@endphp
						<div class="form-check">
							<input class="form-check-input" type="checkbox" id="unixgroup-{{ $u->id }}" name="unixgroups[{{ $u->id }}]" value="1"<?php if ($has !== false) { echo ' checked="checked"'; } ?> />
							<label class="form-check-label" for="unixgroup-{{ $u->id }}">{{ trans('global.yes') }}</label>
						</div>
					</td>
					<td class="text-right">
						@if ($has !== false)
							<time>{{ $u->datetimecreated }}</time>
						@endif
					</td>
				</tr>
			@endforeach
			</tbody>
		</table>
	</fieldset>

	<input type="hidden" name="userid" value="{{ $row->userid }}" />

	@csrf
</form>
@stop