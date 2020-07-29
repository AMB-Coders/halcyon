<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace App\Modules\Users\Helpers;

use App\Halcyon\Facades\Html;
use App\Halcyon\Access\Gate;

/**
 * Users module debugging helper.
 */
class Debug
{
	/**
	 * Get a list of the modules.
	 *
	 * @return  array
	 */
	public static function getModules()
	{
		// Initialise variable.
		$items = app('db')
			->table('extensions')
			->select(['name AS text', 'element AS value'])
			->where('enabled', '>=', '1')
			->where('type', '=', 'module')
			->get();

		if (count($items))
		{
			foreach ($items as $item)
			{
				// Load language
				app('translator')->addNamespace($item->value, module_path($item->text) . '/Resources/lang');

				// Translate module name
				$item->text = trans($item->value . '::system.' . $item->text);
			}

			// Sort by module name
			$items->sortBy('text');
		}

		return $items;
	}

	/**
	 * Get a list of the actions for the module or code actions.
	 *
	 * @param   string  $module  The name of the module.
	 * @return  array
	 */
	public static function getActions($module = null)
	{
		$actions = array();

		if (empty($module))
		{
			$module = 'core';
		}

		// Try to get actions for the module
		if (!empty($module))
		{
			$path = module_path($module) . '/Config/permissions.php'; //'xml';

			$module_actions = Gate::getActionsFromFile($path);
			//$module_actions ?: array();

			if (!empty($module_actions))
			{
				/*foreach ($module_actions as $action)
				{
					$actions[$action->title] = array($action->name, $action->description);
				}*/
				foreach ($module_actions as $name => $title)
				{
					if (is_array($title))
					{
						$actions[$title['title']] = array($name, $title['title']);
					}
					else
					{
						$actions[$title] = array($name, $title);
					}
				}
			}
		}

		// Use default actions from configuration if no module selected or module doesn't have actions
		/*if (empty($actions))
		{
			$filename = module_path('config') . '/Models/Forms/application.xml';

			if (is_file($filename))
			{
				$xml = simplexml_load_file($filename);

				foreach ($xml->children()->fieldset as $fieldset)
				{
					if ('permissions' == (string) $fieldset['name'])
					{
						foreach ($fieldset->children() as $field)
						{
							if ('rules' == (string) $field['name'])
							{
								foreach ($field->children() as $action)
								{
									$actions[(string) $action['title']] = array(
										(string) $action['name'],
										(string) $action['description']
									);
								}
								break;
								break;
								break;
							}
						}
					}
				}

				// Load language
				app('translator')->addNamespace('config', module_path('config') . '/Resources/lang');
			}
		}*/

		return $actions;
	}

	/**
	 * Get a list of filter options for the levels.
	 *
	 * @return  array  An array of Option elements.
	 */
	static function getLevelsOptions()
	{
		// Build the filter options.
		$options = array();
		$options[] = Html::select('option', '1', trans('users::access.OPTION_LEVEL_MODULE', ['level' => 1]));
		$options[] = Html::select('option', '2', trans('users::access.OPTION_LEVEL_CATEGORY', ['level' => 2]));
		$options[] = Html::select('option', '3', trans('users::access.OPTION_LEVEL_DEEPER', ['level' => 3]));
		$options[] = Html::select('option', '4', '4');
		$options[] = Html::select('option', '5', '5');
		$options[] = Html::select('option', '6', '6');

		return $options;
	}
}
