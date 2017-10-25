@extends('app')

@section('title')
    CataLex - Edit Billing Details
@endsection

@section('content')
    <div class="container">

        @if (!$subscriptionUpToDate)
            <div class="alert alert-danger">
                <h3 class="alert-heading">Your last bill failed.</h3>

                @if ($billingDetails)
                    <p>Please click the button below to retry billing. If that fails, please check your card details.</p>

                    <a href="{{ route('billing.retry') }}" class="btn btn-danger">Retry Billing</a>
                @else
                    <p>Please add a card below, so regain access to paid CataLex services.</p>
                    <p>Once you add your new card, we will retry the failed bill and you will be able to access your paid CataLex services.</p>
                @endif
            </div>
        @endif

        @include('components.messages')

        <h2>Billing Options</h2>

        <div class="row">
            <div class="col-xs-12">
                <div class="panel panel-default">
                    <div class="panel-body">
                        @if ($billingDetails)
                            <div class="row">
                                <div class="col-xs-12">
                                    @if ($cardDetails)
                                        @if ($cardDetails->masked_card_number)
                                            <h4>Card: {{ $cardDetails->masked_card_number }}</h4>
                                        @else
                                            <h4>Card</h4>
                                        @endif

                                        <form method="POST" role="form" class="form" action="{{ route('billing.delete') }}">
                                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                            <input type="hidden" name="_method" value="DELETE">

                                            <button type="submit" class="btn btn-danger btn-xs">Remove Card</button>
                                        </form>
                                    @else
                                        <h4>Payments</h4>

                                        <a href="{{ route('billing.register-card') }}" class="btn btn-danger btn-xs">+&nbsp;&nbsp;Add Card</a>
                                    @endif
                                </div>

                                <div class="col-xs-12">
                                    <hr/>

                                    <h4>Billing Period</h4>

                                    <form method="POST" role="form" class="form" action="{{ route('billing.update') }}">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <input type="hidden" name="user_id" value="{{ $user->id }}">

                                        <p>Your billing day is the {!! Helper::ordinal($billingDetails->billing_day) !!} day of the month.</p>

                                        <div class="form-group">
                                            <div class="row">
                                                <div class="col-xs-3 ">
                                                    <label>
                                                        <input type="radio" name="period" value="monthly" {{ $billingDetails->period == 'monthly' ? 'checked' : '' }} />
                                                        Monthly
                                                    </label>
                                                </div>
                                                <div class="col-xs-3">
                                                    <label>
                                                        <input type="radio" name="period" value="annually" {{ $billingDetails->period == 'annually' ? 'checked' : '' }}/>
                                                        Annually
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <hr />

                                        <div class="form-group">
                                            <button type="submit" class="btn btn-primary">Save Changes</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
