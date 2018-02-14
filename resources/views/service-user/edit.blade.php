@extends('app')

@section('title')
CataLex - Edit Services
@endsection

<?php
    $showQuickLinks = false;

    foreach ($services as $service) {
        if ($service->is_paid_service && $service->userHasService) {
            $showQuickLinks = true;
        }
    }
?>

@section('content')
<div class="container">
    @include('components.messages')
    <h2 class="text-center">Edit My Services</h2>

    @if ($showQuickLinks)
        <div class="row">
            <div class="col-xs-12">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <h3>Quick Links</h3>
                        <hr />

                        <div style="padding-left: 25px;">
                            @foreach ($services as $service)
                                @if ($service->userHasService && $service->is_paid_service)
                                    @if ($service->name === 'Good Companies')
                                        <div>Go to <a href="{{ route('good-companies-login') }}">Good Companies</a></div>
                                    @elseif ($service->name === 'CataLex Sign')
                                        <div>Go to <a href="{{ route('sign-login') }}">CataLex Sign</a></div>
                                    @endif
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="row">
        <div class="col-xs-12">
            <div class="panel panel-default">
                <div class="panel-body">

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
                                                <h2><i class="fa fa-search"></i> CataLex Sign</h2>

                                                <div>
                                                    <p>Sign legal documents online.</p>
                                                    <p>CataLex Sign is free for up to 3 signs per month. For unlimited signing, subscribe to CataLex Sign for only <strong>$6 per month</strong> ($60 annually)!</p>
                                                </div>
                                            </div>
                                        @endif

                                        @if($service->name == 'Court Costs')
                                            <div class="col-xs-10">
                                                <h2><i class="fa fa-search"></i> Court Costs</h2>

                                                <div>
                                                    <p>Calculate court costs and create formatted costs schedules.</p>
                                                    <p>Court Costs is only <strong>$5 per month</strong> ($50 annually)!</p>
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
