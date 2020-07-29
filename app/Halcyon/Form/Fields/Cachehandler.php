<?php
/**
 * @package    framework
 * @copyright  Copyright 2020 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace App\Halcyon\Form\Fields;

use App\Halcyon\Html\Builder\Select as Dropdown;
use App\Halcyon\Cache\Manager;
use App;

/**
 * Provides a list of available cache handlers
 */
class Cachehandler extends Select
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	public $type = 'Cachehandler';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 */
	protected function getOptions()
	{
		// Initialize variables.
		$options = array();

		// Convert to name => name array.
		foreach (Manager::getStores() as $store)
		{
			$options[] = Dropdown::option($store, trans('JLIB_FORM_VALUE_CACHE_' . $store), 'value', 'text');
		}

		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
