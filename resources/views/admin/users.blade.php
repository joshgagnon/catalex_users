@extends('app')

@section('content')
<div class="container">
	<h2>User Admin</h2>
	<div class="row">
		<div class="col-xs-12 col-sm-6">
			<a href="{{ action('UserController@getAddUser') }}"><span class="glyphicon glyphicon-plus"></span>Add user</a>
		</div>
		<div class="col-xs-12 col-sm-6">
			@if($showDeleted)
				<a href="{{ URL::current() }}">Hide deleted users</a>
			@else
				<a href="{{ URL::current() }}?deleted=1">Show deleted users</a>
			@endif
		</div>
	</div>
	<div class="row">
		<div class="col-xs-12">
			@include('components.messages')
			@include('user.components.list', ['users' => $userList, 'title' => 'All Users', 'viewPermission' => 'view_any_user', 'editPermission' => 'edit_any_user', 'allowDeleteUsers' => true, 'showFilterControls' => true])
		</div>
	</div>
</div>
@endsection
