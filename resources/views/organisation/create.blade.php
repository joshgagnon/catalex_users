@extends('app')

@section('content')
<div class="container">
	<h2>Create Organisation</h2>
	<div class="row">
		<div class="col-xs-12">
			@include('components.messages')
			<p>Creating an organisation will allow you to invite team members and manage their Law Browser subscription. Simply choose an organisation name and click 'Create'.</p>
			<form class="form-inline" role="form" method="POST" action="{{ action('OrganisationController@postCreate') }}">
				<input type="hidden" name="_token" value="{{ csrf_token() }}">
				<div class="form-group">
					<label for="organisation_name">Organisation Name</label>
					<input type="text" class="form-control" id="organisation_name" name="organisation_name" value="{{ old('organisation_name') }}">
				</div>
				<div class="form-group">
					<label>&nbsp;</label>
					<button type="submit" class="btn btn-default">Create</button>
				</div>
			</form>
		</div>
	</div>
</div>
@endsection
