<?php

namespace App;

use Config;
use Carbon\Carbon;
use App\Library\Mail;
use App\Models\Billable;
use App\Models\ActiveUser;
use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

use App\Service;
use App\BillingItem;

class User extends Model implements AuthenticatableContract, CanResetPasswordContract {

	use Authenticatable, CanResetPassword, SoftDeletes, ActiveUser, Billable;

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'users';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['name', 'email', 'password', 'organisation_id', 'billing_detail_id'];

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = ['password', 'remember_token'];

	/**
	 * The attributes that should be mutated to dates.
	 *
	 * @var array
	 */
	protected $dates = ['deleted_at', 'paid_until'];

	public function organisation() {
		return $this->belongsTo('App\Organisation');
	}

	public function roles() {
		return $this->belongsToMany('App\Role');
	}

	public function fullName() {
		return  $this->name;
	}

	public function billingExempt() {
		if($this->organisation && $this->organisation->billingExempt()) return true;

		return $this->hasRole('global_admin');
	}

	/**
	 * Return the charge required to bring this user's paid date up to the target date.
	 *
	 * @param  Carbon\Carbon $targetDate
	 * @return string
	 */
	public function prorate($targetDate) {
		$startDate = $this->paid_until ? $this->paid_until->hour(23)->minute(59) : Carbon::now();

		if($startDate->gte($targetDate)) return '0.00';

		// Use interval, not billing period to determine montly or yearly rate
		if($startDate->diffInMonths($targetDate) === 0) {
			$max = Config::get('constants.monthly_price');
			$perDay = bcdiv($max, '31', 4);
		}
		else {
			$max = Config::get('constants.annual_price');
			$perDay = bcdiv($max, '365', 4);
		}

		$total = bcmul($perDay, (string)$startDate->diffInDays($targetDate), 2);

		return bccomp($total, $max) === 1 ? $max : $total;
	}

	public function paymentAmount() {
		if(!$this->billing_detail) return '0.00';

		switch($this->billing_detail->period) {
			case 'monthly':
				return Config::get('constants.monthly_price');
			case 'annually':
				return Config::get('constants.annual_price');
			default:
				throw new Exception('Billing period must be one of "monthly" or "annually"');
		}
	}

	public function sendInvoices($type, $invoiceNumber, $listItem, $orgName=null, $orgId=null) {
		$name = $this->fullName();
		$date = Carbon::now()->format('j/m/Y');
		$accountNumber = $orgId ?: 'CU' . str_pad((string)$this->id, 5, '0', STR_PAD_LEFT);

		$baseName = tempnam(base_path('storage/tmp'), 'invoice');
		$html = $baseName . '.html';
		$handle = fopen($html, 'w');
		fwrite(
			$handle,
			view('emails.invoice-attachment', compact('orgName', 'name', 'date', 'type', 'invoiceNumber', 'accountNumber', 'listItem'))->render()
		);
		fclose($handle);

		$pdf = $baseName . '.pdf';
		exec(implode(' ', ['phantomjs', base_path('scripts/pdferize.js'), $html, $pdf]));

		Mail::queueStyledMail('emails.invoice', ['name' => $this->fullName()], $this->getEmailForPasswordReset(), $this->fullName(), 'CataLex | Invoice/Receipt', $pdf);

		unlink($baseName);
		unlink($html);
		unlink($pdf);
	}

	public function addRole($role) {
		if (is_object($role)) {
			$role = $role->getKey();
		}
		elseif (is_string($role)) {
			// Check named roles first to avoid attach collision
			if($this->hasRole($role)) return;
			$role = Role::where('name', '=', $role)->pluck('id');
		}

		$this->roles()->attach($role);
	}

	public function addRoles($roles) {
		foreach($roles as $role) {
			$this->addRole($role);
		}
	}

	public function removeRole($role) {
		if(is_object($role)) {
			$role = $role->getKey();
		}
		elseif(is_string($role)) {
			$role = Role::where('name', '=', $role)->pluck('id');
		}

		$this->roles()->detach($role);
	}

	public function removeRoles($roles) {
		foreach($roles as $role) {
			$this->removeRole($role);
		}
	}

	/**
	 * Return whether the user has the named role.
	 *
	 * @param  string  $role
	 * @return bool
	 */
	public function hasRole($role) {
		foreach($this->roles as $r) {
			if($r->name === $role) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Return whether the user has the requested permission via any of its roles.
	 *
	 * @param  string  $permission
	 * @return bool
	 */
	public function can($permission) {
		foreach($this->roles as $role) {
			foreach($role->permissions as $p) {
				if($p->name === $permission) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Return whether this user and another belong to the same organisation.
	 *
	 * @param  App\User  $other
	 * @return bool
	 */
	public function sharesOrganisation($other) {
		return $this->organisation && $other->organisation && $this->organisation->id === $other->organisation->id;
	}

	/**
	 * Override SoftDelete trait implementation to play nicely with ActiveUser trait.
	 *
	 * @return void
	 */
	protected function runSoftDelete() {
		$query = $this->newQueryWithoutScopes()->where($this->getKeyName(), $this->getKey());

		$this->{$this->getDeletedAtColumn()} = $time = $this->freshTimestamp();

		$query->update(array($this->getDeletedAtColumn() => $this->fromDateTime($time)));
	}

	/**
	 * Make this user active.
	 *
	 * @return void
	 */
	public function activate() {
		$this->active = true;

		$this->save();
	}

	/**
	 * Make this user inactive.
	 *
	 * @return void
	 */
	public function deactivate() {
		$this->active = false;

		$this->save();
	}

	protected function getAllDueBillingItems($service)
	{
		return $service->billingItems()
                       ->where('user_id', '=', $this->id)
                       ->dueForPayment()
                       ->get();
	}
}
