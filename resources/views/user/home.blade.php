@extends('app')

@section('content')

<div class="container">
    @include('components.messages')
</div>

<div class="container">
    @include('billing.billing-alert')
</div>


@if (!$user->organisation && $userHasPendingInvite)
    <div class="container">
        <div class="alert alert-info inline clearfix">
            <a href="{{ route('organisation-invites.index') }}" class="btn btn-info">View invite</a>
            <p>You have a pending organisation invite.</p>
        </div>
    </div>
@endif

<div class="services container">

    <div class="row">
        <div class="col-md-4">
            <div class="user-menu-item panel panel-default ">
                <div class="top-menu">
                <a href="{{ route('index') }}">
                    <i class="fa fa-briefcase"></i>

                    <h4>CataLex Services</h4>
                 </a>
                 </div>
                 <div class="bottom-menu">
                     <div class="section summary"><span class="sub-title">Access CataLex services, including:</span></div>
                     <a href="{{ route('browser-login') }}" class="section ">
                        <span class="with-icon">
                            <i class="fa fa-search"></i>
                            <span class="title">
                                <span class="main-title">Law Browser</span>
                                <span class="sub-title">Find New Zealand law faster</span>
                            </span>
                        </span>
                     </a>
                     <a href="{{ route('good-companies-login') }}" class="section">
                        <span class="with-icon">
                            <i class="fa fa-briefcase"></i>
                            <span class="title">
                                <span class="main-title">Good Companies</span>
                                <span class="sub-title">Takes care of your companys' disclosure and administrative requirements</span>
                                </span>
                        </span>
                    </a>
                    <a href="http://workingdays.catalex.nz/" class="section"><span class="with-icon"><i class="fa fa-calendar"></i><span class="title"><span class="main-title">Working Days</span><span class="sub-title">Calculate legal deadlines</span></span></span></a>
                    <a href="https://concat.catalex.nz/" class="section"><span class="with-icon"><i class="fa fa-copy"></i><span class="title"><span class="main-title">ConCat</span><span class="sub-title">Combine PDF documents quickly and securely</span></span></span></a>

                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="user-menu-item panel panel-default ">
                <div class="top-menu">
                <a href="{{ route('user.profile') }}">
                    <i class="fa fa-id-card"></i>

                    <h4>My Account</h4>
                 </a>
                 </div>
                 <div class="bottom-menu">
                     <div class="section summary"><span class="sub-title"> Set your account details and subscriptions</span></div>
                     <a href="{{ route('user.profile') }}" class="section ">
                        <span class="with-icon">
                            <i class="fa fa-user"></i>
                            <span class="title">
                                <span class="main-title">Account Details</span>
                                <span class="sub-title">Set email address and password</span>
                            </span>
                        </span>
                     </a>
                     <a href="{{ route('user-services.index') }}" class="section">
                        <span class="with-icon">
                            <i class="fa fa-check-square"></i>
                            <span class="title">
                                <span class="main-title">Subscriptions</span>
                                <span class="sub-title">Manage CataLex service subscriptions</span>
                                </span>
                        </span>
                    </a>
                     <a href="{{ route('billing') }}" class="section">
                        <span class="with-icon">
                            <i class="fa fa-credit-card"></i>
                            <span class="title">
                                <span class="main-title">Billing</span>
                                <span class="sub-title">View invoices and manage billing details</span>
                                </span>
                        </span>
                    </a>
                </div>
            </div>
        </div>


        <div class="col-md-4">
            <div class="user-menu-item panel panel-default ">
                <div class="top-menu">
                <a href="{{ route('organisation.index') }}">
                    <i class="fa fa-users"></i>

                    <h4>Organisation</h4>
                 </a>
                 </div>
                 <div class="bottom-menu">
                     <div class="section summary"><span class="sub-title"> Invite and manage others user through a single billing account</span></div>
                     <a href="{{ route('organisation.index') }}" class="section ">
                        <span class="with-icon">
                            <i class="fa fa-user-plus"></i>
                            <span class="title">
                                <span class="main-title">Organisation Settings</span>
                                <span class="sub-title">Invite and view organisations members </span>
                            </span>
                        </span>
                     </a>

                </div>
            </div>
        </div>



    </div>

</div>

@endsection
