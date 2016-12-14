@extends('app')

@section('content')
<div class="container">
    <h2 class="text-center">Card Details</h2>
    <div class="row">
        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-body">
                    @include('components.messages')
                    <form class="form-horizontal payment-form" role="form" method="POST" action="{{ route('billing.finish-create-card') }}">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <p>
                                    Please enter your credit or debit card details.
                                </p>
                                <p>
                                    We will validate your card with a $1 authorisation charge, which will be refunded automatically.
                                </p>
                                <p>
                                    In 14 days, we will charge your card based on the number of companies connected to your account on that day, and your chosen billing period.  Additional charges will be made if companies are added to your account within the billing period.
                                </p>


                        <div class="billing-period well">
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
                        </div>

                        <p>All transactions will be billed in New Zealand Dollars.</p>

                        <div class="form-group text-center">
                                <button type="submit" class="btn btn-primary">Done</button>
                        </div>


        </form>
        </div>
        </div>
    </div>
        <div class="col-md-6">
            <div class="panel panel-default">
                <iframe  style="width: 100%;  height: 766px; " src="{{ $gatewayURL }}"></iframe>
            </div>
        </div>

    </div>
</div>
@endsection
