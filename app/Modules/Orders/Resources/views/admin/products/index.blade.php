@extends('layouts.master')

@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/orders/css/orders.css') }}" />
@endpush

@push('scripts')
<script src="{{ asset('modules/orders/js/orders.js') }}"></script>
<script>
jQuery(document).ready(function($){
	$(".sortable").sortable({
		handle: '.drag-handle'/*,
		stop: function( event, ui ) {
			var data = "";

			$("#sortable li").each(function(i, el){
				var p = $(el).text().toLowerCase().replace(" ", "_");
				data += p+"="+$(el).index()+",";
			});

			$("form > [name='new_order']").val(data.slice(0, -1));
			$("form").submit();
		}*/
	});
});
</script>
@endpush

@php
app('pathway')
	->append(
		trans('orders::orders.module name'),
		route('admin.orders.index')
	)
	->append(
		trans('orders::orders.products')
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('delete orders.products'))
		{!! Toolbar::deleteList('', route('admin.orders.products.delete')) !!}
	@endif

	@if (auth()->user()->can('create orders.products'))
		{!! Toolbar::addNew(route('admin.orders.products.create')) !!}
	@endif

	@if (auth()->user()->can('admin orders'))
		{!!
			Toolbar::spacer();
			Toolbar::preferences('orders');
		!!}
	@endif

	{!! Toolbar::render() !!}
@stop

@section('title')
{!! config('orders.name') !!}: {{ trans('orders::orders.products') }}
@stop

@section('content')

@component('orders::admin.submenu')
	products
@endcomponent

<form action="{{ route('admin.orders.products') }}" method="post" name="adminForm" id="adminForm" class="form-inline">

	<fieldset id="filter-bar" class="container-fluid">
		<div class="row">
			<div class="col col-md-3 filter-search">
				<label class="sr-only" for="filter_search">{{ trans('search.label') }}</label>
				<span class="input-group">
					<input type="text" name="search" id="filter_search" class="form-control filter" placeholder="{{ trans('search.placeholder') }}" value="{{ $filters['search'] }}" />
					<span class="input-group-append"><span class="input-group-text"><span class="icon-search" aria-hidden="true"></span></span></span>
				</span>
			</div>
			<div class="col col-md-9 filter-select text-right">
				<label class="sr-only" for="filter_state">{{ trans('global.state') }}</label>
				<select name="state" id="filter_state" class="form-control filter filter-submit">
					<option value="*"<?php if ($filters['state'] == '*'): echo ' selected="selected"'; endif;?>>{{ trans('global.option.all states') }}</option>
					<option value="published"<?php if ($filters['state'] == 'published'): echo ' selected="selected"'; endif;?>>{{ trans('global.published') }}</option>
					<option value="trashed"<?php if ($filters['state'] == 'trashed'): echo ' selected="selected"'; endif;?>>{{ trans('global.trashed') }}</option>
				</select>

				<label class="sr-only" for="filter_category">{{ trans('orders::orders.category') }}</label>
				<select name="category" id="filter_category" class="form-control filter filter-submit">
					<option value="0"<?php if (!$filters['category']): echo ' selected="selected"'; endif;?>>{{ trans('orders::orders.all categories') }}</option>
					@foreach ($categories as $category)
						<option value="<?php echo $category->id; ?>"<?php if ($filters['category'] == $category->id): echo ' selected="selected"'; endif;?>>{{ $category->name }}</option>
					@endforeach
				</select>

				<label class="sr-only" for="filter_restricteddata">{{ trans('orders::orders.restricted data') }}</label>
				<select name="restricteddata" id="filter_restricteddata" class="form-control filter filter-submit">
					<option value="*"<?php if ($filters['restricteddata'] == '*'): echo ' selected="selected"'; endif;?>>{{ trans('orders::orders.all restricted data') }}</option>
					<option value="0"<?php if (!$filters['restricteddata']): echo ' selected="selected"'; endif;?>>{{ trans('global.no') }}</option>
					<option value="1"<?php if ($filters['restricteddata'] == 1): echo ' selected="selected"'; endif;?>>{{ trans('global.yes') }}</option>
				</select>

				<label class="sr-only" for="filter_public">{{ trans('orders::orders.visibility') }}</label>
				<select name="public" id="filter_public" class="form-control filter filter-submit">
					<option value="*"<?php if ($filters['public'] == '*'): echo ' selected="selected"'; endif;?>>{{ trans('orders::orders.all visibilities') }}</option>
					<option value="1"<?php if ($filters['public'] == 1): echo ' selected="selected"'; endif;?>>{{ trans('orders::orders.public') }}</option>
					<option value="0"<?php if (!$filters['public']): echo ' selected="selected"'; endif;?>>{{ trans('orders::orders.hidden') }}</option>
				</select>

				<label class="sr-only" for="filter_recurrence">{{ trans('orders::orders.recurrence') }}</label>
				<select name="recurrence" id="filter_recurrence" class="form-control filter filter-submit">
					<option value="*"<?php if ($filters['recurrence'] == '*'): echo ' selected="selected"'; endif;?>>{{ trans('orders::orders.all recurrence') }}</option>
					@foreach (App\Modules\Orders\Models\Timeperiod::all() as $timeperiod)
						<option value="<?php echo $timeperiod->id; ?>"<?php if ($filters['recurrence'] == $timeperiod->id): echo ' selected="selected"'; endif;?>>{{ $timeperiod->name }}</option>
					@endforeach
				</select>
			</div>
		</div>

		<input type="hidden" name="order" value="{{ $filters['order'] }}" />
		<input type="hidden" name="order_dir" value="{{ $filters['order_dir'] }}" />

		<button class="btn btn-secondary sr-only" type="submit">{{ trans('search.submit') }}</button>
	</fieldset>

	<div class="card w-100 mb-4">
	<table class="table table-hover adminlist">
		<thead>
			<tr>
				@if (auth()->user()->can('delete orders.products'))
					<th>
						{!! Html::grid('checkall') !!}
					</th>
				@endif
				<th scope="col" class="priority-5">
					{!! Html::grid('sort', trans('orders::orders.id'), 'id', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col">
					{!! Html::grid('sort', trans('orders::orders.name'), 'name', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="priority-2">
					{!! Html::grid('sort', trans('orders::orders.category'), 'ordercategoryid', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="priority-2 text-center">
					{!! Html::grid('sort', trans('orders::orders.recurrence'), 'recurringtimeperiodid', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="priority-2 text-right">
					{!! Html::grid('sort', trans('orders::orders.price'), 'unitprice', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="text-center">/</th>
				<th scope="col">
					{!! Html::grid('sort', trans('orders::orders.unit'), 'unit', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="priority-2 text-right" colspan="2">
					{!! Html::grid('sort', trans('orders::orders.sequence'), 'sequence', $filters['order_dir'], $filters['order']) !!}
				</th>
			</tr>
		</thead>
		<tbody class="sortable">
		@foreach ($rows as $i => $row)
			<tr<?php if ($row->istrashed()) { echo ' class="trashed"'; } ?>>
				@if (auth()->user()->can('delete orders.products'))
					<td>
						<span class="form-check"><input type="checkbox" name="id[]" id="cb{{ $i }}" value="{{ $row->id }}" class="form-check-input checkbox-toggle" /><label for="cb{{ $i }}"></label></span>
					</td>
				@endif
				<td class="priority-5">
					{{ $row->id }}
				</td>
				<td>
					@if ($row->istrashed())
						<span class="glyph icon-trash text-danger" aria-hidden="true" data-tip="{{ trans('global.trashed') }}"></span>
					@endif
					@if (!$row->public)
						<span class="glyph icon-eye-off text-warning" aria-hidden="true" data-tip="{{ trans('orders::orders.hidden') }}"></span>
					@endif
					@if (auth()->user()->can('edit orders.products'))
						<a href="{{ route('admin.orders.products.edit', ['id' => $row->id]) }}">
							{{ $row->name }}
						</a>
					@else
						{{ $row->name }}
					@endif
				</td>
				<td class="priority-2">
					{!! $row->category_name ? $row->category_name : '<span class="unknown">' . trans('global.unknown') . '</span>' !!}
				</td>
				<td class="priority-2 text-center">
					{{ $row->timeperiod ? $row->timeperiod->name : '' }}
				</td>
				<td class="priority-2 text-right">
					{{ number_format($row->unitprice / 100, 2) }}
				</td>
				<td class="text-center">
					/
				</td>
				<td>
					{{ $row->unit }}
				</td>
				<td class="priority-2 text-right">
					{{ $row->sequence }}
				</td>
				<td class="text-right">
					@if ($filters['order'] == 'sequence')
						<span class="drag-handle" draggable="true">
							<svg class="MiniIcon DragMiniIcon DragHandle-icon" viewBox="0 0 24 24"><path d="M10,4c0,1.1-0.9,2-2,2S6,5.1,6,4s0.9-2,2-2S10,2.9,10,4z M16,2c-1.1,0-2,0.9-2,2s0.9,2,2,2s2-0.9,2-2S17.1,2,16,2z M8,10 c-1.1,0-2,0.9-2,2s0.9,2,2,2s2-0.9,2-2S9.1,10,8,10z M16,10c-1.1,0-2,0.9-2,2s0.9,2,2,2s2-0.9,2-2S17.1,10,16,10z M8,18 c-1.1,0-2,0.9-2,2s0.9,2,2,2s2-0.9,2-2S9.1,18,8,18z M16,18c-1.1,0-2,0.9-2,2s0.9,2,2,2s2-0.9,2-2S17.1,18,16,18z"></path></svg>
						</span>
					@endif
				</td>
			</tr>
		@endforeach
		</tbody>
	</table>
	</div>

	{{ $rows->render() }}

	<input type="hidden" name="task" value="" autocomplete="off" />
	<input type="hidden" name="boxchecked" value="0" />

	@csrf
</form>

@stop