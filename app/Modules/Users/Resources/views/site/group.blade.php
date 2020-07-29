@extends('layouts.master')

@section('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('vendor/datatables/datatables.bootstrap.min.css') }}" />
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('vendor/select2/css/select2.css') }}" />
@stop

@section('scripts')
<script src="{{ asset('vendor/handlebars/handlebars.min-v4.7.6.js') }}"></script>
<script src="{{ asset('vendor/datatables/datatables.min.js') }}"></script>
<script src="{{ asset('vendor/datatables/datatables.bootstrap.min.js') }}"></script>
<script src="{{ asset('vendor/select2/js/select2.min.js?v=' . filemtime(public_path() . '/vendor/select2/js/select2.min.js')) }}"></script>
<script>
var _DEBUG = true;
/**
 * Message of the Day
 */
var motd = {
	/**
	 * Set the MOTD for a group
	 *
	 * @param   {string}  group
	 * @return  {void}
	 */
	set: function(group) {
		var message = document.getElementById("MotdText_" + group).value;

		if (!group) {
			alert('No group ID provided.');
			return false;
		}

		var post = {
			'group': group,
			'motd' : message
		};

		post = JSON.stringify(post);

		_DEBUG ? console.log('post: ' + ROOT_URL + "groupmotd", post) : null;

		WSPostURL(ROOT_URL + "groupmotd", post, function(xml) {
			// reload the page so the user can see the change to the group message
			if (xml.status == 200) {
				window.location.reload();
			} else {
				_DEBUG ? console.log('xml.status: ' + xml.status) : null;

				alert("An error occurred while creating MOTD. Please refresh page and try again or if problem persists contact rcac-help@purdue.edu.");
			}
		});
	},

	/**
	 * Delete the MOTD for a group
	 *
	 * @param   {string}  group
	 * @return  {void}
	 */
	delete: function(group) {
		if (!group) {
			alert('No group ID provided.');
			return false;
		}

		_DEBUG ? console.log('delete: ' + ROOT_URL + "groupmotd/" + /\d+$/.exec(group)) : null;

		WSDeleteURL(ROOT_URL + "groups/motd/" + /\d+$/.exec(group), function(xml) {
			// reload the page so the user can see the change to the group message
			if (xml.status == 200) {
				window.location.reload();
			} else {
				_DEBUG ? console.log('xml.status: ' + xml.status) : null;

				alert("An error occurred while deleting MOTD. Please refresh page and try again or if problem persists contact rcac-help@purdue.edu.");
			}
		});
	}
}
/*
document.addEventListener('DOMContentLoaded', function() {
	var dels = document.getElementsByClassName('motd-delete');
	var i;
	for (i = 0; i < dels.length; i++)
	{
		dels[i].addEventListener('click', function(e){
			e.preventDefault();
			motd.delete(this.getAttribute('data-group'));
		});
	}

	var sets = document.getElementsByClassName('motd-set');
	for (i = 0; i < sets.length; i++)
	{
		sets[i].addEventListener('click', function(e){
			e.preventDefault();
			motd.set(this.getAttribute('data-group'));
		});
	}
});*/

	$(document).ready(function() {
		$('.reveal').on('click', function(e){
			$($(this).data('toggle')).toggleClass('hide');

			var text = $(this).data('text');
			$(this).data('text', $(this).html()); //.replace(/"/, /'/));
			$(this).html(text);
		});

		//$('.tabbed').tabs();

		$('.add-row').on('click', function(e){
			e.preventDefault();

			var val = $($(this).attr('href')).val();
			if (!val) {
				return;
			}

			var container = $(this).closest('ul');

			//$.post($(this).data('api'), data, function(e){
				var source   = $($(this).data('row')).html(),
					template = Handlebars.compile(source),
					context  = {
						"index" : container.find('li').length,
						"ancestors": [{name: 'foo'}, {name: 'bar'}],
						"name": val
					},
					html = template(context);

				$(html).insertBefore(container.find('li:last-child'));
			//});
		});
		/*$('.add-fieldofscience-row').on('click', function(e){
			e.preventDefault();

			var val = $($(this).attr('href')).val();
			if (!val) {
				return;
			}

			var container = $(this).closest('ul');

			//$.post($(this).data('api'), data, function(e){
				var source   = $('#new-fieldofscience-row').html(),
					template = Handlebars.compile(source),
					context  = {
						"index" : container.find('li').length,
						"ancestors": [{name: 'foo'}, {name: 'bar'}],
						"name": val
					},
					html = template(context);

				$(html).insertBefore(container.find('li:last-child'));
			//});
		});*/
		$('.list-group').on('click', '.delete-row', function(e){
			e.preventDefault();

			var result = confirm('Are you sure you want to remove this?');

			if (result) {
				var container = $(this).closest('li');

				//$.post($(this).data('api'), data, function(e){
					container.remove();
				//});
			}
		});


		$('#new_group_btn').on('click', function (event) {
			event.preventDefault();

			CreateNewGroup();
		});
		$('#new_group_input').on('keyup', function (event) {
			if (event.keyCode == 13) {
				CreateNewGroup();
			}
		});

		$('#create_gitorg_btn').on('click', function (event) {
			event.preventDefault();
			CreateGitOrg($(this).data('value'));
		});

		$('.add-property').on('click', function(e){
			e.preventDefault();

			AddProperty($(this).data('prop'), $(this).data('value'));
		});
		$('.add-property-input').on('keyup', function(e){
			e.preventDefault();

			if (event.keyCode==13){
				AddProperty($(this).data('prop'), $(this).data('value'));
			}
		});
		$('.edit-property').on('click', function(e){
			e.preventDefault();

			EditProperty($(this).data('prop'), $(this).data('value'));
		});
		$('.edit-property-input').on('keyup', function(event){
			if (event.keyCode==13){
				EditProperty($(this).data('prop'), $(this).data('value'));
			}
		});
		$('.cancel-edit-property').on('click', function(e){
			e.preventDefault();

			CancelEditProperty($(this).data('prop'), $(this).data('value'));
		});
		$('.create-default-unix-groups').on('click', function(e){
			e.preventDefault();
			CreateDefaultUnixGroups($(this).data('value'), $(this).data('group'));
		});
		$('.delete-unix-group').on('click', function(e){
			e.preventDefault();
			DeleteUnixGroup($(this).data('unixgroup'), $(this).data('value'));
		});

		$('.searchable-select').select2();

		$('.datatablse').DataTable({
			pageLength: 20,
			pagingType: 'numbers',
			info: false,
			ordering: false,
			lengthChange: false,
			scrollX: true,
			autoWidth: false,
			language: {
				searchPlaceholder: "Filter users..."
			},
			fixedColumns: {
				leftColumns: 1,
				rightColumns: 1
			},/*,
			lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'All']],
			fixedColumns: {
				leftColumns: 1
			},*/
			initComplete: function () {
				//this.page(0).draw(true);
				$($.fn.dataTable.tables( true ) ).css('width', '100%');
				$($.fn.dataTable.tables( true ) ).DataTable().columns.adjust().draw();
				/*this.api().columns().every(function (i) {
					var column = this;
					var select = $('<select data-index="' + i + '"><option value=""></option></select>')
						.appendTo($(column.footer()).empty());

					column.data().unique().sort().each(function (d, j) {
						select.append('<option value="'+d+'">'+d+'</option>');
					});
				});

				var table = this;

				$(table.api().table().container()).on('change', 'tfoot select', function () {
					var val = $.fn.dataTable.util.escapeRegex(
						$(this).val()
					);

					table.api()
						.column($(this).data('index'))
						.search(val ? '^'+val+'$' : '', true, false)
						.draw();
				});*/
			}
		});

		/*
		 $('a[data-toggle="tab"]').on( 'shown.bs.tab', function (e) {
			$($.fn.dataTable.tables( true ) ).css('width', '100%');
			$($.fn.dataTable.tables( true ) ).DataTable().columns.adjust().draw();
		});
		*/

		//$('.dataTables_filter input').addClass('form-control');
	});
</script>
@stop

@section('title'){{ trans('users::users.quotas') }}@stop

@section('content')
	@php
	$canEdit = auth()->user()->can('edit groups') || (auth()->user()->can('edit.own groups') && $group->ownerid == $user->id);
	@endphp

@include('users::site.admin', ['user' => $user])

<div class="sidenav col-lg-3 col-md-3 col-sm-12 col-xs-12">
	<div class="qlinks">
		<ul class="dropdown-menu">
			<li><a href="{{ route('site.users.account') }}">{{ trans('users::users.my accounts') }}</a></li>
			<li class="active"><a href="{{ route('site.users.account.groups') }}">{{ trans('users::users.my groups') }}</a></li>
			<li><a href="{{ route('site.users.account.quotas') }}">{{ trans('users::users.my quotas') }}</a></li>
			<li><a href="{{ route('site.orders.index') }}">{{ trans('users::users.my orders') }}</a></li>
		</ul>
	</div>
</div>

<div class="col-lg-9 col-md-9 col-sm-12 col-xs-12">
	<div class="contentInner">
		@if (auth()->user()->can('create groups'))
			<a class="btn btn-default right" href="{{ route('site.users.account.groups') }}">
				<i class="fa fa-plus-circle"></i> {{ trans('global.create') }}
			</a>
		@endif

		<h2>{{ $g->group->name }}</h2>

		<!-- 
		@if (auth()->user()->can('manage users'))
			<div class="card panel panel-default card-admin">
				<div class="card-header panel-heading">
					Admin Options
				</div>
				<div class="card-body panel-body">
					<form method="get" action="{{ route('site.users.account.groups') }}">
						<div class="form-group">
							<label for="newuser">Search for someone:</label>
							<div class="input-group">
								<input type="text" name="newuser" id="newuser" class="form-control searchuser" autocorrect="off" autocapitalize="off" />
								<div id="user_results" class="searchMain usersearch_results"></div>
								<div class="input-group-addon">
									<span class="input-group-text">
										<i class="fa fa-search" aria-hidden="true" id="add_button_a"></i>
										<img src="/include/images/loading.gif" width="14" id="search_loading" alt="Loading..." class="icon" />
									</span>
								</div>
							</div>
							<span id="add_errors"></span>
						</div>
					</form>

					@if ($user->id != auth()->user()->id)
						<p>
							Showing information for "{{ $user->name }}":
						</p>
					@endif
				</div>
			</div>
		@endif
		 -->

		<div id="everything">
			<ul class="nav nav-tabs tabs">
				<li class="nav-item">
					<a href="#DIV_group-overview" id="group-overview" class="nav-link tab active activeTab">
						Overview
					</a>
				</li>
				<li class="nav-item">
					<a href="#DIV_group-motd" id="group-motd" class="nav-link tab">
						Notices
					</a>
				</li>
				<li class="nav-item">
					<a href="#DIV_group-queues" id="group-queues" class="nav-link tab">
						Queues
					</a>
				</li>
				<li class="nav-item">
					<a href="#DIV_group-members" id="group-members" class="nav-link tab">
						Members
					</a>
				</li>
				<li class="nav-item">
					<a href="#DIV_group-history" id="group-history" class="nav-link tab">
						History
					</a>
				</li>
			</ul>

			<!-- <div class="tabMain" id="tabMain"> -->

				<div id="DIV_group-overview">
					<?php
					$group = $g->group;
					//$group = \App\Modules\Groups\Models\Group::find(1639);
					?>

					<input type="hidden" id="HIDDEN_property_<?php echo $group->id; ?>" value="<?php echo $group->id; ?>" />

					<div class="card panel panel-default">
						<div class="card-header panel-heading">
							Details
						</div>
						<div class="card-body panel-body">
							<div class="form-inline row">
								<label class="col-md-3" for="INPUT_name_<?php echo $group->id; ?>">Research Group Name:</label>

								<div class="col-md-7">
									<span id="SPAN_name_<?php echo $group->id; ?>"><?php echo $group->name; ?></span>
									<input type="text" class="stash edit-property-input" id="INPUT_name_<?php echo $group->id; ?>" data-prop="name" data-value="<?php echo $group->id; ?>" />
								</div>
								<div class="col-md-2 text-right">
									@if ($canEdit)
									<a href="{{ route('site.users.account.groups', ['edit' => 'name']) }}" class="edit-property tip" data-prop="name" data-value="<?php echo $group->id; ?>" title="Edit"><!--
										--><i class="fa fa-pencil" id="IMG_name_<?php echo $group->id; ?>"></i><span class="sr-only">Edit</span><!--
									--></a>
									<a href="{{ route('site.users.account.groups') }}" class="cancel-edit-property tip stash" data-prop="name" data-value="<?php echo $group->id; ?>" title="Cancel"><!--
										--><i class="fa fa-ban" id="CANCELIMG_name_<?php echo $group->id; ?>"></i><span class="sr-only">Cancel</span><!--
									--></a>
									@endif
								</div>
							</div>
							<?php
							$departments = App\Modules\Groups\Models\Department::tree();
							$fields = App\Halcyon\Models\FieldOfScience::tree();
							?>
						<!-- </div>
					</div>

					<div class="card panel panel-default">
						<div class="card-header panel-heading">
							Departments
						</div> 
							<div class="card panel panel-default">
							
							<table class="table">
								<caption class="sr-only">{{ trans('groups::groups.department') }}</caption>
								<thead>
									<tr>
										<th scope="col">{{ trans('groups::groups.department') }}</th>
										<th scope="col"></th>
									</tr>
								</thead>
								<tbody>
								@foreach ($group->departments as $dept)
									<tr id="department-{{ $dept->id }}" data-id="{{ $dept->id }}">
										<td>{{ $dept->department->name }}</td>
										<td class="text-right">
											<a href="#" class="delete"><i class="fa fa-trash" aria-hidden="true"></i><span class="sr-only">{{ trans('global.trash') }}</span></a>
										</td>
									</tr>
								@endforeach
								</tbody>
								<tfoot>
									<tr>
										<td>
											<select name="department" class="form-control">
												<option value="0">{{ trans('groups::groups.select department') }}</option>
												@foreach ($departments as $d)
													@php
													if ($d->level == 0):
														continue;
													endif;
													@endphp
													<option value="{{ $d->id }}">{{ str_repeat('- ', $d->level) . $d->name }}</option>
												@endforeach
											</select>
										</td>
										<td class="text-right">
											<button class="btn btn-success"><i class="fa fa-plus-circle"></i> {{ trans('global.add') }}</button>
										</td>
									</tr>
								</tfoot>
							</table>
							</div> -->
						</div>
					</div>

					<div class="card panel panel-default">
						<div class="card-header panel-heading">
							Departments
						</div>
						<ul class="list-group list-group-flush">
						@foreach ($group->departments as $dept)
							<li class="list-group-item" id="department-{{ $dept->id }}" data-id="{{ $dept->id }}">
								<div class="row">
									<div class="col-md-11">
										@foreach ($dept->department->ancestors() as $ancestor)
											<?php if (!$ancestor->parentid) { continue; } ?>
											{{ $ancestor->name }} <span class="text-muted">&rsaquo;</span>
										@endforeach
										{{ $dept->department->name }}
									</div>
									<div class="col-md-1 text-right">
										@if ($canEdit)
											<a href="#department-{{ $dept->id }}" class="delete delete-department delete-row" data-api="{{ url('/') }}/api/groups/departments/{{ $dept->id }}"><i class="fa fa-trash" aria-hidden="true"></i><span class="sr-only">{{ trans('global.trash') }}</span></a>
										@endif
									</div>
								</div>
							</li>
						@endforeach
						@if ($canEdit)
							<li class="list-group-item">
								<div class="row">
									<div class="col-md-11">
										<select name="department" id="new-department" class="form-control searchable-select">
											<option value="0">{{ trans('groups::groups.select department') }}</option>
											@foreach ($departments as $d)
												@php
												if ($d->level == 0):
													continue;
												endif;
												@endphp
												<option value="{{ $d->id }}">{{ $d->prefix . $d->name }}</option>
											@endforeach
										</select>
									</div>
									<div class="col-md-1 text-right">
										<a href="#new-department" class="add add-department-row add-row" data-row="#new-department-row" data-api="{{ url('/') }}/api/groups/departments"><i class="fa fa-plus-circle" aria-hidden="true"></i><span class="sr-only">{{ trans('global.add') }}</span></a>
									</div>
								</div>
							</li>
						@endif
						</ul>
						<script id="new-department-row" type="text/x-handlebars-template">
							<li class="list-group-item" id="department-<?php echo '{{ id }}'; ?>" data-id="<?php echo '{{ id }}'; ?>">
								<div class="row">
									<div class="col-md-11">
										<?php echo '{{#each ancestors}}'; ?>
											<?php echo '{{ this.name }}'; ?> <span class="text-muted">&rsaquo;</span>
										<?php echo '{{/each}}'; ?>
										<?php echo '{{ name }}'; ?>
									</div>
									<div class="col-md-1 text-right">
										<a href="#department-<?php echo '{{ id }}'; ?>" class="delete delete-department delete-row"><i class="fa fa-trash" aria-hidden="true"></i><span class="sr-only">{{ trans('global.trash') }}</span></a>
									</div>
								</div>
							</li>
						</script>
					</div>

					<div class="card panel panel-default">
						<div class="card-header panel-heading">
							Field of Science
						</div>
						<ul class="list-group list-group-flush">
						@foreach ($group->fieldsOfScience as $field)
							<li class="list-group-item" id="fieldofscience-{{ $field->id }}" data-id="{{ $field->id }}">
								<div class="row">
									<div class="col-md-11">
										@foreach ($field->field->ancestors() as $ancestor)
											<?php if (!$ancestor->parentid) { continue; } ?>
											{{ $ancestor->name }} <span class="text-muted">&rsaquo;</span>
										@endforeach
										{{ $field->field->name }}
									</div>
									<div class="col-md-1 text-right">
										@if ($canEdit)
											<a href="#fieldofscience-{{ $field->id }}" class="delete delete-fieldofscience delete-row" data-api="{{ url('/') }}/api/groups/fieldofscience/{{ $field->id }}"><i class="fa fa-trash" aria-hidden="true"></i><span class="sr-only">{{ trans('global.trash') }}</span></a>
										@endif
									</div>
								</div>
							</li>
						@endforeach
						@if ($canEdit)
							<li class="list-group-item">
								<div class="row">
									<div class="col-md-11">
										<select name="fieldofscience" id="new-fieldofscience" class="form-control searchable-select">
											<option value="0">{{ trans('groups::groups.select field of science') }}</option>
											@foreach ($fields as $f)
												@php
												if ($f->level == 0):
													continue;
												endif;
												@endphp
												<option value="{{ $f->id }}">{{ >prefix . $f->name }}</option>
											@endforeach
										</select>
									</div>
									<div class="col-md-1 text-right">
										<a href="#new-fieldofscience" class="add add-fieldofscience-row add-row" data-row="#new-fieldofscience-row" data-api="{{ url('/') }}/api/groups/fieldofscience/"><i class="fa fa-plus-circle" aria-hidden="true"></i><span class="sr-only">{{ trans('global.add') }}</span></a>
									</div>
								</div>
							</li>
						@endif
						</ul>
						<script id="new-fieldofscience-row" type="text/x-handlebars-template">
							<li class="list-group-item" id="fieldofscience-<?php echo '{{ id }}'; ?>" data-id="<?php echo '{{ id }}'; ?>">
								<div class="row">
									<div class="col-md-11">
										<?php echo '{{#each ancestors}}'; ?>
											<?php echo '{{ this.name }}'; ?> <span class="text-muted">&rsaquo;</span>
										<?php echo '{{/each}}'; ?>
										<?php echo '{{ name }}'; ?>
									</div>
									<div class="col-md-1 text-right">
										<a href="#fieldofscience-<?php echo '{{ id }}'; ?>" class="delete delete-fieldofscience delete-row"><i class="fa fa-trash" aria-hidden="true"></i><span class="sr-only">{{ trans('global.trash') }}</span></a>
									</div>
								</div>
							</li>
						</script>
					</div>

					<div class="card panel panel-default">
						<div class="card-header panel-heading">
							<div class="row">
								<div class="col col-md-6">
									Unix Groups
									<a href="#box2_<?php echo $group->id; ?>" class="help tip" title="Help"><i class="fa fa-question-circle" aria-hidden="true"></i><span class="sr-only">Help</span></a>
								</div>
								<div class="col col-md-6 text-right">
									@if ($canEdit)
										<?php if (count($group->unixgroups) > 0) { ?>
											<button class="add-property btn btn-default btn-sm" data-prop="unixgroup" data-value="<?php echo $group->id; ?>"><i class="fa fa-plus-circle" aria-hidden="true"></i> Add New Unix Group</button>
										<?php } elseif ($group->unixgroup != '') { ?>
											<button class="btn btn-default create-default-unix-groups" data-group="<?php echo $group->id; ?>" data-value="<?php echo $group->unixgroup; ?>" id="INPUT_groupsbutton_<?php echo $group->id; ?>"><i class="fa fa-plus-circle" aria-hidden="true"></i> Create Default Unix Groups</button>
										<?php } ?>
									@endif
								</div>
							</div>
						</div>
						<div class="card-body panel-body">
							<div class="card panel panel-default">
								<div class="card-body panel-body">
									<?php if (count($group->unixgroups) == 0) { ?>
										<div class="form-inline row">
											<label class="col-md-2" for="INPUT_unixgroup_<?php echo $group->id; ?>">Base Name: <a href="#box1_<?php echo $group->id; ?>" class="help tip" title="Help"><i class="fa fa-question-circle" aria-hidden="true"></i><span class="sr-only">Help</span></a></label>

											<div class="col-md-6">
												<span id="SPAN_unixgroup_<?php echo $group->id; ?>"><?php echo trans('global.none'); ?></span>

												<input type="text" class="hide form-control edit-property-input" id="INPUT_unixgroup_<?php echo $group->id; ?>" data-prop="unixgroup" data-value="<?php echo $group->id; ?>" placeholder="{{ trans('global.none') }}" value="" />
											</div>
											<div class="col-md-4 text-right">
												<a href="{{ route('site.users.account.groups') }}#edit-property" class="edit-property tip" data-prop="unixgroup" data-value="<?php echo $group->id; ?>" title="Edit"><!--
													--><i class="fa fa-pencil" id="IMG_unixgroup_<?php echo $group->id; ?>"></i><span class="sr-only">Edit</span><!--
												--></a>
											</div>
										</div>
									<?php } else { ?>
										<div class="form-inline row">
											<label class="col-md-2" for="INPUT_unixgroup_<?php echo $group->id; ?>">Base Name: <a href="#box1_<?php echo $group->id; ?>" class="help tip" title="Help"><i class="fa fa-question-circle" aria-hidden="true"></i><span class="sr-only">Help</span></a></label>
											<div class="col-md-10">
												<?php echo $group->unixgroup ? $group->unixgroup : '<span class="text-muted">' . trans('global.none') . '</span>'; ?>
											</div>
										</div>
									<?php } ?>
								</div>
							</div>

							<div class="dialog dialog-help" id="box1_<?php echo $group->id; ?>" title="Base Unix Group">
								<p>This is the base name for all of your group's Unix groups. Once set, this name is not easily changed so please carefully consider your choice. If you wish to change it, email <a href="mailto:rcac-help@purdue.edu">rcac-help@purdue.edu</a> to discuss your options. Group base names may be named with the following guidelines.</p>
								<ul>
									<li>Should typically be the same as your queue name for consistency.</li>
									<li>May only contain lower case letters or numbers and must not begin with a number. Upper case letters and other characters are not permitted.</li>
									<li>Names must be at least 2 characters and no more than 10 characters.</li>
									<li>Must be unique.</li>
								</ul>
							</div>

							<?php if (count($group->unixgroups) > 0) { ?>
								<table id="actmaint_info" class="table table-hover">
									<caption class="sr-only">Unix Groups</caption>
									<thead>
										<tr>
											<th scope="col">Name</th>
											<th scope="col" class="extendedinfo hide">ACMaint Name</th>
											<th scope="col" class="extendedinfo hide">Short Name</th>
											<th scope="col" class="extendedinfo hide text-right">GID Number</th>
											<th scope="col" class="text-right">Actions</th>
										</tr>
									</thead>
									<tfoot>
										<tr>
											<td colspan="5" class="text-right">
												<button class="btn btn-default btn-sm reveal" data-toggle=".extendedinfo" data-text="<i class='fa fa-eye-slash'></i> Hide Extended Info</button>"><i class="fa fa-eye"></i> Show Extended Info</button>
											</td>
										</tr>
									</tfoot>
									<tbody>
										@foreach ($group->unixgroups as $unixgroup)
											<tr>
												<td>{{ $unixgroup->longname }}</td>
												<td class="extendedinfo hide">{{ config('modules.groups.unix_prefix', 'rcac-') . $unixgroup->longname }}</td>
												<td class="extendedinfo hide">{{ $unixgroup->shortname }}</td>
												<td class="extendedinfo hide text-right">{{ $unixgroup->unixgid }}</td>
												<td class="text-right">
													@if ($canEdit && !preg_match("/rcs[0-9]{4}[0-9]/", $unixgroup->shortname))
														<a href="{{ route('site.users.account.groups', ['delete' => $unixgroup->id]) }}" class="delete delete-unix-group tip" data-unixgroup="<?php echo $unixgroup->id; ?>" data-value="<?php echo $group->id; ?>" id="deletegroup_<?php echo $unixgroup->id; ?>" title="Delete"><!--
															--><i class="fa fa-trash" id="IMG_deletegroup_<?php echo $unixgroup->id; ?>"></i><span class="sr-only">Delete</span><!--
														--></a>
													@endif
												</td>
											</tr>
										@endforeach
									</tbody>
								</table>
							<?php } ?>

							<div class="dialog dialog-help" id="box2_<?php echo $group->id; ?>" title="Unix Groups">
								<?php
								$doc = '';
								if (count($group->unixgroups) > 0)
								{
									?>
									<p>These are your group's Unix groups. You may create and delete additional custom groups as you need them. Any custom groups will be prefixed by your base name. Groups may be named with the following guidelines.</p>
									<ul>
										<li>May only contain lower case letters and numbers. Upper case letters and other characters are not permitted.</li>
										<li>Total name length, including prefix and hyphen may not exceed 17 characters.</li>
										<li>Must be a unique name.</li>
									</ul>
									<?php
								}
								else
								{
									?>
									<p>Your group's default groups may be created by pressing this button. You will need to create the default before creating any custom groups. Three groups will be created, a base group, apps, and data group. The names will be prefixed by your chosen group base name. Once these are created they are not easily changed to please carefully consider your base name choice.</p>
									<p>If you have any existing Unix groups, please do not continue with creating the defaults. Contact <a href="mailt:rcac-help@purdue.edu">rcac-help@purdue.edu</a> and ITaP Research Computing staff will assist in importing existing groups into the management system and create any remaining groups.<p>
									<?php
								}
								?>
							</div>
						</div><!-- / .card-body -->
					</div><!-- / .card -->

				</div><!-- / #group-overview -->
				<div id="DIV_group-queues" class="stash">

					<div class="card panel panel-default">
						<div class="card-header panel-heading">
							Queues
						</div>
						<div class="card-body panel-body">
							<table class="table table-hover">
								<caption class="sr-only">Below is a list of all queues:</caption>
								<thead class="resource">
									<tr>
										<th scope="col">Resource</th>
										<th scope="col">Name</th>
										<th scope="col" class="text-right">Cores</th>
										<th scope="col" class="text-right">Nodes</th>
										<th scope="col" class="text-right">Walltime</th>
									</tr>
								</thead>
								<tbody>
									<?php
									$queues = $group->queues;

									if (count($queues) > 0)
									{
										foreach ($queues as $q)
										{
											?>
											<tr>
												<?php
												$title = '';
												if ($q->subresource->nodecores)
												{
													$title .= $q->subresource->nodecores . ' cores, ';
												}
												else
												{
													$title .= '-- cores, ';
												}

												if ($q->subresource->nodemem)
												{
													$title .= $q->subresource->nodemem . ' memory';
												}
												else
												{
													$title .= '-- memory';
												}
												?>
												<td title="<?php echo $title; ?>">
													<?php echo $q->subresource->name; ?>
												</td>
												<td>
													@if (auth()->user()->can('manage queues'))
														<a href="{{ route('admin.queues.edit', ['id' => $q->id]) }}" title="Edit queue">{{ $q->name }}</a>
													@else
														{{ $q->name }}
													@endif
												</td>
												<?php
												/*$title = '';
												if (count($q->loans) > 0)
												{
													foreach ($q->loans as $loan)
													{
														if (strtotime($loan->start) <= time())
														{
															$lender = $loan->lender;

															if ($loan->corecount < 0)
															{
																$title .= abs($loan->corecount) . ' cores to ';
															}
															else
															{
																$title .= $loan->corecount . ' cores from ';
															}

															if ($lender)
															{
																$title .= $lender->name . ', ';
															}
														}
													}
												}
												$title = rtrim($title, ', ');*/
												?>
												<td class="text-right">
													<?php echo $q->totalcores; ?>
												</td>
												<td class="text-right">
													<?php if ($q->subresource->nodecores > 0) { ?>
														<?php echo round($q->totalcores/$q->subresource->nodecores, 1); ?>
													<?php } ?>
												</td>
												<td class="text-right">
													<?php
													if (count($q->walltimes) > 0)
													{
														$walltime = $q->walltimes->first()->walltime;
														$unit = '';
														if ($walltime < 60)
														{
															$unit = 'sec';
														}
														elseif ($walltime < 3600)
														{
															$walltime /= 60;
															$unit = 'min';
														}
														elseif ($walltime < 86400)
														{
															$walltime /= 3600;
															$unit = 'hrs';
														}
														else
														{
															$walltime /= 86400;
															$unit = 'days';
														}
														echo $walltime . ' ' . $unit;
													}
													?>
												</td>
											</tr>
										<?php } ?>
									<?php } else { ?>
										<tr>
											<td colspan="6">(No queues found)</td>
										</tr>
									<?php } ?>
								</tbody>
							</table>
						</div><!-- / .card-body -->
					</div><!-- / .card -->

				</div><!-- / #group-queues -->
				<div id="DIV_group-motd" class="stash">

					
					<div class="card panel panel-default">
						<div class="card-header panel-heading">
							Group Notice
						</div>
						<div class="card-body panel-body">
							@if ($canEdit)
								<form method="post" action="{{ route('site.users.account.groups') }}">
									<fieldset>
										<legend class="sr-only">Set Group Notice</legend>

										<div class="form-group">
											<label for="MotdText_<?php echo $group->id; ?>">Enter the notice your group will see at login</label>
											<textarea id="MotdText_<?php echo $group->id; ?>" class="form-control" cols="38" rows="4"><?php echo $group->motd ? $group->motd->motd : ''; ?></textarea>
										</div>

										<div class="form-group">
											<input type="button" value="Set Notice" class="motd-set btn btn-success" data-group="<?php echo $group->id; ?>" />
											<?php if ($group->motd) { ?>
												<input type="button" value="Delete Notice" class="motd-delete btn btn-danger" data-group="<?php echo $group->id; ?>" />
											<?php } ?>
										</div>
									</fieldset>
								</form>
							@else
								<p class="text-muted">
									{{ $group->datetimecreated }} to {{ $group->datetimeremoved }}
								</p>
								<blockquote>
									<p>{{ $group->motd }}</p>
								</blockquote>
							@endif
						</div><!-- / .card-body -->
					</div><!-- / .card -->

					<?php
					$motds = $group->motds();

					if ($group->motd)
					{
						$motds->where('id', '!=', $group->motd->id);
					}

					$past = $motds
						->orderBy('datetimecreated', 'desc')
						->get();

					if (count($past))
					{
						?>
						<div class="card panel panel-default">
							<div class="card-header panel-heading">
								Past Notices
							</div>
							<ul class="list-group list-group-flush">
								@foreach ($past as $motd)
									<li class="list-group-item">
										<a href="{{ route('site.users.account.group', ['group' => $group->id, 'deletemotd' => $motd->id]) }}" class="delete motd-delete"><i class="fa fa-trash"></i><span class="sr-only">Delete</span></a>
										<p class="text-muted">
											{{ $motd->datetimecreated }} to
											@if ($motd->datetimeremoved && $motd->datetimeremoved != '0000-00-00 00:00:00')
												{{ $motd->datetimeremoved }}
											@else
												trans('global.never')
											@endif
										</p>
										<blockquote>
											<p>{{ $motd->motd }}</p>
										</blockquote>
									</li>
								@endforeach
							</ul>
						</div>
						<?php
					}
					?>

				</div><!-- / #group-motd -->
				<div id="DIV_group-history" class="stash">

					<!--<div class="card panel panel-default">
						<div class="card-header panel-heading">
							History
						</div>
						<div class="card-body panel-body">-->
							<p>Any actions taken by you or the other managers of this group are listed below. There may be a short delay in actions showing up in the log.</p>

							<?php
							// Get manager adds
							$l = App\Modules\History\Models\Log::query()
								->where('groupid', '=', $group->id)
								->where('app', '=', 'ws')
								->whereIn('classname', ['groupowner', 'groupviewer', 'queuemember', 'groupqueuemember', 'unixgroupmember', 'unixgroup', 'userrequest'])
								->where('classmethod', '!=', 'read')
								//->where('datetime', '>', Carbon\Carbon::now()->modify('-1 month')->toDateTimeString())
								->orderBy('datetime', 'desc')
								->limit(20)
								->paginate();

							if (count($l))
							{
								?>
								<table class="table table-hover history">
									<caption class="sr-only">Group history</caption>
									<thead>
										<tr>
											<th scope="col" colspan="2">Date / Time</th>
											<th scope="col">Manager</th>
											<th scope="col">User</th>
											<th scope="col">Action Taken</th>
										</tr>
									</thead>
									<tbody>
										<?php
										foreach ($l as $log)
										{
											switch ($log->classname)
											{
												case 'groupowner':
													if ($log->classmethod == 'create')
													{
														$log->action = 'Promoted to manager';
													}

													if ($log->classmethod == 'delete')
													{
														$log->action = 'Demoted as manager';
													}
												break;

												case 'groupviewer':
													if ($log->classmethod == 'create')
													{
														$log->action = 'Promoted to group usage viewer';
													}

													if ($log->classmethod == 'delete')
													{
														$log->action = 'Demoted as group usage viewer';
													}
												break;

												case 'queuemember':
												case 'groupqueuemember':
													$queue = App\Modules\Queues\Models\Queue::find($log->tagretobjectid);
													if ($log->classmethod == 'create')
													{
														$log->action = 'Added to queue ' . $queue->name . ' (' . $queue->subresource->name . ')';
													}

													if ($log->classmethod == 'delete')
													{
														$log->action = 'Removed from queue ' . $queue->name . ' (' . $queue->subresource->name . ')';
													}
												break;

												case 'unixgroupmember':
													$group = App\Modules\Groups\Models\UnixGroup::find($log->tagretobjectid);
													if ($log->classmethod == 'create')
													{
														$log->action = 'Added to Unix group ' . $group->longname;
													}

													if ($log->classmethod == 'delete')
													{
														$log->action = 'Removed from Unix group ' . $group->longname;
													}
												break;

												case 'unixgroup':
													$group = App\Modules\Groups\Models\UnixGroup::find($log->tagretobjectid);
													if ($log->classmethod == 'create')
													{
														$log->action = 'Created Unix group ' . $group->longname;
													}

													if ($log->classmethod == 'delete')
													{
														$log->action = 'Deleted Unix group ' . $group->longname;
													}
												break;

												case 'userrequest':
													$queue = App\Modules\Queues\Models\Queue::find($log->tagretobjectid);
													if ($log->classmethod == 'create')
													{
														$log->action = 'Submitted request to queue ' . $queue->name . ' (' . $queue->subresource->name . ')';
													}

													if ($log->classmethod == 'update')
													{
														$log->action = 'Approved request to queue ' . $queue->name . ' (' . $queue->subresource->name . ')';
													}

													if ($log->classmethod == 'delete')
													{
														$log->action = 'Canceled request to queue ' . $queue->name . ' (' . $queue->subresource->name . ')';
													}
												break;
											}
											?>
											<tr>
												<td><?php echo $log->datetime->format('M j, Y'); ?></td>
												<td class="numCol"><?php echo $log->datetime->format('g:ia'); ?></td>
												<td><?php echo $log->user->name; ?></td>
												<td><?php echo $log->targetuser->name; ?></td>
												<td>
													<?php if (substr($log->status, 0, 1) != '2') { ?>
														<img src="/include/images/error.png" class="img editicon" alt="An error occurred while performing this action. Action may not have completed." />
													<?php } ?>
													<?php echo $log->action; ?>
												</td>
											</tr>
											<?php
										}
										?>
									</tbody>
								</table>

								<?php
								echo $l->render();
							}
							else
							{
								?>
								<p class="alert alert-warning">No activity found.</p>
								<?php
							}
							?>
						<!--</div>
					</div>-->

				</div><!-- / #group-history -->

				<div id="DIV_group-members" class="stash">
					<?php
					$m = (new \App\Modules\Groups\Models\Member)->getTable();
					$u = (new \App\Modules\Users\Models\User)->getTable();

					$managers = $group->members()
						->select($m . '.*')
						->join($u, $u . '.id', $m . '.userid')
						->where(function($where) use ($u)
						{
							$where->where($u . '.deleted_at', '=', '0000-00-00 00:00:00')
								->orWhereNull($u . '.deleted_at');
						})
						->where($m . '.membertype', '=', 2)
						->orderBy($m . '.datecreated', 'desc')
						->get();

					$members = $group->members()
						->select($m . '.*')
						->join($u, $u . '.id', $m . '.userid')
						->where(function($where) use ($u)
						{
							$where->where($u . '.deleted_at', '=', '0000-00-00 00:00:00')
								->orWhereNull($u . '.deleted_at');
						})
						->where($m . '.membertype', '=', 1)
						->orderBy($m . '.datecreated', 'desc')
						->get();

					/*$managers = $group->members->filter(function ($value, $key)
						{
							return $value->membertype == 2;
						});

					$members = $group->members->filter(function ($value, $key)
						{
							return $value->membertype == 1;
						});

					$viewers = $group->members->filter(function ($value, $key)
						{
							return $value->membertype == 3;
						});*/
					?>

					<div class="card panel panel-default">
						<div class="card-header panel-heading">
							Managers
						</div>
						<div class="card-body panel-body">
							<table class="table">
								<caption class="sr-only">Managers</caption>
								<thead>
									<tr>
										<th>User</th>
										<th>Queues</th>
										<th>Unix Groups</th>
										<th class="text-right">Options</th>
									</tr>
								</thead>
								<tbody>
									@foreach ($managers as $member)
										<tr>
											<td>{{ $member->user ? $member->user->name : trans('global.unknown') }}</td>
											<td>
											<?php
											$in = array();
											foreach ($group->queues as $queue):
												foreach ($queue->users as $m):
													if ($m->userid == $member->userid):
														$in[] = $queue->name;
													endif;
												endforeach;
											endforeach;
											echo implode(', ', $in);
											?>
											</td>
											<td>
												<?php
											$in = array();
											foreach ($group->unixgroups as $unix):
												foreach ($unix->members as $m):
													if ($m->userid == $member->userid):
														$in[] = $unix->longname;
													endif;
												endforeach;
											endforeach;
											echo implode(', ', $in);
											?>
											</td>
											<td class="text-right">
												<a href="#"><i class="fa fa-pencil" aria-hidden="true"></i><span class="sr-only">Edit memberships</span></a>
												<a href="#"><i class="fa fa-arrow-down" aria-hidden="true"></i><span class="sr-only">Demote</span></a>
												<a href="#" class="delete"><i class="fa fa-trash" aria-hidden="true"></i><span class="sr-only">Remove from group</span></a>
											</td>
										</tr>
									@endforeach
								</tbody>
							</table>
						</div>
					</div>

					<div class="card panel panel-default">
						<div class="card-header panel-heading">
							Members
						</div>
						<div class="card-body panel-body">
							<table class="table table-hover hover datatable">
								<caption class="sr-only">Members</caption>
								<thead>
									<tr>
										<th scope="col"></th>
										<th scope="col" colspan="{{ count($group->queues) }}">Queues</th>
										<th scope="col" colspan="{{ count($group->unixgroups) }}">Unix Groups</th>
										<th scope="col"></th>
									</tr>
									<tr>
										<th scope="col" >User</th>
										<?php
										$qu = array();
										foreach ($group->queues as $queue):
											$qu[$queue->id] = $queue->users->pluck('userid')->toArray();
											?>
											<th scope="col" class="text-center">{{ $queue->name }}</th>
											<?php
										endforeach;

										$uu = array();
										foreach ($group->unixgroups as $unix):
											$uu[$unix->id] = $unix->members->pluck('userid')->toArray();
											?>
											<th scope="col" class="text-center">{{ $unix->longname }}</th>
											<?php
										endforeach;
										?>
										<th scope="col" class="text-right">Options</th>
										<!-- <th scope="col" class="text-right">Options</th> -->
									</tr>
								</thead>
								<tbody>
									@foreach ($members as $member)
										<tr>
											<td>{{ $member->user ? $member->user->name : trans('global.unknown') }}</td>
											<!-- <td> -->
											<?php
											$in = array();
											foreach ($group->queues as $queue):
												$checked = '';
												//foreach ($queue->users as $m):
													//if ($m->userid == $member->userid):
														//$in[] = $queue->name;
													if (in_array($member->userid, $qu[$queue->id])):
														//$in[] = $unix->longname;
														$checked = ' checked="checked"';
													endif;
														?>
														<td class="text-center"><input type="checkbox" name="unix[{{ $unix->longname }}]"{{ $checked }} value="1" /></td>
														<?php
													//endif;
												//endforeach;
											endforeach;
											/*echo implode(', ', $in);
											?>
											</td>
											<td>
												<?php*/
											$in = array();
											foreach ($group->unixgroups as $unix):
												$checked = '';
												//foreach ($unix->members as $m):
													if (in_array($member->userid, $uu[$unix->id])):
														//$in[] = $unix->longname;
														$checked = ' checked="checked"';
													endif;
														?>
														<td class="text-center"><input type="checkbox" name="unix[{{ $unix->longname }}]"{{ $checked }} value="1" /></td>
														<?php
													//endif;
												//endforeach;
											endforeach;
											//echo implode(', ', $in);
											?>
											<!-- </td> -->
											<td class="text-right">
												<a href="#" class="promote tip" title="Promote to manager"><i class="fa fa-arrow-up" aria-hidden="true"></i><span class="sr-only">Promote</span></a>
												<a href="#" class="delete tip" title="Remove from group"><i class="fa fa-trash" aria-hidden="true"></i><span class="sr-only">Remove from group</span></a>
											</td>
										</tr>
									@endforeach
								</tbody>
							</table>
						</div>
					</div>

					<?php /*<div class="tabbed">
						<ul>
							<li><a href="#queues">queues</a></li>
							<li><a href="#unix-groups">unix group</a></li>
						</ul>

						<div id="queues">
							<table>
								<caption>Managers</caption>
								<thead>
									<tr>
										<th>User</th>
										@foreach ($group->queues as $queue)
										<th>{{ $queue->name }}</th>
										@endforeach
									</tr>
								</thead>
								<tbody>
							@foreach ($managers as $member)
								<tr>
									<td>{{ $member->user ? $member->user->name : trans('global.unknown') }}</td>
									@foreach ($group->queues as $queue)
										<td><input type="checkbox" name="queue[{{ $queue->name }}]" value="1" /></td>
									@endforeach
								</tr>
							@endforeach
								</tbody>
							</table>

							<table>
								<caption>Members</caption>
								<thead>
									<tr>
										<th>User</th>
										@foreach ($group->queues as $queue)
										<th>{{ $queue->name }}</th>
										@endforeach
									</tr>
								</thead>
								<tbody>
							@foreach ($members as $member)
								<tr>
									<td>{{ $member->user ? $member->user->name : trans('global.unknown') }}</td>
									@foreach ($group->queues as $queue)
										<td><input type="checkbox" name="queue[{{ $queue->name }}]" value="1" /></td>
									@endforeach
								</tr>
							@endforeach
								</tbody>
							</table>
						</div>

						<div id="unix-groups">
							<table class="datatable">
								<caption>Managers</caption>
								<thead>
									<tr>
										<th>User</th>
										@foreach ($group->unixgroups as $unix)
										<th>{{ $unix->longname }}</th>
										@endforeach
									</tr>
								</thead>
								<tbody>
									@foreach ($managers as $member)
										<tr>
											<td>{{ $member->user ? $member->user->name : trans('global.unknown') }}</td>
											@foreach ($group->unixgroups as $unix)
											<td><input type="checkbox" name="unix[{{ $unix->longname }}]" value="1" /></td>
											@endforeach
										</tr>
									@endforeach
								</tbody>
							</table>
						</div>

					</div>*/ ?>

				</div><!-- / #group-members -->

			<!--</div>-->
		</div><!-- / #everything -->

	</div>
</div>

@stop