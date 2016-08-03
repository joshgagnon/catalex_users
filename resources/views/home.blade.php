@extends('app')

@section('content')
<div class="container">
	<div class="row">
		<div class="col-md-10 col-md-offset-1">
			<div class="panel panel-default">
				<div class="panel-heading">CataLex Home</div>
				<div class="panel-body">
					<p>Welcome to CataLex.</p>
                    <div class="row services">
                        <div class="col-md-6 service">
    					@if($user->hasBrowserAccess())
    						<a href="{{ route('browser-login') }}"><span class="image-button"><img src="/images/law-browser.png" /></span><br/>Go to Law Browser</a></p>
    					@elseif($user->everBilled()/*TODO: && $user->can('editpaymentdetails')*/)
    						<p>You last billing cycle has not completed correctly. Please <a href="#TODO">click here</a> to update your payment details.</p>
    					@else
    						<p>Your trial period has expired. Please <a href="{{ action('BillingController@getStart') }}">click here</a> to confirm your subscription to regain access to Law Browser.</p>
    					@endif
                        </div>
                        <div class="col-md-6 service">

                        @if($user->hasGoodCompaniesAccess())
                            <p><a href="{{ route('good-companies-login') }}"><span class="image-button"><img src="/images/good-company-lg.png"/></span><br/>Go to Good Companies (In Development)</a></p>
                        @elseif($user->everBilled()/*TODO: && $user->can('editpaymentdetails')*/)
                            <p>You last billing cycle has not completed correctly. Please <a href="#TODO">click here</a> to update your payment details.</p>
                        @else
                            <p>Your trial period has expired. Please <a href="{{ action('BillingController@getStart') }}">click here</a> to confirm your subscription to regain access to Good Companies.</p>
                        @endif
                        </div>
                    </div>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection
