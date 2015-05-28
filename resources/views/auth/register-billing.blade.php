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
						<div class="row">
							<div class="col-md-2"></div>
							<div class="col-md-8">
								<p>Please ensure you click the 'Next' button after successful credit card authorisation.</p>
							</div>
						</div>
						<div class="row">
							<div class="col-xs-12">
								<div class="embed-responsive embed-responsive-4by3">
									<iframe class="embed-responsive-item" src="{{ $gatewayURL }}"></iframe>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-4"></div>
							<div class="col-md-6">
								<p>All transactions will be billed in New Zealand dollars.</p>
								<p>You will be able to access Law Browser immediately once your credit card details have been accepted.</p>
							</div>
						</div>
						<div class="row">
							<label class="col-md-4 control-label">Billing Cycle</label>
							<div class="col-md-4">
								<div class="form-group">
									<label class="radio-inline">
										<input type="radio" {{ old('billing_period') === 'monthly' ? 'checked' : '' }} value="monthly" name="billing_period" id="billing-monthly"> Monthly (${{ Config::get('constants.monthly_price') }})<span class="per-user"> per user</span>
									</label>
								</div>
							</div>
							<div class="col-md-4">
								<div class="form-group">
									<label class="radio-inline">
										<input type="radio" {{ old('billing_period') === 'annually' ? 'checked' : '' }} value="annually" name="billing_period" id="billing-annually"> Annual (${{ Config::get('constants.annual_price') }})<span class="per-user"> per user</span>
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
