<?php

namespace App;

use App\Library\Mail;
use App\Library\PhantomJS;
use Illuminate\Database\Eloquent\Model;

class ChargeLog extends Model
{
    const CREATED_AT = 'timestamp';

    const SUCCESSFUL = 'successful';
    const PENDING = 'pending';
    const FAILED = 'failed';

    // We don't have an updated timestamp, so turn off timestamps and manually set the created at timestamp below
    public $timestamps = false;

    protected $fillable = ['success', 'pending', 'user_id', 'organisation_id', 'total_amount', 'gst', 'discount_percent', 'total_before_discount'];

    protected $dates = ['timestamp'];

    public static function boot()
    {
        // manually set the created at timestamp below
        static::creating(function ($model) {
            $model->setCreatedAt($model->freshTimestamp());
        });
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function organisation()
    {
        return $this->belongsTo('App\Organisation');
    }

    public function billingItemPayments()
    {
        return $this->hasMany(BillingItemPayment::class);
    }

    public function status()
    {
        if ($this->pending) {
            return self::PENDING;
        }

        if ($this->success) {
            return self::SUCCESSFUL;
        }

        return self::FAILED;
    }

    public function itemSummary()
    {
        $billingSummary = [];

        $billingItems = $this->billingItemPayments()->with('billingItem')->get();

        foreach ($billingItems as $item) {
            $itemType = $item->billingItem->item_type;
            $itemData = json_decode($item->billingItem->json_data, true);

            $billingSummary[] = [
                'description' => BillingItem::description($itemType, $itemData),
                'paidUntil'   => $item->paid_until->format('j M Y'),
                'amount'      => $item->amount ?: null,
            ];
        }

        return $billingSummary;
    }

    /**
     * Render the invoice for a charge log as HTML
     *
     * @param null $recipientName
     * @return string
     */
    public function renderInvoice($recipientName = null)
    {
        $organisation = $this->organisation;
        $accountNumber = $organisation ? $organisation->accountNumber() : $this->user->accountNumber();

        $invoice = view('emails.invoice-attachment')->with([
            'orgName'             => $organisation ? $organisation->name : null,
            'name'                => $recipientName ?: 'CataLex User',
            'date'                => $this->timestamp->format('d/m/Y'),
            'invoiceNumber'       => $this->id,
            'totalAmount'         => $this->total_amount,
            'gst'                 => $this->gst,
            'accountNumber'       => $accountNumber,
            'listItems'           => $this->itemSummary(),
            'discountPercent'     => $this->discount_percent,
            'discountAmount'      => bcsub($this->total_before_discount, $this->total_amount, 2),
            'totalBeforeDiscount' => $this->total_before_discount,
        ]);

        return $invoice->render();
    }

    /**
     * Generate an invoice from this charge log
     *
     * @param null $recipientName
     * @return string
     */
    public function generateInvoice($recipientName = null)
    {
        $invoiceHtml = $this->renderInvoice($recipientName);
        $pdfPath = PhantomJS::htmlToPdf($invoiceHtml);

        return $pdfPath;
    }

    /**
     * Email this charge log as an invoice to all people who should receive it
     *
     * @return array
     */
    public function sendInvoices()
    {
        // Get the users to send the invoice to
        $users = $this->organisation ? $this->organisation->invoiceableUsers() : [$this->user];

        // Send all users a copy of the invoice
        foreach ($users as $user) {
            // Create a PDF version of the invoice - needs to be recreated for each user because the recipient name changes
            $pdfPath = $this->generateInvoice($user->fullName());

            // Queue the invoice to be sent to the current user
            Mail::queueStyledMail('emails.invoice', ['name' => $user->fullName()], $user->email, $user->fullName(), 'CataLex | Invoice/Receipt', [['path' => $pdfPath, 'name' => 'Invoice.pdf']]);
        }

        return $users;
    }

    /**
     * Send failed billing notice all users responsible for this charge log
     *
     * @return bool
     */
    public function sendFailedNotice()
    {
        if ($this->status() !== ChargeLog::FAILED) {
            return false;
        }

        // Get the users responsible for this charge log
        $users = $this->organisation ? $this->organisation->invoiceableUsers() : [$this->user];

        // Send all users a notice that the bill failed
        foreach ($users as $user) {
            Mail::queueStyledMail('emails.billing-failed', ['name' => $user->fullName()], $user->email, $user->fullName(), 'CataLex | Bill Failed');
        }

        return true;
    }
}
