<?php

use App\BillingItem;
use App\Library\Billing;
use App\Service;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class InvoiceControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected $seeder = 'DatabaseSeeder';

    /**
     * @test
     */
    public function renderInvoice()
    {
        // Create user and login
        $user = $this->createUserWithBilling();
        Auth::login($user);

        // Create some billing items and bill the user to create a charge log
        $gcService = Service::where('name', 'Good Companies')->first();

        BillingItem::create(['user_id' => $user->id, 'item_id' => 1, 'json_data' => json_encode(['company_name' => 'test company 1']), 'active' => true, 'service_id' => $gcService->id, 'item_type' => 'gc_company']);
        BillingItem::create(['user_id' => $user->id, 'item_id' => 2, 'json_data' => json_encode(['company_name' => 'test company 2']), 'active' => true, 'service_id' => $gcService->id, 'item_type' => 'gc_company']);
        BillingItem::create(['user_id' => $user->id, 'item_id' => 3, 'json_data' => json_encode(['company_name' => 'test company 3']), 'active' => true, 'service_id' => $gcService->id, 'item_type' => 'gc_company']);

        $user->bill();

        $chargeLog = $user->chargeLogs()->first();
        $itemPayments = $chargeLog->billingItemPayments()->with('billingItem')->get();

        $totalAmount = 0;

        // Change each of the item payments so they are different on the invoice and we can check each record is there
        foreach ($itemPayments as $index => $payment) {
            $totalAmount = $index + 1;
            $payment->amount = ($index + 1) . '.00';
            $payment->gst = Billing::includingGst($payment->amount);
            $payment->paid_until = Carbon::now()->addDays($index + 1);

            $payment->save();
        }

        // Recalculate the amount on the charge log
        $payment->total_amount = $totalAmount + '.00';
        $payment->gst = Billing::includingGst($payment->total_amount);

        // Render the invoice and check the basics - amount, gst, account number, and date
        $this->visit('/billing')
            ->click('View Invoice')
            ->see('$' . $chargeLog->total_amount)
            ->see('$' . $chargeLog->gst)
            ->see($chargeLog->timestamp->format('d/m/Y'))
            ->see($user->accountNumber());

        // Check each payment is displayed as list items
        foreach ($itemPayments as $payment) {
            $billingItemData = json_decode($payment->billingItem->json_data);

            $this->see($billingItemData->company_name)
                ->see($payment->amount)
                ->see($payment->paid_until->format('j M Y'));
        }
    }
}
