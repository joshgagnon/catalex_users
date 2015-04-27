<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class AccessLog extends Model {

	/**
	 * The primary key for the model.
	 *
	 * @var string
	 */
	protected $primaryKey = null;

	/**
	 * Indicates if the IDs are auto-incrementing.
	 *
	 * @var bool
	 */
	public $incrementing = false;

	/**
	 * The name of the "created at" column.
	 *
	 * @var string
	 */
	const CREATED_AT = 'timestamp';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['user_id', 'route'];

	/**
	 * Don't use updated_at timestamp for this model.
	 *
	 * @param  mixed  $value
	 * @return void
	 */
	public function setUpdatedAt($value) {
		// Nothing to do
	}

	public function user() {
		return $this->belongsTo('App\User');
	}
}
