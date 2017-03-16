<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Library\Billing;

class ChargeLog extends Model
{
	const CREATED_AT = 'timestamp';

	// We don't have an updated timestamp, so turn off timestamps and manually set the created at timestamp below
	public $timestamps = false;

    protected $fillable = ['success', 'pending', 'user_id', 'organisation_id', 'total_amount', 'gst'];

	protected $dates = ['timestamp'];

	public static function boot()
    {
		// manually set the created at timestamp below
	    static::creating( function ($model) {
	        $model->setCreatedAt($model->freshTimestamp());
	    });
	}

	public function user() {
		return $this->belongsTo('App\User');
	}

	public function organisation() {
		return $this->belongsTo('App\Organisation');
	}

	public function billingItemPayments()
	{
		return $this->hasMany(BillingItemPayment::class);
	}

	public function itemSummary()
    {
        $billingSummary = [];

        $billingItems = $this->billingItemPayments()->with('billingItem')->get();

        foreach ($billingItems as $item) {
            $billingSummary[] = [
                'description' =>  json_decode($item->billingItem->json_data, true)['company_name'],
                'paidUntil' => $item->paid_until->format('j M Y'),
                'amount' => $item->amount ? Billing::centsToDollars($item->amount) : null,
            ];
        }

        return $billingSummary;
    }

    public function renderInvoice($recipientName=null)
    {
        $organisation = $this->organisation;
        $accountNumber = $organisation ? $organisation->accountNumber() : $this->user->accountNumber();

        $invoice = view('emails.invoice-attachment')->with([
            'orgName' => $organisation ? $organisation->name : null,
            'name' => $recipientName ? : 'CataLex User',
            'date' => $this->timestamp->format('j/m/Y'),
            'invoiceNumber' => $this->id,
            'totalAmount' => $this->total_amount,
            'gst' => $this->gst,
            'accountNumber' => $accountNumber,
            'listItems' => $this->itemSummary(),
        ]);

        return $invoice->render();
    }
}
