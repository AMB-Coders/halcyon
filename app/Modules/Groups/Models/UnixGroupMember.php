<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace App\Modules\Groups\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Halcyon\Traits\ErrorBag;
use App\Halcyon\Traits\Validatable;
use App\Modules\History\Traits\Historable;
use App\Modules\Groups\Events\UnixGroupMemberCreated;
use App\Modules\Groups\Events\UnixGroupMemberDeleted;

/**
 * Unix Group member model
 */
class UnixGroupMember extends Model
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
	 * @var  string
	 **/
	protected $table = 'unixgroupusers';

	/**
	 * Default order by for model
	 *
	 * @var string
	 */
	public static $orderBy = 'id';

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
		'unixgroupid' => 'required|integer',
		'userid' => 'required|integer'
	);

	/**
	 * The event map for the model.
	 *
	 * @var array
	 */
	protected $dispatchesEvents = [
		'creating' => UnixGroupMemberCreated::class,
		//'created'  => UnixGroupMemberCreated::class,
		//'updating' => UnixGroupMemberUpdating::class,
		//'updated'  => UnixGroupMemberUpdated::class,
		'deleted'  => UnixGroupMemberDeleted::class,
	];

	/**
	 * If entry is trashed
	 *
	 * @return  bool
	 **/
	public function isTrashed()
	{
		return ($this->datetimeremoved && $this->datetimeremoved != '0000-00-00 00:00:00' && $this->datetimeremoved != '-0001-11-30 00:00:00');
	}

	/**
	 * Get parent group
	 *
	 * @return  object
	 */
	public function unixgroup()
	{
		return $this->belongsTo(UnixGroup::class, 'unixgroupid');
	}

	/**
	 * Get parent user
	 *
	 * @return  object
	 */
	public function user()
	{
		return $this->belongsTo('App\Modules\Users\Models\User', 'userid');
	}

	/**
	 * Query scope where record isn't trashed
	 *
	 * @param   object  $query
	 * @return  object
	 */
	public function scopeWhereIsActive($query)
	{
		$t = $this->getTable();

		return $query->where(function($where) use ($t)
		{
			$where->whereNull($t . '.datetimeremoved')
					->orWhere($t . '.datetimeremoved', '=', '0000-00-00 00:00:00');
		});
	}

	/**
	 * Query scope where record is trashed
	 *
	 * @param   object  $query
	 * @return  object
	 */
	public function scopeWhereIsTrashed($query)
	{
		$t = $this->getTable();

		return $query->where(function($where) use ($t)
		{
			$where->whereNotNull($t . '.datetimeremoved')
				->where($t . '.datetimeremoved', '!=', '0000-00-00 00:00:00');
		});
	}
}
