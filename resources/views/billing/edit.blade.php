@extends('app')

@section('title')
CataLex - Edit Billing Details
@endsection

@section('content')
<div class="container">
    <h2>Edit Billing Details</h2>
    <div class="row">
        <div class="col-xs-12">
            <div class="panel panel-default">
                <div class="panel-body">
                    @include('components.messages')

                    @if ($billingDetails)

                    <h4>Card</h4>

                    <div class="row">
                        <div class="col-xs-12">
                            <a href="#">-&nbsp;&nbsp;Remove Card</a>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-xs-12">
                            <h4>Billing period</h4>

                            <form method="POST" role="form" class="form" action="{{ route('billing.update') }}">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <input type="hidden" name="user_id" value="{{ $user->id }}">

                                <p>Your billing day is the {!! Helper::ordinal($billingDetails->billing_day) !!} day of the month.</p>
                                <div class="form-group">
                                 <div class="row">
                                    <div class="col-xs-3 ">
                                            <label>
                                                <input type="radio" name="period" value="monthly"  {{ $billingDetails->period == 'monthly' ? 'checked' : '' }} />
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

                                <div class="form-group text-center">
                                    <button type="submit" class="btn btn-primary">Save Changes</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    @else

                    <h4>Card</h4>

                    <div class="form-group">
                        <div class="col-xs-12">
                            <a href="#">+&nbsp;&nbsp;Add Card</a>
                        </div>
                    </div>

                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
