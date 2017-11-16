@extends('app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-body">

                        <div class="text-center">
                            <h3>Welcome {{ $user->name }}</h3>

                            <p>Please set a password below to get started.</p>
                        </div>

                        <hr />

                        @include('components.messages')

                        <form class="form-horizontal" role="form" method="POST" action="{{ route('shadow-user.set-password') }}">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            @if (!empty($next))
                                <input type="hidden" name="next" value="{{ $next }}">
                            @endif

                            <div class="form-group">
                                <label class="col-md-4 control-label">Password</label>
                                <div class="col-md-6">
                                    <input type="password" class="form-control" name="password">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-4 control-label">Confirm Password</label>
                                <div class="col-md-6">
                                    <input type="password" class="form-control" name="password_confirmation">
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-md-6 col-md-offset-4">
                                    <button type="submit" class="btn btn-primary">Set Password</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
