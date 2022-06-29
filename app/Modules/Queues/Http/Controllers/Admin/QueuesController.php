<?php

namespace App\Modules\Queues\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Artisan;
use App\Halcyon\Http\StatefulRequest;
use App\Modules\Queues\Models\Queue;
use App\Modules\Queues\Models\Type;
use App\Modules\Queues\Models\Scheduler;
use App\Modules\Queues\Models\SchedulerPolicy;
use App\Modules\Queues\Models\Walltime;
use App\Modules\Queues\Models\Size;
use App\Modules\Resources\Models\Subresource;
use App\Modules\Resources\Models\Child;
use App\Modules\Resources\Models\Asset;

class QueuesController extends Controller
{
	/**
	 * Display a listing of the queue.
	 * 
	 * @param  StatefulRequest $request
	 * @return Response
	 */
	public function index(StatefulRequest $request)
	{
		// Get filters
		$filters = array(
			'search'    => null,
			'state'     => 'enabled',
			'type'      => 0,
			'scheduler' => 0,
			'resource'  => 0,
			'class'     => null,
			// Paging
			'limit'     => config('list_limit', 20),
			'page'      => 1,
			// Sorting
			'order'     => Queue::$orderBy,
			'order_dir' => Queue::$orderDir,
		);

		$reset = false;
		$request = $request->mergeWithBase();
		foreach ($filters as $key => $default)
		{
			if ($key != 'page'
			 && $request->has($key) //&& session()->has('queues.filter_' . $key)
			 && $request->input($key) != session()->get('queues.filter_' . $key))
			{
				$reset = true;
			}
			$filters[$key] = $request->state('queues.filter_' . $key, $key, $default);
		}
		$filters['page'] = $reset ? 1 : $filters['page'];

		if (!in_array($filters['order'], ['id', 'name', 'enabled', 'type', 'parent', 'queuetype', 'groupid']))
		{
			$filters['order'] = Queue::$orderBy;
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = Queue::$orderDir;
		}

		// Build query
		$q = (new Queue)->getTable();
		$c = (new Child)->getTable();
		$r = (new Asset)->getTable();

		$query = Queue::query()
			->select($q . '.*')
			->with('subresource')
			->with('group')
			->leftJoin($c, $c . '.subresourceid', $q . '.subresourceid')
			->leftJoin($r, $r . '.id', $c . '.resourceid')
			->whereNull($r . '.datetimeremoved')
			->withTrashed();

		if ($filters['search'])
		{
			if (is_numeric($filters['search']))
			{
				$query->where($q . '.id', '=', (int)$filters['search']);
			}
			else
			{
				$query->where($q . '.name', 'like', '%' . strtolower((string)$filters['search']) . '%');
			}
		}

		if ($filters['state'] == 'trashed')
		{
			$query->whereNotNull($q . '.datetimeremoved');
		}
		elseif ($filters['state'] == 'enabled')
		{
			$query
				->whereNull($q . '.datetimeremoved')
				->where($q . '.enabled', '=', 1);
		}
		elseif ($filters['state'] == 'disabled')
		{
			$query
				->whereNull($q . '.datetimeremoved')
				->where($q . '.enabled', '=', 0);
		}
		else
		{
			$query
				->whereNull($q . '.datetimeremoved');
		}

		if ($filters['type'] > 0)
		{
			$query->where($q . '.queuetype', '=', (int)$filters['type']);
		}

		if ($filters['scheduler'])
		{
			$query->where($q . '.schedulerid', '=', (int)$filters['scheduler']);
		}

		if ($filters['resource'])
		{
			if (substr($filters['resource'], 0, 1) == 's')
			{
				$query->where($q . '.subresourceid', '=', (int)substr($filters['resource'], 1));
			}
			else
			{
				$query->where($r . '.id', '=', (int)$filters['resource']);
			}
		}

		if ($filters['class'] == 'system')
		{
			$query->where($q . '.groupid', '<=', 0);
		}
		elseif ($filters['class'] == 'owner')
		{
			$query->where($q . '.groupid', '>', 0);
		}

		$rows = $query
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'], ['*'], 'page', $filters['page']);

		/*$resources = (new Asset)->tree();*/
		$resources = Asset::query()
			//->where('batchsystem', '>', 0)
			//->where('listname', '!=', '')
			->where('rolename', '!=', '')
			->orderBy('name', 'asc')
			->get();

		$types = Type::orderBy('name', 'asc')->get();

		return view('queues::admin.queues.index', [
			'rows'  => $rows,
			'types' => $types,
			'resources' => $resources,
			'filters' => $filters,
		]);
	}

	/**
	 * Show the form for creating a new queue.
	 * 
	 * @param  Request $request
	 * @return Response
	 */
	public function create(Request $request)
	{
		$row = new Queue();
		$row->queuetype = 1;
		$row->maxjobsqueued = 12000;
		$row->maxjobsqueueduser = 5000;
		$row->maxjobsrun = 0;
		$row->maxjobcores = 0;
		$row->maxjobsrunuser = 0;
		$row->maxijobfactor = 2;
		$row->maxijobuserfactor = 1;
		$row->nodecoresdefault = 0;
		$row->priority = 1000;
		$row->defaultwalltime = 0.5;
		$row->enabled = 1;
		$row->started = 1;

		if ($fields = app('request')->old('fields'))
		{
			$row->fill($fields);
		}

		$row->cluster = $row->cluster ?: '';

		$types = Type::orderBy('name', 'asc')->get();
		$schedulers = Scheduler::orderBy('hostname', 'asc')->get();
		$schedulerpolicies = SchedulerPolicy::orderBy('name', 'asc')->get();
		$subresources = array();
		$resources = (new Asset)->tree();

		return view('queues::admin.queues.edit', [
			'row'   => $row,
			'types' => $types,
			'schedulers' => $schedulers,
			'schedulerpolicies' => $schedulerpolicies,
			'resources' => $resources,
			'subresources' => $subresources,
		]);
	}

	/**
	 * Show the form for editing the specified queue.
	 * 
	 * @param  Request $request
	 * @param  integer $id
	 * @return Response
	 */
	public function edit(Request $request, $id)
	{
		$row = Queue::query()
			->withTrashed()
			->where('id', '=', $id)
			->first();

		if (!$row)
		{
			abort(404);
		}

		if ($fields = app('request')->old('fields'))
		{
			$row->fill($fields);
		}

		$types = Type::orderBy('name', 'asc')->get();
		$schedulers = Scheduler::orderBy('hostname', 'asc')->get();
		$schedulerpolicies = SchedulerPolicy::orderBy('name', 'asc')->get();
		$subresources = array();
		$resources = (new Asset)->tree();

		return view('queues::admin.queues.edit', [
			'row'   => $row,
			'types' => $types,
			'schedulers' => $schedulers,
			'schedulerpolicies' => $schedulerpolicies,
			'resources' => $resources,
			'subresources' => $subresources,
		]);
	}

	/**
	 * Update the specified queue in storage.
	 * 
	 * @param  Request $request
	 * @return Response
	 */
	public function store(Request $request)
	{
		$rules = [
		//$request->validate([
			'fields.name' => 'required|string|max:64',
			'fields.schedulerid' => 'required|integer',
			'fields.subresourceid' => 'required|integer',
			'fields.groupid' => 'nullable|integer',
			'fields.free' => 'nullable|integer',
		//]);
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return redirect()->back()
				->withInput($request->input())
				->withErrors($validator->messages());
		}

		$id = $request->input('id');

		$row = $id ? Queue::findOrFail($id) : new Queue();
		$row->fill($request->input('fields'));
		if (!$row->groupid)
		{
			$row->groupid = -1;
		}

		if (!$id)
		{
			$exists = Queue::query()
				->where('name', '=', $row->name)
				->where('schedulerid', '=', $row->schedulerid)
				->first();

			if ($exists)
			{
				return redirect()->back()->withError(trans('queues::queues.error.queue already exists'));
			}
		}

		if (!$request->has('fields.free'))
		{
			$row->free = 0;
		}

		if (!$request->has('fields.reservation'))
		{
			$row->reservation = 0;
		}

		if (!$row->aclgroups)
		{
			$row->aclgroups = '';
		}
		if (!$row->nodememmin)
		{
			$row->nodememmin = 0;
		}
		if (!$row->nodememmax)
		{
			$row->nodememmax = 0;
		}

		if (!$row->save())
		{
			$error = $row->getError() ? $row->getError() : trans('global.messages.save failed');

			return redirect()->back()->withError($error);
		}

		$walltime = Walltime::query()
			->where('queueid', '=', $row->id)
			->whereNull('datetimestop')
			->orderBy('id', 'asc')
			->first();
		if (!$walltime)
		{
			$walltime = new Walltime;
		}
		$walltime->queueid = $row->id;
		$walltime->walltime = intval(floatval($request->input('maxwalltime')) * 60 * 60);
		$walltime->datetimestart = $row->datetimecreated;
		$walltime->save();

		if (!$id && $request->input('queueclass') == 'standby')
		{
			$size = new Size;
			$size->queueid = $row->id;
			$size->corecount = 20000;
			$size->datetimestart = $row->datetimecreated;
			$size->save();
		}

		return $this->cancel()->withSuccess($id ? trans('global.messages.item updated') : trans('global.messages.item created'));
	}

	/**
	 * Sets the state of one or more entries
	 * 
	 * @param  Request $request
	 * @param  integer $id
	 * @return  Response
	 */
	public function state(Request $request, $id = 0)
	{
		$action = app('request')->segment(3);
		$state  = $action == 'enable' ? 1 : 0;

		// Incoming
		$ids = $request->input('id', $id);
		$ids = (!is_array($ids) ? array($ids) : $ids);

		// Check for an ID
		if (count($ids) < 1)
		{
			$request->session()->flash('warning', trans($state ? 'queues::queues.select to enable' : 'queues::queues.select to disable'));
			return $this->cancel();
		}

		$success = 0;

		// Update record(s)
		foreach ($ids as $id)
		{
			$row = Queue::findOrFail(intval($id));

			if ($row->enabled != $state)
			{
				if (!$row->update(['enabled' => $state]))
				{
					$request->session()->flash('error', $row->getError());
					continue;
				}
			}

			$success++;
		}

		// Set message
		if ($success)
		{
			$msg = $state
				? 'queues::queues.messages.items enabled'
				: 'queues::queues.messages.items disabled';

			$request->session()->flash('success', trans($msg, ['count' => $success]));
		}

		return $this->cancel();
	}

	/**
	 * Sets the state of one or more entries
	 * 
	 * @param  Request $request
	 * @param  integer $id
	 * @return  Response
	 */
	public function scheduling(Request $request, $id = 0)
	{
		$action = app('request')->segment(3);
		$state  = $action == 'start' ? 1 : 0;

		// Incoming
		$ids = $request->input('id', $id);
		$ids = (!is_array($ids) ? array($ids) : $ids);

		if ($request->has('resource'))
		{
			$rids = array();
			$rid = $request->input('resource');

			// Is this a specific subresource
			if (substr($rid, 0, 1) == 's')
			{
				$rids[] = substr($rid, 1);
			}
			else
			{
				$resource = Asset::find($rid);

				if ($resource)
				{
					$rids = $resource->subresources->pluck('id')->toArray();
				}
			}

			foreach ($rids as $rid)
			{
				$subresource = Subresource::find($rid);

				if ($subresource)
				{
					$ids += $subresource->queues()
						->select('id')
						->get()
						->pluck('id')
						->toArray();
				}
			}
		}

		/*Artisan::call($state ? 'queues:start' : 'queues:stop', [
			'--debug' => 1
		]);

		$output = Artisan::output();

		$data = explode("\n", $output);*/

		// Check for an ID
		if (count($ids) < 1)
		{
			$request->session()->flash('warning', trans($state ? 'queues::queues.select to start' : 'queues::queues.select to stop'));
			return $this->cancel();
		}

		$success = 0;

		// Update record(s)
		foreach ($ids as $id)
		{
			$row = Queue::findOrFail(intval($id));

			if ($row->started != $state)
			{
				if (!$row->update(['started' => $state]))
				{
					$request->session()->flash('error', $row->getError());
					continue;
				}
			}

			$success++;
		}

		// Set message
		if ($success)
		{
			$msg = $state
				? 'queues::queues.messages.items started'
				: 'queues::queues.messages.items stopped';

			$request->session()->flash('success', trans($msg, ['count' => $success]));
		}

		return $this->cancel();
	}

	/**
	 * Sets the state of one or more entries
	 * 
	 * @param  Request $request
	 * @param  integer $id
	 * @return Response
	 */
	public function allscheduling(Request $request, $id = 0)
	{
		$action = $request->segment(3);
		$state  = $action == 'startall' ? 1 : 0;

		$resource = Asset::find($id);

		if (!$resource)
		{
			$request->session()->flash('danger', trans('queues::queues.resource not found'));
			return $this->cancel();
		}

		Artisan::call($state ? 'queues:start' : 'queues:stop', [
			'-v' => 1,
			'-r' => $id,
			//'--debug' => 1,
		]);

		$output = Artisan::output();

		$request->session()->flash('success', $output);

		return $this->cancel();
	}

	/**
	 * Remove the specified queue from storage.
	 * 
	 * @param  Request  $request
	 * @return Response
	 */
	public function delete(Request $request)
	{
		$ids = $request->input('id', array());
		$ids = (!is_array($ids) ? array($ids) : $ids);

		$success = 0;

		foreach ($ids as $id)
		{
			$row = Queue::findOrFail($id);

			if (!$row->delete())
			{
				$request->session()->flash('error', $row->getError());
				continue;
			}

			$success++;
		}

		if ($success)
		{
			$request->session()->flash('success', trans('global.messages.item deleted', ['count' => $success]));
		}

		return $this->cancel();
	}

	/**
	 * Return to default page
	 *
	 * @return  Response
	 */
	public function cancel()
	{
		return redirect(route('admin.queues.index'));
	}
}
