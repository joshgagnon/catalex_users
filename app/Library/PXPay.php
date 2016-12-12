<?php

namespace App\Library;

use App\BillingDetail;
use GuzzleHttp\Client;
use Log;
use Omnipay\Omnipay;

class PXPay
{
    const CURRENCY_NZD = 'NZD';

    public static function requestPayment($billable, $totalDollars) {
        if (env('DISABLE_PAYMENT', false)) {
            $billableType = $billable instanceof User ? 'User' : 'Organisation';
            Log::info('Simulated charge of $' . $totalDollars . ' to type: ' . $billableType . ', id: ' . $billable->id);

            return true;
        }

        if ($totalDollars == '0.00') {
            return true;
        }

        // Get the user or organisation's billing details
        $billingDetails = $billable->billing_detail()->first();

        // No billing detail = something has gone terribly wrong
        if (!$billingDetails) {
            throw new \Exception('Billable must have billing details set before requesting payment');
        }

        // Build the XML to send to PXPost
        $xmlRequest = $this->buildPaymentRequestXML($totalDollars, $billingDetails);

        // Send the request to the PXPost API
        $postClient = new Client(['base_uri' => 'https://sec.paymentexpress.com']);
        $response = $postClient->post('pxpost.aspx', ['body' => $xmlRequest]);
        $xmlResponse = new \SimpleXMLElement((string)$response->getBody());

        // Check if the payment request was successful
        $success = boolval((string)$xmlResponse->Success);

        // Return the successfulness
        return $success;
    }

    private function buildPaymentRequestXML($totalDollars, $billingDetails)
    {
        $xmlRequest = view('billing.pxpost', [
            'postUsername' => env('PXPOST_USERNAME'),
            'postPassword' => env('PXPOST_KEY'),
            'amount' => $totalDollars,
            'dpsBillingId' => $billingDetails->dps_billing_token,
            'id' => $billingDetails->id,
        ])->render();

        return $xmlRequest;
    }

    public static function getGateway()
    {
        $gateway = Omnipay::create('PaymentExpress_PxPay');
        $gateway->setUsername(env('PXPAY_USERNAME'));
        $gateway->setPassword(env('PXPAY_KEY'));

        return $gateway;
    }
}
