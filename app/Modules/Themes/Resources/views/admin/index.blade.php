@extends('layouts.master')

@php
app('pathway')
	->append(
		trans('themes::themes.module name'),
		route('admin.themes.index')
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('delete themes'))
		{!! Toolbar::deleteList('', route('admin.themes.delete')) !!}
	@endif

	@if (auth()->user()->can('admin themes'))
		{!!
			Toolbar::spacer();
			Toolbar::preferences('themes')
		!!}
	@endif

	{!! Toolbar::render() !!}
@stop

@section('title')
trans('themes::themes.module name')
@stop

@section('content')
<form action="{{ route('admin.themes.store') }}" method="post" name="adminForm" id="adminForm" class="form-inline">

	<fieldset id="filter-bar" class="container-fluid">
		<div class="row">
			<div class="col col-md-4 filter-search">
				<label class="sr-only" for="filter_search">{{ trans('search.label') }}</label>
				<input type="text" name="search" id="filter_search" class="form-control filter" placeholder="{{ trans('search.placeholder') }}" value="" />

				<button class="btn btn-secondary" type="submit">{{ trans('search.submit') }}</button>
			</div>
			<div class="col col-md-8 text-right filter-select">
				<label class="sr-only" for="filter_clinet_id">{{ trans('themes::themes.type') }}</label>
				<select name="clinet_id" id="filter_clinet_id" class="form-control filter filter-submit">
					<option value="*">{{ trans('themes::themes.all') }}</option>
					<option value="0"<?php if ($filters['client_id'] == '0'): echo ' selected="selected"'; endif;?>>{{ trans('themes::themes.site') }}</option>
					<option value="1"<?php if ($filters['client_id'] == '1'): echo ' selected="selected"'; endif;?>>{{ trans('themes::themes.admin') }}</option>
				</select>
			</div>
		</div>
	</fieldset>

	<table class="table table-hover adminlist">
		<caption class="sr-only">{{ trans('themes::themes.themes') }}</caption>
		<thead>
			<tr>
				<th>
					{!! Html::grid('checkall') !!}
				</th>
				<th scope="col" class="priority-5">
					{!! Html::grid('sort', trans('themes::themes.id'), 'id', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col">
					{!! Html::grid('sort', trans('themes::themes.title'), 'title', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col">
					{!! Html::grid('sort', trans('themes::themes.type'), 'client_id', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="priority-4">
					{!! Html::grid('sort', trans('themes::themes.home'), 'home', $filters['order_dir'], $filters['order']) !!}
				</th>
				<!-- <th scope="col" class="priority-3">
					{{ trans('themes::themes.assigned') }}
				</th> -->
			</tr>
		</thead>
		<tbody>
		<?php
		//$canCreate = auth()->user()->can('create themes');
		$canEdit = auth()->user()->can('edit themes') || auth()->user()->can('edit.state themes');
		$canDelete = auth()->user()->can('delete themes');
		?>
		@foreach ($rows as $i => $row)
			<tr>
				<td>
					@if ($canDelete)
						<span class="form-check"><input type="checkbox" name="id[]" id="cb{{ $i }}" value="{{ $row->id }}" class="form-check-input checkbox-toggle" /><label for="cb{{ $i }}"></label></span>
					@endif
				</td>
				<td class="priority-5">
					{{ $row->id }}
				</td>
				<td>
					@if ($canEdit)
						<a href="{{ route('admin.themes.edit', ['id' => $row->id]) }}">
							{{ $row->name }}
						</a>
					@else
						{{ $row->name }}
					@endif
					@if (!$row->path())
						<p class="smallsub">{{ trans('COM_TEMPLATES_ERROR_MISSING_FILES') }}</p>
					@endif
				</td>
				<td>
					@if ($canEdit)
						<a href="{{ route('admin.themes.edit', ['id' => $row->id]) }}">
							{{ $row->client_id ? trans('themes::themes.admin') : trans('themes::themes.site') }}
						</a>
					@else
						{{ $row->client_id ? trans('themes::themes.admin') : trans('themes::themes.site') }}
					@endif
				</td>
				<td class="priority-4">
					@if ($row->enabled)
						<span class="badge yes">
							{{ trans('global.yes') }}
						</span>
					@else
						<span class="badge no">
							{{ trans('global.no') }}
						</span>
					@endif
				</td>
				<!-- <td class="priority-4">
					@if ($row->assigned > 0)
						<span class="badge yes" title="{{ trans('themes::themes.ASSIGNED', ['count' => $item->assigned]) }}">
							<span class="text">{{ trans('themes::themes.ASSIGNED', ['count' => $item->assigned]) }}</span>
						</span>
					@else
						&#160;
					@endif
				</td> -->
			</tr>
		@endforeach
		</tbody>
	</table>

	{{ $rows->render() }}

	<input type="hidden" name="boxchecked" value="0" />

	@csrf
</form>

@stop