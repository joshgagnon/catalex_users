@extends('app')

@section('content')
<div class="container">
    <h2 class="text-center">Card Details</h2>
    <div class="row">
        <div class="col-xs-12 col-md-8  col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-body">
                    @include('components.messages')
                    <form class="form-horizontal" role="form" method="POST" action="{{ route('billing.finish-create-card') }}">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">

                        <div class="row">
                            <div class="col-md-8 col-md-offset-2">
                                <p>
                                    Please enter your credit or debit card details.
                                </p>
                                <p>
                                    We will validate your card with a $1 authorisation charge, which will be refunded automatically.
                                </p>
                                <p>
                                    In 14 days, we will charge your card based on the number of companies connected to your account on that day, and your chosen billing period.  Additional charges will be made if companies are added to your account within the billing period.
                                </p>
                                <p>
                                    Please click the Next button once your card has been authorised.
                                </p>
                                {{-- TODO: Look into way to force redirect without clicking next, dps docs suggest this is possible --}}
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-xs-12">

                                    <iframe  style="width: 100%;  height: 766px; " src="{{ $gatewayURL }}"></iframe>

                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-8 col-md-offset-2 text-center">
                                <p>All transactions will be billed in New Zealand Dollars.</p>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-md-8 col-md-offset-2 text-center">
                                <button type="submit" class="btn btn-primary">Done</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
