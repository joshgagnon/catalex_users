<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;

class PXPay extends \App\Library\PXPay
{
    public $paymentRequestSent = false;

    /**
     * Make this function public for testing
     */
    public function buildPaymentRequestXML($totalDollars, $cardToken, $billingDetailId)
    {
        return parent::buildPaymentRequestXML($totalDollars, $cardToken, $billingDetailId);
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
        $billingDetailId = 1;
        $totalDollars = '420.00';
        $username = env('PXPOST_USERNAME');
        $key = env('PXPOST_KEY');
        $currency = 'NZD';
        $txnType = 'Purchase';

        $pxPay = new PXPay();
        $xml = $pxPay->buildPaymentRequestXML($totalDollars, $dpsBillingToken, $billingDetailId);
        $xml = new \SimpleXMLElement($xml);

        $this->assertEquals($username, $xml->PostUsername);
        $this->assertEquals($key, $xml->PostPassword);
        $this->assertEquals($totalDollars, $xml->Amount);
        $this->assertEquals($currency, $xml->InputCurrency);
        $this->assertEquals($txnType, $xml->TxnType);
        $this->assertEquals($dpsBillingToken, $xml->DpsBillingId);
        $this->assertEquals('CataLex Ltd - ID ' . $billingDetailId, $xml->MerchantReference);
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
        $this->createCardDetails($billingDetails->id);
        $user = $this->createUser(['billing_detail_id' => $billingDetails->id]);

        $pxPay = new PXPay();
        $success = $pxPay->requestPayment($user, $totalDollars);

        $this->assertTrue($success);
        $this->assertTrue($pxPay->paymentRequestSent);
    }

    /**
     * @test
     */
    public function requestPaymentForUserWithoutBillingDetails()
    {
        if (env('DISABLE_PAYMENT')) {
            throw new \Exception('Payment must be enabled to run payment tests.');
        }

        $totalDollars = '413.27';

        $user = $this->createUser();

        $pxPay = new PXPay();
        $success = $pxPay->requestPayment($user, $totalDollars);

        $this->assertFalse($success);
        $this->assertFalse($pxPay->paymentRequestSent);
    }
}
