@extends('layouts.master')

@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/finder/css/finder.css') }}" />
@endpush

@push('scripts')
<script src="{{ asset('modules/finder/js/contrib/jquery.ba-throttle-debounce.min.js') }}"></script>
<script src="{{ asset('modules/finder/js/contrib/jquery.detect_swipe.js') }}"></script>
<script src="{{ asset('modules/finder/js/cwd_popups.js') }}"></script>
<script src="{{ asset('modules/finder/js/cwd_tables.js') }}"></script>
<script src="{{ asset('modules/finder/js/jquery.mustache.js') }}"></script>
<script src="{{ asset('modules/finder/js/mustache.js') }}"></script>
<script src="{{ asset('modules/finder/js/app.js') }}"></script>
@endpush

@php
app('pathway')->append(
    trans('finder::finder.module name'),
    route('site.finder.index')
);
@endphp

@section('title'){{ trans('finder::finder.module name') }}@stop

@section('content')
<div id="app" role="main">
    <div class="row">
        <div class="col-md-12 app-title">
            <h2 class="title" id="pagetitle">Page Title</h2>
            <p class="lead" id="pagesubtitle">This tool is intended to help you choose among services.</p>

            <p class="cert"><span class="fa fa-comments"></span>We welcome feedback on this tool.</p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <hr class="section-break">
            <h2 class="sub-heading heading-services" >Describe your data</h2>
            <div class="cd-row">
                <div class="cd-cell cd-questions">
                    <div class="cd-overflow">
                      <div class="questions-header">
                        <h3 class="step-1" id="pagequestionheader">
                          Answer these questions to help identify services that are suitable for your needs.
                        </h3>
                        <button class="btn btn-secondary btn-clear-filters ">Clear Answers</button>
                      </div>
                      <ol id="questionlist"></ol>
                    </div>
                </div>
                <div class="cd-cell cd-services">
                    <hr class="section-break hidden-sm hidden-md hidden-lg">
                    <h2 class="sub-heading hidden-sm hidden-md hidden-lg">Services</h2>
                    <div class="services-header">
                      <div>
                        <h3>
                          <span class="fa fa-arrow-circle-right" ></span>
                          <span id='pageserviceheader'>
                            Select services you would like to compare.
                          </span>
                        </h3>
                      </div>
                      <div>
                          <button class="btn btn-secondary btn-select-all selectall-button">Select All</button>
                          <button class="btn btn-secondary btn-select-none clear-button">Clear Selections</button>
                      </div>
                    </div>
                    <div id="modularstorage-services"></div>
                </div>
            </div>
        </div>
    </div>

    <div id="container34" class="hide">
        <hr class="section-break">
 
        <div class="row">
            <div class="col-md-12">
                <h2 class="comparisonchart-wrapper-wrapper sub-heading" id='pagechartheader'>
                    Compare the services which match your selected criteria.
                </h2>
                <div>
                    <button class="btn btn-sm btn-secondary chart-select-all selectall-button">Select All</button>
                    <button class="btn btn-sm btn-primary chart-select-none clear-button">Clear Selections</button>
                </div>
                <fieldset>
                    <legend class="sr-only">Present in comparison table?</legend>
                    <div class="comparisonlist-wrapper"></div>
                </fieldset>
                <div class="comparisonchart-wrapper">
                    <table class="table table-striped table-bordered scrolling" id="comparisonchart"></table>
                </div>
            </div>
        </div>

        <hr class="section-break">

        <div class="row">
            <div class="col-md-12">
                <h2 class="sub-heading">
                    Would you like more help, and/or to email your results to yourself?
                </h2>
                <span id="pageemailformheader">
                    Email selected criteria and resulting choices to yourself and/or the consultants to learn more.
                </span>

                <div class="form-group">
                    <label for="name"> Name:
                    <input name="name" id="name" type="text" class="form-control">
                    </label>
                </div>

                <div class="form-group">
                    <label for="emailaddr"> Email:
                    <input name="emailaddr" id="emailaddr" type="email" class="form-control">
                    </label>
                </div>

                <div class="email-options">
                  <fieldset>
                    <legend>Send email with selections to:</legend>
                    <input type="checkbox" id="emailtoself" checked>  <label for="emailtoself">self</label>; and/or
                    <input type="checkbox" id="emailtordmsg" checked>  <label for="emailtordmsg">helpdesk</label>.
                  </fieldset>
                </div>

                <button type="button" id="send_email" class="btn btn-secondary">Send Email</button>
            </div>
        </div>
    </div>

    <div class="jump-to-chart">
        <div class="container">
            <div class="media">
                <div class="media-body">
                    <p>
                        <span id="selection-number">0</span> Services Selected
                    </p>
                </div>
                <div class="media-right media-middle">
                    <button class="btn btn-secondary jump_button">Compare Results</button>
                </div>
            </div>
        </div>
    </div>

    <div style="margin-top:25px;">
        <p class="cert"><span class="fa fa-comments"></span>We welcome feedback on this tool.</p>
    </div>
</div>
@stop