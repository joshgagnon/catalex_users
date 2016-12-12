@extends('app')

@section('title')
CataLex Law Browser - Edit Services
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

                        <div class="form-group">
                            @foreach($services as $service)
                                <div class="service-well well ">
                                <div class="row">
                                    <div class="col-xs-2">
                                    <label>
                                        <input
                                            type="checkbox"
                                            value="{{ $service->id }}"
                                            name="services[{{ $service->id }}]"
                                            {{ $service->userHasService || !$service->is_paid_service ? 'checked' : '' }}
                                            {{ !$service->is_paid_service ? 'disabled' : '' }}>

                                    </label>
                                    </div>
                                    @if($service->name == 'Good Companies')
                                        <div class="col-xs-10">
                                            <h2>
                                            <i class="fa fa-briefcase"></i>
                                             {{ $service->name }}
                                            </h2>
                                        <div>
                                        <p>Takes care of a company’s disclosure and administrative requirements under the Companies Act 1993.</p>
                                        <p>Click <a href="https://catalex.nz/good-companies.html" target="_blank">here</a> to see features and pricing.</p>
                                    @endif

                                    @if($service->name == 'Law Browser')
                                        <div class="col-xs-10">
                                            <h2>
                                                     <i class="fa fa-search"></i>


                                            {{ $service->name }}
                                            </h2>
                                        <div>

                                        <p>Takes care of a company’s disclosure and administrative requirements under the Companies Act 1993.</p>
                                        <p>This service is <strong>FREE</strong>, with added enhancements for CataLex users.</p>
                                    @endif

                                    @if($service->name == 'Working Days')
                                        <div class="col-xs-10">
                                            <h2>
                                                 <i class="fa fa-calendar"></i>
                                            {{ $service->name }}
                                            </h2>
                                        <div>

                                        <p>Calculates legal deadlines based on common definitions of “working day”.</p>
                                        <p>This service is <strong>FREE</strong>.</p>
                                    @endif

                                    @if($service->name == 'ConCat')
                                        <div class="col-xs-10">
                                            <h2>
                                                 <i class="fa fa-copy"></i>
                                            {{ $service->name }}
                                            </h2>
                                        <div>

                                        <p>Concatenates (combines) PDF documents quickly and securely.</p>
                                        <p>This service is <strong>FREE</strong>.</p>
                                    @endif

                                    </div>
                                    </div>

                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="form-group text-center">
                            <button type="submit" class="btn btn-primary">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
