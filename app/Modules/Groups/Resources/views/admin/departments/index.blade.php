@extends('layouts.master')

@php
app('pathway')
	->append(
		trans('groups::groups.module name'),
		route('admin.groups.index')
	)
	->append(
		trans('groups::groups.departments'),
		route('admin.groups.departments')
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('delete groups'))
		{!! Toolbar::deleteList(trans('global.confirm delete'), route('admin.groups.departments.delete')) !!}
	@endif

	@if (auth()->user()->can('create groups'))
		{!! Toolbar::addNew(route('admin.groups.departments.create')) !!}
	@endif

	@if (auth()->user()->can('admin groups'))
		{!!
			Toolbar::spacer();
			Toolbar::preferences('groups')
		!!}
	@endif

	{!! Toolbar::render() !!}
@stop

@section('title')
{!! config('groups.name') !!}: {{ trans('groups::groups.departments') }}
@stop

@section('content')
@component('groups::admin.submenu')
	<?php echo request()->segment(3); ?>
@endcomponent

<form action="{{ route('admin.groups.departments') }}" method="post" name="adminForm" id="adminForm" class="form-inline">

	<fieldset id="filter-bar" class="container-fluid">
		<div class="row">
			<div class="col col-md-12">
				<div class="form-group">
					<label class="sr-only" for="filter_search">{{ trans('search.label') }}</label>
					<span class="input-group">
						<input type="text" name="search" id="filter_search" class="form-control filter" placeholder="{{ trans('search.placeholder') }}" value="{{ $filters['search'] }}" />
						<span class="input-group-append"><span class="input-group-text"><span class="icon-search" aria-hidden="true"></span></span></span>
					</span>
				</div>

				<button class="btn btn-secondary sr-only" type="submit">{{ trans('search.submit') }}</button>
			</div>
		</div>

		<input type="hidden" name="order" value="{{ $filters['order'] }}" />
		<input type="hidden" name="order_dir" value="{{ $filters['order_dir'] }}" />
	</fieldset>

	<div class="card mb-4">
	<table class="table table-hover adminlist">
		<caption class="sr-only">{{ trans('groups::groups.departments') }}</caption>
		<thead>
			<tr>
				@if (auth()->user()->can('delete groups.departments'))
					<th>
						{!! Html::grid('checkall') !!}
					</th>
				@endif
				<th scope="col" class="priority-5">
					{!! Html::grid('sort', trans('groups::groups.id'), 'id', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col">
					{!! Html::grid('sort', trans('groups::groups.name'), 'name', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="priority-4 text-right">
					{!! Html::grid('sort', trans('groups::groups.groups'), 'members_count', $filters['order_dir'], $filters['order']) !!}
				</th>
			</tr>
		</thead>
		<tbody>
		@foreach ($rows as $i => $row)
			<tr>
				@if (auth()->user()->can('delete groups.departments'))
					<td>
						<span class="form-check"><input type="checkbox" name="id[]" id="cb{{ $i }}" value="{{ $row->id }}" class="form-check-input checkbox-toggle" /><label for="cb{{ $i }}"></label></span>
					</td>
				@endif
				<td class="priority-5">
					@if (auth()->user()->can('edit groups'))
						<a href="{{ route('admin.groups.departments.edit', ['id' => $row->id]) }}">
					@endif
					{{ $row->id }}
					@if (auth()->user()->can('edit groups'))
						</a>
					@endif
				</td>
				<td>
					<span class="gi">{!! str_repeat('|&mdash;', $row->level - 1) !!}</span>
					@if (auth()->user()->can('edit groups'))
						<a href="{{ route('admin.groups.departments.edit', ['id' => $row->id]) }}">
					@endif
						{{ $row->name }}
					@if (auth()->user()->can('edit groups'))
						</a>
					@endif
				</td>
				<td class="priority-4 text-right">
					<a href="{{ route('admin.groups.index', ['department' => $row->id]) }}">
						{{ $row->groups()->count() }}
					</a>
				</td>
			</tr>
		@endforeach
		</tbody>
	</table>
</div>

	{{ $paginator->render() }}

	<input type="hidden" name="task" value="" autocomplete="off" />
	<input type="hidden" name="boxchecked" value="0" />

	@csrf
</form>
@stop
