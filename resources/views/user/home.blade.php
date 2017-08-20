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
        <div class="container">
            <h2 class="page-title">CataLex Home</h2>
        </div>

        <div class="row">
            <div class="col-md-8">
            <div class="row">
            <div class="col-sm-6">
                <div class="user-menu-item panel panel-default ">
                    <div class="top-menu">
                        <a href="{{ route('index') }}">
                            <i class="fa fa-briefcase"></i>

                            <h4>CataLex Services</h4>
                        </a>
                    </div>

                    <div class="bottom-menu">
                        <div class="section summary">
                            <span class="sub-title">Access CataLex services</span>
                        </div>

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
                                    <span class="sub-title">Maintain legally compliant companies</span>
                                </span>
                            </span>
                        </a>

                        <a href="{{ route('sign-login') }}" class="section">
                            <span class="with-icon">
                                <i class="fa fa-pencil" aria-hidden="true"></i>

                                <span class="title">
                                    <span class="main-title">CataLex Sign</span>
                                    <span class="sub-title">Sign legal documents online</span>
                                </span>
                            </span>
                        </a>

                        <a href="http://workingdays.catalex.nz/" class="section">
                            <span class="with-icon">
                                <i class="fa fa-calendar"></i>

                                <span class="title">
                                    <span class="main-title">Working Days</span>
                                    <span class="sub-title">Calculate legal deadlines</span>
                                </span>
                            </span>
                        </a>

                        <a href="https://concat.catalex.nz/" class="section">
                            <span class="with-icon">
                                <i class="fa fa-copy"></i>

                                <span class="title">
                                    <span class="main-title">ConCat</span>
                                    <span class="sub-title">Combine PDF documents</span>
                                </span>
                            </span>
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-sm-6">
                <div class="user-menu-item panel panel-default ">
                    <div class="top-menu">
                        <a href="{{ route('user.profile') }}">
                            <i class="fa fa-id-card"></i>

                            <h4>My Account</h4>
                        </a>
                    </div>

                    <div class="bottom-menu">
                        <div class="section summary">
                            <span class="sub-title">Manage your account and subscriptions</span>
                        </div>

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
            </div>
            </div>

            <div class="col-md-4 col-sm-6">
                <div class="user-menu-item panel panel-default ">
                    <div class="top-menu">
                        <a href="{{ route('organisation.index') }}">
                            <i class="fa fa-users"></i>

                            <h4>Organisation</h4>
                        </a>
                    </div>

                    <div class="bottom-menu">
                        <div class="section summary">
                            <span class="sub-title">Create and manage your organisation</span>
                        </div>

                        <a href="{{ route('organisation.index') }}" class="section ">
                            <span class="with-icon">
                                <i class="fa fa-user-plus"></i>

                                <span class="title">
                                    <span class="main-title">Organisation Settings</span>
                                    <span class="sub-title">Invite and view organisation members </span>
                                </span>
                            </span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
