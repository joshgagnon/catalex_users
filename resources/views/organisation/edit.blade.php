@extends('app')

@section('title')
CataLex Law Browser - Edit Organisation
@endsection

@section('content')
<div class="container">
	<h2>Edit {{ $organisation->name }}</h2>
	<div class="row">
		<div class="col-xs-12">
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
								<a href="{{ action('AdminController@getEditOrganisation', [$organisation->id, 'add-members']) }}" class="btn btn-info">Add Users</a>
							</div>
						</div>
						<!-- <h4>Billing Status</h4>
						<div class="form-group">
							<div class="col-xs-12 col-md-12">
								@if($organisation->billingExempt())
									<p>Exempt from billing</p>
								@elseif($organisation->inTrial())
									<p>In trial period</p>
								@elseif(!$organisation->everBilled())
									<p>Trial period expired, awaiting subscription confirmation</p>
								@elseif($organisation->isPaid())
									@if($organisation->owedAmount() !== '0.00')
										<p>Payment of {{ $organisation->owedAmount() }} due immediately</p>
									@else
										<p>Up to date - next payment of {{ $organisation->paymentAmount() }} due on {{ $organisation->paid_until->format('j M Y') }}</p>
									@endif
								@else
									<p><strong>WARNING:</strong> Billing details in an inconsistent state - please contact a developer</p>
								@endif
							</div>
						</div> -->
						<!-- @if($user->hasRole('global_admin'))
							<h4>Flags</h4>
							<div class="form-group">
								<div class="col-xs-12 col-md-4">
									<div class="checkbox">
										<input type="hidden" value="0" name="free">
										<label><input type="checkbox" value="1" name="free" {{ $organisation->free ? 'checked' : '' }}> Free (non-billable) Organisation</label>
									</div>
								</div>
							</div>
						@endif -->
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
