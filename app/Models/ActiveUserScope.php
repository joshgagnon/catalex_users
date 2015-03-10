<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ScopeInterface;

class ActiveUserScope implements ScopeInterface {

	/**
	 * Stores the location of the created active binding in bindings array in
	 * case it has to be removed.
	 *
	 * @var integer
	 */
	private $bindingIndex;

	/**
	 * Apply the scope to a given Eloquent query builder.
	 *
	 * @param  \Illuminate\Database\Eloquent\Builder  $builder
	 * @param  \Illuminate\Database\Eloquent\Model  $model
	 * @return void
	 */
	public function apply(Builder $builder, Model $model) {
		// By default only get active users
		$builder->where($model->getTable() . '.active', true);

		$this->bindingIndex = count($builder->getQuery()->getRawBindings()['where']) - 1;

		$builder->macro('withInactive', function(Builder $builder) {
			$this->remove($builder, $builder->getModel());

			return $builder;
		});
	}

	/**
	 * Remove the scope from the given Eloquent query builder.
	 *
	 * @param  \Illuminate\Database\Eloquent\Builder  $builder
	 * @param  \Illuminate\Database\Eloquent\Model  $model
	 * @return void
	 */
	public function remove(Builder $builder, Model $model) {
		$column = $model->getTable() . '.active';

		$query = $builder->getQuery();

		foreach((array)$query->wheres as $key => $where) {
			if($where['type'] === 'Basic' && $where['column'] === $column) {
				unset($query->wheres[$key]);

				$bindings = $query->getRawBindings()['where'];
				unset($bindings[$this->bindingIndex]);
				$query->setBindings(array_values($bindings));

				$query->wheres = array_values($query->wheres);

				break;
			}
		}
	}
}
