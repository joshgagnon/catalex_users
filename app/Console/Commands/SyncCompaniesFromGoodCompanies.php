<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use GuzzleHttp\Client;
use App\BillingItem;
use App\Service;
use App\User;
use Log;

class SyncCompaniesFromGoodCompanies extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gc:sync-companies';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync billing items with companies in Good Companies';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->log('');
        $this->log('-------------------------------');
        $this->log('-- Running sync of GC companies');
        $this->log('');

        // Get the Good Companies service and check we actually got a result
        $service = Service::where('name', '=', 'Good Companies')->first();

        if (!$service) {
            throw new \Exception('Service \'Good Companies\' does not exist in Services');
        }

        // Get a list of all companies
        $companies = $this->getCompanies();

        // Make sure every company from the response is either
        foreach ($companies as $company) {
            // Check the companies user ID is valid
            if (!User::where('id', '=', $company->userId)->exists()) {
                throw new \Exception('User with id \'' . $company->userId . '\' does not exist');
            }

            // This is the JSON data that should be stored for the company
            $companyJsonData = json_encode(['company_name' => $company->companyName]);

            // Get the billing item associated with this company
            $billingItem = BillingItem::forTypeAndId(BillingItem::ITEM_TYPE_GC_COMPANY, $company->companyId);

            if (!$billingItem) {
                // If the billing item does not exist
                $billingItem = new BillingItem([
                    'item_id' => $company->companyId,
                    'item_type' => BillingItem::ITEM_TYPE_GC_COMPANY,
                    'json_data' => $companyJsonData,
                    'active' => $company->active,
                ]);

                $billingItem->user_id = $company->userId;
                $billingItem->service_id = $service->id;
                $billingItem->save();

                $this->log('Created billing item for ' . $company->companyName . ' during Good Companies gc_company sync');
            } else {
                // Billing item exists; make sure it is up-to-date
                if ($billingItem->json_data != $companyJsonData || $billingItem->active != $company->active) {
                    $billingItem->json_data = $companyJsonData;
                    $billingItem->active = $company->active;

                    $billingItem->save();
                }
            }
        }
    }

    /**
     * Extracted to separate method as we may want to override how logging is done during testing
     */
    protected function log($details)
    {
        Log::info($details);
    }

    /**
     * Get a list of all companies in GC
     */
    protected function getCompanies()
    {
        // Hit the Good Companies API and get a list of all companies
        $guzzleClient = new Client(['base_uri' => env('GC_BASE_URI')]);

        $response = $guzzleClient->request('POST', 'api/admin/billing', ['form_params' => ['key' => env('GC_ADMIN_KEY')]]);
        // Get the response content. Decode and return it
        $responseContent = $response->getBody()->getContents();
        $companies = json_decode($responseContent);

        return $companies;
    }
}
