@extends('app')

@section('content')
<div class="container">
	<div class="row">
		<div class="col-sm-6 col-sm-offset-3">
            @if(Request::query('product') == 'gc')
                <div class="login-heading">Login to Good Companies</div>
            @else
                <div class="login-heading">Login to CataLex</div>
            @endif
            <div class="panel panel-default login">
				<div class="panel-body">
					@if(count($errors))
						<div class="alert alert-danger">
							<ul>
								@foreach ($errors->all() as $error)
									<li>{{ $error }}</li>
								@endforeach
							</ul>
						</div>
					@endif

					<form class="form-horizontal" role="form" method="POST" action="/auth/login">
						<input type="hidden" name="_token" value="{{ csrf_token() }}">

						<div class="form-group">
							<label class="col-md-4 control-label">Sign in with</label>
							<div class="col-md-6">
								<ul class="social-logins">
									{{-- TODO: Other auth methods <li><a href="/auth/github">Github</a></li> --}}
									<li><a href="/auth/linkedin" class="linkedin"><img alt="LinkedIn" src="/images/social-login/Logo-White-21px-R.png"></a></li>
								</ul>
							</div>
						</div>

						<div class="form-group text-center form-label">&mdash; or &mdash;</div>

						<div class="form-group">
							<label class="col-md-4 control-label">E-Mail Address</label>
							<div class="col-md-8">
								<input type="email" class="form-control" name="email" value="{{ old('email') }}">
							</div>
						</div>

						<div class="form-group">
							<label class="col-md-4 control-label">Password</label>
							<div class="col-md-8">
								<input type="password" class="form-control" name="password">
                                    <a class="form-label forgot-password" href="/password/email">Forgot Your Password?</a>
							</div>
						</div>

						<div class="form-group">
							<div class="col-md-8 col-md-offset-4">
                                <div class="checkbox remember-me">
                                    <label>
                                        <input type="checkbox" name="remember"> Remember Me
                                    </label>
                                </div>

								<button type="submit" class="btn btn-primary" >
									Login
								</button>


							</div>
						</div>

						<div class="form-group">
							<div class="col-md-6 col-md-offset-4 form-label sign-up">
								Not yet a member? <a href="/auth/register">Sign up here</a>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection
