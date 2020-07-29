<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace App\Modules\Widgets\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Halcyon\Config\Registry;
use App\Halcyon\Traits\ErrorBag;
use App\Halcyon\Traits\Validatable;
use App\Halcyon\Traits\Checkable;
use App\Halcyon\Form\Form;
use App\Modules\Widgets\Events\WidgetCreating;
use App\Modules\Widgets\Events\WidgetCreated;
use App\Modules\Widgets\Events\WidgetUpdating;
use App\Modules\Widgets\Events\WidgetUpdated;
use App\Modules\Widgets\Events\WidgetDeleted;
use Carbon\Carbon;
use App\Halcyon\Models\Casts\Params;

/**
 * Module extension model
 */
class Widget extends Model
{
	use ErrorBag, Validatable, Checkable;

	/**
	 * Indicates if the model should be timestamped.
	 *
	 * @var bool
	 */
	public $timestamps = false;

	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'widgets';

	/**
	 * Default order by for model
	 *
	 * @var  string
	 */
	public static $orderBy = 'position';

	/**
	 * Default order direction for select queries
	 *
	 * @var  string
	 */
	public static $orderDir = 'asc';

	/**
	 * Fields and their validation criteria
	 *
	 * @var  array
	 */
	protected $rules = array(
		'title'    => 'required',
		'position' => 'required'
	);

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $guarded = [
		'id',
		'params',
	];

	/**
	 * The attributes that should be cast to native types.
	 *
	 * @var array
	 */
	protected $casts = [
		'published' => 'integer',
		'access' => 'integer',
		'params' => Params::class,
	];

	/**
	 * The attributes that should be cast to native types.
	 *
	 * @var array
	 */
	protected $dates = [
		'publish_up',
		'publish_down',
	];

	/**
	 * The event map for the model.
	 *
	 * @var array
	 */
	protected $dispatchesEvents = [
		'creating' => WidgetCreating::class,
		'created'  => WidgetCreated::class,
		'updating' => WidgetUpdating::class,
		'updated'  => WidgetUpdated::class,
		'deleted'  => WidgetDeleted::class,
	];

	/**
	 * Configuration registry
	 *
	 * @var  object
	 */
	//protected $paramsRegistry = null;

	/**
	 * The path to the installed files
	 *
	 * @var  string
	 */
	protected $path = null;

	/**
	 * The "booted" method of the model.
	 *
	 * @return void
	 */
	protected static function booted()
	{
		static::creating(function ($model)
		{
			$result = self::query()
				->select(DB::raw('MAX(ordering) + 1 AS seq'))
				->where('position', '=', $model->position)
				->get()
				->first()
				->seq;

			$model->setAttribute('ordering', (int)$result);
		});
	}

	/**
	 * Get params as a Registry object
	 *
	 * @return  object
	 */
	//public function getParamsAttribute()
	/*public function params()
	{
		if (!($this->paramsRegistry instanceof Registry))
		{
			$this->paramsRegistry = new Registry($this->getOriginal('params'));
		}
		return $this->paramsRegistry;
	}*/

	/**
	 * Determine if record is published
	 * 
	 * @return  boolean
	 */
	public function isPublished()
	{
		if ($this->published != 1)
		{
			return false;
		}

		if ($this->publish_up
		 && $this->publish_up != '0000-00-00 00:00:00'
		 && $this->publish_up > Carbon::now()->toDateTimeString())
		{
			return false;
		}

		if ($this->publish_down
		 && $this->publish_down != '0000-00-00 00:00:00'
		 && $this->publish_down <= Carbon::now()->toDateTimeString())
		{
			return false;
		}

		return true;
	}

	/**
	 * Method to change the title.
	 *
	 * @param   string   $title        The title.
	 * @param   string   $position     The position.
	 * @return  array    Contains the modified title.
	 */
	public function generateNewTitle($title, $position)
	{
		// Alter the title & alias
		$models = self::query()
			->where('position', '=', $position)
			->where('title', '=', $title)
			->count();

		for ($i = 0; $i < $models; $i++)
		{
			$title = self::incrementString($title);
		}

		return array($title);
	}

	/**
	 * Increments a trailing number in a string.
	 *
	 * Used to easily create distinct labels when copying objects. The method has the following styles:
	 *
	 * default: "Label" becomes "Label (2)"
	 * dash:    "Label" becomes "Label-2"
	 *
	 * @param   string   $string  The source string.
	 * @param   string   $style   The the style (default|dash).
	 * @param   integer  $n       If supplied, this number is used for the copy, otherwise it is the 'next' number.
	 * @return  string   The incremented string.
	 */
	protected static function incrementString($string, $style = 'default', $n = 0)
	{
		$incrementStyles = array(
			'dash' => array(
				'#-(\d+)$#',
				'-%d'
			),
			'default' => array(
				array('#\((\d+)\)$#', '#\(\d+\)$#'),
				array(' (%d)', '(%d)'),
			),
		);

		$styleSpec = isset($incrementStyles[$style]) ? $incrementStyles[$style] : $incrementStyles['default'];

		// Regular expression search and replace patterns.
		if (is_array($styleSpec[0]))
		{
			$rxSearch  = $styleSpec[0][0];
			$rxReplace = $styleSpec[0][1];
		}
		else
		{
			$rxSearch = $rxReplace = $styleSpec[0];
		}

		// New and old (existing) sprintf formats.
		if (is_array($styleSpec[1]))
		{
			$newFormat = $styleSpec[1][0];
			$oldFormat = $styleSpec[1][1];
		}
		else
		{
			$newFormat = $oldFormat = $styleSpec[1];
		}

		// Check if we are incrementing an existing pattern, or appending a new one.
		if (preg_match($rxSearch, $string, $matches))
		{
			$n = empty($n) ? ($matches[1] + 1) : $n;
			$string = preg_replace($rxReplace, sprintf($oldFormat, $n), $string);
		}
		else
		{
			$n = empty($n) ? 2 : $n;
			$string .= sprintf($newFormat, $n);
		}

		return $string;
	}

	/**
	 * Get the installed path
	 *
	 * @return  string
	 */
	public function path()
	{
		if (is_null($this->path))
		{
			$this->path = '';

			if ($widget = $this->module)
			{
				if (substr($widget, 0, 4) == 'mod_')
				{
					$widget = substr($widget, 4);
				}
				$widget = ucfirst($widget);

				$path = app_path() . '/Widgets/' . $widget . '/' . $widget . '.php';

				if (file_exists($path))
				{
					$this->path = dirname($path);
				}
			}
		}

		return $this->path;
	}

	/**
	 * Get a form
	 *
	 * @return  object
	 */
	public function getForm()
	{
		$file = __DIR__ . '/Forms/Widget.xml';

		Form::addFieldPath(__DIR__ . '/Fields');

		$form = new Form('module', array('control' => 'fields'));

		if (!$form->loadFile($file, false, '//form'))
		{
			$this->addError(trans('global.load file failed'));
		}

		$paths = array();
		$paths[] = $this->path() . '/Config/Params.xml';

		/*if (substr($this->module, 0, 4) == 'mod_')
		{
			$paths[] = $this->path() . '/' . substr($this->module, 4) . '.xml';
		}

		$paths[] = $this->path() . '/' . $this->module . '.xml';*/

		foreach ($paths as $file)
		{
			if (file_exists($file))
			{
				// Get the plugin form.
				if (!$form->loadFile($file, false, '//config'))
				{
					$this->addError(trans('global.load file failed'));
				}
				break;
			}
		}

		$data = $this->toArray();
		$data['params'] = $this->params->all(); //()->toArray();

		$form->bind($data);

		return $form;
	}

	public function registerLanguage()
	{
		$name = $this->module;
		if (substr($name, 0, 4) == 'mod_')
		{
			$name = substr($name, 4);
		}

		app('translator')->addNamespace('widget.' . $name, $this->path() . '/lang');
	}

	/**
	 * Save the record
	 *
	 * @return  boolean  False if error, True on success
	 */
	/*public function save(array $options = array())
	{
		if (!is_string($this->params))
		{
			$this->params = json_encode($this->params);
		}

		return parent::save($options);
	}*/

	/**
	 * Get menu assignments
	 *
	 * @return  array
	 */
	public function menuAssigned()
	{
		/*$db = App::get('db');
		$db->setQuery(
			'SELECT menuid' .
			' FROM #__modules_menu' .
			' WHERE moduleid = '.$this->get('id')
		);
		return $db->loadColumn();*/

		return Menu::query()
			->where('moduleid', '=', (int)$this->id)
			->get()
			->pluck('menuid')
			->toArray();
	}

	/**
	 * Determine the assignment
	 *
	 * @return  array
	 */
	public function menuAssignment()
	{
		// Determine the page assignment mode.
		$assigned = $this->menuAssigned();

		if (!$this->id)
		{
			// If this is a new module, assign to all pages.
			$assignment = 0;
		}
		elseif (empty($assigned))
		{
			// For an existing module it is assigned to none.
			$assignment = '-';
		}
		else
		{
			if ($assigned[0] > 0)
			{
				$assignment = +1;
			}
			elseif ($assigned[0] < 0)
			{
				$assignment = -1;
			}
			else
			{
				$assignment = 0;
			}
		}

		return $assignment;
	}

	/**
	 * Save menu assignments for a module
	 *
	 * @param   integer  $assignment
	 * @param   array    $assigned
	 * @return  bool
	 */
	public function saveAssignment($assignment, $assigned)
	{
		$assignment = $assignment ? $assignment : 0;

		// Delete old module to menu item associations
		if (!Menu::deleteByWidget($this->id))
		{
			$this->addError('Failed to remove previous menu assignments.');
			return false;
		}

		// If the assignment is numeric, then something is selected (otherwise it's none).
		if (is_numeric($assignment))
		{
			// Variable is numeric, but could be a string.
			$assignment = (int) $assignment;

			// Logic check: if no module excluded then convert to display on all.
			if ($assignment == -1 && empty($assigned))
			{
				$assignment = 0;
			}

			// Check needed to stop a module being assigned to `All`
			// and other menu items resulting in a module being displayed twice.
			if ($assignment === 0)
			{
				// assign new module to `all` menu item associations
				$menu = new Menu(array(
					'moduleid' => $this->id,
					'menuid'   => 0
				));

				if (!$menu->save())
				{
					$this->addError('Failed saving: ' . $menu->getError());
					return false;
				}
			}
			elseif (!empty($assigned))
			{
				// Get the sign of the number.
				$sign = $assignment < 0 ? -1 : +1;

				// Preprocess the assigned array.
				$tuples = array();
				foreach ($assigned as &$pk)
				{
					$menu = new Menu(array(
						'moduleid' => $this->id,
						'menuid'   => ((int) $pk * $sign)
					));

					if (!$menu->save())
					{
						$this->addError('More failed: ' . $menu->getError());
						return false;
					}
				}
			}
		}

		return true;
	}

		/**
	 * Saves the manually set order of records.
	 *
	 * @param   array  $pks    An array of primary key ids.
	 * @param   array  $order  An array of order values.
	 * @return  bool
	 */
	public static function saveOrder($pks = null, $order = null)
	{
		if (empty($pks))
		{
			return false;
		}

		// Update ordering values
		foreach ($pks as $i => $pk)
		{
			$model = self::findOrFail((int) $pk);

			if ($model->ordering != $order[$i])
			{
				$model->ordering = $order[$i];

				if (!$model->save())
				{
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Delete the record and all associated data
	 *
	 * @return  boolean  False if error, True on success
	 */
	public function delete(array $options = [])
	{
		// Delete old module to menu item associations
		if (!Menu::deleteByWidget($this->id))
		{
			$this->addError('Failed to remove previous menu assignments.');
			return false;
		}

		// Attempt to delete the record
		return parent::delete($options);
	}
}
