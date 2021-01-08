@extends('layouts.master')

@php
$content = '';
@endphp

@section('content')
<div class="sidenav col-lg-3 col-md-3 col-sm-12 col-xs-12">
	<h2>{{ $resource->name }}</h2>

	@if ($pic = $resource->picture)
		<div class="resource_pic">
			<img src="{{ $pic }}" alt="">
		</div>
	@endif

	<ul class="dropdown-menu">
		@foreach ($sections as $section)
			<?php
			$active = '';
			if ($section['active'])
			{
				$active = ' class="active"';
				$content = $section['content'];
			}
			?>
			<li{!! $active !!}>
				<a href="{{ $section['route'] }}">{!! $section['name'] !!}</a>
			</li>
		@endforeach
	</ul>

	<h2>{{ $type->name }} Resources</h2>
	<ul class="dropdown-menu">
		@foreach ($rows as $i => $row)
			@php
			if (!$row->listname)
			{
				continue;
			}
			$active = '';
			if ($row->listname == $resource->listname)
			{
				$active = ' class="active"';
			}
			@endphp
			<li{!! $active !!}>
				<a href="{{ route('site.resources.' . $type->alias . '.show', ['name' => $row->listname]) }}">{{ $row->name }}</a>
				<?php /*if ($active)
					<ul>
						@foreach ($sections as $section)
							<?php
							$act = '';
							if ($section['active'])
							{
								$act = ' class="active"';
								$content = $section['content'];
							}
							?>
							<li{!! $act !!}>
								<a href="{{ $section['route'] }}">{!! $section['name'] !!}</a>
							</li>
						@endforeach
					</ul>
				@endif*/ ?>
			</li>
		@endforeach
		<li><div class="separator"></div></li>
		<li<?php if ($resource->isTrashed()) { echo ' class="active"'; } ?>><a href="{{ route('site.resources.' . $type->alias . '.retired') }}">{{ trans('resources::resources.retired') }}</a></li>
	</ul>
</div>

<div class="contentInner col-lg-9 col-md-9 col-sm-12 col-xs-12">
	@if ($resource->params->get('gateway') || $resource->params->get('desktop') || $resource->params->get('notebook'))
		<div class="launch">
			@if ($gateway = $resource->params->get('gateway'))
				<div class="panel">
					Gateway
					<a class="btn btn-launch" href="{{ $gateway }}" title="Launch OnDemand Gateway" target="_blank" rel="noopener">Launch</a>
				</div>
			@endif

			@if ($desktop = $resource->params->get('desktop'))
				<div class="panel">
					Remote Desktop
					<a class="btn btn-launch" href="{{ $desktop }}" title="Launch Remote Desktop" target="_blank" rel="noopener">Launch</a>
				</div>
			@endif

			@if ($notebook = $resource->params->get('notebook'))
				<div class="panel">
					Jupyter Hub
					<a class="btn btn-launch" href="{{ $notebook }}" title="Launch Jupyter Hub" target="_blank" rel="noopener">Launch</a>
				</div>
			@endif
		</div>
	@endif

	@if ($content)
		{!! $content !!}
	@else
		<h2>{{ $resource->name }}</h2>
		<p>{{ $resource->description }}</p>
	@endif
</div>
@stop