<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace App\Modules\Messages\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Halcyon\Traits\ErrorBag;
use App\Halcyon\Traits\Validatable;
use App\Modules\History\Traits\Historable;
use App\Modules\Resources\Entities\Asset;
use App\Modules\Messages\Events\TypeCreating;
use App\Modules\Messages\Events\TypeCreated;
use App\Modules\Messages\Events\TypeUpdating;
use App\Modules\Messages\Events\TypeUpdated;
use App\Modules\Messages\Events\TypeDeleted;

/**
 * Model for news type
 */
class Type extends Model
{
	use ErrorBag, Validatable, Historable;

	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'messagequeuetypes';

	/**
	 * Indicates if the model should be timestamped.
	 *
	 * @var bool
	 */
	public $timestamps = false;

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
	 * The model's default values for attributes.
	 *
	 * @var array
	 */
	protected $attributes = [
		'resourceid' => 0
	];

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $guarded = [
		'id',
	];

	/**
	 * Fields and their validation criteria
	 *
	 * @var array
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
		'creating' => TypeCreating::class,
		'created'  => TypeCreated::class,
		'updating' => TypeUpdating::class,
		'updated'  => TypeUpdated::class,
		'deleted'  => TypeDeleted::class,
	];

	/**
	 * Defines a relationship to creator
	 *
	 * @return  object
	 */
	public function messages()
	{
		return $this->hasMany(Message::class, 'messagequeuetypeid');
	}

	/**
	 * Defines a relationship to resource
	 *
	 * @return  object
	 */
	public function resource()
	{
		return $this->belongsTo(Asset::class, 'resourceid')->withTrashed();
	}
}
