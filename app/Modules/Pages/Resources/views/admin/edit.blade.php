@extends('layouts.master')

@section('scripts')
<script src="{{ asset('modules/core/js/validate.js?v=' . filemtime(public_path() . '/modules/core/js/validate.js')) }}"></script>
<script src="{{ asset('modules/pages/js/pages.js?v=' . filemtime(public_path() . '/modules/pages/js/pages.js')) }}"></script>
@stop

@php
app('pathway')
	->append(
		trans('pages::pages.module name'),
		route('admin.pages.index')
	)
	->append(
		($row->id ? trans('global.edit') . ' #' . $row->id : trans('global.create'))
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('edit users'))
		{!! Toolbar::save(route('admin.pages.store')) !!}
	@endif

	{!!
		Toolbar::spacer();
		Toolbar::cancel(route('admin.pages.cancel'));
	!!}

	{!! Toolbar::render() !!}
@stop

@section('title')
{!! config('pages.name') !!}: {{ $row->id ? 'Edit: #' . $row->id : 'Create' }}
@stop

@section('content')
<form action="{{ route('admin.pages.store') }}" method="post" name="adminForm" id="item-form" class="editform form-validate" data-invalid-msg="{{ trans('global.validation failed') }}">

	<input type="hidden" name="id" id="field-id" value="{{ $row->id }}" />

	<div class="row grid">
		<div class="col col-md-8">
			<fieldset class="adminform">
				<legend>{{ trans('global.details') }}</legend>

				<?php if ($row->alias != 'home'): ?>
					<div class="form-group">
						<label for="field-parent_id">{{ trans('pages::pages.parent') }}: <span class="required">{{ trans('global.required') }}</span></label>
						<select name="fields[parent_id]" id="field-parent_id" class="form-control">
							<option value="1" data-path="">{{ trans('pages::pages.home') }}</option>
							<?php foreach ($parents as $page): ?>
								<?php $selected = ($page->id == $row->parent_id ? ' selected="selected"' : ''); ?>
								<option value="{{ $page->id }}"<?php echo $selected; ?> data-path="/{{ $page->path }}"><?php echo str_repeat('|&mdash; ', $page->level) . e($page->title); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				<?php else: ?>
					<input type="hidden" name="fields[parent_id]" value="{{ $row->parent_id }}" />
				<?php endif; ?>

				<div class="form-group">
					<label for="field-title">{{ trans('pages::pages.title') }}: <span class="required">{{ trans('global.required') }}</span></label>
					<input type="text" name="fields[title]" id="field-title" class="form-control required" maxlength="250" value="{{ $row->title }}" />
				</div>

				<div class="form-group" data-hint="{{ trans('pages::pages.path hint') }}">
					<label for="field-alias">{{ trans('pages::pages.path') }}:</label>
					<div class="input-group mb-2 mr-sm-2">
						<div class="input-group-prepend">
							<div class="input-group-text">{{ url('/') }}<span id="parent-path">{{ ($row->parent && trim($row->parent->path, '/') ? '/' . $row->parent->path : '') }}</span>/</div>
						</div>
						<input type="text" name="fields[alias]" id="field-alias" class="form-control" maxlength="250"<?php if ($row->alias == 'home'): ?> disabled="disabled"<?php endif; ?> value="{{ $row->alias }}" />
					</div>
					<span class="form-text text-muted">{{ trans('pages::pages.path hint') }}</span>
				</div>

				<div class="form-group">
					<label for="field-content">{{ trans('pages::pages.content') }}: <span class="required">{{ trans('global.required') }}</span></label>
					<!-- <textarea name="fields[content]" id="field-content" class="form-control" rows="35" cols="40">{{ $row->content }}</textarea> -->
					{!! editor('fields[content]', $row->content, ['rows' => 35, 'class' => 'required']) !!}
				</div>
			</fieldset>
		</div>
		<div class="col col-md-4">
			<fieldset class="adminform">
				<legend>{{ trans('global.publishing') }}</legend>

				<div class="form-group">
					<label for="field-access">{{ trans('pages::pages.access') }}:</label>
					<select class="form-control" name="fields[access]" id="field-access"<?php if ($row->isRoot()) { echo ' readonly="readonly" disabled="disabled"'; } ?>>
						<?php foreach (App\Halcyon\Access\Viewlevel::all() as $access): ?>
							<option value="<?php echo $access->id; ?>"<?php if ($row->access == $access->id) { echo ' selected="selected"'; } ?>><?php echo e($access->title); ?></option>
						<?php endforeach; ?>
					</select>
				</div>

				<div class="form-group">
					<label for="field-state">{{ trans('pages::pages.state') }}:</label>
					<select class="form-control" name="fields[state]" id="field-state"<?php if ($row->isRoot()) { echo ' readonly="readonly" disabled="disabled"'; } ?>>
						<option value="0"<?php if ($row->state == 0) { echo ' selected="selected"'; } ?>>{{ trans('global.unpublished') }}</option>
						<option value="1"<?php if ($row->state == 1) { echo ' selected="selected"'; } ?>>{{ trans('global.published') }}</option>
					</select>
				</div>

				<div class="form-group">
					<label for="field-publish_up">{{ trans('pages::pages.publish up') }}:</label>
					{!! Html::input('calendar', 'fields[publish_up]', Carbon\Carbon::parse($row->publish_up ? $row->publish_up : $row->created)) !!}
				</div>

				<div class="form-group">
					<label for="field-publish_down">{{ trans('pages::pages.publish down') }}:</label>
					<span class="input-group input-datetime">
						<input type="text" name="fields[publish_down]" id="field-publish_down" class="form-control datetime" value="<?php echo ($row->publish_down ? e(Carbon\Carbon::parse($row->publish_down)->toDateTimeString()) : ''); ?>" placeholder="<?php echo ($row->publish_down ? '' : trans('global.never')); ?>" />
						<span class="input-group-append"><span class="input-group-text icon-calendar"></span></span>
					</span>
				</div>
			</fieldset>

			@sliders('start', 'module-sliders')
				@sliders('panel', trans('pages::pages.options'), 'params-options')
					<fieldset class="panelform">
						<div class="form-group" data-tip="{{ trans('pages::pages.params.show title desc') }}">
							<label for="params-show_title">{{ trans('pages::pages.params.show title') }}:</label>
							<select class="form-control" name="params[show_title]" id="params-show_title">
								<option value="0"<?php if (!$row->params->get('show_title', 1)) { echo ' selected="selected"'; } ?>>{{ trans('global.no') }}</option>
								<option value="1"<?php if ($row->params->get('show_title', 1)) { echo ' selected="selected"'; } ?>>{{ trans('global.yes') }}</option>
							</select>
							<span class="form-text sr-only">{{ trans('pages::pages.params.show title desc') }}</span>
						</div>

						<div class="form-group" data-tip="{{ trans('pages::pages.params.show author desc') }}">
							<label for="params-show_author">{{ trans('pages::pages.params.show author') }}:</label>
							<select class="form-control" name="params[show_author]" id="params-show_author">
								<option value="0"<?php if (!$row->params->get('show_author')) { echo ' selected="selected"'; } ?>>{{ trans('global.no') }}</option>
								<option value="1"<?php if ($row->params->get('show_author')) { echo ' selected="selected"'; } ?>>{{ trans('global.yes') }}</option>
							</select>
							<span class="form-text sr-only">{{ trans('pages::pages.params.show author desc') }}</span>
						</div>

						<div class="form-group" data-tip="{{ trans('pages::pages.params.show create date desc') }}">
							<label for="params-show_create_date">{{ trans('pages::pages.params.show create date') }}:</label>
							<select class="form-control" name="params[show_create_date]" id="params-show_create_date">
								<option value="0"<?php if (!$row->params->get('show_create_date')) { echo ' selected="selected"'; } ?>>{{ trans('global.no') }}</option>
								<option value="1"<?php if ($row->params->get('show_create_date')) { echo ' selected="selected"'; } ?>>{{ trans('global.yes') }}</option>
							</select>
							<span class="form-text sr-only">{{ trans('pages::pages.params.show create date desc') }}</span>
						</div>

						<div class="form-group" data-tip="{{ trans('pages::pages.params.show modify date desc') }}">
							<label for="params-show_modify_date">{{ trans('pages::pages.params.show modify date') }}:</label>
							<select class="form-control" name="params[show_modify_date]" id="params-show_modify_date">
								<option value="0"<?php if (!$row->params->get('show_modify_date')) { echo ' selected="selected"'; } ?>>{{ trans('global.no') }}</option>
								<option value="1"<?php if ($row->params->get('show_modify_date')) { echo ' selected="selected"'; } ?>>{{ trans('global.yes') }}</option>
							</select>
							<span class="form-text sr-only">{{ trans('pages::pages.params.show modify date desc') }}</span>
						</div>

						<div class="form-group" data-tip="{{ trans('pages::pages.params.show publish date desc') }}">
							<label for="params-show_publish_date">{{ trans('pages::pages.params.show publish date') }}:</label>
							<select class="form-control" name="params[show_publish_date]" id="params-show_publish_date">
								<option value="0"<?php if (!$row->params->get('show_publish_date')) { echo ' selected="selected"'; } ?>>{{ trans('global.no') }}</option>
								<option value="1"<?php if ($row->params->get('show_publish_date')) { echo ' selected="selected"'; } ?>>{{ trans('global.yes') }}</option>
							</select>
						</div>

						<fieldset id="param-styles">
							<legend>{{ trans('pages::pages.params.styles') }}</legend>
							<div class="px-3 py-3">
							@php
							$i = 0;
							@endphp
							@foreach ($row->params->get('styles', []) as $style)
								<div class="input-group mb-3">
									<label class="sr-only" for="params-styles-{{ $i }}">{{ trans('pages::pages.styles') }}:</label>
									<input type="text" class="form-control" name="params[styles][{{ $i }}]" id="params-styles-{{ $i }}" value="{{ $style }}" />
									<div class="input-group-append">
										<button class="btn btn-outline-secondary btn-danger" type="button" id="params-styles-{{ $i }}-btn" data-id="params-styles-{{ $i }}"><span class="glyph icon-trash">{{ trans('global.delete') }}</span></button>
									</div>
								</div>
								@php
								$i++;
								@endphp
							@endforeach

							<div class="input-group mb-3">
								<label class="sr-only" for="params-styles-{{ $i }}">{{ trans('pages::pages.styles') }}:</label>
								<input type="text" class="form-control" name="params[styles][{{ $i }}]" id="params-styles-{{ $i }}" value="" />
								<div class="input-group-append">
									<button class="btn btn-outline-secondary btn-danger disabled" type="button" id="params-styles-{{ $i }}-btn" data-id="params-styles-{{ $i }}"><span class="glyph icon-trash">{{ trans('global.delete') }}</span></button>
								</div>
							</div>

							<div class="text-right">
								<a href="#params_styles_{{ $i }}" data-type="style" data-container="param-styles" class="add-row btn btn-secondary param-style-new"><span class="glyph icon-plus">{{ trans('global.add') }}</span></a>
							</div>
						</div>
						</fieldset>

						<fieldset id="param-scripts">
							<legend>{{ trans('pages::pages.params.scripts') }}</legend>

							<table>
								<tbody>
							@php
							$i = 0;
							@endphp
							@foreach ($row->params->get('scripts', []) as $script)
								<tr>
								<!-- <div class="row param-item" id="params_scripts_{{ $i }}">
									<div class="col-md-9 form-group"> -->
									<td>
										<label class="sr-only" for="params-scripts-{{ $i }}">{{ trans('pages::pages.scripts') }}:</label>
										<input type="text" class="form-control" name="params[scripts][{{ $i }}]" id="params-scripts-{{ $i }}" value="{{ $script }}" />
									<!-- </div>
									<div class="col-md-3"> -->
									</td>
									<td>
										<a href="#params_scripts_{{ $i }}" class="btn btn-secondary"><span class="glyph icon-trash">{{ trans('global.delete') }}</span></a>
									<!-- </div>
								</div> -->
									</td>
								</tr>
								@php
								$i++;
								@endphp
							@endforeach
							<tr>
								<td>
							<!-- <div class="row">
								<div class="col-md-10 form-group"> -->
									<label class="sr-only" for="params-scripts-{{ $i }}">{{ trans('pages::pages.scripts') }}:</label>
									<input type="text" class="form-control" name="params[scripts][{{ $i }}]" id="params-scripts-{{ $i }}" value="" />
								<!-- </div>
								<div class="col-md-2 text-right"> -->
								</td>
								<td>
									<a href="#params_scripts_{{ $i }}" class="btn btn-secondary disabled"><span class="glyph icon-trash">{{ trans('global.delete') }}</span></a>
								<!-- </div>
							</div> -->
								</td>
							</tr>
						</tbody>
						<tfoot>
							<tr>
								<td></td>
								<td>
									<a href="#params_scripts_{{ $i }}" data-type="script" data-container="param-scripts" class="add-row btn btn-secondary param-script-new"><span class="glyph icon-plus">{{ trans('global.add') }}</span></a>
								</td>
							</tr>
						</tfoot>
					</table>
						</fieldset>
					</fieldset>
			@sliders('panel', trans('pages::pages.metadata'), 'params-metadata')
					<fieldset class="panelform">
						<div class="form-group">
							<label for="field-metakey">{{ trans('pages::pages.metakey') }}:</label>
							<textarea class="form-control" name="fields[metakey]" id="field-metakey" rows="3" cols="40">{{ $row->metakey }}</textarea>
						</div>

						<div class="form-group">
							<label for="field-metadesc">{{ trans('pages::pages.metadesc') }}:</label>
							<textarea class="form-control" name="fields[metadesc]" id="field-metadesc" rows="3" cols="40">{{ $row->metadesc }}</textarea>
						</div>

						<div class="form-group">
							<label for="field-metadata">{{ trans('pages::pages.metadata') }}:</label>
							<textarea class="form-control" name="fields[metadata]" id="field-metadata" rows="3" cols="40">{{ json_encode($row->metadata->all()) }}</textarea>
						</div>
					</fieldset>
			@sliders('end')

			@include('history::admin.history')
		</div>
	</div>

	@csrf
</form>
@stop