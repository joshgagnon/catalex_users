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

        $billingDetails = $billable->billing_detail()->first();

        if (!$billingDetails) {
            throw new \Exception('Billable must have billing details set before requesting payment');
        }

        $xmlRequest = view('billing.pxpost', [
            'postUsername' => env('PXPOST_USERNAME'),
            'postPassword' => env('PXPOST_KEY'),
            'amount' => $totalDollars,
            'dpsBillingId' => $billingDetails->dps_billing_token,
            'id' => $billingDetails->id,
        ])->render();

        Log::info($xmlRequest);

        // $postClient = new Client(['base_uri' => 'https://sec.paymentexpress.com']);
        // $response = $postClient->post('pxpost.aspx', ['body' => $xmlRequest]);
        // $xmlResponse = new \SimpleXMLElement((string)$response->getBody());

        // $success = boolval((string)$xmlResponse->Success);

        return true; //$success;
    }

    public static function getGateway()
    {
        $gateway = Omnipay::create('PaymentExpress_PxPay');
        $gateway->setUsername(env('PXPAY_USERNAME'));
        $gateway->setPassword(env('PXPAY_KEY'));

        return $gateway;
    }
}
