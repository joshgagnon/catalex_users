<?php namespace App\Auth;

use Illuminate\Auth\EloquentUserProvider;

class CaseInsensitiveUserProvider extends EloquentUserProvider {

	public function retrieveByCredentials(array $credentials) {
		$query = $this->createModel()->newQuery();

		$query->whereRaw('LOWER(email) = LOWER(?)', [$credentials['email']]);

		return $query->first();
	}
}
