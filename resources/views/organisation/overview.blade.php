@extends('app')

@section('content')
<div class="container">
	<div class="row">
		<div class="col-xs-12">
			<h3>Members in {{ $organisation->name }}</h3>
			<ul>
				@foreach($organisation->members as $member)
					<li>{{ $member->fullName() }}</li>
				@endforeach
			</ul>
			<form class="form-horizontal" role="form" method="POST" action="/auth/login">
				Invite new member
			</form>
		</div>
	</div>
</div>
@endsection
