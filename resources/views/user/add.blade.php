@extends('app')

@section('title')
CataLex Law Browser - Add User
@endsection

@section('content')
<div class="container">
	<h2>Add New User</h2>
	<div class="row">
		<div class="col-xs-12">
			<div class="panel panel-default">
				<div class="panel-body">
					@include('components.messages')
					<form method="POST" role="form" class="form-horizontal">
						<input type="hidden" name="_token" value="{{ csrf_token() }}">
						<h4>Personal Details</h4>
						<div class="row form-group">
							<div class="col-xs-12 col-md-6">
								<label class="control-label">Name</label>
								<input type="text" class="form-control" name="name" value="{{ old('name') }}">
							</div>
							<div class="col-xs-12">
								<label class="control-label">E-Mail Address</label>
								<input type="email" class="form-control" name="email" value="{{ old('email') }}">
							</div>
						</div>
						<h4>Organisation</h4>
						<div class="row form-group">
							<div class="col-xs-12">
								<label class="control-label">Assign to organisation</label>
								{!! Form::select('organisation_id', $organisations, ['class' => 'form-control']) !!}
							</div>
						</div>
						<h4>Address Details</h4>
						<p>Only required with no assigned organisation</p>
						<div class="row form-group">
							<div class="col-xs-12">
								<label class="control-label">Address Line 1</label>
								<input type="text" class="form-control" name="address_line_1" value="{{ old('address_line_1') }}">
							</div>
							<div class="col-xs-12">
								<label class="control-label">Address Line 2</label>
								<input type="text" class="form-control" name="address_line_2" value="{{ old('address_line_2') }}">
							</div>
							<div class="col-xs-12">
								<label class="control-label">City</label>
								<input type="text" class="form-control" name="city" value="{{ old('city') }}">
							</div>
							<div class="col-xs-12">
								<label class="control-label">State</label>
								<input type="text" class="form-control" name="state" value="{{ old('state') }}">
							</div>
							<div class="col-xs-12">
								<label class="control-label">Country</label>
								<select class="form-control" name="country">
									{!! implode('', \App\Data\Countries::getOptions(old('country'))) !!}
								</select>
							</div>
						</div>
                        <!--
						<h4>Billing</h4>
						<p>Only required with no assigned organisation</p>
						<div class="row form-group">
							<div class="col-xs-12 col-md-6">
								<label class="radio-inline">
									<input type="radio" {{ old('billing_period') === 'monthly' ? 'checked' : '' }} value="monthly" name="billing_period" id="billing-monthly"> Monthly
								</label>
							</div>
							<div class="col-xs-12 col-md-6">
								<label class="radio-inline">
									<input type="radio" {{ old('billing_period') === 'annually' ? 'checked' : '' }} value="annually" name="billing_period" id="billing-annually"> Annual
								</label>
							</div>
						</div>
                        -->
						<div class="row form-group">
							<div class="col-xs-12">
								<div class="checkbox">
									<label>
										<input type="checkbox" name="send_invite" checked> Send user invition (if not, you will have to edit this user to set a login password)
									</label>
								</div>
							</div>
						</div>
						<button type="submit" class="btn btn-primary">Create User</button>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection
