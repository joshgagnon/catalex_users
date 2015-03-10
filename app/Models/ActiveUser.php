<?php namespace App\Models;

trait ActiveUser {

	/**
	 * Boot the ActiveUser trait, enabling it by default.
	 *
	 * @return void
	 */
	public static function bootActiveUser() {
		static::addGlobalScope(new ActiveUserScope);
	}

	/**
	 * Get a new query builder that includes incative users.
	 *
	 * @return \Illuminate\Database\Eloquent\Builder|static
	 */
	public static function withInactive() {
		return (new static)->newQueryWithoutScope(new ActiveUserScope);
	}
}
