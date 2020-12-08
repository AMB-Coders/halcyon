<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace App\Modules\Groups\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Halcyon\Traits\ErrorBag;
use App\Halcyon\Traits\Validatable;
use App\Modules\Messages\Models\Message;
use App\Modules\History\Traits\Historable;
use App\Modules\Storage\Models\Directory;
use App\Modules\Storage\Models\Loan;
use App\Modules\Storage\Models\Purchase;
use App\Modules\Queues\Models\Queue;
use App\Modules\Groups\Events\GroupCreating;
use App\Modules\Groups\Events\GroupCreated;
use App\Modules\Groups\Events\GroupUpdating;
use App\Modules\Groups\Events\GroupUpdated;
use App\Modules\Groups\Events\GroupDeleted;
use Carbon\Carbon;

/**
 * Group model
 */
class Group extends Model
{
	use ErrorBag, Validatable, Historable;

	public $timestamps = false;

	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'groups';

	/**
	 * Default order by for model
	 *
	 * @var string
	 */
	public static $orderBy = 'name';

	/**
	 * Default order direction for select queries
	 *
	 * @var  string
	 */
	public static $orderDir = 'asc';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $guarded = [
		'id'
	];

	/**
	 * Fields and their validation criteria
	 *
	 * @var  array
	 */
	protected $rules = array(
		'name' => 'required'
	);

	/**
	 * The event map for the model.
	 *
	 * @var array
	 */
	protected $dispatchesEvents = [
		'creating' => GroupCreating::class,
		'created'  => GroupCreated::class,
		'updating' => GroupUpdating::class,
		'updated'  => GroupUpdated::class,
		'deleted'  => GroupDeleted::class,
	];

	/**
	 * Owner
	 *
	 * @return  object
	 */
	public function owner()
	{
		return $this->belongsTo('App\Modules\Users\Models\User', 'owneruserid');
	}

	/**
	 * Determine if a user is a manager
	 *
	 * @return  boolean  True if modified, false if not
	 */
	public function isManager($user)
	{
		$managers = $this->managers->pluck('userid')->toArray();
		return in_array($user->id, $managers);
	}

	/**
	 * Department
	 *
	 * @return  object
	 */
	public function departments()
	{
		return $this->hasMany(GroupDepartment::class, 'groupid');
		//return $this->hasOneThrough(Department::class, GroupDepartment::class, 'groupid', 'id', 'groupid', 'collegedeptid');
	}

	/**
	 * Fields of science
	 *
	 * @return  object
	 */
	/*public function fieldsOfScience()
	{
		return $this->hasMany(GroupFieldOfScience::class, 'groupid');
	}*/

	/**
	 * Department
	 *
	 * @return  object
	 */
	public function departmentList()
	{
		return $this->hasManyThrough(Department::class, GroupDepartment::class, 'groupid', 'id', 'id', 'collegedeptid');
	}

	/**
	 * Get a list of users
	 *
	 * @return  object
	 */
	public function members()
	{
		return $this->hasMany(Member::class, 'groupid');
	}

	/**
	 * Get a list of managers
	 *
	 * @return  object
	 */
	public function getManagersAttribute()
	{
		$m = (new Member)->getTable();
		$u = (new \App\Modules\Users\Models\UserUsername)->getTable();

		$managers = $this->members()
			->select($m . '.*')
			->join($u, $u . '.id', $m . '.userid')
			->where(function($where) use ($u)
			{
				$where->whereNull($u . '.dateremoved')
					->orWhere($u . '.dateremoved', '=', '0000-00-00 00:00:00');
			})
			->where($m . '.membertype', '=', 2)
			->orderBy($m . '.datecreated', 'desc')
			->get();

		return $managers;
	}

	/**
	 * Get a list of "message of the day"
	 *
	 * @return  object
	 */
	public function motds()
	{
		return $this->hasMany(Motd::class, 'groupid');
	}

	/**
	 * Get a list of storage directories
	 *
	 * @return  object
	 */
	public function directories()
	{
		return $this->hasMany(Directory::class, 'groupid');
	}

	/**
	 * Get a list of messages
	 *
	 * @return  object
	 */
	public function getMessagesAttribute()
	{
		$ids = $this->directories->pluck('id')->toArray();

		return Message::query()->whereIn('targetobjectid', $ids);
	}

	/**
	 * Get a list of messages
	 *
	 * @return  object
	 */
	public function getStorageBucketsAttribute()
	{
		$allocated = array();
		$now = Carbon::now();

		// Fetch allocated amounts
		$data = Directory::query()
			->select(DB::raw('SUM(bytes) AS allocated', 'resourceid'))
			->where('groupid', '=', $this->id)
			->where(function($where) use ($now)
			{
				$where->whereNull('datetimecreated')
					->orWhere('datetimecreated', '=', '0000-00-00 00:00:00')
					->orWhere('datetimecreated', '<', $now->toDateTimeString());
			})
			->where(function($where) use ($now)
			{
				$where->whereNull('datetimeremoved')
					->orWhere('datetimeremoved', '=', '0000-00-00 00:00:00')
					->orWhere('datetimeremoved', '>', $now->toDateTimeString());
			})
			->get();

		foreach ($data as $row)
		{
			$allocated[$row->resourceid] = $row->allocated;
		}

		// Fetch storage buckets under this group
		$storagebuckets = array();

		$data = Purchase::query()
			->select(DB::raw('SUM(bytes) AS soldbytes'), 'resourceid')
			->where('groupid', '=', $this->id)
			->where(function($where) use ($now)
			{
				$where->whereNull('datetimestop')
					->orWhere('datetimestop', '=', '0000-00-00 00:00:00')
					->orWhere('datetimestop', '>', $now->toDateTimeString());
			})
			->where(function($where) use ($now)
			{
				$where->whereNull('datetimestart')
					->orWhere('datetimestart', '=', '0000-00-00 00:00:00')
					->orWhere('datetimestart', '<', $now->toDateTimeString());
			})
			->groupBy('resourceid')
			->get();

		foreach ($data as $row)
		{
			array_push($storagebuckets, array(
				'resourceid'  => $row->resourceid,
				'soldbytes'   => $row->soldbytes,
				'loanedbytes' => 0,
				'totalbytes'  => $row->soldbytes,
				'unallocatedbytes' => 0,
			));
		}

		$data = array();

		$data = Loan::query()
			->select(DB::raw('SUM(bytes) AS loanedbytes'), 'resourceid')
			->where('groupid', '=', $this->id)
			->where(function($where) use ($now)
			{
				$where->whereNull('datetimestop')
					->orWhere('datetimestop', '=', '0000-00-00 00:00:00')
					->orWhere('datetimestop', '>', $now->toDateTimeString());
			})
			->where(function($where) use ($now)
			{
				$where->whereNull('datetimestart')
					->orWhere('datetimestart', '=', '0000-00-00 00:00:00')
					->orWhere('datetimestart', '<', $now->toDateTimeString());
			})
			->groupBy('resourceid')
			->get();

		foreach ($data as $row)
		{
			$found = false;
			foreach ($storagebuckets as $bucket)
			{
				if ($bucket['resourceid'] == $row->resourceid)
				{
					$bucket['loanedbytes'] = $row->loanedbytes;
					$bucket['totalbytes'] += $row->loanedbytes;
					$found = true;
					break;
				}
			}

			if (!$found)
			{
				// TODO: calculate remainder quota
				array_push($storagebuckets, array(
					'resourceid'       => $row->resourceid,
					'soldbytes'        => 0,
					'unallocatedbytes' => 0,
					'loanedbytes'      => $row->loanedbytes,
					'totalbytes'       => $row->loanedbytes,
				));
			}
		}

		foreach ($storagebuckets as $bucket)
		{
			if (!isset($allocated[$bucket['resourceid']]))
			{
				$allocated[$bucket['resourceid']] = 0;
			}

			$b = Directory::query()
				->select(DB::raw('SUM(bytes)'))
				->where('groupid', '=', $this->id)
				->where('resourceid', '=', $bucket['resourceid'])
				->where(function($where)
				{
					$where->whereNull('datetimeremoved')
						->orWhere('datetimeremoved', '=', '0000-00-00 00:00:00');
				})
				->get()
				->first();

			$allocatedbytes = 0;

			if ($b)
			{
				$allocatedbytes = $b->bytes;
			}

			$bucket['unallocatedbytes'] = ($bucket['totalbytes'] - $allocated[$bucket['resourceid']]);
			$bucket['allocatedbytes'] = $allocatedbytes;
		}

		return $storagebuckets;
	}

	/**
	 * Get a list of storage loans
	 *
	 * @return  object
	 */
	public function loans()
	{
		return $this->hasMany(Loan::class, 'groupid');
	}

	/**
	 * Get a list of storage purchases
	 *
	 * @return  object
	 */
	public function purchases()
	{
		return $this->hasMany(Purchase::class, 'groupid');
	}

	/**
	 * Get a list of queues
	 *
	 * @return  object
	 */
	public function queues()
	{
		return $this->hasMany(Queue::class, 'groupid');
	}

	/**
	 * Get a list of storage loans
	 *
	 * @return  object
	 */
	public function fieldsOfScience()
	{
		return $this->hasMany(FieldOfScience::class, 'groupid');
	}

	/**
	 * Get a list of unix groups
	 *
	 * @return  object
	 */
	public function unixGroups()
	{
		return $this->hasMany(UnixGroup::class, 'groupid');
	}

	/**
	 * Get a list of "message of the day"
	 *
	 * @return  object
	 */
	public function getMotdAttribute()
	{
		return $this->motds()
			->whereNull('datetimeremoved')
			->orderBy('datetimecreated', 'desc')
			->first();
	}

	/**
	 * Get a list of resources
	 *
	 * @return  object
	 */
	public function getResourcesAttribute()
	{
		$resources = [];

		foreach ($this->queues as $queue)
		{
			if (!$queue->resource)
			{
				continue;
			}

			//if ($queue->resource->trashed())
			if ($queue->resource->datetimeremoved
			 && $queue->resource->datetimeremoved != '0000-00-00 00:00:00'
			 && $queue->resource->datetimeremoved != '-0001-11-30 00:00:00')
			{
				continue;
			}

			if (!isset($resources[$queue->resource->id]))
			{
				$resources[$queue->resource->id] = $queue->resource;
			}
		}

		return collect(array_values($resources));
	}

	/**
	 * Get a list of resources
	 *
	 * @return  object
	 */
	public function getPriorResourcesAttribute()
	{
		$resources = [];

		foreach ($this->queues as $queue)
		{
			if (!$queue->resource)
			{
				continue;
			}

			if (!$queue->resource->datetimeremoved
			 || $queue->resource->datetimeremoved == '0000-00-00 00:00:00'
			 || $queue->resource->datetimeremoved == '-0001-11-30 00:00:00')
			{
				continue;
			}

			if (!isset($resources[$queue->resource->id]))
			{
				$resources[$queue->resource->id] = $queue->resource;
			}
		}

		return collect(array_values($resources));
	}

	/**
	 * Get a list of "message of the day"
	 *
	 * @param   string  $name
	 * @return  object
	 */
	public static function findByName($name)
	{
		return self::query()
			->where('name', '=', $name)
			->first();
	}

	/**
	 * Get a list of "message of the day"
	 *
	 * @param   string  $unixgroup
	 * @return  object
	 */
	public static function findByUnixgroup($unixgroup)
	{
		return self::query()
			->where('unixgroup', '=', $unixgroup)
			->first();
	}

	/**
	 * Delete entry and associated data
	 *
	 * @param   array  $options
	 * @return  bool
	 */
	public function delete(array $options = [])
	{
		foreach ($this->members as $row)
		{
			$row->delete();
		}

		foreach ($this->motds as $row)
		{
			$row->delete();
		}

		foreach ($this->queues as $row)
		{
			$row->delete();
		}

		foreach ($this->directories as $row)
		{
			$row->delete();
		}

		foreach ($this->fieldsOfScience as $row)
		{
			$row->delete();
		}

		foreach ($this->unixGroups as $row)
		{
			$row->delete();
		}

		return parent::delete($options);
	}
}
