<?php

namespace App\Modules\History\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Halcyon\Http\StatefulRequest;
use App\Modules\History\Models\History;

class HistoryController extends Controller
{
	/**
	 * Display a listing of the resource.
	 *
	 * @param   Request  $request
	 * @return  Response
	 */
	public function index(StatefulRequest $request)
	{
		// Get filters
		$filters = array(
			'search'    => null,
			'limit'     => config('list_limit', 20),
			'page'      => 1,
			'order'     => History::$orderBy,
			'order_dir' => History::$orderDir,
			'action'    => '',
			'type'      => ''
		);

		foreach ($filters as $key => $default)
		{
			$filters[$key] = $request->state('history.filter_' . $key, $key, $default);
		}

		if (!in_array($filters['order'], ['id', 'name']))
		{
			$filters['order'] = History::$orderBy;
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = History::$orderDir;
		}

		$query = History::query();

		if ($filters['search'])
		{
			$query->where(function($query) use ($filters)
			{
				$query->where('historable_type', 'like', '%' . $filters['search'] . '%')
					->orWhere('historable_table', 'like', '%' . $filters['search'] . '%');
			});
		}

		if ($filters['action'])
		{
			$query->where('action', '=', $filters['action']);
		}

		$rows = $query
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'], ['*'], 'page', $filters['page']);

		$types = History::query()
			->select('historable_type')
			->distinct()
			->orderBy('historable_type', 'asc')
			->get();

		return view('history::admin.index', [
			'filters' => $filters,
			'rows'    => $rows,
			'types'   => $types
		]);
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param   Request  $request
	 * @return  Response
	 */
	/*public function store(Request $request)
	{
		$request->validate([
			'fields.name' => 'required'
		]);

		$id = $request->input('id');

		$row = $id ? History::findOrFail($id) : new History();
		$row->fill($request->input('fields'));

		if (!$row->save())
		{
			$error = $row->getError() ? $row->getError() : trans('messages.save failed');

			return redirect()->back()->withError($error);
		}

		return $this->cancel()->with('success', trans('messages.item saved'));
	}*/

	/**
	 * Show the form for editing the specified entry
	 *
	 * @param   integer   $id
	 * @return  Response
	 */
	public function show($id)
	{
		app('request')->merge(['hidemainmenu' => 1]);

		$row = History::findOrFail($id);

		return view('history::admin.show', [
			'row' => $row
		]);
	}

	/**
	 * Remove the specified entry
	 *
	 * @param   integer   $id
	 * @return  Response
	 */
	public function delete(Request $request)
	{
		$ids = $request->input('id', array());
		$ids = (!is_array($ids) ? array($ids) : $ids);

		$success = 0;

		foreach ($ids as $id)
		{
			$row = History::findOrFail($id);

			if (!$row->delete())
			{
				$request->session()->flash('error', $row->getError());
				continue;
			}

			$success++;
		}

		if ($success)
		{
			$request->session()->flash('success', trans('messages.item deleted', ['count' => $success]));
		}

		return $this->cancel();
	}

	/**
	 * Return to the main view
	 *
	 * @return  Response
	 */
	public function cancel()
	{
		return redirect(route('admin.history.index'));
	}
}
