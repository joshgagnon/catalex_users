@extends('app')

@section('title')
CataLex - Select Billing Period
@endsection

@section('content')
<div class="container">
    <h2>Select Billing Period</h2>
    <div class="row">
        <div class="col-xs-12">
            <div class="panel panel-default">
                <div class="panel-body">
                    <div class="row">
                        <div class="col-xs-12">
                            <h4>Billing period</h4>

                            <form method="POST" role="form" class="form" action="{{ route('billing.move-to-create-card') }}">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <input type="hidden" name="user_id" value="{{ $user->id }}">
                                
                                <div class="form-group">
                                    <div class="col-xs-12">
                                        <label>
                                            <input type="radio" name="period" value="monthly" checked /> Monthly
                                        </label>
                                        <label>
                                            <input type="radio" name="period" value="annually" /> Annually
                                        </label>
                                    </div>
                                </div>

                                <div class="form-group text-center">
                                    <button type="submit" class="btn btn-primary">Next</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
