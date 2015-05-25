@extends('app')

@section('content')
<div class="container">
	<div class="row">
		<div class="col-xs-12 col-md-8">
			<div class="panel panel-default">
				<div class="panel-heading">Billing Details</div>
				<div class="panel-body">
					@include('components.messages')
					<form class="form-horizontal" role="form" method="POST" action="/auth/register">
						<input type="hidden" name="_token" value="{{ csrf_token() }}">
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
						<h4>Billing Cycle</h4>
						<div class="row">
							<div class="col-md-4"></div>
							<div class="col-md-6">
								<p>All transactions will be billed in New Zealand dollars.</p>
								<p>You will be able to access Law Browser immediately once your credit card details have been accepted.</p>
							</div>
						</div>
						<div class="row">
							<div class="col-md-4"></div>
							<div class="col-md-4">
								<div class="form-group">
									<label class="radio-inline">
										<input type="radio" {{ old('billing_period') === 'monthly' ? 'checked' : '' }} value="monthly" name="billing_period" id="billing-monthly"> Monthly ($24.99)<span class="per-user"> per user</span>
									</label>
								</div>
							</div>
							<div class="col-md-4">
								<div class="form-group">
									<label class="radio-inline">
										<input type="radio" {{ old('billing_period') === 'annually' ? 'checked' : '' }} value="annually" name="billing_period" id="billing-annually"> Annual ($299.88)<span class="per-user"> per user</span>
									</label>
								</div>
							</div>
						</div>
						<div class="form-group">
							<div class="col-md-6 col-md-offset-4">
								<button type="submit" class="btn btn-primary">
									Complete Registration
								</button>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection
