<?php

namespace App\Modules\Menus\Events;

use App\Modules\Menus\Models\Item;

class ItemUpdated
{
	/**
	 * @var Item
	 */
	public $item;

	/**
	 * Constructor
	 *
	 * @param Item $item
	 * @return void
	 */
	public function __construct(Item $item)
	{
		$this->item = $item;
	}

	/**
	 * Return the entity
	 *
	 * @return Item
	 */
	public function getItem()
	{
		return $this->item;
	}
}
