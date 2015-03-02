@extends('app')

@section('content')
<div class="container">
	<div class="row">
		<div class="col-md-10 col-md-offset-1">
			<div class="panel panel-default">
				<div class="panel-heading">Home</div>

				<div class="panel-body">
					<p>You are logged in!</p>
					<p><a href="{{ route('browser-login') }}">Go to law browser</a></p>
					<p><a href="{{ route('send-welcome') }}">Resend welcome email</a></p>{{-- TODO: Remove after email test --}}
				</div>
			</div>
		</div>
	</div>
</div>
@endsection
