<?php

namespace App\Modules\News\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use App\Modules\News\Models\Type;
use App\Halcyon\Http\StatefulRequest;

class TypesController extends Controller
{
	/**
	 * Display a listing of the resource.
	 *
	 * @param   StatefulRequest  $request
	 * @return  Response
	 */
	public function index(StatefulRequest $request)
	{
		// Get filters
		$filters = array(
			'search'    => null,
			'limit'     => config('list_limit', 20),
			'page'      => 1,
			'order'     => Type::$orderBy,
			'order_dir' => Type::$orderDir,
		);

		$reset = false;
		$request = $request->mergeWithBase();
		foreach ($filters as $key => $default)
		{
			if ($key != 'page'
			 && $request->has($key) //&& session()->has('news.types.filter_' . $key)
			 && $request->input($key) != session()->get('news.types.filter_' . $key))
			{
				$reset = true;
			}
			$filters[$key] = $request->state('news.types.filter_' . $key, $key, $default);
		}
		$filters['page'] = $reset ? 1 : $filters['page'];
		$filters['start'] = ($filters['limit'] * $filters['page']) - $filters['limit'];

		if (!in_array($filters['order'], ['id', 'name']))
		{
			$filters['order'] = Type::$orderBy;
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = Type::$orderDir;
		}

		if ($filters['search'])
		{
			$query = Type::query();

			if ($filters['search'])
			{
				$query->where('name', 'like', '%' . $filters['search'] . '%');
			}

			$rows = $query
				->orderBy($filters['order'], $filters['order_dir'])
				->paginate($filters['limit'], ['*'], 'page', $filters['page']);
		}
		else
		{
			$rows = Type::tree($filters['order'], $filters['order_dir']);

			$total = count($rows);
			$rows = array_slice($rows, $filters['start'], $filters['limit']);
		}

		$paginator = new \Illuminate\Pagination\LengthAwarePaginator($rows, $total, $filters['limit'], $filters['page']);
		$paginator->withPath(route('admin.news.types'));

		return view('news::admin.types.index', [
			'filters' => $filters,
			'rows'    => $rows,
			'paginator' => $paginator,
		]);
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return  Response
	 */
	public function create()
	{
		$row = new Type();

		if ($fields = app('request')->old('fields'))
		{
			$row->fill($fields);
		}

		$parents = Type::query()
			->where('parentid', '=', 0)
			->orderBy('name', 'asc')
			->get();

		return view('news::admin.types.edit', [
			'row' => $row,
			'parents' => $parents,
		]);
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param   Request  $request
	 * @return  Response
	 */
	public function store(Request $request)
	{
		$rules = [
			'fields.name' => 'required|string|max:32',
			'fields.parentid' => 'nullable|integer',
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return redirect()->back()
				->withInput($request->input())
				->withErrors($validator->messages());
		}

		$id = $request->input('id');

		$row = $id ? Type::findOrFail($id) : new Type();
		$row->fill($request->input('fields'));

		foreach (['tagusers', 'tagresources', 'future', 'location', 'ongoing', 'calendar', 'url'] as $key)
		{
			if (!$request->has('fields.' . $key))
			{
				$row->{$key} = 0;
			}
		}

		if (!$row->save())
		{
			$error = $row->getError() ? $row->getError() : trans('global.messages.save failed');

			return redirect()->back()->withError($error);
		}

		return $this->cancel()->with('success', trans('global.messages.item saved'));
	}

	/**
	 * Show the form for editing the specified entry
	 *
	 * @param   integer   $id
	 * @return  Response
	 */
	public function edit($id)
	{
		$row = Type::findOrFail($id);

		if ($fields = app('request')->old('fields'))
		{
			$row->fill($fields);
		}

		$parents = Type::query()
			->where('id', '!=', $id)
			->where('parentid', '=', 0)
			->orderBy('name', 'asc')
			->get();

		return view('news::admin.types.edit', [
			'row' => $row,
			'parents' => $parents,
		]);
	}

	/**
	 * Remove the specified entry
	 *
	 * @param   Request  $request
	 * @return  Response
	 */
	public function delete(Request $request)
	{
		$ids = $request->input('id', array());
		$ids = (!is_array($ids) ? array($ids) : $ids);

		$success = 0;

		foreach ($ids as $id)
		{
			$row = Type::findOrFail($id);

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
	 * Return to the main view
	 *
	 * @return  Response
	 */
	public function cancel()
	{
		return redirect(route('admin.news.types'));
	}
}
