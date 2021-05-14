<?php

namespace App\Modules\Resources\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Modules\Resources\Models\Asset;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Modules\Resources\Events\ResourceMemberStatus;
use App\Modules\Resources\Events\ResourceMemberCreated;
use App\Modules\Resources\Events\ResourceMemberDeleted;
use App\Modules\Users\Models\User;

/**
 * Members
 *
 * @apiUri    /api/resources/members
 */
class MembersController extends Controller
{
	/**
	 * Create a resource
	 *
	 * @apiMethod POST
	 * @apiUri    /api/resources/members
	 * @apiAuthorization  true
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "user",
	 * 		"description":   "User ID",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "resource",
	 * 		"description":   "Resource ID",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "loginshell",
	 * 		"description":   "Login shell",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "pilogin",
	 * 		"description":   "PI's username",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "piid",
	 * 		"description":   "PI's user ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @param  Request $request
	 * @return Response
	 */
	public function create(Request $request)
	{
		$request->validate([
			'user' => 'required|integer',
			'resource' => 'required|integer',
			'primarygroup' => 'nullable|string',
			'loginshell' => 'nullable|string',
			'pilogin' => 'nullable|string',
			'piid' => 'nullable|integer',
		]);

		$userid = $request->input('user');
		$resourceid = $request->input('resource');
		$loginshell = $request->input('loginshell');

		// Look up the current username of the user
		$user = User::findOrFail($userid);

		if (!$user || $user->isTrashed())
		{
			return response()->json(['message' => trans('Failed to find user for ID :id', ['id' => $userid])], 404);
		}

		$asset = Asset::findOrFail($resourceid);

		if (!$asset || $asset->isTrashed())
		{
			return response()->json(['message' => trans('Failed to find resource for ID :id', ['id' => $resourceid])], 404);
		}

		// Ensure the client is authorized to manage a group with queues on the resource in question.
		if (!auth()->user()->can('manage resources')
		 && $user->id != auth()->user()->id)
		{
			$owned = auth()->user()->groups->pluck('id')->toArray();

			$queues = array();
			foreach ($resource->subresources as $sub)
			{
				$queues += $sub->queues()
					->whereIn('groupid', $owned)
					->pluck('queuid')
					->toArray();
			}
			array_filter($queues);

			// If no queues found
			if (count($queues) < 1) // && !in_array($resource->id, array(48, 2, 12, 66)))
			{
				return response()->json(null, 403);
			}
		}

		// Is the shell valid?
		if (!file_exists($loginshell) && $loginshell != 'nologin')
		{
			return response()->json(['message' => trans('Invalid loginshell')], 409);
		}

		// Look up the current username of the PI if ID was specified
		if ($piid = $request->input('piid'))
		{
			$pi = User::findOrFail($piid);

			$pilogin = $pi->username;
		}
		// Verify PI login is valid if that was specified
		elseif ($pilogin = $request->input('pilogin'))
		{
			$pi = User::findByUsername($pilogin);

			if (!$pi)
			{
				return response()->json(['message' => trans('Invalid pilogin')], 409);
			}
		}

		event($event = new ResourceMemberCreated($asset, $user));

		$data = array(
			'resource' => array(
				'id'   => $asset->id,
				'name' => $asset->name,
			),
			'user' => array(
				'id'   => $user->id,
				'name' => $user->name
			),
			'status'       => $event->status,
			'loginshell'   => $event->user->loginshell,
			'primarygroup' => $event->user->primarygroup,
			'pilogin'      => $event->user->pilogin,
			'api'          => route('api.resources.members.read', $asset->id . '.' . $user->id)
		);

		return new JsonResource($data);
	}

	/**
	 * Read a resource
	 *
	 * @apiMethod GET
	 * @apiUri    /api/resources/members/{user id}.{resource id}
	 * @apiAuthorization  true
	 * @apiParameter {
	 * 		"in":            "path",
	 * 		"name":          "user id.resource id",
	 * 		"description":   "User ID and Resource ID separated by a period",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"example":   "12345.67"
	 * 		}
	 * }
	 * @param   integer $id
	 * @return  Response
	 */
	public function read($id)
	{
		// Split id into parts
		$parts = explode('.', $id);

		$resource = $parts[0];
		$userid = $parts[1];

		if (!is_numeric($resource)
		 || !is_numeric($userid))
		{
			return response()->json(['message' => trans('Field resource or user is not numeric')], 415);
		}

		// Ensure the client is authorized to manage roles
		if (!auth()->user()->can('manage resources')
		 && $userid != auth()->user()->id)
		{
			return response()->json(null, 403);
		}

		// Look up the current user
		$user = User::findOrFail($userid);

		if (!$user || $user->isTrashed())
		{
			return response()->json(['message' => trans('Failed to find user for ID :id', ['id' => $userid])], 404);
		}

		// Look up the current resource
		$asset = Asset::findOrFail($resource);

		if (!$asset || $asset->isTrashed())
		{
			return response()->json(['message' => trans('Failed to find resource for ID :id', ['id' => $resource])], 404);
		}

		// Look up the ACMaint role name of the resource
		if (!$asset->rolename)
		{
			return response()->json(null, 404);
		}

		// Call central accounting service to request status
		event($event = new ResourceMemberStatus($asset, $user));

		$data = array(
			'resource' => array(
				'id'   => $asset->id,
				'name' => $asset->name,
			),
			'user' => array(
				'id'   => $user->id,
				'name' => $user->name
			),
			'status'       => $event->status,
			'loginshell'   => $event->user->loginshell,
			'primarygroup' => $event->user->primarygroup,
			'pilogin'      => $event->user->pilogin,
			'api'          => route('api.resources.members.read', $id)
		);

		return new JsonResource($data);
	}

	/**
	 * Delete a resource
	 *
	 * @apiMethod DELETE
	 * @apiUri    /api/resources/members/{user id}.{resource id}
	 * @apiAuthorization  true
	 * @apiParameter {
	 * 		"in":            "path",
	 * 		"name":          "user id.resource id",
	 * 		"description":   "User ID and Resource ID separated by a period",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"example":   "12345.67"
	 * 		}
	 * }
	 * @param  string $id
	 * @return Response
	 */
	public function delete($id)
	{
		$parts = explode('.', $id);

		if (count($parts) != 2)
		{
			return response()->json(['message' => trans('Missing or invalid value. Must be of format `resourceid.userid`')], 412);
		}

		$resourceid = $parts[0];
		$userid = $parts[1];

		if (!is_numeric($resourceid)
		 || !is_numeric($userid))
		{
			return response()->json(['message' => trans('Missing or invalid value. Must be of format `resourceid.userid`')], 415);
		}

		// Look up the current username of the user being removed
		$user = User::findOrFail($userid);

		// Look up the ACMaint role name of the resource to which access is being granted.
		$resource = Asset::findOrFail($resourceid);

		// Ensure the client is authorized to manage a group with queues on the resource in question.
		if (!auth()->user()->can('manage resources')
		 && $user->id != auth()->user()->id)
		{
			$owned = auth()->user()->groups->pluck('id')->toArray();

			$queues = array();
			$subresources = $resource->subresources()
				->where(function($where)
				{
					$where->whereNull('datetimeremoved')
						->orWhere('datetimeremoved', '=', '0000-00-00 00:00:00');
				})
				->get();
			foreach ($subresources as $sub)
			{
				$queues += $sub->queues()
					->whereIn('groupid', $owned)
					->where(function($where)
					{
						$where->whereNull('datetimeremoved')
							->orWhere('datetimeremoved', '=', '0000-00-00 00:00:00');
					})
					->pluck('queuid')
					->toArray();
			}
			array_filter($queues);

			// If no queues found
			if (count($queues) < 1) // && !in_array($resource->id, array(48, 2, 12, 66)))
			{
				return response()->json(null, 403);
			}
		}

		// Check for other queue memberships on this resource that might conflict with removing the role
		$rows = 0;

		$resources = Asset::query()
			->withTrashed()
			->whereIsActive()
			->where('rolename', '!=', '')
			->where('listname', '!=', '')
			->get();

		foreach ($resources as $res)
		{
			$subresources = $res->subresources()
				->where(function($where)
				{
					$where->whereNull('datetimeremoved')
						->orWhere('datetimeremoved', '=', '0000-00-00 00:00:00');
				})
				->get();

			foreach ($subresources as $sub)
			{
				$queues = $sub->queues()
					->whereIn('groupid', $owned)
					->where(function($where)
					{
						$where->whereNull('datetimeremoved')
							->orWhere('datetimeremoved', '=', '0000-00-00 00:00:00');
					})
					->get();
					//->pluck('queuid')
					//->toArray();

				foreach ($queues as $queue)
				{
					$rows += $queue->users()
						->whereIsMember()
						->where('userid', '=', $user->id)
						->count();

					$rows += $queue->group->members()
						->whereIsManager()
						->where('userid', '=', $user->id)
						->count();
				}
			}
		}

		if ($rows > 0)
		{
			return 202;
		}

		// Call central accounting service to remove ACMaint role from this user's account.
		event(new ResourceMemberDeleted($resource, $user));

		return response()->json(null, 204);
	}
}
