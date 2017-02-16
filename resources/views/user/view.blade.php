@extends('app')

@section('title')
CataLex - View User
@endsection

@section('content')
<div class="container">
	<h2>{{ $subject->fullName() }}</h2>
	<div class="row tabular">
		<div class="col-xs-12">
			<div class="form-inline">
				<div class="form-group">
					<label>Name</label>
					{{ $subject->fullName() }}
				</div>
			</div>
			<div class="form-inline">
				<div class="form-group">
					<label>Email</label>
					{{ $subject->email }}
				</div>
			</div>
		</div>
	</div>
</div>
@endsection
