@extends('app')

@section('content')
<div class="container">
	<div class="row">
		<div class="col-md-10 col-md-offset-1">
			<div class="panel panel-default">
				<div class="panel-heading">Home</div>

				<div class="panel-body">
					<p>You are logged in!</p>

					<h3>Permission summary:</h3>
					<ul>
						@foreach($permissions as $p)
							<li>You {{ $user->can($p->name) ? 'CAN' : 'CANNOT' }} {{ $p->display_name }}</li>
						@endforeach
					</ul>
					<div><form method="post"><input type="hidden" name="_token" value="{{ csrf_token() }}"><input type="submit" name="perm" value="Make me organisation admin"></form></div>
					<div><form method="post"><input type="hidden" name="_token" value="{{ csrf_token() }}"><input type="submit" name="perm" value="Make me global admin"></form></div>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection
