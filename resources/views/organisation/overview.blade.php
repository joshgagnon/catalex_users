@extends('app')

@section('title')
CataLex - {{ $organisation->name }}
@endsection

@section('content')
<div class="container">
    @include('components.messages')

	<h2>{{ $organisation->name }}</h2>

    @if (!$user->hasRole('organisation_admin'))
        <form class="form-inline" role="form" method="POST" action="{{ route('organisation.leave') }}">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <button type="submit" class="btn btn-danger btn-xs">Leave Organisation</button>
        </form>
    @endif

	<div class="row">
		<div class="col-xs-12">
			@include('user.components.list', ['users' => $organisation->members()->paginate(Config::get('constants.items_per_page')), 'title' => 'Organisation Members', 'viewPermission' => 'view_organisation_user', 'editPermission' => 'edit_organisation_user'])
			@if($user->can('edit_own_organisation'))
				<form class="form-inline" role="form" method="POST" action="{{ action('OrganisationController@postInvite') }}">
					<input type="hidden" name="_token" value="{{ csrf_token() }}">
					<h4>Add users</h4>
					<div class="form-group">
						<label for="first_name">Name</label>
						<input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}">
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
	<br/>
	<div class="row">
		<div class="col-xs-12">
			@if($user->can('edit_own_organisation'))
				<form class="form-inline" role="form" method="POST" action="{{ action('OrganisationController@postEdit2fa') }}">
					<input type="hidden" name="_token" value="{{ csrf_token() }}">
					<div class="checkbox">
						<input type="hidden" value="0" name="require_2fa">
						<label><input type="checkbox" value="1" name="require_2fa" {{ $organisation->require_2fa ? 'checked' : '' }}> Require 2FA</label>
					</div>
					<div>
						<button type="submit" class="btn btn-default">Save</button>
					</div>
					<br/>
					<br/>
					<br/>
				</form>
			@endif
		</div>
	</div>
</div>





@endsection
