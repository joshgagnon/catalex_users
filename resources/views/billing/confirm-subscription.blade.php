@extends('app')

@section('content')
<div class="container">
	<div class="row">
		<div class="col-xs-12 col-md-10 col-md-offset-1">
			<div class="panel panel-default">
				<div class="panel-heading">Confirm Subscription</div>
				<div class="panel-body">
					@include('components.messages')
					<form class="form-horizontal" role="form" method="POST" action="/billing/start">
						<input type="hidden" name="_token" value="{{ csrf_token() }}">
						<div class="row">
							<div class="col-md-2"></div>
							<div class="col-md-8">
								<p>We will deduct the access fee from the credit card you provided when you created your account with CataLex. The access fee is payable monthly or annually, in advance.</p>
							</div>
						</div>
						@if($user->organisation)
							<div class="row">
								<div class="col-md-2"></div>
								<div class="col-md-8">
									<p>{{-- TODO: Summarise price for ogranisation --}}</p>
								</div>
							</div>
						@endif
						<div class="row">
							<label class="col-md-4 control-label">Billing Cycle</label>
							<div class="col-md-4">
								<div class="form-group">
									<label class="radio-inline">
										<input type="radio" {{ old('billing_period') !== 'annually' ? 'checked' : '' }} value="monthly" name="billing_period" id="billing-monthly"> Monthly (${{ Config::get('constants.monthly_price') }})<span class="per-user"> per user</span>
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
									Confirm Subscription
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
