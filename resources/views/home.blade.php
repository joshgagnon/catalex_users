@extends('app')

@section('content')
<div class="container">
	<div class="row">
		<div class="col-md-10 col-md-offset-1">
			<div class="panel panel-default">
				<div class="panel-heading">Home</div>
				<div class="panel-body">
					<p>You are logged in!</p>
					@if($user->hasBrowserAccess())
						<p><a href="{{ route('browser-login') }}">Go to Law Browser</a></p>
					@elseif($user->everBilled()/*TODO: && $user->can('editpaymentdetails')*/)
						<p>You last billing cycle has not completed correctly. Please <a href="#TODO">click here</a> to update your payment details.</p>
					@else
						<p>Your trial period has expired. Please <a href="{{ action('BillingController@getStart') }}">click here</a> to confirm your subscription to regain access to Law Browser.</p>
					@endif
				</div>
			</div>
		</div>
	</div>
</div>
@endsection
