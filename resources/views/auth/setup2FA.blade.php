@extends('app')

@section('content')
    <div class="container login-container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">Setup 2-Factor Authentication</div>
                    <div class="panel-body">
                        <form class="form-horizontal" role="form" method="POST" action="{{ route('save-2fa') }}">
                            <input id="secret" type="hidden"  name="secret" value="{{ $secret }}">


                            @if (session('csrf_error'))
                                <span class="help-block text-center">
                                <strong>{{ session('csrf_error') }}</strong>
                            </span>
                            @endif
                            <p>Please use of the following apps and scan in the QR Code:</p>
                            <ul>
                                <li><a href="https://www.authy.com/" rel="nofollow">Authy for iOS, Android, Chrome, OS X</a></li>
                                <li><a href="https://apps.getpebble.com/en_US/application/52f1a4c3c4117252f9000bb8" rel="nofollow">FreeOTP for iOS, Android and Pebble</a></li>
                                <li><a href="https://itunes.apple.com/us/app/google-authenticator/id388497605?mt=8" rel="nofollow">Google Authenticator for iOS</a></li>
                                <li><a href="https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2" rel="nofollow">Google Authenticator for Android</a></li>
                                <li><a href="https://www.microsoft.com/en-us/store/p/google-authenticator/9wzdncrdnkrf" rel="nofollow">Google Authenticator (port) on Windows Store</a></li>
                                <li><a href="https://www.microsoft.com/en-us/store/apps/authenticator/9wzdncrfj3rj" rel="nofollow">Microsoft Authenticator for Windows Phone</a></li>
                                <li><a href="https://lastpass.com/auth/" rel="nofollow">LastPass Authenticator for iOS, Android, OS X, Windows</a></li>
                                <li><a href="https://1password.com" rel="nofollow">1Password for iOS, Android, OS X, Windows</a></li>
                            </ul>
                            <div class="text-center">
                                <img src="data:image/png;base64,{{ $qrcode_image }}"/>
                            </div>


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