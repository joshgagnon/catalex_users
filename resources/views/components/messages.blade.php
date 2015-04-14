@if(Session::has('success') || Session::has('status'))
	<div class="alert alert-success">
		{{ Session::has('success') ? Session::get('success') : Session::get('status') }}
	</div>
@endif
@if(count($errors) > 0)
	<div class="alert alert-danger">
		<ul>
			@foreach($errors->all() as $error)
				<li>{{ $error }}</li>
			@endforeach
		</ul>
	</div>
@endif
