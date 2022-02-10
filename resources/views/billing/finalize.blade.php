@extends('app')

@section('content')
<div class="container">
    <h2 class="text-center">Card Details</h2>
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-body">
                    @include('components.messages')
                    <form class="form-horizontal payment-form" role="form" method="POST" action="{{ route('billing.finish-create-card') }}">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <p>Please select your desired billing period:</p>
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


                        <div class="form-group text-center">
                                <button type="submit" class="btn btn-primary">Done</button>
                        </div>


        </form>
        </div>
        </div>
    </div>


    </div>
</div>
@endsection
