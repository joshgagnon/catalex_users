@extends('app')

@section('content')

<div class="container">
    @include('components.messages')
</div>

<div class="container">
    @include('billing.billing-alert')
</div>

<div class="services container">

    <div class="row">
        <div class="col-md-6">
            <div class="service">
                <a href="{{ route('browser-login') }}">
                    <i class="fa fa-search"></i>

                    <h4>Law Browser</h4>
                    <p>Find law faster and easier than ever before. Definition recognition, automatic cross-references, side-by-side browsing, and more!</p>
                 </a>
            </div>
        </div>

        <div class="col-md-6">
            @if ($user->hasGoodCompaniesAccess())

                @if ($subscriptionUpToDate)
                    <div class="service">
                        <a href="{{ route('good-companies-login') }}">
                            <i class="fa fa-briefcase"></i>
                            <h4>Good Companies</h4>
                            <p>Takes care of a company’s disclosure and administrative requirements under the Companies Act 1993.</p>
                        </a>
                    </div>
                @else
                    <div class="disabled-service">
                        <a href="{{ route('good-companies-login') }}">
                            <i class="fa fa-briefcase"></i>
                            <h4>Good Companies</h4>
                            <p>Takes care of a company’s disclosure and administrative requirements under the Companies Act 1993.</p>
                        </a>

                        @if ($user->can('edit_own_organisation') || !$user->organisation)
                            <p class="dont-gray">Your last bill failed. Please click below to update your billing details, before you can access Good Companies.</p>
                            <a class="dont-gray btn btn-danger" href="{{ route('billing.edit') }}">Update Billing</a>
                        @else
                            <p class="dont-gray">You are unable to access Good Companies at the moment. Please contact one of your organisation's administrators to regain access.</p>
                        @endif
                    </div>
                @endif
            @else
                <div class="disabled-service">
                    <div>
                        <i class="fa fa-briefcase"></i>

                        <h4>Good Companies</h4>
                        <p>Takes care of a company’s disclosure and administrative requirements under the Companies Act 1993.</p>
                    </div>

                    <p><a href="{{ route('user-services.index', array(urlencode('Good Companies') => 1)) }}">Click here to subscribe</a></p>
                    <p><a href="https://catalex.nz/good-companies.html" target="_blank">Click here for features and pricing</a></p>
                </div>
            @endif
        </div>

    </div>


    <div class="row">
        <div class="col-md-6">
            <div class="service">
            <a href="http://workingdays.catalex.nz/">
                 <i class="fa fa-calendar"></i>
                <h4>Working Days</h4>
                <p>
                    Calculates legal deadlines based on common definitions of “working day”. 
                </p>
                 </a>
            </div>
        </div>

        <div class="col-md-6">
            <div class="service">
            <a href="https://concat.catalex.nz/">
                 <i class="fa fa-copy"></i>
                <h4>ConCat</h4>
                <p>
                    Concatenates (combines) PDF documents quickly and securely.
                </p>
                </a>
            </div>
        </div>

    </div>

</div>

@endsection
