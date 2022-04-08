<?php

namespace App\Modules\Publications\Http\Controllers\Site;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use App\Modules\Publications\Models\Type;
use App\Modules\Publications\Models\Publication;
use App\Halcyon\Http\StatefulRequest;
use Carbon\Carbon;

class PublicationsController extends Controller
{
	/**
	 * Display a listing of the resource.
	 * 
	 * @param  StatefulRequest $request
	 * @return Response
	 */
	public function index(StatefulRequest $request)
	{
		// Get filters
		$filters = array(
			'search'   => null,
			'state'    => 'published',
			'type'     => null,
			'year'     => null,
			// Paging
			'limit'    => config('list_limit', 20),
			'page'     => 1,
			// Sorting
			'order'     => Publication::$orderBy,
			'order_dir' => Publication::$orderDir,
		);

		$reset = false;
		$request = $request->mergeWithBase();
		foreach ($filters as $key => $default)
		{
			if ($key != 'page'
			 && $request->has($key) //&& session()->has('publications.filter_' . $key)
			 && $request->input($key) != session()->get('publications.filter_' . $key))
			{
				$reset = true;
			}
			$filters[$key] = $request->state('publications.filter_' . $key, $key, $default);
		}
		$filters['page'] = $reset ? 1 : $filters['page'];

		if (!in_array($filters['order'], ['id', 'title', 'state', 'published_at']))
		{
			$filters['order'] = Publication::$orderBy;
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = Publication::$orderDir;
		}

		if (!auth()->user() || !auth()->user()->can('manage publications'))
		{
			$filters['state'] = 'published';
		}

		$types = Type::query()
			->orderBy('id', 'asc')
			->get();

		// Get records
		$query = Publication::query();

		if ($filters['state'] == 'published')
		{
			$query->where('state', '=', 1);
		}
		elseif ($filters['state'] == 'unpublished')
		{
			$query->where('state', '=', 0);
		}
		elseif ($filters['state'] == 'trashed')
		{
			$query->onlyTrashed();
		}

		if ($filters['year'] && $filters['year'] != '*')
		{
			$query->where('published_at', '>=', $filters['year'] . '-01-01 00:00:00')
				->where('published_at', '<', Carbon::parse($filters['year'] . '-01-01 00:00:00')->modify('+1 year')->format('Y') . '-01-01 00:00:00');
		}

		if ($filters['type'] && $filters['type'] != '*')
		{
			foreach ($types as $type)
			{
				if ($type->alias == $filters['type'])
				{
					$query->where('type_id', '=', $type->id);
					break;
				}
			}
		}

		$rows = $query
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'], ['*'], 'page', $filters['page']);

		$now = date("Y");
		$start = date("Y");
		$first = Publication::query()
			->orderBy('published_at', 'asc')
			->first();
		if ($first)
		{
			$start = $first->published_at->format('Y');
		}

		$years = array();
		for ($start; $start < $now; $start++)
		{
			$years[] = $start;
		}
		$years[] = $now;
		rsort($years);

		return view('publications::site.publications.index', [
			'rows' => $rows,
			'filters' => $filters,
			'types' => $types,
			'years' => $years,
		]);
	}

	/**
	 * Show the form for creating a new article
	 *
	 * @return  Response
	 */
	public function create()
	{
		$row = new Publication();
		$row->state = 1;

		if ($fields = app('request')->old())
		{
			$row->fill($fields);
		}

		$types = Type::query()
			->orderBy('name', 'asc')
			->get();

		return view('publications::site.publications.edit', [
			'row' => $row,
			'types' => $types,
		]);
	}

	/**
	 * Show the form for editing the specified entry
	 *
	 * @param   integer  $id
	 * @return  Response
	 */
	public function edit($id)
	{
		$row = Publication::findOrFail($id);

		if ($fields = app('request')->old())
		{
			$row->fill($fields);
		}

		$types = Type::query()
			->orderBy('name', 'asc')
			->get();

		return view('publications::site.publications.edit', [
			'row' => $row,
			'types' => $types,
		]);
	}

	/**
	 * Store a newly created entry
	 *
	 * @param   Request  $request
	 * @return  Response
	 */
	public function store(Request $request)
	{
		//$request->validate([
		$rules = [
			'type_id' => 'required|integer|min:1',
			'title' => 'required|string|max:500',
			'author' => 'nullable|string|max:3000',
			'editor' => 'nullable|string|max:3000',
			'url' => 'nullable|string|max:2083',
			'series' => 'nullable|string|max:255',
			'booktitle' => 'nullable|string|max:1000',
			'edition' => 'nullable|string|max:100',
			'chapter' => 'nullable|string|max:40',
			'issuetitle' => 'nullable|string|max:255',
			'journal' => 'nullable|string|max:255',
			'issue' => 'nullable|string|max:40',
			'volume' => 'nullable|string|max:40',
			'number' => 'nullable|string|max:40',
			'pages' => 'nullable|string|max:40',
			'publisher' => 'nullable|string|max:500',
			'address' => 'nullable|string|max:300',
			'institution' => 'nullable|string|max:500',
			'organization' => 'nullable|string|max:500',
			'school' => 'nullable|string|max:200',
			'crossref' => 'nullable|string|max:100',
			'isbn' => 'nullable|string|max:50',
			'doi' => 'nullable|string|max:255',
			'note' => 'nullable|string|max:2000',
			'state' => 'nullable|integer',
			'published_at' => 'nullable|datetime',
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return redirect()->back()
				->withInput($request->input())
				->withErrors($validator->messages());
		}

		$id = $request->input('id');

		$row = $id ? Publication::findOrFail($id) : new Publication();
		foreach ($rules as $key => $rule)
		{
			if ($request->has($key))
			{
				$row->$key = $request->input($key);
			}
		}
		if ($request->has('year'))
		{
			$row->published_at = $request->input('year') . '-' . $request->input('month', '01') . ' -01 00:00:00';
		}

		if (!$row->save())
		{
			$error = $row->getError() ? $row->getError() : trans('global.messages.save failed');

			return redirect()->back()->withError($error);
		}

		return $this->cancel()->with('success', trans('global.messages.item ' . ($id ? 'updated' : 'created')));
	}

	/**
	 * Return to default page
	 *
	 * @return  Response
	 */
	public function cancel()
	{
		return redirect(route('site.publications.index'));
	}
}
