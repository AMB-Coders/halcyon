<?php

namespace App\Modules\Resources\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Modules\Resources\Entities\Asset;
use App\Modules\Resources\Entities\Type;
use App\Halcyon\Http\StatefulRequest;

class TypesController extends Controller
{
	/**
	 * Display a listing of the resource.
	 * @return Response
	 */
	public function index(StatefulRequest $request)
	{
		// Get filters
		$filters = array(
			'search'   => null,
			// Paging
			'limit'    => config('list_limit', 20),
			'page'     => 1,
			//'start'    => $request->input('limitstart', 0),
			// Sorting
			'order'     => Type::$orderBy,
			'order_dir' => Type::$orderDir,
		);

		foreach ($filters as $key => $default)
		{
			$filters[$key] = $request->state('resources.types.filter_' . $key, $key, $default);
		}

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
			if (is_numeric($filters['search']))
			{
				$query->where('id', '=', $filters['search']);
			}
			else
			{
				$query->where('name', 'like', '%' . $filters['search'] . '%');
			}
		}

		// Build query
		$rows = Type::query()
			->withCount('resources')
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'], ['*'], 'page', $filters['page']);

		return view('resources::admin.types.index', [
			'rows'    => $rows,
			'filters' => $filters
		]);
	}

	/**
	 * Show the form for creating a new resource.
	 * @return Response
	 */
	public function create()
	{
		$row = new Type();

		return view('resources::admin.types.edit', [
			'row' => $row
		]);
	}

	/**
	 * Show the form for editing the specified resource.
	 * @return Response
	 */
	public function edit($id)
	{
		app('request')->merge(['hidemainmenu' => 1]);

		$row = Type::find($id);

		if ($fields = app('request')->old('fields'))
		{
			$row->fill($fields);
		}

		return view('resources::admin.types.edit', [
			'row' => $row
		]);
	}

	/**
	 * Update the specified resource in storage.
	 * @param  Request $request
	 * @return Response
	 */
	public function store(Request $request)
	{
		$request->validate([
			'fields.name' => 'required|max:20'
		]);

		$id = $request->input('id');

		$row = $id ? Type::findOrFail($id) : new Type();

		//$row->fill($request->input('fields'));
		$row->set([
			'name' => $request->input('name')
		]);

		if (!$row->save())
		{
			$error = $row->getError() ? $row->getError() : trans('messages.save failed');

			return redirect()->back()->withError($error);
		}

		return $this->cancel()->withSuccess(trans('messages.update success'));
	}

	/**
	 * Remove the specified resource from storage.
	 * @return Response
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
			$request->session()->flash('success', trans('messages.item deleted', ['count' => $success]));
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
		return redirect(route('admin.resources.types'));
	}
}
