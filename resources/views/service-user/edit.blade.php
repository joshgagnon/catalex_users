@extends('app')

@section('title')
CataLex - Edit Services
@endsection

@section('content')
<div class="container">
    <h2 class="text-center">Edit My Services</h2>
    <div class="row">
        <div class="col-xs-12">
            <div class="panel panel-default">
                <div class="panel-body">
                    @include('components.messages')

                    <form method="POST" role="form" class="form">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <input type="hidden" name="user_id" value="{{ $user->id }}">

                        <div class="form-group text-center">
                            <button type="submit" class="btn btn-primary">Update My Subscriptions</button>
                        </div>


                        <div class="form-group">
                            @foreach($services as $service)
                                <div class="service-well well">
                                    <div class="row">
                                        <div class="col-xs-2">
                                            <label>
                                                <input
                                                    type="checkbox"
                                                    value="{{ $service->id }}"
                                                    name="services[{{ $service->id }}]"
                                                    {{ $service->userHasService || !$service->is_paid_service || isset($_GET[urlencode($service->name)]) ? 'checked' : '' }}
                                                    {{ !$service->is_paid_service ? 'disabled' : '' }}>

                                            </label>
                                        </div>

                                        @if($service->name == 'Good Companies')
                                            <div class="col-xs-10">
                                                <h2><i class="fa fa-briefcase"></i> Good Companies</h2>

                                                <div>
                                                    <p>Takes care of a company’s disclosure and administrative requirements under the Companies Act 1993.</p>
                                                    <p>Good Companies costs just <strong>$12 annually</strong> or <strong>$1.50 monthly</strong>, per company.  Click <a href="https://catalex.nz/good-companies.html" target="_blank">here</a> to see features.</p>
                                                </div>
                                            </div>
                                        @endif

                                        @if($service->name == 'Law Browser')
                                            <div class="col-xs-10">
                                                <h2><i class="fa fa-search"></i> Law Browser</h2>

                                                <div>
                                                    <p>Takes care of a company’s disclosure and administrative requirements under the Companies Act 1993.</p>
                                                    <p>This service is <strong>FREE</strong>, with added enhancements for CataLex users.</p>
                                                </div>
                                            </div>
                                        @endif

                                        @if($service->name == 'CataLex Sign')
                                            <div class="col-xs-10">
                                                <h2><i class="fa fa-search"></i> CataLex Sign Unlimited</h2>

                                                <div>
                                                    <p>Digitally sign documents online.</p>
                                                    <p>CataLex Sign is free for up to 10 signs per month. For unlimited signing, subscribe to CataLex Sign Unlimited for only <strong>$5 per month</strong>!</p>
                                                </div>
                                            </div>
                                        @endif

                                        @if($service->name == 'Working Days')
                                            <div class="col-xs-10">
                                                <h2><i class="fa fa-calendar"></i> Working Days</h2>

                                                <div>
                                                    <p>Calculates legal deadlines based on common definitions of “working day”.</p>
                                                    <p>This service is <strong>FREE</strong>.</p>
                                                </div>
                                            </div>
                                        @endif

                                        @if($service->name == 'ConCat')
                                            <div class="col-xs-10">
                                                <h2><i class="fa fa-copy"></i> ConCat</h2>

                                                <div>
                                                    <p>Concatenates (combines) PDF documents quickly and securely.</p>
                                                    <p>This service is <strong>FREE</strong>.</p>
                                                </div>
                                            </div>
                                        @endif

                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="form-group text-center">
                          <button type="submit" class="btn btn-primary">Update My Subscriptions</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
