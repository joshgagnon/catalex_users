@extends('app')

@section('title')
CataLex Law Browser - {{ $organisation->name }}
@endsection

@section('content')
<div class="container">
	<h2>{{ $organisation->name }}</h2>
	<div class="row">
		<div class="col-xs-12">
			@include('components.messages')
			@include('user.components.list', ['users' => $organisation->members()->paginate(Config::get('constants.items_per_page')), 'title' => 'Organisation Members', 'viewPermission' => 'view_organisation_user', 'editPermission' => 'edit_organisation_user'])
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
