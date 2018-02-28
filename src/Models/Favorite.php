<?php 

namespace Moonlight\Models;

use Illuminate\Database\Eloquent\Model;
use Moonlight\Main\Element;

class Favorite extends Model {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'admin_favorites';

	public function getElement()
	{
		if ( ! $this->class_id) return null;

		return Element::getByClassId($this->class_id);
	}

	public function rubric()
	{
		return $this->belongsTo('Moonlight\Models\FavoriteRubric');
	}
}
