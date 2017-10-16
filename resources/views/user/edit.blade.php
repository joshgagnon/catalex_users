@extends('app')

@section('title')
CataLex - Edit User
@endsection

@section('content')
<div class="container">
	<h2>Edit {{ $subject->fullName() }}</h2>

	<div class="row">
		<div class="col-md-9">
			@if($user->hasRole('global_admin'))
				<div class="panel panel-default">
					<div class="panel-body">
						@if ($subject->organisation && $subject->organisation->billing_detail_id)
							<a href="{{ url('admin/billing', $subject->organisation->billing_detail_id)  }}" class="btn btn-info">View Billing</a>
						@elseif ($subject->billing_detail_id)
							<a href="{{ url('admin/billing', $subject->billing_detail_id)  }}" class="btn btn-info">View Billing</a>
						@elseif ($subject->hasRole('global_admin'))
							<div class="text-center">
								No admin controls for this user.
							</div>
						@endif

						@if (!$subject->hasRole('global_admin'))
							<form action="{{ url('impersonation', $subject->id) }}" method="post" style="display: inline-block;">
								<input type="hidden" name="_token" value="{{ csrf_token() }}">
								<button type="submit" class="btn btn-info">Login As {{ $subject->name }}</button>
							</form>
						@endif
					</div>
				</div>
			@endif

			<div class="panel panel-default">
				<div class="panel-body">
					@include('components.messages')

					<form method="POST" role="form" class="form-horizontal">
						<input type="hidden" name="_token" value="{{ csrf_token() }}">
						<input type="hidden" name="user_id" value="{{ $subject->id }}">
						{{-- Prevent autocomplete from trying to fill new_password --}}
						<input type="text" style="display:none"><input type="password" style="display:none">

						<h4>Personal Details</h4>
						
						<div class="form-group">
							<div class="col-xs-12 col-md-6">
								<label class="control-label">Name</label>
								<input type="text" class="form-control" name="name" value="{{ $subject->name }}">
							</div>
							<div class="col-xs-12">
								<label class="control-label">E-Mail Address</label>
								<input type="email" class="form-control" name="email" value="{{ $subject->email }}">
							</div>
						</div>
						@if(isset($roles) && count($roles))
							<hr />
							<h4>User Roles</h4>
							<div class="form-group">
								@foreach($roles as $roleName => $roleActive)
									<div class="col-xs-12 col-md-4">
										<div class="checkbox">
											<input type="hidden" value="0" name="{{ $roleName }}">
											<label><input type="checkbox" value="1" name="{{ $roleName }}" {{ $roleActive ? 'checked' : '' }}> {{ str_replace('_', ' ', \Illuminate\Support\Str::title($roleName)) }}</label>
										</div>
									</div>
								@endforeach
							</div>
						@endif

						@if ($user->hasRole('global_admin') && !$subject->organisation_id)
							<hr />
							<h4>Billing Options</h4>

							<div class="form-group">
								<div class="col-xs-12 col-md-4">
									<div class="checkbox">
										<label>
											<input type="checkbox" value="1" name="free" {{ $subject->free ? 'checked' : '' }}>
											Free User
										</label>
									</div>
								</div>

								<div class="col-xs-12 col-md-4">
									<div class="checkbox">
										<label>
											<input type="checkbox" value="1" name="is_invoice_customer" {{ $subject->is_invoice_customer ? 'checked' : '' }}>
											Invoice Customer
										</label>
									</div>
								</div>
							</div>
						@endif

						<hr />
						<h4>Change Password</h4>
						<div class="form-group">
							<div class="col-md-6">
								<label class="control-label">New Password</label>
								<input type="password" class="form-control" name="new_password">
							</div>
							<div class="col-md-6">
								<label class="control-label">Confirm Password</label>
								<input type="password" class="form-control" name="new_password_confirmation">
							</div>
						</div>
						<div class="form-group">
							<div class="col-xs-12">
								<button type="submit" class="btn btn-primary">Update</button>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
		@if ($editServicesAndBilling)
			<div class="col-md-3">
				<div class="panel panel-default">
					<div class="panel-body">
						<h4>More</h4>
						<ul>
							<li><a href="{{ route('user-services.index') }}">Edit My Services</a></li>
							<li><a href="{{ route('billing.edit') }}">Edit Billing Details</a></li>
						</ul>
					</div>
				</div>
			</div>
		@endif
	</div>
</div>
@endsection
