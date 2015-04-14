@extends('app')

@section('title')
CataLex Law Browser - Edit User
@endsection

@section('content')
<div class="container">
	<h2>Edit {{ $subject->fullName() }}</h2>
	<div class="row">
		<div class="col-xs-12">
			<div class="panel panel-default">
				<div class="panel-body">
					@include('components.messages')
					<form method="POST" role="form" class="form-horizontal">
						<input type="hidden" name="_token" value="{{ csrf_token() }}">
						<input type="hidden" name="user_id" value="{{ $subject->id }}">
						{{-- Prevent autocomplete from trying to fill new_password --}}
						<input type="text" style="display:none"><input type="password" style="display:none">
						<h4>Personal Details</h4>
						<div class="row">
							<div class="col-xs-12 col-md-6">
								<label class="control-label">First Name</label>
								<input type="text" class="form-control" name="first_name" value="{{ $subject->first_name }}">
							</div>
							<div class="col-xs-12 col-md-6">
								<label class="control-label">Last Name</label>
								<input type="text" class="form-control" name="last_name" value="{{ $subject->last_name }}">
							</div>
							<div class="col-xs-12">
								<label class="control-label">E-Mail Address</label>
								<input type="email" class="form-control" name="email" value="{{ $subject->email }}">
							</div>
						</div>
						<h4>Change Password</h4>
						<div class="row">
							<div class="col-md-6">
								<label class="control-label">New Password</label>
								<input type="password" class="form-control" name="new_password">
							</div>
							<div class="col-md-6">
								<label class="control-label">Confirm Password</label>
								<input type="password" class="form-control" name="new_password_confirmation">
							</div>
						</div>
						<button type="submit" class="btn btn-primary">
							Update
						</button>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection
