@extends('layouts.master')

@push('scripts')
<script src="{{ asset('modules/history/js/admin.js?v=' . filemtime(public_path() . '/modules/history/js/admin.js')) }}"></script>
@endpush

@php
app('pathway')
	->append(
		trans('history::history.module name'),
		route('admin.history.index')
	)
	->append(
		trans('history::history.activity'),
		route('admin.history.activity')
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('admin history'))
		{!!
			Toolbar::spacer();
			Toolbar::preferences('history');
		!!}
	@endif

	{!! Toolbar::render() !!}
@stop

@section('title')
{{ trans('history::history.activity') }}
@stop

@section('content')

@component('history::admin.submenu')
	<?php echo request()->segment(3); ?>
@endcomponent

<form action="{{ route('admin.history.activity') }}" method="get" name="adminForm" id="adminForm" class="form-inline">
	<fieldset id="filter-bar" class="container-fluid">
		<div class="row">
			<div class="col filter-search col-md-4">
				<div class="form-group">
					<label class="sr-only" for="filter_search">{{ trans('search.label') }}</label>
					<span class="input-group">
						<input type="text" name="search" id="filter_search" class="form-control filter" placeholder="{{ trans('search.placeholder') }}" value="{{ $filters['search'] }}" />
						<span class="input-group-append"><span class="input-group-text"><span class="icon-search" aria-hidden="true"></span></span></span>
					</span>
				</div>
			</div>
			<div class="col filter-select col-md-8 text-right">
				<label class="sr-only" for="filter_status">{{ trans('history::history.status') }}</label>
				<select name="status" id="filter_status" class="form-control filter filter-submit">
					<option value=""<?php if ($filters['status'] == ''): echo ' selected="selected"'; endif;?>>{{ trans('history::history.all status') }}</option>
					<option value="200"<?php if ($filters['status'] == '200'): echo ' selected="selected"'; endif;?>>200</option>
					<option value="400"<?php if ($filters['status'] == '400'): echo ' selected="selected"'; endif;?>>400</option>
					<option value="403"<?php if ($filters['status'] == '403'): echo ' selected="selected"'; endif;?>>403</option>
					<option value="404"<?php if ($filters['status'] == '404'): echo ' selected="selected"'; endif;?>>404</option>
					<option value="415"<?php if ($filters['status'] == '415'): echo ' selected="selected"'; endif;?>>415</option>
					<option value="500"<?php if ($filters['status'] == '500'): echo ' selected="selected"'; endif;?>>500</option>
				</select>

				<label class="sr-only" for="filter_transport">{{ trans('history::history.transport') }}</label>
				<select name="transport" id="filter_transport" class="form-control filter filter-submit">
					<option value=""<?php if ($filters['transport'] == ''): echo ' selected="selected"'; endif;?>>{{ trans('history::history.all transports') }}</option>
					<option value="GET"<?php if ($filters['transport'] == 'GET'): echo ' selected="selected"'; endif;?>>GET</option>
					<option value="POST"<?php if ($filters['transport'] == 'POST'): echo ' selected="selected"'; endif;?>>POST</option>
					<option value="PUT"<?php if ($filters['transport'] == 'PUT'): echo ' selected="selected"'; endif;?>>PUT</option>
					<option value="DELETE"<?php if ($filters['transport'] == 'DELETE'): echo ' selected="selected"'; endif;?>>DELETE</option>
				</select>
			</div>
		</div>

		<input type="hidden" name="order" value="{{ $filters['order'] }}" />
		<input type="hidden" name="order_dir" value="{{ $filters['order_dir'] }}" />

		<button class="btn btn-secondary sr-only" type="submit">{{ trans('search.submit') }}</button>
	</fieldset>

	<div class="card mb-4">
	<table class="table table-hover adminlist">
		<caption class="sr-only">{{ trans('history::history.activity') }}</caption>
		<thead>
			<tr>
				<th scope="col">
					{!! Html::grid('sort', trans('history::history.id'), 'id', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col">
					{!! Html::grid('sort', trans('history::history.method'), 'classname', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col">
					{!! Html::grid('sort', trans('history::history.ip'), 'ip', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col">
					{!! Html::grid('sort', trans('history::history.uri'), 'uri', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col">
					{!! Html::grid('sort', trans('history::history.transport'), 'transportmethod', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col">
					{!! Html::grid('sort', trans('history::history.status'), 'status', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col">
					{{ trans('history::history.actor') }}
				</th>
				<th scope="col" class="priority-4">
					{!! Html::grid('sort', trans('history::history.timestamp'), 'datetime', $filters['order_dir'], $filters['order']) !!}
				</th>
			</tr>
		</thead>
		<tbody>
		@foreach ($rows as $i => $row)
			<?php
			$cls = '';
			if ($row->status >= 400)
			{
				$cls = ' class="error-warning"';
			}
			if ($row->status >= 500)
			{
				$cls = ' class="error-danger"';
			}
			?>
			<tr{!! $cls !!}>
				<td class="priority-5">
					{{ $row->id }}
				</td>
				<td>
					@if ($row->app == 'ws' || $row->app == 'api')
						<span class="icon-code" data-tip="API"></span>
					@elseif ($row->app == 'ui')
						<span class="icon-layout" data-tip="Portal"></span>
					@else
						<span class="icon-activity" data-tip="{{ $row->app }}"></span>
					@endif
					@if ($row->classname || $row->classmethod)
						{{ $row->classname . '::' . $row->classmethod }}
					@else
						<span class="unknown">{{ trans('global.unknown') }}</span>
					@endif
				</td>
				<td>
					@if (!$row->ip || $row->ip == '::1')
						loalhost
					@else
						{{ $row->ip }}
					@endif
				</td>
				<td>
					{{ $row->uri }}
				</td>
				<td>
					@if ($row->transportmethod == 'DELETE')
						<span class="badge badge-danger">{{ $row->transportmethod }}</span>
					@elseif ($row->transportmethod == 'POST')
						<span class="badge badge-success">{{ $row->transportmethod }}</span>
					@elseif ($row->transportmethod == 'PUT')
						<span class="badge badge-info">{{ $row->transportmethod }}</span>
					@elseif ($row->transportmethod == 'GET')
						<span class="badge badge-info">{{ $row->transportmethod }}</span>
					@endif
				</td>
				<td class="priority-4">
					{{ $row->status }}
				</td>
				<td>
					@if ($row->user)
						<a href="{{ route('admin.users.edit', ['id' => $row->user->id]) }}">
							{{ $row->user->name }}
						</a>
					@else
						<span class="unknown">{{ trans('global.unknown') }}</span>
					@endif
				</td>
				<td class="priority-4">
					@if ($row->datetime && $row->datetime != '0000-00-00 00:00:00')
						<time datetime="{{ $row->datetime->format('Y-m-d\TH:i:s\Z') }}">{{ $row->datetime }}</time>
					@else
						<span class="never">{{ trans('global.unknown') }}</span>
					@endif
				</td>
			</tr>
		@endforeach
		</tbody>
	</table>
	</div>

	{{ $rows->render() }}

	<input type="hidden" name="boxchecked" value="0" />

	@csrf
</form>

@stop