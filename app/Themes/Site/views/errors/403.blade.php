@extends('layouts.master')

@section('content')
	<div class="error-page">
		<div class="error-header">
			<h2>403</h2>
		</div>
		<div class="error-body">
			<h3><i class="fa fa-warning text-yellow"></i> {{ trans('global.error 403 title') }}</h3>
			<p>{!! trans('global.error 403 description') !!}</p>
		</div>
	</div>
@stop
