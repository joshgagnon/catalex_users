@extends('app')

@section('personal-input')
<div class="form-group">
	<label class="col-md-4 control-label">First Name</label>
	<div class="col-md-6">
		<input type="text" class="form-control" name="first_name" value="{{ Session::get('oauth.first_name', old('first_name')) }}">
	</div>
</div>
<div class="form-group">
	<label class="col-md-4 control-label">Last Name</label>
	<div class="col-md-6">
		<input type="text" class="form-control" name="last_name" value="{{ Session::get('oauth.last_name', old('last_name')) }}">
	</div>
</div>
@stop

@section('address-input')
<div class="form-group">
	<label class="col-md-4 control-label">Address Line 1</label>
	<div class="col-md-6">
		<input type="text" class="form-control" name="address_line_1" value="{{ old('address_line_1') }}">
	</div>
</div>
<div class="form-group">
	<label class="col-md-4 control-label">Address Line 2</label>
	<div class="col-md-6">
		<input type="text" class="form-control" name="address_line_2" value="{{ old('address_line_2') }}">
	</div>
</div>
<div class="form-group">
	<label class="col-md-4 control-label">City</label>
	<div class="col-md-6">
		<input type="text" class="form-control" name="city" value="{{ old('city') }}">
	</div>
</div>
<div class="form-group">
	<label class="col-md-4 control-label">State</label>
	<div class="col-md-6">
		<input type="text" class="form-control" name="state" value="{{ old('state') }}">
	</div>
</div>
<div class="form-group">
	<label class="col-md-4 control-label">Country</label>
	<div class="col-md-6">
		<select class="form-control" name="country">
			{!! implode('', \App\Data\Countries::getOptions(old('country'))) !!}
		</select>
	</div>
</div>
@stop

@section('billing-input')
<h4>Billing Cycle</h4>
<div class="row">
	<div class="col-md-4"></div>
	<div class="col-md-6">
		<p>All transactions will be billed in New Zealand dollars.<p>
		<p>You will be able to access Law Browser immediately once your credit card details have been accepted.</p>
	</div>
</div>
<div class="row">
	<div class="col-md-4"></div>
	<div class="col-md-4">
		<div class="form-group">
			<label class="radio-inline">
				<input type="radio" {{ old('billing_period') === 'monthly' ? 'checked' : '' }} value="monthly" name="billing_period" id="billing-monthly"> Monthly ($14.99) per user
			</label>
		</div>
	</div>
	<div class="col-md-4">
		<div class="form-group">
			<label class="radio-inline">
				<input type="radio" {{ old('billing_period') === 'annually' ? 'checked' : '' }} value="annually" name="billing_period" id="billing-annually"> Annual ($179) per user
			</label>
		</div>
	</div>
</div>
@stop

@section('password-input')
<div class="form-group">
	<label class="col-md-4 control-label">E-Mail Address</label>
	<div class="col-md-6">
		<input type="email" class="form-control" name="email" value="{{ Session::get('oauth.email', old('email')) }}">
	</div>
</div>
@if(!Session::get('oauth.register', false))
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
@endif
@stop

@section('register-button')
<div class="form-group">
	<div class="col-md-6 col-md-offset-4">
		<div class="checkbox">
			<label>
				<input type="checkbox" id="customer_agreement" name="customer_agreement"> I accept the <a href="#">Customer Agreement</a> and <a href="#">Law Browser Terms of Use</a>
			</label>
		</div>
	</div>
</div>
<div class="form-group">
	<div class="col-md-6 col-md-offset-4">
		<button type="submit" class="btn btn-primary">
			Register
		</button>
	</div>
</div>
@stop

@section('content')
<div class="container">
	<div class="row">
		<div class="col-xs-12 col-md-8">
			<div class="panel panel-default">
				<div class="panel-heading">
				@if(Session::get('oauth.register', false))
					Please confirm your details
				@else
					Register
				@endif
				</div>
				<div class="panel-body">
					@if(!Session::get('oauth.register', false))
						<div class="row">
							<label class="col-md-4 control-label text-right">Register with</label>
							<div class="col-md-6">
								<ul class="social-logins">
									<li><a href="/auth/linkedin"><img alt="LinkedIn" src="/images/social-login/linkedin.png"></a></li>
								</ul>
							</div>
						</div>
					@endif
					@if(count($errors) > 0)
						<div class="alert alert-danger">
							<strong>Whoops!</strong> There were some problems with your input.<br><br>
							<ul>
								@foreach ($errors->all() as $error)
									<li>{{ $error }}</li>
								@endforeach
							</ul>
						</div>
					@endif

					<div role="tabpanel">
						<ul class="nav nav-tabs" role="tablist">
							<li role="presentation" {!! old('type') !== 'organisation' ? 'class="active"' : '' !!}><a href="#tab-individual" aria-controls="individual" role="tab" data-toggle="tab">Individual</a></li>
							<li role="presentation" {!! old('type') === 'organisation' ? 'class="active"' : '' !!}><a href="#tab-organisation" aria-controls="organisation" role="tab" data-toggle="tab">Organisation</a></li>
						</ul>
						<div class="tab-content">
							<div role="tabpanel" class="tab-pane {{ old('type') !== 'organisation' ? 'active' : '' }}" id="tab-individual">
								<form class="form-horizontal" role="form" method="POST" action="/auth/register">
									<input type="hidden" name="_token" value="{{ csrf_token() }}">
									<input type="hidden" name="type" value="individual">
									<h4>Your Details</h4>
									@yield('personal-input')
									<h4>Your Address</h4>
									@yield('address-input')
									@yield('billing-input')
									<h4>Login Information</h4>
									@yield('password-input')
									@yield('register-button')
								</form>
							</div>
							<div role="tabpanel" class="tab-pane {{ old('type') === 'organisation' ? 'active' : '' }}" id="tab-organisation">
								<form class="form-horizontal" role="form" method="POST" action="/auth/register">
									<input type="hidden" name="_token" value="{{ csrf_token() }}">
									<input type="hidden" name="type" value="organisation">
									<h4>Your Details</h4>
									@yield('personal-input')
									<h4>Business Details</h4>
									<div class="form-group">
										<label class="col-md-4 control-label">Business Name</label>
										<div class="col-md-6">
											<input type="text" class="form-control" name="business_name" value="{{ old('business_name') }}">
										</div>
									</div>
									<h4>Business Address</h4>
									@yield('address-input')
									@yield('billing-input')
									<h4>Login Information</h4>
									@yield('password-input')
									@yield('register-button')
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="hidden-xs hidden-sm col-med-4"><img src="/images/under-the-surface.jpg" alt="Find what's under the surface"></div>
	</div>
</div>
@endsection
