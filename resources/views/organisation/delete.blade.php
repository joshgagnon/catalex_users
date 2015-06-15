@extends('app')

@section('title')
CataLex Law Browser - Delete Organisation
@endsection

@section('content')
<div class="container">
	<h2>Confirm Deletion: {{ $organisation->name }}</h2>
	<div class="row">
		<div class="col-xs-12">
			@include('components.messages')
			<p>Deleting this organisation will also delete the following users:</p>
			<ul>
				@foreach($organisation->members as $member)
					<li>{{ $member->fullName() }}</li>
				@endforeach
			</ul>
			<form class="form-inline" role="form" method="POST" action="{{ action('AdminController@postDeleteOrganisation', [$organisation->id, 'confirm']) }}">
				<input type="hidden" name="_token" value="{{ csrf_token() }}">
				<div class="form-group">
					<button type="submit" class="btn btn-default">Confirm Deletion</button>
				</div>
			</form>
		</div>
	</div>
</div>
@endsection
