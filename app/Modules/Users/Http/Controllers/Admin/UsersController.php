<?php

namespace App\Modules\Users\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Query\JoinClause;
use App\Modules\Users\Models\User;
use App\Modules\Users\Models\UserUsername;
use App\Modules\Users\Models\Facet;
use App\Modules\Users\Models\RegistrationField;
use App\Modules\Users\Events\UserBeforeDisplay;
use App\Modules\Users\Events\UserDisplay;
use App\Modules\Users\Events\UserDeleted;
use App\Modules\Users\Helpers\Debug;
use App\Modules\Users\Helpers\Admin;
use App\Halcyon\Http\Concerns\UsesFilters;
use App\Halcyon\Access\Map;
use App\Halcyon\Access\Role;
use App\Halcyon\Access\Gate;
use App\Halcyon\Access\Asset;
use Carbon\Carbon;
use App\Modules\Groups\Models\Member;

class UsersController extends Controller
{
	use UsesFilters;

	/**
	 * Display a listing of the resource.
	 * 
	 * @param  Request $request
	 * @return View
	 */
	public function index(Request $request)
	{
		// Get filters
		$filters = $this->getStatefulFilters($request, 'users', [
			'search'     => null,
			'range'      => null,
			'created_at' => null,
			'activation' => 0,
			'state'      => 'enabled',
			'access'     => 0,
			'approved'   => '*',
			'role_id'    => 0,
			// Paging
			'limit'     => config('list_limit', 20),
			'page'      => 1,
			// Sorting
			'order'     => 'name',
			'order_dir' => 'asc',
		]);
		$extraFields = RegistrationField::all()->where('include_admin', true);
		$extraFieldKeys = array();
		foreach ($extraFields as $extra)
		{
			$extraFieldKeys[] = $extra->name;
		}

		if (!in_array($filters['order'], ['id', 'name', 'username', 'email', 'access', 'datecreated', 'datelastseen', ...$extraFieldKeys]))
		{
			$filters['order'] = 'name';
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = 'asc';
		}

		$a = (new User)->getTable();
		$b = (new Map)->getTable();
		$u = (new UserUsername)->getTable();
		$f= (new Facet)->getTable();

		$query = User::query()
			->select(
				$a . '.*', $u . '.username', $u . '.datecreated', $u . '.dateremoved', $u . '.datelastseen', 
				...array_map(
					function($index, $value)
					{
						return 'facet-' . $index . '.value as ' . $value;
					},
					array_keys($extraFieldKeys),
					array_values($extraFieldKeys)
				)
			)
			->with('roles')
			->with('sessions')
			->join($u, $u . '.userid', $a . '.id');
			/*->including(['notes', function ($note){
				$note
					->select('id')
					->select('user_id');
			}]);*/
		if (count($extraFieldKeys))
		{
			foreach ($extraFieldKeys as $index => $extra)
			{
				$facetAlias = 'facet-' . $index;
				$query
					->leftJoin($f . " as $facetAlias", 
						function (JoinClause $join) use ($a, $facetAlias, $extra)
						{
							$join->on($a . '.id', '=', $facetAlias . '.user_id')
								->on($facetAlias . '.key', '=', DB::raw("'" . $extra . "'"));
						}
					);
			}
		}

		if ($filters['role_id'])
		{
			$query
				->leftJoin($b, $b . '.user_id', $a . '.id')
				->where($b . '.role_id', '=', (int)$filters['role_id']);
				/*->group($a . '.id')
				->group($a . '.name')
				->group($a . '.username')
				->group($a . '.password')
				->group($a . '.usertype')
				->group($a . '.block')
				->group($a . '.sendEmail')
				->group($a . '.created_at')
				->group($a . '.last_visit')
				->group($a . '.activation')
				->group($a . '.params')
				->group($a . '.email');*/
		}

		if ($filters['search'])
		{
			if (is_numeric($filters['search']))
			{
				$query->where($a . '.id', '=', (int)$filters['search']);
			}
			else
			{
				$search = strtolower((string)$filters['search']);
				$skipmiddlename = preg_replace('/ /', '% ', $search);
				$query->select(
					$a . '.*', $u . '.username', $u . '.datecreated', $u . '.dateremoved', $u . '.datelastseen',
					DB::raw('IF(' . $u . '.username="' . $filters['search'] . '", 20,
							IF(' . $u . '.username LIKE "' . $filters['search'] . '%", 10, 0)
						)
						+ IF(' . $a . '.name LIKE "%' . $search . '%", 5, 0)
						+ IF(' . $a . '.name LIKE "' . $search . '%", 1, 0)
						+ IF(' . $a . '.name LIKE "%' . $skipmiddlename . '%", 3, 0)
						+ IF(' . $a . '.name LIKE "' . $skipmiddlename . '%", 1, 0)
						AS `weight`'),
						...array_map(
							function($index, $value)
							{
								return 'facet-' . $index . '.value as ' . $value;
							},
							array_keys($extraFieldKeys),
							array_values($extraFieldKeys)
						)

					)
					->orderBy('weight', 'desc');
				$query->where(function($where) use ($search, $skipmiddlename, $a, $u, $extraFieldKeys)
				{
					$where->where($a . '.name', 'like', '% ' . $search . '%')
						->orWhere($a . '.name', 'like', $search . '%')
						->orWhere($a . '.name', 'like', '% ' . $skipmiddlename . '%')
						->orWhere($a . '.name', 'like', $skipmiddlename . '%')
						->orWhere($u . '.username', 'like', '' . $search . '%')
						->orWhere($u . '.username', 'like', '%' . $search . '%');
					foreach (array_keys($extraFieldKeys) as $extraKey)
					{
						$where->orWhere('facet-' . $extraKey . '.value', 'like', '%' . $search . '%');
					}
				});
			}
		}

		if ($filters['created_at'])
		{
			$query->where($u . '.datecreated', '>=', $filters['created_at']);
		}

		if ($filters['state'] == 'enabled')
		{
			$query->whereNull($u . '.dateremoved')
				->where($a . '.enabled', '=', 1);
		}
		elseif ($filters['state'] == 'disabled')
		{
			$query->whereNull($u . '.dateremoved')
				->where($a . '.enabled', '=', 0);
		}
		elseif ($filters['state'] == 'trashed')
		{
			$query->whereNotNull($u . '.dateremoved');
		}

		// Apply the range filter.
		if ($filters['range'])
		{
			// Get now.
			$dNow = Carbon::now();
			$dStart = clone $dNow;

			switch ($filters['range'])
			{
				case 'past_week':
					$dStart->modify('-7 day');
					break;

				case 'past_1month':
					$dStart->modify('-1 month');
					break;

				case 'past_3month':
					$dStart->modify('-3 month');
					break;

				case 'past_6month':
					$dStart->modify('-6 month');
					break;

				case 'post_year':
				case 'past_year':
					$dStart->modify('-1 year');
					break;

				case 'today':
					// Reset the start time to be the beginning of today, local time.
					$dStart->setTime(0, 0, 0);
					break;
			}

			if ($filters['range'] == 'post_year')
			{
				$query->where($u . '.datecreated', '<', $dStart->format('Y-m-d H:i:s'));
			}
			else
			{
				$query->where($u . '.datecreated', '>=', $dStart->format('Y-m-d H:i:s'));
				$query->where($u . '.datecreated', '<=', $dNow->format('Y-m-d H:i:s'));
			}
		}
		$rows = $query
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate((int)$filters['limit'], ['*'], 'page', (int)$filters['page']);

		return view('users::admin.users.index', [
			'rows' => $rows,
			'extraFieldKeys' => $extraFieldKeys,
			'filters' => $filters,
			'roles' => Admin::getAccessRoles(),
		]);
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @param  Request $request
	 * @return View
	 */
	public function create(Request $request)
	{
		$user = new User;

		if ($fields = $request->old('fields'))
		{
			$user->fill($fields);
		}

		return view('users::admin.users.edit', [
			'user' => $user
		]);
	}

	/**
	 * Store a newly created resource in storage.
	 * 
	 * @param  Request $request
	 * @return RedirectResponse
	 */
	public function store(Request $request)
	{
		$rules = [
			'fields.name' => 'required|string|max:128',
			//'fields.email' => 'required',
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return redirect()->back()
				->withInput($request->input())
				->withErrors($validator->messages());
		}

		$id = $request->input('id');
		$fields = $request->input('fields');

		$user = User::findOrNew($id);
		$user->fill($fields);

		if (!$user->id)
		{
			if (empty($user->newroles))
			{
				$user->setDefaultRole();
			}
			$user->api_token = $user->generateApiToken();
		}
		if (!$user->puid)
		{
			$user->puid = 0;
		}

		// Can't block yourself
		/*if ($user->block && $user->id == auth()->user()->id)
		{
			return redirect()
				->back()
				->withError(trans('users::users.error.cannot block self'));
		}*/

		// Make sure that we are not removing ourself from Super Admin role
		$iAmSuperAdmin = auth()->user()->can('admin');

		if ($iAmSuperAdmin && auth()->user()->id == $user->id)
		{
			// Check that at least one of our new roles is Super Admin
			$stillSuperAdmin = false;

			foreach ($fields['newroles'] as $role)
			{
				$stillSuperAdmin = ($stillSuperAdmin ? $stillSuperAdmin : Gate::checkRole($role, 'admin'));
			}

			if (!$stillSuperAdmin)
			{
				return redirect()
					->back()
					->withError(trans('users::users.error.cannot demote self'));
			}
		}

		if (!$user->save())
		{
			return redirect()
				->back()
				->withError(trans('global.messages.save failed'));
		}

		if ($request->has('ufields'))
		{
			$ufields = $request->input('ufields');

			$username = $id ? $user->getUserUsername() : new UserUsername();
			$username->userid = $user->id;
			$username->fill($ufields);
			if (!$id)
			{
				$username->datecreated = Carbon::now();
			}
			$username->save();
		}

		if ($request->has('facet'))
		{
			$facets = $request->input('facet');

			foreach ($facets as $i => $f)
			{
				if (!$f['key'])
				{
					continue;
				}
				$facet = Facet::findByUserAndKey($user->id, $f['key']);
				$facet = $facet ?: new Facet;
				$facet->user_id = $user->id;
				$facet->key     = $f['key'];
				$facet->value   = $f['value'];
				$facet->access  = $f['access'];
				$facet->save();
			}
		}

		/*if (!$user->setRoles($fields['roles']))
		{
			return redirect()
				->back()
				->withError(trans('global.messages.save failed'));
		}*/

		return $this->cancel()->with('success', trans('global.messages.item ' . ($id ? 'updated' : 'created')));
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  Request $request
	 * @param  int  $id
	 * @return View
	 */
	public function edit(Request $request, $id)
	{
		$user = User::findOrFail($id);

		if ($user->puid)
		{
			$user->sourced = 1;
		}

		if ($fields = $request->old('fields'))
		{
			$user->fill($fields);
		}

		return view('users::admin.users.edit', [
			'user' => $user,
		]);
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  Request $request
	 * @param  int  $id
	 * @return View
	 */
	public function show(Request $request, int $id, $section = null)
	{
		$user = User::findOrFail($id);

		if ($user->puid)
		{
			$user->sourced = 1;
		}

		if ($fields = $request->old('fields'))
		{
			$user->fill($fields);
		}

		if ($active = $request->input('active'))
		{
			$section = $active;
		}

		event($event = new UserBeforeDisplay($user));
		$user = $event->getUser();

		event($event = new UserDisplay($user, $section));
		$sections = collect($event->getSections());
		$parts = collect($event->getParts());

		return view('users::admin.users.show', [
			'user' => $user,
			'sections' => $sections,
			'parts' => $parts,
			'active' => $section,
		]);
	}

	/**
	 * Remove the specified resource from storage.
	 * 
	 * @param  Request $request
	 * @return RedirectResponse
	 */
	public function delete(Request $request)
	{
		$ids = $request->input('id', array());
		$ids = (!is_array($ids) ? array($ids) : $ids);

		$success = 0;

		foreach ($ids as $id)
		{
			$row = User::find($id);

			if (!$row)
			{
				continue;
			}

			foreach ($row->usernames as $username)
			{
				$username->delete();
			}

			event(new UserDeleted($row));

			$success++;
		}

		if ($success)
		{
			$request->session()->flash('success', trans('global.messages.item deleted', ['count' => $success]));
		}

		return $this->cancel();
	}

	/**
	 * Return to the main view
	 *
	 * @return  RedirectResponse
	 */
	public function cancel()
	{
		return redirect(route('admin.users.index'));
	}

	/**
	 * Sets the account state of a member to enabled
	 *
	 * @param   Request $request
	 * @return  RedirectResponse
	 */
	public function enable(Request $request)
	{
		return $this->disable($request, 1);
	}

	/**
	 * Sets the account state of a member to disabled
	 *
	 * @param   Request $request
	 * @param   int $state
	 * @return  RedirectResponse
	 */
	public function disable(Request $request, $state = 0)
	{
		// Incoming user ID
		$ids = $request->input('id', array());
		$ids = (!is_array($ids) ? array($ids) : $ids);

		$success = 0;

		foreach ($ids as $id)
		{
			// Load the profile
			$user = User::findOrFail(intval($id));
			$user->enabled = $state;

			if (!$user->enabled && auth()->user() && $user->id == auth()->user()->id)
			{
				$request->session()->flash('error', trans('users::users.error.cannot disable self'));
				continue;
			}

			if (!$user->save())
			{
				$request->session()->flash('error', trans('users::users.error.save failed'));
				continue;
			}

			$success++;
		}

		if ($success)
		{
			$request->session()->flash('success', trans('users::users.user ' . ($state ? 'enabled' : 'disabled'), ['count' => $success]));
		}

		return $this->cancel();
	}

	/**
	 * Debug user permissions
	 *
	 * @param  int $id
	 * @param  Request $request
	 * @return View
	 */
	public function debug($id, Request $request)
	{
		// Get filters
		$filters = array(
			'search' => $request->input('search'),
			'order' => 'lft',
			'order_dir' => 'ASC',
			'level_start' => $request->input('filter_level_start', 0),
			'level_end' => $request->input('filter_level_end', 0),
			'module' => $request->input('filter_module'),
			'limit'     => config('list_limit', 20),
		);

		if ($filters['level_end'] > 0 && $filters['level_end'] < $filters['level_start'])
		{
			$filters['level_end'] = $filters['level_start'];
		}

		//$id = $request->input('id');

		// Load member
		$user = User::findOrFail($id);

		// Select the required fields from the table.
		$query = Asset::query();

		if ($filters['search'])
		{
			$query->where('name', 'like', $filters['search'])
				->orWhere('title', 'like', $filters['search']);
		}

		if ($filters['level_start'] > 0)
		{
			$query->where('level', '>=', $filters['level_start']);
		}
		if ($filters['level_end'] > 0)
		{
			$query->where('level', '<=', $filters['level_end']);
		}

		// Filter the items over the component if set.
		if ($filters['module'])
		{
			$query->where('name', '=', $filters['module'])
				->orWhere('name', 'like', $filters['module']);
		}

		$assets = $query
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit']);

		/*$actions = array(
			'Admin' => ['admin', null], //'Admin things'],
			'Manage' => ['manage', null], //'Manage things'],
			'Create' => ['create', null], //'Create things'],
			'Edit' => ['edit', null], //'Edit things'],
			'Edit state' => ['edit.state', null], //'Edit own things'],
			'Edit own' => ['edit.own', null], //'Edit own things'],
			'Delete' => ['delete', 'users::access.delete desc']
		);*/
		$actions = Debug::getActions($filters['module']);

		//$data = $assets->raw();
		//$assets->clear();

		$assets->map(function ($asset, $key) use ($user, $actions)
		{
			$checks = array();

			foreach ($actions as $action)
			{
				$name  = $action[0];
				/*$level = $action[1];

				// Check that we check this action for the level of the asset.
				if ($action[1] === null || $action[1] >= $asset->level)
				{
					// We need to test this action.
					//echo $id . ',' . $action[0] . ',' . $asset->name . '<br />';
					$checks[$name] = Gate::check($id, $action[0], $asset->name);
				}
				else
				{
					// We ignore this action.
					$checks[$name] = 'skip';
				}*/
				$checks[$name] = $user->can($name . ' ' . $asset->name);
			}

			$asset->checks = $checks;

			return $asset;
		});

		$levels  = Debug::getLevelsOptions();
		$modules = Debug::getModules();

		// Output the HTML
		return view('users::admin.users.debug', [
			'user'    => $user,
			'filters' => $filters,
			'assets'  => $assets,
			'actions' => $actions,
			'levels'  => $levels,
			'modules' => $modules,
		]);
	}
}
