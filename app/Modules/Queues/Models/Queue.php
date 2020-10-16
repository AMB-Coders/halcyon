<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace App\Modules\Queues\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Halcyon\Traits\ErrorBag;
use App\Halcyon\Traits\Validatable;
use App\Halcyon\Models\Casts\Bytesize;
use App\Modules\History\Traits\Historable;
use App\Modules\Queues\Events\QueueCreating;
use App\Modules\Queues\Events\QueueCreated;
use App\Modules\Queues\Events\QueueUpdating;
use App\Modules\Queues\Events\QueueUpdated;
use App\Modules\Queues\Events\QueueDeleted;
use App\Modules\Resources\Entities\Subresource;
use App\Modules\Resources\Entities\Child;
use App\Modules\Resources\Entities\Asset;
use App\Modules\Groups\Models\Group;
use Carbon\Carbon;

/**
 * Queue queue
 */
class Queue extends Model
{
	use ErrorBag, Validatable, Historable, SoftDeletes;

	/**
	 * The name of the "created at" column.
	 *
	 * @var string
	 */
	const CREATED_AT = 'datetimecreated';

	/**
	 * The name of the "updated at" column.
	 *
	 * @var  string
	 */
	const UPDATED_AT = null;

	/**
	 * The name of the "deleted at" column.
	 *
	 * @var  string
	 */
	const DELETED_AT = 'datetimeremoved';

	/**
	 * The table to which the class pertains
	 *
	 * This will default to #__{namespace}_{modelName} unless otherwise
	 * overwritten by a given subclass. Definition of this property likely
	 * indicates some derivation from standard naming conventions.
	 *
	 * @var  string
	 **/
	protected $table = 'queues';

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
	 * The attributes that should be mutated to dates.
	 *
	 * @var array
	 */
	protected $casts = [
		'nodememmin' => Bytesize::class,
		'nodememmax' => Bytesize::class,
	];

	/**
	 * The model's default values for attributes.
	 *
	 * @var array
	 */
	protected $attributes = [
		'groupid' => 0,
		'nodecoresmin' => 0,
		'nodecoresmax' => 0,
	];

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $guarded = [
		'id'
	];

	/**
	 * The event map for the model.
	 *
	 * @var array
	 */
	protected $dispatchesEvents = [
		'creating' => QueueCreating::class,
		'created'  => QueueCreated::class,
		'updating' => QueueUpdating::class,
		'updated'  => QueueUpdated::class,
		'deleted'  => QueueDeleted::class,
	];

	/**
	 * Determine if in a trashed state
	 *
	 * @return  bool
	 */
	public function isTrashed()
	{
		return ($this->datetimeremoved && $this->datetimeremoved != '0000-00-00 00:00:00' && $this->datetimeremoved != '-0001-11-30 00:00:00');
	}

	/**
	 * Set defaultwalltime. Incoming value is expected to be # hours.
	 *
	 * @param   integer  $value
	 * @return  void
	 */
	public function setDefaultwalltimeAttribute($value)
	{
		$this->attributes['defaultwalltime'] = $value * 60 * 60;
	}

	/**
	 * Set maxwalltime. Incoming value is expected to be # hours.
	 *
	 * @param   integer  $value
	 * @return  void
	 */
	public function setMaxwalltimeAttribute($value)
	{
		$this->attributes['maxwalltime'] = $value * 60 * 60;
	}

	/**
	 * Set nodecoresmin.
	 *
	 * @param   integer  $value
	 * @return  void
	 */
	public function setNodecoresminAttribute($value)
	{
		if (!is_numeric($value) || $value < 0)
		{
			$value = 0;
		}

		$this->attributes['nodecoresmin'] = $value;
	}

	/**
	 * Set nodecoresmax.
	 *
	 * @param   integer  $value
	 * @return  void
	 */
	public function setNodecoresmaxAttribute($value)
	{
		if (!is_numeric($value) || $value < 0)
		{
			$value = 0;
		}

		$this->attributes['nodecoresmax'] = $value;
	}

	/**
	 * Set nodememmin. Value is expected to be numeric followed by letter. Ex: 64G
	 *
	 * @param   string  $value
	 * @return  void
	 */
	/*public function setNodememminAttribute($value)
	{
		if (!preg_match('/^[0-9]+[BKMGTP]$/', $value))
		{
			$value = 0;
		}

		$this->attributes['nodememmin'] = $value;
	}*/

	/**
	 * Set nodememmax. Value is expected to be numeric followed by letter. Ex: 64G
	 *
	 * @param   string  $value
	 * @return  void
	 */
	/*public function setNodememmaxAttribute($value)
	{
		if (!preg_match('/^[0-9]+[BKMGTP]$/', $value))
		{
			$value = 0;
		}

		$this->attributes['nodememmax'] = $value;
	}*/

	/**
	 * Defines a relationship to type
	 *
	 * @return  object
	 */
	public function type()
	{
		return $this->belongsTo(Type::class, 'queuetype')->withDefault(['id' => 0, 'name' => trans('global.none')]);
	}

	/**
	 * Defines a relationship to subqueues
	 *
	 * @return  object
	 */
	public function group()
	{
		return $this->belongsTo(Group::class, 'groupid');
	}

	/**
	 * Defines a relationship to subqueues
	 *
	 * @return  object
	 */
	public function scheduler()
	{
		return $this->belongsTo(Scheduler::class, 'schedulerid');
	}

	/**
	 * Defines a relationship to subqueues
	 *
	 * @return  object
	 */
	public function schedulerPolicy()
	{
		return $this->belongsTo(SchedulerPolicy::class, 'schedulerpolicyid');
	}

	/**
	 * Defines a relationship to subresource
	 *
	 * @return  object
	 */
	public function subresource()
	{
		return $this->belongsTo(Subresource::class, 'subresourceid')->withTrashed();
		//return $this->hasOneThrough(Subresource::class, Child::class, 'subresourceid', 'id', 'subresourceid', 'subresourceid');
	}

	/**
	 * Get the resource
	 */
	public function resource()
	{
		return $this->hasOneThrough(Asset::class, Child::class, 'subresourceid', 'id', 'subresourceid', 'resourceid')->withTrashed();
	}

	/**
	 * Defines a relationship to sizes
	 *
	 * @return  object
	 */
	public function sizes()
	{
		return $this->hasMany(Size::class, 'queueid');
	}

	/**
	 * Defines a relationship to sizes
	 *
	 * @return  object
	 */
	public function loans()
	{
		return $this->hasMany(Loan::class, 'queueid');
	}

	/**
	 * Defines a relationship to sizes
	 *
	 * @return  object
	 */
	public function users()
	{
		return $this->hasMany(User::class, 'queueid');
	}

	/**
	 * Defines a relationship to walltimes
	 *
	 * @return  object
	 */
	public function walltimes()
	{
		return $this->hasMany(Walltime::class, 'queueid');
	}

	/**
	 * Get active
	 *
	 * @return  integer
	 */
	public function getActiveAttribute()
	{
		if (!array_key_exists('active', $this->attributes))
		{
			$this->sumCoresAndNodes();
		}

		return $this->attributes['active'];
	}

	/**
	 * Calculate total cores and nodes
	 *
	 * @return  void
	 */
	private function sumCoresAndNodes()
	{
		$nodecores = $this->subresource->nodecores;
		$active = 0;

		$totalcores  = 0;
		$totalnodes  = 0;
		$soldcores   = 0;
		$soldnodes   = 0;
		$loanedcores = 0;
		$loanednodes = 0;

		$now = Carbon::now();

		$sizes = $this->sizes()
			->where(function($where) use ($now)
			{
				$where->whereNull('datetimestop')
					->orWhere('datetimestop', '=', '0000-00-00 00:00:00')
					->orWhere('datetimestop', '>', $now->toDateTimeString());
			})
			->where('datetimestart', '<=', $now->toDateTimeString())
			->get();

		foreach ($sizes as $size)
		{
			$soldcores += (int) $size->corecount;

			if ($nodecores != 0)
			{
				$soldnodes += round($size->corecount / $nodecores, 1);
			}
			else
			{
				$soldnodes += $size->nodecount;
			}

			if ($size->corecount == 0)
			{
				$active = 1;
			}
		}

		$loans = $this->loans()
			->where(function($where) use ($now)
			{
				$where->whereNull('datetimestop')
					->orWhere('datetimestop', '=', '0000-00-00 00:00:00')
					->orWhere('datetimestop', '>', $now->toDateTimeString());
			})
			->where('datetimestart', '<=', $now->toDateTimeString())
			->get();

		foreach ($loans as $loan)
		{
			$loanedcores += (int) $loan->corecount;

			if ($nodecores != 0)
			{
				$loanednodes += round($loan->corecount / $nodecores, 1);
			}
			else
			{
				$loanednodes += $loan->nodecount;
			}

			if ($loan->corecount == 0)
			{
				$active = 1;
			}
		}

		$totalcores = $soldcores + $loanedcores;
		$totalnodes = $soldnodes + $loanednodes;

		// If we didn't get marked active by zero-core entry, set active if we have active noses
		if ($active == 0 && $totalcores > 0)
		{
			$active = 1;
		}

		$this->setAttribute('active', $active);

		$this->setAttribute('totalcores', $totalcores);
		$this->setAttribute('totalnodes', $totalnodes);
		$this->setAttribute('soldcores', $soldcores);
		$this->setAttribute('soldnodes', $soldnodes);
		$this->setAttribute('loanedcores', $loanedcores);
		$this->setAttribute('loanednodes', $loanednodes);
	}

	/**
	 * Get total cores
	 *
	 * @return  integer
	 */
	public function getTotalcoresAttribute()
	{
		if (!array_key_exists('totalcores', $this->attributes))
		{
			$this->sumCoresAndNodes();
		}

		return $this->attributes['totalcores'];
	}

	/**
	 * Get total nodes
	 *
	 * @return  integer
	 */
	public function getTotalnodesAttribute()
	{
		if (!array_key_exists('totalnodes', $this->attributes))
		{
			$this->sumCoresAndNodes();
		}

		return $this->attributes['totalnodes'];
	}

	/**
	 * Get sold cores
	 *
	 * @return  integer
	 */
	public function getSoldcoresAttribute()
	{
		if (!array_key_exists('soldcores', $this->attributes))
		{
			$this->sumCoresAndNodes();
		}

		return $this->attributes['soldcores'];
	}

	/**
	 * Get sold nodes
	 *
	 * @return  integer
	 */
	public function getSoldnodesAttribute()
	{
		if (!array_key_exists('soldnodes', $this->attributes))
		{
			$this->sumCoresAndNodes();
		}

		return $this->attributes['soldnodes'];
	}

	/**
	 * Get loaned cores
	 *
	 * @return  integer
	 */
	public function getLoanedcoresAttribute()
	{
		if (!array_key_exists('loanedcores', $this->attributes))
		{
			$this->sumCoresAndNodes();
		}

		return $this->attributes['loanedcores'];
	}

	/**
	 * Get loaned nodes
	 *
	 * @return  integer
	 */
	public function getLoanednodesAttribute()
	{
		if (!array_key_exists('loanednodes', $this->attributes))
		{
			$this->sumCoresAndNodes();
		}

		return $this->attributes['loanednodes'];
	}
}
