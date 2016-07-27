@extends('app')

@section('content')
<div class="container">
	<div class="row">
		<div class="col-xs-12 col-md-8">
			<div class="panel panel-default">
				<div class="panel-heading">
					@if(Session::get('oauth.register', false))
						Please confirm your details
					@else
						Sign Up
					@endif
				</div>
				<div class="panel-body">
					@include('components.messages')

					@if(!Session::get('oauth.register', false))
						<div class="row form-group">
							<div class="col-md-4"></div>
							<div class="col-md-6">
								<a href="/auth/linkedin">Sign up with <img class="small-social" alt="LinkedIn" src="/images/social-login/linkedin.png"></a>
							</div>
						</div>
					@endif
					<div class="row form-group">
						<div class="col-md-4"></div>
						<div class="col-md-6">
							<p class="small">Asterisks (*) denote a required field</p>
						</div>
					</div>
                    <!--<form class="form-horizontal" role="form" method="POST" action="/auth/billing"> -->
					<form class="form-horizontal" role="form" method="POST" action="/auth/register">
						<input type="hidden" name="_token" value="{{ csrf_token() }}">
						<div class="form-group">
							<label class="col-md-4 control-label">Name *</label>
							<div class="col-md-6">
								<input type="text" class="form-control" name="name" value="{{ Session::get('oauth.name', old('name')) }}">
							</div>
						</div>
						<div class="form-group">
							<label class="col-md-4 control-label">E-Mail Address *</label>
							<div class="col-md-6">
								<input type="email" class="form-control" name="email" value="{{ Session::get('oauth.email', old('email')) }}">
							</div>
						</div>
						@if(!Session::get('oauth.register', false))
							<div class="form-group">
								<label class="col-md-4 control-label">Password *</label>
								<div class="col-md-6">
									<input type="password" class="form-control" name="password">
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-4 control-label">Confirm Password *</label>
								<div class="col-md-6">
									<input type="password" class="form-control" name="password_confirmation">
								</div>
							</div>
						@endif
						<div class="form-group">
							<div class="col-md-4"></div>
							<div class="col-md-6">
								<p class="small">Optionally enter a name to create an administrator's account for your organisation. Once your organisation is signed up with CataLex, you can add as many users as you like.</p>
							</div>
						</div>
						<div class="form-group">
							<label class="col-md-4 control-label">Organisation Name</label>
							<div class="col-md-6">
								<input type="text" class="form-control" name="business_name" value="{{ old('business_name') }}">
							</div>
						</div>
						<div class="form-group">
							<label class="col-md-4 control-label">*</label>
							<div class="col-md-6">
								<div class="checkbox">
									<label>
										<input type="checkbox" id="customer_agreement" name="customer_agreement"> I accept the <a href="/customeragreement">Customer Agreement</a> and <a href="/termsofuse">Law Browser Terms of Use</a>
									</label>
								</div>
							</div>
						</div>
						<div class="form-group">
							<div class="col-md-6 col-md-offset-4">
								<button type="submit" class="btn btn-primary">
									Create Account
								</button>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
		<div class="col-xs-12 col-md-4">
			<div class="trial-info">
				<!-- <h2>How the free trial works.</h2>
				<p>Create an account with CataLex to gain free access to Law Browser for a trial period of 14 days. After that, the access fee is just NZ${{ Config::get('constants.monthly_price') }} per user per month.</p>
				<p>You will need to enter your credit card details in order to create your account and have access to the free trial. However, we will not deduct any funds unless you elect to proceed with a paid subscription once the trial period is over.</p> -->
				<img class="hidden-xs hidden-sm" src="/images/under-the-surface.jpg" alt="Find what's under the surface">
			</div>
		</div>
	</div>
</div>
@endsection
