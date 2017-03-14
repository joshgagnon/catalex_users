<?php

namespace App\Library;

use App\BillingDetail;
use GuzzleHttp\Client;
use Log;
use Omnipay\Omnipay;

class PXPay
{
    const CURRENCY_NZD = 'NZD';

    public function requestPayment($billable, $totalDollars) {
        // If billing is disabled, log and leave
        if (env('DISABLE_PAYMENT', false)) {
            $billableType = $billable instanceof User ? 'User' : 'Organisation';
            Log::info('Simulated charge of $' . $totalDollars . ' to type: ' . $billableType . ', id: ' . $billable->id);

            return true;
        }

        // If the billing amount is nothing, don't bother billing
        if ($totalDollars == '0.00') {
            return true;
        }

        // Get the user or organisation's billing details
        $billingDetails = $billable->billing_detail()->first();

        // Check the user has billing details
        if (!$billingDetails) {
            $billableType = $billable instanceof User ? 'user' : 'organisation';
            Log::error('Tried to bill ' . $billableType . ' with id ' . $billable->id . ', but failed because they have no billing details');
            
            // Failed
            return false;
        }

        // Build the XML and send the request
        $xmlRequest = $this->buildPaymentRequestXML($totalDollars, $billingDetails);
        $success = $this->sendPaymentRequest($xmlRequest);

        // Return the successfulness
        return $success;
    }

    protected function sendPaymentRequest($xmlRequest)
    {
        $postClient = new Client(['base_uri' => 'https://sec.paymentexpress.com']);
        $response = $postClient->post('pxpost.aspx', ['body' => $xmlRequest]);
        $xmlResponse = new \SimpleXMLElement((string)$response->getBody());

        $success = boolval((string)$xmlResponse->Success);

        return $success;
    }

    protected function buildPaymentRequestXML($totalDollars, $billingDetails)
    {
        $username = env('PXPOST_USERNAME');
        $key = env('PXPOST_KEY');

        if (!$username || !$key) {
            throw new \Exception('PXPost username and password are required. Please check they are setup in the .env file.');
        }

        return view('billing.pxpost', [
            'postUsername' => $username,
            'postPassword' => $key,
            'amount' => $totalDollars,
            'dpsBillingId' => $billingDetails->dps_billing_token,
            'id' => $billingDetails->id,
        ])->render();
    }

    public static function getGateway()
    {
        $username = env('PXPAY_USERNAME');
        $key = env('PXPAY_KEY');

        if (!$username || !$key) {
            throw new \Exception('PXPay username and password are required. Please check they are setup in the .env file.');
        }

        $gateway = Omnipay::create('PaymentExpress_PxPay');
        $gateway->setUsername($username);
        $gateway->setPassword($key);

        return $gateway;
    }
}
