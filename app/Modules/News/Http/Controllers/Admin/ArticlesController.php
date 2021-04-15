<?php

namespace App\Modules\News\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use App\Modules\News\Models\Article;
use App\Modules\News\Models\Type;
use App\Modules\News\Models\Stemmedtext;
use App\Halcyon\Http\StatefulRequest;
use App\Halcyon\Utility\PorterStemmer;

class ArticlesController extends Controller
{
	/**
	 * Display templates?
	 *
	 * @var  integer
	 */
	private $template = 0;

	/**
	 * Display a listing of articles
	 *
	 * @param   StatefulRequest  $request
	 * @return  Response
	 */
	public function index(StatefulRequest $request)
	{
		// Get filters
		$filters = array(
			'search'    => null,
			'state'     => 'published',
			'access'    => null,
			'limit'     => config('list_limit', 20),
			'page'      => 1,
			'order'     => 'datetimecreated',
			'order_dir' => 'desc',
			'type'      => null,
		);

		$action = 'index';
		if ($this->template)
		{
			$action = 'template';
			$filters['state'] = '*';
		}

		foreach ($filters as $key => $default)
		{
			$filters[$key] = $request->state('news.' . $action . '.filter_' . $key, $key, $default);
		}

		if (!in_array($filters['order'], ['id', 'headline', 'datetimecreated']))
		{
			$filters['order'] = 'datetimecreated';
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = 'desc';
		}

		$n = (new Article)->getTable();

		$query = Article::query()
			->select($n . '.*')
			->with('type')
			->with('associations')
			->where($n . '.template', '=', $this->template);

		if ($filters['search'])
		{
			/*$query->where(function($query) use ($filters)
			{
				$query->where('headline', 'like', '%' . $filters['search'] . '%')
					->orWhere('body', 'like', '%' . $filters['search'] . '%');
			});*/

			$keywords = explode(' ', $filters['search']);

			$from_sql = array();
			foreach ($keywords as $keyword)
			{
				// Trim extra garbage
				$keyword = preg_replace('/[^A-Za-z0-9]/', ' ', $keyword);

				// Calculate stem for the word
				$stem = PorterStemmer::Stem($keyword);
				$stem = substr($stem, 0, 1) . $stem;

				$from_sql[] = "+" . $stem;
			}

			$s = (new Stemmedtext)->getTable();

			$query->join($s, $s . '.id', $n . '.id');
			$query->select($n . '.*', DB::raw("(MATCH($s.stemmedtext) AGAINST ('" . implode(' ', $from_sql) . "') * 10 + 2 * (1 / (ABS(DATEDIFF(NOW(), $n.datetimenews)) + 1))) AS score"));
			$query->whereRaw("MATCH($s.stemmedtext) AGAINST ('" . implode(' ', $from_sql) . "' IN BOOLEAN MODE)");
			$query->orderBy('score', 'desc');
		}

		if ($filters['state'] == 'published')
		{
			$query->where($n . '.published', '=', 1);
		}
		elseif ($filters['state'] == 'unpublished')
		{
			$query->where($n . '.published', '=', 0);
		}

		if ($filters['type'])
		{
			$query->where($n . '.newstypeid', '=', $filters['type']);
		}

		$rows = $query
			->withCount('updates')
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'], ['*'], 'page', $filters['page']);

		$types = Type::query()
			->orderBy('name', 'asc')
			->get();

		return view('news::admin.articles.index', [
			'filters' => $filters,
			'rows'    => $rows,
			'types'   => $types,
			'template' => $this->template
		]);
	}

	/**
	 * Display a listing of templates
	 *
	 * @param   StatefulRequest  $request
	 * @return  Response
	 */
	public function templates(StatefulRequest $request)
	{
		$this->template = 1;

		return $this->index($request);
	}

	/**
	 * Show the form for creating a new article
	 *
	 * @return  Response
	 */
	public function create()
	{
		$row = new Article();
		$row->published = 1;

		$types = Type::orderBy('name', 'asc')->get();

		if ($df = config('modules.news.default_type', 0))
		{
			foreach ($types as $type)
			{
				if ($type->id == $df)
				{
					$row->newstypeid = $type->id;
					break;
				}
			}
		}
		else
		{
			$row->newstypeid = $types->first()->id;
		}

		return view('news::admin.articles.edit', [
			'row'   => $row,
			'types' => $types
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
		$row = Article::findOrFail($id);

		if ($fields = app('request')->old('fields'))
		{
			$row->fill($fields);
		}

		$types = Type::orderBy('name', 'asc')->get();

		return view('news::admin.articles.edit', [
			'row'   => $row,
			'types' => $types
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
		$request->validate([
			'fields.newstypeid' => 'required|integer|in:0,1',
			'fields.headline' => 'required|string|max:255',
			'fields.body' => 'required|string|max:15000',
			'fields.published' => 'nullable|integer|in:0,1',
			'fields.template' => 'nullable|integer|in:0,1',
			'fields.datetimenews' => 'required|date',
			'fields.datetimenewsend' => 'nullable|date',
			'fields.location' => 'nullable|string|max:32',
			'fields.url' => 'nullable|url',
		]);

		$fields = $request->input('fields');
		$fields['location'] = isset($fields['location']) ? (string)$fields['location'] : '';

		if (array_key_exists('datetimenewsend', $fields) && !trim($fields['datetimenewsend']))
		{
			unset($fields['datetimenewsend']);
		}

		$row = $id ? Article::findOrFail($id) : new Article();
		$row->fill($fields);

		if (!$row->type)
		{
			return redirect()->back()->with('error', trans('news::news.error.invalid type'));
		}

		// Templates shouldn't have datetimes set
		if ($row->template)
		{
			$row->datetimenews = '0000-00-00 00:00:00';
			$row->datetimenewsend = '0000-00-00 00:00:00';
		}

		if ($row->datetimenewsend && $row->datetimenews > $row->datetimenewsend)
		{
			return redirect()->back()->with('error', trans('news::news.error.invalid time range'));
		}

		if ($row->url && !filter_var($row->url, FILTER_VALIDATE_URL))
		{
			return redirect()->back()->with('error', trans('news::news.error.invalid url'));
		}
		
		if (!$row->save())
		{
			return redirect()->back()->with('error', trans('news::news.error.Failed to create item.'));
		}

		if ($request->has('resources'))
		{
			$row->setResources($request->input('resources'));
		}

		if ($request->has('associations'))
		{
			$row->setAssociations($request->input('associations'));
		}

		return $this->cancel()->with('success', trans('global.messages.item saved'));
	}

	/**
	 * Sets the state of one or more entries
	 * 
	 * @param  Request  $request
	 * @param  integer  $id
	 * @return Response
	 */
	public function state(Request $request, $id)
	{
		$action = $request->segment(count($request->segments()) - 1);
		$state  = $action == 'publish' ? 1 : 0;

		// Incoming
		$ids = $request->input('id', array($id));
		$ids = (!is_array($ids) ? array($ids) : $ids);

		// Check for an ID
		if (count($ids) < 1)
		{
			$request->session()->flash('warning', trans($state ? 'news::news.select to publish' : 'news::news.select to unpublish'));
			return $this->cancel();
		}

		$success = 0;

		// Update record(s)
		foreach ($ids as $id)
		{
			$row = Article::findOrFail(intval($id));

			if ($row->published == $state)
			{
				continue;
			}

			// Don't update last modified timestamp for state changes
			$row->timestamps = false;

			if (!$row->update(['published' => $state]))
			{
				$request->session()->flash('error', $row->getError());
				continue;
			}

			$success++;
		}

		// Set message
		if ($success)
		{
			$msg = $state
				? 'news::news.items published'
				: 'news::news.items unpublished';

			$request->session()->flash('success', trans($msg, ['count' => $success]));
		}

		return $this->cancel();
	}

	/**
	 * Remove the specified entry
	 *
	 * @param  Request  $request
	 * @return Response
	 */
	public function delete(Request $request)
	{
		// Incoming
		$ids = $request->input('id', array());
		$ids = (!is_array($ids) ? array($ids) : $ids);

		$success = 0;

		foreach ($ids as $id)
		{
			// Delete the entry
			// Note: This is recursive and will also remove all descendents
			$row = Article::findOrFail($id);

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
		return redirect(route('admin.news.index'));
	}
}
