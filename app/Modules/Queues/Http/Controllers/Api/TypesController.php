<?php

namespace App\Modules\Queues\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Modules\Queues\Models\Type;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class TypesController extends Controller
{
	/**
	 * Display a listing of queue types.
	 *
	 * @apiMethod GET
	 * @apiUri    /queues/types
	 * @apiParameter {
	 *      "name":          "limit",
	 *      "description":   "Number of result to return.",
	 *      "type":          "integer",
	 *      "required":      false,
	 *      "default":       25
	 * }
	 * @apiParameter {
	 *      "name":          "page",
	 *      "description":   "Number of where to start returning results.",
	 *      "type":          "integer",
	 *      "required":      false,
	 *      "default":       0
	 * }
	 * @apiParameter {
	 *      "name":          "search",
	 *      "description":   "A word or phrase to search for.",
	 *      "type":          "string",
	 *      "required":      false,
	 *      "default":       ""
	 * }
	 * @apiParameter {
	 *      "name":          "sort",
	 *      "description":   "Field to sort results by.",
	 *      "type":          "string",
	 *      "required":      false,
	 *      "default":       "created",
	 *      "allowedValues": "id, name, datetimecreated, datetimeremoved, parentid"
	 * }
	 * @apiParameter {
	 *      "name":          "sort_dir",
	 *      "description":   "Direction to sort results by.",
	 *      "type":          "string",
	 *      "required":      false,
	 *      "default":       "desc",
	 *      "allowedValues": "asc, desc"
	 * }
	 * @param   Request  $request
	 * @return Response
	 */
	public function index(Request $request)
	{
		$filters = array(
			'search'   => $request->input('search', ''),
			// Paging
			'limit'    => $request->input('limit', config('list_limit', 20)),
			//'start' => $request->input('limitstart', 0),
			// Sorting
			'sort'     => $request->input('sort', 'name'),
			'sort_dir' => $request->input('sort_dir', 'asc')
		);

		if (!in_array($filters['sort_dir'], ['asc', 'desc']))
		{
			$filters['sort_dir'] = 'asc';
		}

		$query = Type::query()
			->withCount('queues');

		if ($filters['search'])
		{
			$query->where('name', 'like', '%' . $filters['search'] . '%');
		}

		$rows = $query
			->withCount('queues')
			->orderBy($filters['sort'], $filters['sort_dir'])
			->paginate($filters['limit'])
			->appends(array_filter($filters));

		return new ResourceCollection($rows);
	}

	/**
	 * Create a queue type
	 *
	 * @apiMethod POST
	 * @apiUri    /queues/types
	 * @apiParameter {
	 *      "name":          "name",
	 *      "description":   "The name of the queue type",
	 *      "type":          "string",
	 *      "required":      true,
	 *      "default":       ""
	 * }
	 * @param   Request  $request
	 * @return Response
	 */
	public function create(Request $request)
	{
		$request->validate([
			'name' => 'required|string|max:20'
		]);

		$row = Type::create($request->all());

		return new JsonResource($row);
	}

	/**
	 * Read a queue type
	 *
	 * @apiMethod POST
	 * @apiUri    /queues/types/{id}
	 * @apiParameter {
	 *      "name":          "id",
	 *      "description":   "The ID of the queue type",
	 *      "type":          "integer",
	 *      "required":      true,
	 *      "default":       ""
	 * }
	 * @param   integer  $id
	 * @return  Response
	 */
	public function read($id)
	{
		$row = Type::findOrFail($id);

		return new JsonResource($row);
	}

	/**
	 * Update a queue type
	 *
	 * @apiMethod PUT
	 * @apiUri    /queues/types/{id}
	 * @apiParameter {
	 *      "name":          "id",
	 *      "description":   "The ID of the queue type",
	 *      "type":          "integer",
	 *      "required":      true,
	 *      "default":       ""
	 * }
	 * @apiParameter {
	 *      "name":          "name",
	 *      "description":   "The name of the queue type",
	 *      "type":          "string",
	 *      "required":      true,
	 *      "default":       ""
	 * }
	 * @param   integer  $id
	 * @param   Request  $request
	 * @return  Response
	 */
	public function update($id, Request $request)
	{
		$row = Type::findOrFail($id);

		$request->validate([
			'name' => 'nullable|string|max:20'
		]);

		$row->update($request->all());

		return new JsonResource($row);
	}

	/**
	 * Delete a queue type
	 *
	 * @apiMethod DELETE
	 * @apiUri    /queues/types/{id}
	 * @apiParameter {
	 *      "name":          "id",
	 *      "description":   "The ID of the queue type",
	 *      "type":          "integer",
	 *      "required":      true,
	 *      "default":       ""
	 * }
	 * @param   integer  $id
	 * @return  Response
	 */
	public function delete($id)
	{
		$row = Type::findOrFail($id);

		if (!$row->delete())
		{
			return response()->json(['message' => trans('global.messages.delete failed', ['id' => $id])], 500);
		}

		return response()->json(null, 204);
	}
}
