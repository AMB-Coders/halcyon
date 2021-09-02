<?php
namespace App\Modules\Orders\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Modules\History\Traits\Historable;
use App\Modules\Core\Traits\LegacyTrash;
use App\Modules\Groups\Models\Group;
use App\Modules\Users\Models\User;
use App\Modules\Orders\Events\OrderCreated;
use App\Modules\Orders\Events\OrderDeleted;
use Carbon\Carbon;

/**
 * Model for an order
 */
class Order extends Model
{
	use SoftDeletes, LegacyTrash, Historable;

	/**
	 * The name of the "created at" column.
	 *
	 * @var string
	 */
	const CREATED_AT = 'datetimecreated';

	/**
	 * The name of the "updated at" column.
	 *
	 * @var string
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
	protected $table = 'orders';

	/**
	 * The attributes that should be mutated to dates.
	 *
	 * @var array
	 */
	public $dates = array(
		'datetimecreated',
		'datetimeremoved'
	);

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $guarded = [
		'id',
		'datetimecreated',
		'datetimeremoved'
	];

	/**
	 * The event map for the model.
	 *
	 * @var  array
	 */
	protected $dispatchesEvents = [
		'created'  => OrderCreated::class,
		'deleted'  => OrderDeleted::class,
	];

	/**
	 * Default order by for model
	 *
	 * @var string
	 */
	public static $orderBy = 'datetimecreated';

	/**
	 * Default order direction for select queries
	 *
	 * @var  string
	 */
	public static $orderDir = 'desc';

	/**
	 * Defines a relationship to updates
	 *
	 * @return  object
	 */
	public function items()
	{
		return $this->hasMany(Item::class, 'orderid')->where('quantity', '>', 0);
	}

	/**
	 * Defines a relationship to updates
	 *
	 * @return  object
	 */
	public function accounts()
	{
		return $this->hasMany(Account::class, 'orderid');
	}

	/**
	 * Defines a relationship to updates
	 *
	 * @return  object
	 */
	public function group()
	{
		return $this->belongsTo(Group::class, 'groupid');
	}

	/**
	 * Defines a relationship to updates
	 *
	 * @return  object
	 */
	public function user()
	{
		return $this->belongsTo(User::class, 'userid');
	}

	/**
	 * Defines a relationship to updates
	 *
	 * @return  object
	 */
	public function submitter()
	{
		return $this->belongsTo(User::class, 'submitteruserid');
	}

	/**
	 * Set user notes
	 *
	 * @return  void
	 */
	public function setUsernotesAttribute($value)
	{
		$this->attributes['usernotes'] = strip_tags($value);
	}

	/**
	 * Set staff notes
	 *
	 * @return  void
	 */
	public function setStaffnotesAttribute($value)
	{
		$this->attributes['staffnotes'] = strip_tags($value);
	}

	/**
	 * Get order total
	 *
	 * @return  integer
	 */
	public function getTotalAttribute()
	{
		$ordertotal = 0;

		foreach ($this->items as $item)
		{
			$ordertotal += $item->price;
		}

		//return number_format($ordertotal / 100, 2);
		return $ordertotal;
	}

	/**
	 * Format unit price
	 *
	 * @return  string
	 */
	public function formatNumber($val)
	{
		$number = preg_replace('/[^0-9\-]/', '', $val);

		$neg = '';
		if ($number < 0)
		{
			$neg = '-';
			$number = -$number;
		}

		if ($number > 99)
		{
			$dollars = substr($number, 0, strlen($number) - 2);
			$cents   = substr($number, strlen($number) - 2, 2);
			$dollars = number_format($dollars);

			$number = $dollars . '.' . $cents;
		}
		elseif ($number > 9 && $number < 100)
		{
			$number = '0.' . $number;
		}
		else
		{
			$number = '0.0' . $number;
		}

		return $neg . $number;
	}

	/**
	 * Is the order canceled?
	 *
	 * @return  bool
	 */
	public function isCanceled()
	{
		return $this->isTrashed();
	}

	/**
	 * Get order status
	 *
	 * @return  string
	 */
	public function getStatusAttribute()
	{
		if ($state = $this->getAttribute('state'))
		{
			switch ($state)
			{
				case 1:
					return 'pending_fulfillment';
				break;
				case 2:
					return 'pending_boassignment';
				break;
				case 3:
					return 'pending_payment';
				break;
				case 4:
					return 'pending_approval';
				break;
				case 5:
					return 'pending_collection';
				break;
				case 6:
					return 'complete';
				break;
				case 7:
					return 'canceled';
				break;
			}
		}

		//if ($this->trashed())
		if ($this->isTrashed())
		{
			$status = 'canceled';
		}
		else
		{
			$its = $this->items;

			$items = count($its);
			$itemsfulfilled = 0;
			$ordertotal = 0;

			foreach ($its as $item)
			{
				if ($item->isFulfilled())
				{
					$itemsfulfilled++;
				}

				$ordertotal += $item->price;
			}

			$acc = $this->accounts()->withTrashed()->whereIsActive()->get();

			$accounts = 0;
			$accountspaid     = 0;
			$accountsapproved = 0;
			$accountsassigned = 0;
			$accountsdenied   = 0;
			$amountassigned   = 0;

			if ($acc)
			{
				$accounts = count($acc);

				foreach ($acc as $account)
				{
					if ($account->approveruserid)
					{
						$accountsassigned++;
					}

					if ($account->isApproved())
					{
						$accountsapproved++;
					}

					if ($account->isPaid())
					{
						$accountspaid++;
					}

					if ($account->isDenied())
					{
						$accountsdenied++;
					}

					$amountassigned += $account->amount;
				}
			}

			if (($accounts == 0 && $ordertotal > 0)
			 || $amountassigned < $ordertotal
			 || ($accountsdenied > 0 && ($accountsdenied + $accountsapproved) == $accounts))
			{
				$status = 'pending_payment';
			}
			elseif ($accountsassigned < $accounts)
			{
				$status = 'pending_boassignment';
			}
			elseif ($accountsapproved < $accounts)
			{
				$status = 'pending_approval';
			}
			elseif ($accountsapproved == $accounts && $itemsfulfilled < $items)
			{
				$status = 'pending_fulfillment';
			}
			elseif ($itemsfulfilled == $items && $accountspaid < $accounts)
			{
				$status = 'pending_collection';
			}
			else
			{
				$status = 'complete';
			}
		}

		return $status;
	}

	/**
	 * Is the order in an active state?
	 *
	 * @return  bool
	 */
	public function isActive()
	{
		return ($this->status != 'canceled' && $this->status != 'complete');
	}

	/**
	 * Delete entry and associated data
	 *
	 * @param   array  $options
	 * @return  bool
	 */
	/*public function delete(array $options = [])
	{
		foreach ($this->accounts as $row)
		{
			$row->delete();
		}

		foreach ($this->items as $row)
		{
			$row->delete();
		}

		return parent::delete($options);
	}*/

	/**
	 * Format unit price
	 *
	 * @return  string
	 */
	public function getFormattedTotalAttribute()
	{
		$number = preg_replace('/[^0-9\-]/', '', $this->total);

		$neg = '';
		if ($number < 0)
		{
			$neg = '-';
			$number = -$number;
		}

		if ($number > 99)
		{
			$dollars = substr($number, 0, strlen($number) - 2);
			$cents   = substr($number, strlen($number) - 2, 2);
			$dollars = number_format($dollars);

			$number = $dollars . '.' . $cents;
		}
		elseif ($number > 9 && $number < 100)
		{
			$number = '0.' . $number;
		}
		else
		{
			$number = '0.0' . $number;
		}

		return $neg . $number;
	}

	public static function stats($timeframe = 7)
	{
		$now = Carbon::now();
		$placed = array();
		for ($d = $timeframe; $d >= 0; $d--)
		{
			$yesterday = Carbon::now()->modify('- ' . $d . ' days');
			$tomorrow  = Carbon::now()->modify(($d ? '- ' . ($d - 1) : '+ 1') . ' days');

			$placed[$yesterday->format('Y-m-d')] = self::query()
				->withTrashed()
				->whereIsActive()
				->where('datetimecreated', '>', $yesterday->format('Y-m-d') . ' 00:00:00')
				->where('datetimecreated', '<', $tomorrow->format('Y-m-d') . ' 00:00:00')
				->count();
		}

		$yesterday = Carbon::now()->modify('- ' . $timeframe . ' days');
		$tomorrow  = Carbon::now()->modify('+ 1 days');

		$past = self::query()
			->withTrashed()
			->whereIsActive()
			->where('datetimecreated', '>', $yesterday->format('Y-m-d') . ' 00:00:00')
			->where('datetimecreated', '<', $tomorrow->format('Y-m-d') . ' 00:00:00')
			->get();

		$total = count($past);
		$canceled = self::query()
			->withTrashed()
			->whereIsTrashed()
			->where('datetimecreated', '>', $yesterday->format('Y-m-d') . ' 00:00:00')
			->where('datetimecreated', '<', $tomorrow->format('Y-m-d') . ' 00:00:00')
			->count();
		$fulfilled = 0;

		$step = array(
			'payment' => array(
				'value' => 0,
				'total' => 0
			),
			'approval' => array(
				'value' => 0,
				'total' => 0
			),
			'fulfilled' => array(
				'value' => 0,
				'total' => 0
			),
			'completed' => array(
				'value' => 0,
				'total' => 0
			),
		);

		foreach ($past as $order)
		{
			$accounts = $order->accounts()->orderBy('datetimecreated', 'asc')->get();

			if (!count($accounts))
			{
				continue;
			}

			$step['payment']['value'] += $accounts->first()->datetimecreated->timestamp - $order->datetimecreated->timestamp;
			$step['payment']['total']++;

			$lastapproved = $order->datetimecreated->timestamp;
			foreach ($accounts as $account)
			{
				if (!$account->isApproved())
				{
					continue;
				}

				$step['approval']['value'] += $account->datetimeapproved->timestamp - $account->datetimecreated->timestamp;
				$step['approval']['total']++;
				$lastapproved = $account->datetimeapproved->timestamp > $lastapproved ? $account->datetimeapproved->timestamp : $lastapproved;
			}

			$items = $order->items()->orderBy('datetimecreated', 'asc')->get();

			if (!count($items))
			{
				continue;
			}

			foreach ($items as $item)
			{
				if ($item->isFulfilled())
				{
					$fulfilled++;

					$step['fulfilled']['value'] += $item->datetimefulfilled->timestamp - $lastapproved;
					$step['fulfilled']['total']++;

					$step['completed']['value'] += $item->datetimefulfilled->timestamp - $order->datetimecreated->timestamp;
					$step['completed']['total']++;
				}
			}
		}

		$avg = $step['payment']['value'] ? $step['payment']['value'] / $step['payment']['total'] : 0;
		if ($avg)
		{
			$avg = self::toHumanReadable($avg);
		}
		$step['payment']['average'] = $avg;

		$avg = $step['approval']['value'] ? $step['approval']['value'] / $step['approval']['total'] : null;
		if ($avg)
		{
			$avg = self::toHumanReadable($avg);
		}
		$step['approval']['average'] = $avg;

		$avg = $step['fulfilled']['value'] ? $step['fulfilled']['value'] / $step['fulfilled']['total'] : null;
		if ($avg)
		{
			$avg = self::toHumanReadable($avg);
		}
		$step['fulfilled']['average'] = $avg;

		$avg = $step['completed']['value'] ? $step['completed']['value'] / $step['completed']['total'] : null;
		if ($avg)
		{
			$avg = self::toHumanReadable($avg);
		}
		$step['completed']['average'] = $avg;

		$stats = array(
			'timeframe' => $timeframe,
			'submitted' => $total,
			'canceled'  => $canceled,
			'fulfilled' => $fulfilled,
			'steps'     => $step,
			'daily'     => $placed,
		);

		return $stats;
	}

	public static function toHumanReadable($avg)
	{
		$unit = '';

		if ($avg < 60)
		{
			$unit = 'sec';
		}
		else if ($avg < 3600)
		{
			$avg /= 60;
			$unit = 'min';
		}
		else if ($avg < 86400)
		{
			$avg /= 3600;
			$unit = 'hrs';
		}
		else
		{
			$avg /= 86400;
			$unit = 'days';
		}

		return round($avg, 2) . ' ' . $unit;
	}
}
