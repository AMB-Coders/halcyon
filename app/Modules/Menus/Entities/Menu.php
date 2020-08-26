<?php
/**
 * @package    framework
 * @copyright  Copyright 2020 Purdue University
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace App\Modules\Menus\Entities;

use App\Halcyon\Config\Registry;
use Illuminate\Support\Fluent;
use Illuminate\Support\Facades\DB;
use App\Modules\Menus\Models\Item;

/**
 * Menu class
 */
class Menu extends Fluent
{
	/**
	 * Array to hold the menu items
	 *
	 * @var  array
	 */
	protected $_items = array();

	/**
	 * Identifier of the default menu item
	 *
	 * @var  integer
	 */
	protected $_default = array();

	/**
	 * Identifier of the active menu item
	 *
	 * @var  integer
	 */
	protected $_active = 0;

	/**
	 * Menu instances container.
	 *
	 * @var  array
	 */
	protected static $instances = array();

	/**
	 * Class constructor
	 *
	 * @param   array  $options  An array of configuration options.
	 * @return  void
	 */
	public function __construct($options = array())
	{
		parent::__construct($options);

		// Load the menu items
		$this->load();

		foreach ($this->_items as $item)
		{
			if ($item->home)
			{
				$this->_default[trim($item->language)] = $item->id;
			}

			// Decode the item params
			$item->params = new Registry($item->params);
		}
	}

	/**
	 * Get menu item by id
	 *
	 * @param   integer  $id  The item id
	 * @return  mixed    The item object, or null if not found
	 */
	public function getItem($id)
	{
		$result = null;

		if (isset($this->_items[$id]))
		{
			$result =& $this->_items[$id];
		}

		return $result;
	}

	/**
	 * Set the default item by id and language code.
	 *
	 * @param   integer  $id        The menu item id.
	 * @param   string   $language  The language cod (since 1.6).
	 * @return  boolean  True, if successful
	 */
	public function setDefault($id, $language = '')
	{
		if (isset($this->_items[$id]))
		{
			$this->_default[$language] = $id;
			return true;
		}

		return false;
	}

	/**
	 * Get the default item by language code.
	 *
	 * @param   string  $language  The language code, default value of * means all.
	 * @return  mixed   The item object
	 */
	public function getDefault($language = '*')
	{
		if (array_key_exists($language, $this->_default) && $this->get('language_filter'))
		{
			return $this->_items[$this->_default[$language]];
		}

		if (array_key_exists('*', $this->_default))
		{
			return $this->_items[$this->_default['*']];
		}

		return 0;
	}

	/**
	 * Set the default item by id
	 *
	 * @param   integer  $id  The item id
	 * @return  mixed    If successful the active item, otherwise null
	 */
	public function setActive($id)
	{
		if (isset($this->_items[$id]))
		{
			$this->_active = $id;

			$result =& $this->_items[$id];

			return $result;
		}

		return null;
	}

	/**
	 * Get menu item by id.
	 *
	 * @return  object  The item object.
	 */
	public function getActive()
	{
		if ($this->_active)
		{
			$item =& $this->_items[$this->_active];

			return $item;
		}

		return null;
	}

	/**
	 * Gets menu items by attribute
	 *
	 * @param   mixed    $attributes  The field name(s).
	 * @param   mixed    $values      The value(s) of the field. If an array, need to match field names
	 *                                each attribute may have multiple values to lookup for.
	 * @param   boolean  $firstonly   If true, only returns the first item found
	 * @return  array
	 */
	public function getItems($attributes, $values, $firstonly = false)
	{
		$attributes = (array) $attributes;
		$values     = (array) $values;

		// Filter by language if not set
		if (($key = array_search('language', $attributes)) === false)
		{
			if ($this->get('language_filter'))
			{
				$attributes[] = 'language';
				$values[]     = array($this->get('language'), '*');
			}
		}
		elseif ($values[$key] === null)
		{
			unset($attributes[$key]);
			unset($values[$key]);
		}

		// Filter by access level if not set
		if (($key = array_search('access', $attributes)) === false)
		{
			$attributes[] = 'access';
			$values[]     = $this->get('access', array(1));
		}
		elseif ($values[$key] === null)
		{
			unset($attributes[$key]);
			unset($values[$key]);
		}

		$items      = array();
		//$attributes = (array) $attributes;
		//$values     = (array) $values;

		foreach ($this->_items as $item)
		{
			if (!is_object($item))
			{
				continue;
			}

			$test = true;
			for ($i = 0, $count = count($attributes); $i < $count; $i++)
			{
				$c = $attributes[$i];

				if (is_array($values[$i]))
				{
					if (!in_array($item->$c, $values[$i]))
					{
						$test = false;
						break;
					}
				}
				else
				{
					if ($item->$c != $values[$i])
					{
						$test = false;
						break;
					}
				}
			}

			if ($test)
			{
				if ($firstonly)
				{
					return $item;
				}

				$items[] = $item;
			}
		}

		return $items;
	}

	/**
	 * Gets the parameter object for a certain menu item
	 *
	 * @param   integer  $id  The item id
	 * @return  object   A Registry object
	 */
	public function getParams($id)
	{
		if ($menu = $this->getItem($id))
		{
			return $menu->params;
		}

		return new Registry;
	}

	/**
	 * Getter for the menu array
	 *
	 * @return  array
	 */
	public function getMenu()
	{
		return $this->_items;
	}

	/**
	 * Method to check object authorization against an access control
	 * object and optionally an access extension object
	 *
	 * @param   integer  $id  The menu id
	 * @return  boolean  True if authorised
	 */
	public function authorise($id)
	{
		$menu = $this->getItem($id);

		if ($menu)
		{
			return in_array((int) $menu->access, $this->get('access', array(0)));
		}

		return true;
	}

		/**
	 * Loads the entire menu table into memory.
	 *
	 * @return  array
	 */
	public function load()
	{
		/*if (!($this->get('db') instanceof \Hubzero\Database\Driver))
		{
			return;
		}

		// Initialise variables.
		$db = $this->get('db');

		$query = $db->getQuery()
			->select('m.id')
			->select('m.menutype')
			->select('m.title')
			->select('m.alias')
			->select('m.note')
			->select('m.path', 'route')
			->select('m.link')
			->select('m.type')
			->select('m.level')
			->select('m.language')
			->select('m.browserNav')
			->select('m.access')
			->select('m.params')
			->select('m.home')
			->select('m.img')
			->select('m.template_style_id')
			->select('m.module_id')
			->select('m.parent_id')
			->select('e.element', 'component')
			->from('#__menu', 'm')
			->join('#__extensions AS e', 'e.id', 'm.module_id', 'left')
			->whereEquals('m.published', 1)
			->where('m.parent_id', '>', 0)
			->whereEquals('m.client_id', 0)
			->order('m.lft', 'asc');

		// Set the query
		$db->setQuery($query->toString());

		$this->_items = $db->loadObjectList('id');*/
		$w = (new Item)->getTable();

		$items = DB::table($w)
			->leftJoin('extensions', $w . '.module_id', '=', 'extensions.id')
			->select([$w . '.*', 'extensions.element AS module'])
			->where($w . '.published', '=', 1)
			->where($w . '.parent_id', '>', 0)
			->where($w . '.client_id', '=', 0)
			->orderBy($w . '.lft', 'asc')
			->get();

		foreach ($items as $item)
		{
			$this->_items[$item->id] = $item;
		}

		foreach ($this->_items as &$item)
		{
			// Get parent information.
			$parent_tree = array();
			if (isset($this->_items[$item->parent_id]))
			{
				$parent_tree  = $this->_items[$item->parent_id]->tree;
			}

			// Create tree.
			$parent_tree[] = $item->id;
			$item->tree = $parent_tree;

			// Create the query array.
			$url = str_replace('index.php?', '', $item->link);
			$url = str_replace('&amp;', '&', $url);

			parse_str($url, $item->query);
		}
	}
}
