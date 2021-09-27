@extends('app')

@section('content')
    <div class="container login-container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">2-Factor Authentication</div>
                    <div class="panel-body">
                        <form class="form-horizontal" role="form" method="POST"  action="/otp" >
                            <p>Please your authenticator app to log in.</p>

                            @if (session('csrf_error'))
                                <span class="help-block text-center">
                                <strong>{{ session('csrf_error') }}</strong>
                            </span>
                            @endif

                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <div class="form-group{{ $errors->has('totp') ? ' has-error' : '' }}">
                                <label for="email" class="col-md-4 control-label">One Time Password</label>

                                <div class="col-md-6">
                                    <input id="topt" type="text" class="form-control" name="totp"  required autofocus>

                                    @if ($errors->has('totp'))
                                        <span class="help-block">
                                        <strong>{{ $errors->first('totp') }}</strong>
                                    </span>
                                    @endif

                                </div>
                            </div>
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">

                            <div class="text-center">
                                <button type="submit" class="btn btn-primary">
                                    Submit
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection