<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;

class PXPay extends \App\Library\PXPay
{
    public $paymentRequestSent = false;

    public function buildPaymentRequestXML($totalDollars, $billingDetails)
    {
        return parent::buildPaymentRequestXML($totalDollars, $billingDetails);
    }

    protected function sendPaymentRequest($xmlRequest)
    {
        $this->paymentRequestSent = true;
        return true; // Don't make payment request
    }
}

class PXPayTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @test
     */
    public function buildPaymentRequestXML()
    {
        $dpsBillingToken = '1234123412341234';
        $expiry = '1020';
        $totalDollars = '420.00';
        $username = env('PXPOST_USERNAME');
        $key = env('PXPOST_KEY');
        $currency = 'NZD';
        $txnType = 'Purchase';

        $billingDetails = $this->createBillingDetails([
            'billing_day' => 31,
            'dps_billing_token' => $dpsBillingToken,
            'expiry_date' => $expiry,
        ]);

        $pxPay = new PXPay();
        $xml = $pxPay->buildPaymentRequestXML($totalDollars, $billingDetails);
        $xml = new \SimpleXMLElement($xml);

        $this->assertEquals($username, $xml->PostUsername);
        $this->assertEquals($key, $xml->PostPassword);
        $this->assertEquals($totalDollars, $xml->Amount);
        $this->assertEquals($currency, $xml->InputCurrency);
        $this->assertEquals($txnType, $xml->TxnType);
        $this->assertEquals($dpsBillingToken, $xml->DpsBillingId);
        $this->assertEquals('CataLex Ltd - ID ' . $billingDetails->id, $xml->MerchantReference);
    }

    /**
     * @test
     */
    public function requestPayment()
    {
        if (env('DISABLE_PAYMENT')) {
            throw new \Exception('Payment must be enabled to run payment tests.');
        }

        $totalDollars = '327.00';

        $billingDetails = $this->createBillingDetails();
        $user = $this->createUser(['billing_detail_id' => $billingDetails->id]);

        $pxPay = new PXPay();
        $success = $pxPay->requestPayment($user, $totalDollars);

        $this->assertTrue($success);
        $this->assertTrue($pxPay->paymentRequestSent);
    }
}
