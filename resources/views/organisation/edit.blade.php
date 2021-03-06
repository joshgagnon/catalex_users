@extends('app')

@section('title')
CataLex - Edit Organisation
@endsection

@section('content')
<div class="container">
	<h2>Edit {{ $organisation->name }}</h2>

	<div class="row">
		<div class="col-xs-12">
			@if($user->hasRole('global_admin'))
				<div class="panel panel-default">
					<div class="panel-body">
						@if ($organisation->billing_detail_id)
							<a href="{{ url('admin/billing', $organisation->billing_detail_id)  }}" class="btn btn-info">View Billing</a>
						@else
							<div class="text-center">
								No admin controls for this organisation.
							</div>
						@endif
					</div>
				</div>
			@endif

			<div class="panel panel-default">
				<div class="panel-body">
					@include('components.messages')
					<form method="POST" role="form" class="form-horizontal">
						<input type="hidden" name="_token" value="{{ csrf_token() }}">
						<h4>Details</h4>
						<div class="form-group">
							<div class="col-xs-12 col-md-12">
								<label class="control-label">Name</label>
								<input type="text" class="form-control" name="name" value="{{ $organisation->name }}">
							</div>
						</div>

						<hr />

						<h4>Members</h4>
						<div class="form-group">
							<div class="col-xs-12 col-md-12">
								@foreach($organisation->members as $member)
									{{ $organisation->members[0] !== $member ? ', ' : '' }}<a href="{{ action('UserController@getEdit', $member->id) }}">{{ $member->fullName() }}</a>
								@endforeach
							</div>
						</div>
						<div class="form-group">
							<div class="col-xs-12 col-md-12">
								<a href="{{ action('AdminController@getEditOrganisation', [$organisation->id, 'add-members']) }}" class="btn btn-default">Add Users</a>
							</div>
						</div>
						@if ($user->hasRole('global_admin'))
							<hr />
							<h4>Billing Options</h4>

							<div class="form-group">

								<div class="col-xs-12 col-md-4">
									<div class="checkbox">
										<label>
											<input type="checkbox" value="1" name="skip_billing" {{ $organisation->skip_billing ? 'checked' : '' }}>
											No Billing - Free Organisation
										</label>
									</div>
								</div>



								<div class="col-xs-12 col-md-4">
									<div class="checkbox">
										<label>
											<input type="checkbox" value="1" name="is_invoice_customer" {{ $organisation->is_invoice_customer ? 'checked' : '' }}>
											Invoice Customer
										</label>
									</div>
								</div>

								<div class="col-xs-12 col-md-4">
									<div class="checkbox">
										<label>
											<input type="checkbox" value="1" name="force_no_access" {{ $organisation->force_no_access ? 'checked' : '' }}>
											Remove all service access
										</label>
									</div>
								</div>
							</div>
						@endif

						<hr />

						<div class="form-group">
							<div class="col-xs-12">
								<button type="submit" class="btn btn-primary">Update</button>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection
