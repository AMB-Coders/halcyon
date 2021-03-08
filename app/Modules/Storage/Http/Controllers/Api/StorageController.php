<?php

namespace App\Modules\Storage\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Modules\Storage\Models\StorageResource;

class StorageController extends Controller
{
	/**
	 * Display a listing of the resource.
	 *
	 * @apiMethod GET
	 * @apiUri    /storage
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "state",
	 * 		"description":   "Record state.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"default":   "active",
	 * 			"enum": [
	 * 				"active",
	 * 				"inactive",
	 * 				"all"
	 * 			]
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "limit",
	 * 		"description":   "Number of result to return.",
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
	 * 		"in":            "query",
	 * 		"name":          "search",
	 * 		"description":   "A word or phrase to search for.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "order",
	 * 		"description":   "Field to sort results by.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"default":   "datetimecreated",
	 * 			"enum": [
	 * 				"id",
	 * 				"name",
	 * 				"datetimecreated",
	 * 				"datetimeremoved",
	 * 				"parentid"
	 * 			]
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "order_dir",
	 * 		"description":   "Direction to sort results by.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"default":   "desc",
	 * 			"enum": [
	 * 				"asc",
	 * 				"desc"
	 * 			]
	 * 		}
	 * }
	 * @return Response
	 */
	public function index(Request $request)
	{
		$filters = array(
			'search'   => $request->input('search', ''),
			'state'    => $request->input('state', 'active'),
			// Paging
			'limit'    => $request->input('limit', config('list_limit', 20)),
			'page'     => $request->input('page', 1),
			// Sorting
			'order'     => $request->input('order', 'name'),
			'order_dir' => $request->input('order_dir', 'asc')
		);

		// Get records
		$query = StorageResource::query()->withTrashed();

		if ($filters['state'] != 'all')
		{
			if ($filters['state'] == 'active')
			{
				$query->whereIsActive();
			}
			elseif ($filters['state'] == 'inactive')
			{
				$query->whereIsTrashed();
			}
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

		$rows = $query
			->withCount('directories')
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'], ['*'], 'page', $filters['page'])
			->appends($filters);

		$rows->each(function($item, $key)
		{
			$item->api = route('api.storage.read', ['id' => $item->id]);
		});

		return new ResourceCollection($rows);
	}

	/**
	 * Create a resource
	 *
	 * @apiMethod POST
	 * @apiUri    /storage
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "name",
	 * 		"description":   "The name of the storage resource",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"maxLength": 32
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "path",
	 * 		"description":   "The storage resource base path",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"maxLength": 255,
	 * 			"example":   "/scratch/foo"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "parentresourceid",
	 * 		"description":   "The parent resource's ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "import",
	 * 		"description":   "Import",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "boolean"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "importhostname",
	 * 		"description":   "Import hostname",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "boolean"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "autouserdir",
	 * 		"description":   "Auto create user directory",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "boolean"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "defaultquotaspace",
	 * 		"description":   "Default quota space in bytes",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "defaultquotafile",
	 * 		"description":   "Default number of files",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "getquotatypeid",
	 * 		"description":   "Get Quota message type ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "createtypeid",
	 * 		"description":   "Create Quota message type ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @param  Request  $request
	 * @return Response
	 */
	public function create(Request $request)
	{
		$request->validate([
			'name' => 'required|string|max:32',
			'path' => 'required|string|max:255',
			'parentresourceid' => 'nullable|integer',
			'import' => 'nullable|integer',
			'importhostname' => 'nullable|in:0,1',
			'autouserdir' => 'nullable|in:0,1',
			'defaultquotaspace' => 'nullable|integer',
			'defaultquotafile' => 'nullable|integer',
			'getquotatypeid' => 'nullable|integer',
			'createtypeid' => 'nullable|integer',
		]);

		$row = new StorageResource;
		$row->fill($data);

		// Make sure name is sane
		if (!preg_match("/^([a-zA-Z0-9]+\.?[\-_ ]*)*[a-zA-Z0-9]$/", $row->name))
		{
			return response()->json(['message' => trans('Field `name` has invalid format')], 415);
		}

		if ($row->parentresourceid)
		{
			if (!$row->resource)
			{
				return response()->json(['message' => trans('Invalid `parentresourceid`')], 415);
			}
		}

		if ($row->getquotatypeid)
		{
			if (!$row->quotaType)
			{
				return response()->json(['message' => trans('Invalid `getquotatypeid`')], 415);
			}
		}

		if ($row->createtypeid)
		{
			if (!$row->createType)
			{
				return response()->json(['message' => trans('Invalid `createtypeid`')], 415);
			}
		}

		$row->save();
		$row->directories_count = $row->directories()->count();
		$row->api = route('api.storage.read', ['id' => $row->id]);

		return new JsonResource($row);
	}

	/**
	 * Read a resource
	 *
	 * @apiMethod POST
	 * @apiUri    /storage/{id}
	 * @apiParameter {
	 * 		"in":            "path",
	 * 		"name":          "id",
	 * 		"description":   "Entry identifier",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @return  Response
	 */
	public function read($id)
	{
		$row = StorageResource::findOrFail($id);
		$row->directories_count = $row->directories()->count();
		$row->api = route('api.storage.read', ['id' => $row->id]);

		return new JsonResource($row);
	}

	/**
	 * Update a resource
	 *
	 * @apiMethod PUT
	 * @apiUri    /storage/{id}
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
	 * 		"name":          "name",
	 * 		"description":   "The name of the storage resource",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"maxLength": 32
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "path",
	 * 		"description":   "The storage resource base path",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"maxLength": 255,
	 * 			"example":   "/scratch/foo"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "parentresourceid",
	 * 		"description":   "The parent resource's ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "import",
	 * 		"description":   "Import",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "boolean"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "importhostname",
	 * 		"description":   "Import hostname",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "boolean"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "autouserdir",
	 * 		"description":   "Auto create user directory",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "boolean"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "defaultquotaspace",
	 * 		"description":   "Default quota space in bytes",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "defaultquotafile",
	 * 		"description":   "Default number of files",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "getquotatypeid",
	 * 		"description":   "Get Quota message type ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "createtypeid",
	 * 		"description":   "Create Quota message type ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @param   integer  $id
	 * @param   Request  $request
	 * @return  Response
	 */
	public function update($id, Request $request)
	{
		$row = StorageResource::findOrFail($id);

		$request->validate([
			'name' => 'nullable|string|max:32',
			'path' => 'nullable|string|max:255',
			'parentresourceid' => 'nullable|integer',
			'import' => 'nullable|integer',
			'importhostname' => 'nullable|in:0,1',
			'autouserdir' => 'nullable|in:0,1',
			'defaultquotaspace' => 'nullable|integer',
			'defaultquotafile' => 'nullable|integer',
			'getquotatypeid' => 'nullable|integer',
			'createtypeid' => 'nullable|integer',
		]);

		$row->fill($request->all());

		if ($request->has('parentresourceid'))
		{
			if (!$row->resource)
			{
				return response()->json(['message' => trans('Invalid `parentresourceid`')], 415);
			}
		}

		if ($request->has('getquotatypeid'))
		{
			if (!$row->quotaType)
			{
				return response()->json(['message' => trans('Invalid `getquotatypeid`')], 415);
			}
		}

		if ($request->has('createtypeid'))
		{
			if (!$row->createType)
			{
				return response()->json(['message' => trans('Invalid `createtypeid`')], 415);
			}
		}

		$row->save();
		$row->directories_count = $row->directories()->count();
		$row->api = route('api.storage.read', ['id' => $row->id]);

		return new JsonResource($row);
	}

	/**
	 * Delete a storage directory
	 *
	 * @apiMethod DELETE
	 * @apiUri    /storage/{id}
	 * @apiParameter {
	 * 		"in":            "path",
	 * 		"name":          "id",
	 * 		"description":   "Entry identifier",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @param   integer  $id
	 * @return  Response
	 */
	public function delete($id)
	{
		$row = Directory::findOrFail($id);

		if ($row->directories()->count() > 0)
		{
			return response()->json(['message' => trans('Storage Resource is not empty')], 409);
		}

		if (!$row->delete())
		{
			return response()->json(['message' => trans('global.messages.delete failed', ['id' => $id])], 500);
		}

		return response()->json(null, 204);
	}
}
