<?php

namespace App\Modules\History\Helpers\Diff;

/**
 * Diff operation
 */
class DiffOp
{
	/**
	 * Description for 'type'
	 *
	 * @var unknown
	 */
	public $type;

	/**
	 * Description for 'orig'
	 *
	 * @var unknown
	 */
	public $orig;

	/**
	 * Description for 'closing'
	 *
	 * @var unknown
	 */
	public $closing;

	/**
	 * Short description for 'reverse'
	 *
	 * Long description (if any) ...
	 *
	 * @return     void
	 */
	public function reverse()
	{
		trigger_error('pure virtual', E_USER_ERROR);
	}

	/**
	 * Short description for 'norig'
	 *
	 * Long description (if any) ...
	 *
	 * @return     integer Return description (if any) ...
	 */
	public function norig()
	{
		return $this->orig ? count($this->orig) : 0;
	}

	/**
	 * Short description for 'nclosing'
	 *
	 * Long description (if any) ...
	 *
	 * @return     integer Return description (if any) ...
	 */
	public function nclosing()
	{
		return $this->closing ? count($this->closing) : 0;
	}
}
