@extends('app')

@section('title')
CataLex Law Browser - Edit Services
@endsection

@section('content')
<div class="container">
    <h2>Edit My Services</h2>
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
                                <div class="checkbox">
                                    <label>
                                        <input
                                            type="checkbox"
                                            value="{{ $service->id }}"
                                            name="services[{{ $service->id }}]"
                                            {{ $service->userHasService ? 'checked' : '' }}>
                                        {{ $service->name }}
                                    </label>
                                </div>
                            @endforeach
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
