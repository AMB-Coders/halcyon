<?php

namespace App\Modules\ContactReports\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
{
	/**
	 * Transform the resource collection into an array.
	 *
	 * @param   \Illuminate\Http\Request  $request
	 * @return  array
	 */
	public function toArray($request)
	{
		$data = parent::toArray($request);

		$data['formattedcomment'] = $this->formattedComment();

		$data['api'] = route('api.contactreports.read', ['id' => $this->id]);
		$data['url'] = route('site.contactreports.show', ['id' => $this->contactreportid]);

		unset($data['report']);

		$data['can']['edit']   = false;
		$data['can']['delete'] = false;

		$user = auth()->user();

		if ($user)
		{
			$data['can']['edit']   = ($user->can('edit contactreports') || ($user->can('edit.own contactreports') && $item->userid == $user->id));
			$data['can']['delete'] = $user->can('delete contactreports');
		}

		return $data; //parent::toArray($request);
	}
}
