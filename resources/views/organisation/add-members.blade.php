@extends('app')

@section('title')
CataLex - Add Organisation Members
@endsection

@section('content')
<div class="container">
	<h2>Add Members: {{ $organisation->name }}</h2>
	<div class="row">
		<div class="col-xs-12">
			@include('components.messages')
			<form id="add-form" class="form-inline" role="form" method="POST">
				<input type="hidden" name="_token" value="{{ csrf_token() }}">
				<div id="first-row" class="invite-row">
					<div class="form-group">
						<label for="first_name">Name</label>
						<input type="text" class="form-control" id="first_name" name="name[]">
					</div>
					<div class="form-group">
						<label for="first_name">E-Mail</label>
						<input type="email" class="form-control" id="email" name="email[]">
					</div>
				</div>
				<div id="add-button-group" class="form-group">
					<label>&nbsp;</label>
					<button id="add-button" type="button" class="btn btn-info"><span class="glyphicon glyphicon-plus"></span>Add user</button>
				</div>
				<div>
					<div class="form-group">
						<label>&nbsp;</label>
						<button type="submit" class="btn btn-default">Send invitations</button>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>
<script type="text/javascript">
	document.getElementById('add-button').addEventListener('click', function() {
		var newRow = document.getElementById('first-row').cloneNode(true);
		newRow.id = null;
		inputs = newRow.getElementsByTagName('input');
		for(var i = 0; i < inputs.length; i++) {
			inputs.item(i).value = "";
		}
		document.getElementById('add-form').insertBefore(newRow, document.getElementById('add-button-group'));
	});
</script>
@endsection
