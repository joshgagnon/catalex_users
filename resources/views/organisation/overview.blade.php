@extends('app')

@section('content')
<div class="container">
	<div class="row">
		<div class="col-xs-12">
			@if(Session::has('success'))
				<div class="alert alert-success">
					{{ Session::get('success') }}
				</div>
			@endif
			<h3>Members in {{ $organisation->name }}</h3>
			<ul>
				@foreach($organisation->members as $member)
					<li>{{ $member->fullName() }}</li>
				@endforeach
			</ul>
			@if(count($errors) > 0)
				<div class="alert alert-danger">
					<ul>
						@foreach ($errors->all() as $error)
							<li>{{ $error }}</li>
						@endforeach
					</ul>
				</div>
			@endif
			<form class="form-inline" role="form" method="POST" action="{{ action('OrganisationController@postInvite') }}">
				<input type="hidden" name="_token" value="{{ csrf_token() }}">
				<h4>Add users</h4>
				<div class="form-group">
					<label for="first_name">First Name</label>
					<input type="text" class="form-control" id="first_name" name="first_name" value="{{ old('first_name') }}">
				</div>
				<div class="form-group">
					<label for="first_name">Last Name</label>
					<input type="text" class="form-control" id="last_name" name="last_name" value="{{ old('last_name') }}">
				</div>
				<div class="form-group">
					<label for="first_name">E-Mail</label>
					<input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}">
				</div>
				<button type="submit" class="btn btn-default">Send invitation</button>
			</form>
		</div>
	</div>
</div>
@endsection
