<?php

namespace App\Modules\Menus\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Modules\Menus\Models\Type;
use App\Modules\Menus\Models\Item;
use App\Halcyon\Access\Viewlevel;

/**
 * Menus
 *
 * @apiUri    /menus
 */
class MenusController extends Controller
{
	/**
	 * Display a listing of entries
	 *
	 * @apiMethod GET
	 * @apiUri    /menus
	 * @apiAuthorization  true
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "search",
	 * 		"description":   "A word or phrase to search for.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"name":          "limit",
	 * 		"description":   "Number of result per page.",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   20
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "page",
	 * 		"description":   "Number of where to start returning results.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   1
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"name":          "order",
	 * 		"description":   "Field to sort results by.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"default":   "menutype",
	 * 			"enum": [
	 * 				"id",
	 * 				"title",
	 * 				"menutype",
	 * 				"description",
	 * 				"client_id",
	 * 				"created_at",
	 * 				"updated_at"
	 * 			]
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"name":          "order_dir",
	 * 		"description":   "Direction to sort results by.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"default":   "asc",
	 * 			"enum": [
	 * 				"asc",
	 * 				"desc"
	 * 			]
	 * 		}
	 * }
	 * @apiResponse {
	 * 		"200": {
	 * 			"description": "Successful list cread",
	 * 			"content": {
	 * 				"application/json": {
	 * 					"example": {"data":[{
	 * 						"id": 2,
	 * 						"menutype": "about",
	 * 						"title": "About",
	 * 						"description": "About Side Menu",
	 * 						"client_id": 0,
	 * 						"created_at": null,
	 * 						"updated_at": null,
	 * 						"deleted_at": null,
	 * 						"items_count": 12,
	 * 						"counts": {
	 * 							"published": 0,
	 * 							"unpublished": 0,
	 * 							"trashed": 0
	 * 						},
	 * 						"api": "https://example.org/api/menus/2"
	 * 					}]}
	 * 				}
	 * 			}
	 * 		}
	 * }
	 * @param  Request  $request
	 * @return ResourceCollection
	 */
	public function index(Request $request)
	{
		// Get filters
		$filters = array(
			'search'    => null,
			// Paging
			'limit'     => config('list_limit', 20),
			// Sorting
			'order'     => Type::$orderBy,
			'order_dir' => Type::$orderDir,
		);

		foreach ($filters as $key => $default)
		{
			$filters[$key] = $request->input($key, $default);
		}

		if (!in_array($filters['order'], ['id', 'title', 'menutype', 'client_id', 'description', 'updated_at', 'created_at']))
		{
			$filters['order'] = Type::$orderBy;
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = Type::$orderDir;
		}

		// Get records
		$query = Type::query();

		if ($filters['search'])
		{
			$query->where(function($where) use ($filters)
			{
				$where->where('title', 'like', '%' . $filters['search'] . '%')
					->orWhere('menutype', 'like', '%' . $filters['search'] . '%')
					->orWhere('description', 'like', '%' . $filters['search'] . '%');
			});
		}

		$rows = $query
			->withCount('items')
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'])
			->appends(array_filter($filters))
			->each(function($row, $key)
			{
				$row->api = route('api.menus.read', ['id' => $row->id]);
				$row->counts = [
					'published' => number_format($row->countPublishedItems()),
					'unpublished' => number_format($row->countUnpublishedItems()),
					'trashed' => number_format($row->countTrashedItems()),
				];
			});

		return new ResourceCollection($rows);
	}

	/**
	 * Create a new entry
	 *
	 * @apiMethod POST
	 * @apiUri    /menus
	 * @apiAuthorization  true
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "title",
	 * 		"description":   "Menu title",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "description",
	 * 		"description":   "A description of the menu",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "client_id",
	 * 		"description":   "Client (admin = 1|site = 0) ID",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   0
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "menutype",
	 * 		"description":   "A short alias for the menu. If none provided, one will be generated from the title.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiResponse {
	 * 		"201": {
	 * 			"description": "Successful entry creation",
	 * 			"content": {
	 * 				"application/json": {
	 * 					"example": {
	 * 						"id": 2,
	 * 						"menutype": "about",
	 * 						"title": "About",
	 * 						"description": "About Side Menu",
	 * 						"client_id": 0,
	 * 						"created_at": null,
	 * 						"updated_at": null,
	 * 						"deleted_at": null,
	 * 						"items_count": 12,
	 * 						"counts": {
	 * 							"published": 0,
	 * 							"unpublished": 0,
	 * 							"trashed": 0
	 * 						},
	 * 						"api": "https://example.org/api/menus/2"
	 * 					}
	 * 				}
	 * 			}
	 * 		},
	 * 		"409": {
	 * 			"description": "Invalid data"
	 * 		}
	 * }
	 * @param  Request $request
	 * @return JsonResponse|JsonResource
	 */
	public function create(Request $request)
	{
		$rules = [
			'title' => 'required|string|max:48',
			'menutype' => 'required|string|max:24',
			'description' => 'nullable|string|max:255',
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return response()->json(['message' => $validator->messages()], 415);
		}

		$row = new Type();
		$row->title = $request->input('title');
		$row->menutype = $request->input('menutype');
		$row->description = $request->input('description');

		if (!$row->save())
		{
			return response()->json(['message' => trans('global.messages.save failed')], 500);
		}

		$row->api = route('api.menus.read', ['id' => $row->id]);
		$row->item_coutns = 0;
		$row->counts = [
			'published' => 0,
			'unpublished' => 0,
			'trashed' => 0,
		];

		return new JsonResource($row);
	}

	/**
	 * Retrieve an entry
	 *
	 * @apiMethod GET
	 * @apiUri    /menus/{id}
	 * @apiAuthorization  true
	 * @apiParameter {
	 * 		"in":            "path",
	 * 		"name":          "id",
	 * 		"description":   "Entry identifier",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiResponse {
	 * 		"200": {
	 * 			"description": "Successful entry read",
	 * 			"content": {
	 * 				"application/json": {
	 * 					"example": {
	 * 						"id": 2,
	 * 						"menutype": "about",
	 * 						"title": "About",
	 * 						"description": "About Side Menu",
	 * 						"client_id": 0,
	 * 						"created_at": null,
	 * 						"updated_at": null,
	 * 						"deleted_at": null,
	 * 						"items_count": 12,
	 * 						"counts": {
	 * 							"published": 0,
	 * 							"unpublished": 0,
	 * 							"trashed": 0
	 * 						},
	 * 						"api": "https://example.org/api/menus/2"
	 * 					}
	 * 				}
	 * 			}
	 * 		},
	 * 		"404": {
	 * 			"description": "Record not found"
	 * 		}
	 * }
	 * @param  int  $id
	 * @return JsonResource
	 */
	public function read(int $id)
	{
		$row = Type::findOrFail((int)$id);

		$row->api = route('api.menus.read', ['id' => $row->id]);
		$row->items_count = $row->items()->count();
		$row->counts = [
			'published' => number_format($row->countPublishedItems()),
			'unpublished' => number_format($row->countUnpublishedItems()),
			'trashed' => number_format($row->countTrashedItems()),
		];

		return new JsonResource($row);
	}

	/**
	 * Update an entry
	 *
	 * @apiMethod PUT
	 * @apiUri    /menus/{id}
	 * @apiAuthorization  true
	 * @apiParameter {
	 * 		"in":            "path",
	 * 		"name":          "id",
	 * 		"description":   "Entry identifier",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "title",
	 * 		"description":   "Menu title",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "description",
	 * 		"description":   "A description of the menu",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "client_id",
	 * 		"description":   "Client (admin = 1|site = 0) ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   0
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "menutype",
	 * 		"description":   "A short alias for the menu. If none provided, one will be generated from the title.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiResponse {
	 * 		"202": {
	 * 			"description": "Successful entry modification",
	 * 			"content": {
	 * 				"application/json": {
	 * 					"example": {
	 * 						"id": 2,
	 * 						"menutype": "about",
	 * 						"title": "About",
	 * 						"description": "About Side Menu",
	 * 						"client_id": 0,
	 * 						"created_at": null,
	 * 						"updated_at": null,
	 * 						"deleted_at": null,
	 * 						"items_count": 12,
	 * 						"counts": {
	 * 							"published": 0,
	 * 							"unpublished": 0,
	 * 							"trashed": 0
	 * 						},
	 * 						"api": "https://example.org/api/menus/2"
	 * 					}
	 * 				}
	 * 			}
	 * 		},
	 * 		"404": {
	 * 			"description": "Record not found"
	 * 		},
	 * 		"409": {
	 * 			"description": "Invalid data"
	 * 		}
	 * }
	 * @param   Request $request
	 * @param   int $id
	 * @return  JsonResponse|JsonResource
	 */
	public function update(Request $request, int $id)
	{
		$rules = [
			'title' => 'nullable|string|max:48',
			'menutype' => 'nullable|string|max:24',
			'description' => 'nullable|string|max:255',
			'ordering' => 'nullable|array',
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return response()->json(['message' => $validator->messages()], 415);
		}

		$row = Type::findOrFail($id);
		$row->title = $request->input('title', $row->title);
		$row->menutype = $request->input('menutype', $row->menutype);
		$row->description = $request->input('description', $row->description);

		if ($request->has('title')
		 || $request->has('menutype')
		 || $request->has('description'))
		{
			if (!$row->save())
			{
				return response()->json(['message' => trans('global.messages.save failed')], 500);
			}
		}

		if ($request->has('ordering'))
		{
			$order = $request->input('ordering', []);

			if (count($order))
			{
				$item = null;

				foreach ($order as $i => $it)
				{
					list($parent_id, $id) = explode(':', $it);

					$item = Item::find($id);
					if (!$item)
					{
						continue;
					}
					$item->parent_id = intval($parent_id);
					$item->ordering = $i;
					$item->save();
				}

				if ($item)
				{
					$root = Item::rootNode();
					$item->rebuild($root->id, 0, 0, '', 'ordering');
				}
			}
		}

		$row->api = route('api.menus.read', ['id' => $row->id]);
		$row->items_count = $row->items()->count();
		$row->counts = [
			'published' => number_format($row->countPublishedItems()),
			'unpublished' => number_format($row->countUnpublishedItems()),
			'trashed' => number_format($row->countTrashedItems()),
		];

		return new JsonResource($row);
	}

	/**
	 * Delete an entry
	 *
	 * @apiMethod DELETE
	 * @apiUri    /menus/{id}
	 * @apiAuthorization  true
	 * @apiParameter {
	 * 		"in":            "path",
	 * 		"name":          "id",
	 * 		"description":   "Entry identifier",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiResponse {
	 * 		"204": {
	 * 			"description": "Successful entry deletion"
	 * 		},
	 * 		"404": {
	 * 			"description": "Record not found"
	 * 		}
	 * }
	 * @param   int  $id
	 * @return  JsonResponse
	 */
	public function delete(int $id)
	{
		$row = Type::findOrFail($id);

		if (!$row->delete())
		{
			return response()->json(['message' => trans('global.messages.delete failed', ['id' => $id])], 500);
		}

		return response()->json(null, 204);
	}
}
