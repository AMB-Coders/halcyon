<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace App\Modules\Menus\Models\Fields;

use App\Halcyon\Form\Fields\Select;
use App\Modules\Menus\Helpers\Menus as MenusHelper;
use App\Modules\Menus\Helpers\ItemType;

/**
 * Form Field class
 */
class MenuType extends Select
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	protected $type = 'MenuType';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 */
	protected function getInput()
	{
		// Initialise variables.
		$html     = array();
		$recordId = (int) $this->form->getValue('id');
		$size     = ($v = $this->element['size']) ? ' size="'.$v.'"' : '';
		$class    = ($v = $this->element['class']) ? ' class="form-control '.$v.'"' : 'class="form-control"';

		// Get a reverse lookup of the base link URL to Title
		$model = new ItemType();
		$rlu = $model->getReverseLookup();

		switch ($this->value)
		{
			case 'url':
				$value = trans('menus::menus.type url');
				break;

			case 'alias':
				$value = trans('menus::menus.type alias');
				break;

			case 'separator':
				$value = trans('menus::menus.type separator');
				break;

			default:
				$link = $this->form->getValue('link');
				// Clean the link back to the option, view and layout
				$value = \App\Halcyon\Utility\Arr::getValue($rlu, $link); //MenusHelper::getLinkKey($link));
				break;
		}
		// Load the javascript and css
		/*Html::behavior('framework');
		Html::behavior('modal');

		Document::addScriptDeclaration("
			jQuery(document).ready(function($){
				$('input.modal').fancybox({
					arrows: false,
					type: 'iframe',
					autoSize: false,
					fitToView: false,
					width: 600,
					height: 450,
					href: '" . route('admin.menus.menutypes', ['record' => $recordId]) . "'
				});
			});
		");*/

		$html[] = '<div class="input-group">';
			//$html[] = '<div class="col">';
				$html[] = '<input type="text" id="'.$this->id.'" readonly="readonly" disabled="disabled" value="'.$value.'"'.$size.$class.' />';
			//$html[] = '</span>';
			$html[] = '<div class="input-group-append">';
				$html[] = '<input type="button" class="btn btn-outline-secondary" value="'.trans('JSELECT').'" />';
				$html[] = '<input type="hidden" name="'.$this->name.'" value="'.htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8').'" />';
			$html[] = '</div>';
		$html[] = '</div>';

		return implode("\n", $html);
	}
}
