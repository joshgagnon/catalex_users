@extends('app')

@section('content')
<div class="container">
    <h2 class="text-center">Card Details</h2>
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-body">
                    @include('components.messages')

                                <p>
                                    Click 'Next' below to continue to our payment processor.
                                </p>
                                <p>
                                    We will validate your card with a $1 authorisation charge, which will be refunded automatically.
                                </p>
                                <p>
                                    In 14 days, we will charge your card based on the number of companies connected to your account on that day, and your chosen billing period.  Additional charges will be made if companies are added to your account within the billing period.
                                </p>
                    <p>All transactions will be billed in New Zealand Dollars.</p>

                       <!-- <div class="billing-period well">
                            <div class="row">
                                <h4><strong>Billing Period</strong></h4>
                                <div class="col-sm-6">
                                    <label>
                                        <input type="radio" name="period" value="monthly" checked }} /> Monthly
                                    </label>
                                </div>
                                <div class="col-sm-6">
                                    <label>
                                        <input type="radio" name="period" value="annually" /> Annually
                                    </label>
                                </div>
                            </div>
                        </div> -->



                        <div class="form-group text-center">
                                <a href="{{ $gatewayURL }}" class="btn btn-primary">Next</a>
                        </div>



        </div>
        </div>
    </div>


    </div>
</div>
@endsection
