@extends('app')

@section('content')
<div class="container">
	<h2>{{ $organisation->name }}</h2>
	<div class="row">
		<div class="col-xs-12">
			@if(Session::has('success'))
				<div class="alert alert-success">
					{{ Session::get('success') }}
				</div>
			@endif
			@if(count($errors) > 0)
				<div class="alert alert-danger">
					<ul>
						@foreach ($errors->all() as $error)
							<li>{{ $error }}</li>
						@endforeach
					</ul>
				</div>
			@endif
			<h3>Active Members</h3>
			<div class="tabular">
				@foreach($organisation->members as $member)
					<div>
						<label>{{ $member->fullName() }}</label>
						@if($user->can('view_organisation_user'))
							<a href="{{ action('UserController@getView', $member->id) }}">View</a>
						@endif
						@if($user->can('edit_organisation_user'))
							<a href="{{ action('UserController@getEdit', $member->id) }}">Edit</a>
						@endif
					</div>
				@endforeach
			</div>
			@if($user->can('edit_own_organisation'))
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
					<div class="form-group">
						<label>&nbsp;</label>
						<button type="submit" class="btn btn-default">Send invitation</button>
					</div>
				</form>
			@endif
		</div>
	</div>
</div>
@endsection
