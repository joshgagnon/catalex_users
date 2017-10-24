<?php

namespace App\Library;

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
        $cardDetails = $billingDetails ? $billingDetails->cardDetail()->first() : null;

        // Check the user has card details
        if (!$cardDetails) {
            $billableType = $billable instanceof User ? 'user' : 'organisation';
            Log::error('Tried to bill ' . $billableType . ' with id ' . $billable->id . ', but failed because they have no card details');
            
            // Failed
            return false;
        }

        // Build the XML and send the request
        $xmlRequest = $this->buildPaymentRequestXML($totalDollars, $cardDetails->card_token, $billingDetails->id);
        $success = $this->sendPaymentRequest($xmlRequest);

        // Return the successfulness
        return $success;
    }

    protected function sendPaymentRequest($xmlRequest)
    {
        $postClient = new Client(['base_uri' => 'https://sec.paymentexpress.com']);
        $response = $postClient->post('pxpost.aspx', ['body' => $xmlRequest]);
        $responseBody = $response->getBody();
        $xmlResponse = new \SimpleXMLElement((string)$responseBody);

        $success = boolval((string)$xmlResponse->Success);

        // If not successful, log the response
        if (!$success) {
            Log::error('');
            Log::error('-----------');
            Log::error('Failed PXPOST request. Response below.');
            Log::error($responseBody);
            Log::error('-----------');
            Log::error('');
        }

        return $success;
    }

    protected function buildPaymentRequestXML($totalDollars, $cardToken, $billingDetailId)
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
            'dpsBillingId' => $cardToken,
            'id' => $billingDetailId,
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
