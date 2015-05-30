@extends('app')

@section('content')
<div class="container">
	<div class="row">
		<div class="col-md-10 col-md-offset-1">
			<div class="panel panel-default">
				<div class="panel-heading">Success!</div>
				<div class="panel-body">
					<p>Your subscription was successfully processed. You can begin using Law Browser right away by <a href="{{ route('browser-login') }}">clicking here</a></p>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection
