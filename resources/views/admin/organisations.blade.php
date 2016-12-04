@extends('app')

@section('content')
<div class="container">
	<h2>Organisation Admin</h2>
	<div class="row">
		<div class="col-xs-12 col-sm-6">
			<a href="{{ action('AdminController@getCreateOrganisation') }}"><span class="glyphicon glyphicon-plus"></span>Add organisation</a>
		</div>
		<div class="col-xs-12 col-sm-6">
			@if($showDeleted)
				<a href="{{ URL::current() }}">Hide deleted organisations</a>
			@else
				<a href="{{ URL::current() }}?deleted=1">Show deleted organisations</a>
			@endif
		</div>
	</div>
	<div class="row">
		<div class="col-xs-12">
			@include('components.messages')
			<table class="table table-condensed user-list">
				<thead>
					<tr>
						<th>Name</th>
						<th class="small-cell">Members</th>
						<th class="small-cell">Details</th>
						<th class="small-cell">Delete</th>
					</tr>
				</thead>
				<tbody>
					@foreach($organisations as $o)
						<tr{!! $o->deleted_at ? ' class="deleted"' : '' !!}>
							<td>{{ $o->name }}</td>
							<td class="small-cell">{{ count($o->members) }}</td>
							<td class="small-cell">
								<a href="{{ action('AdminController@getEditOrganisation', $o->id) }}">Edit</a>
							</td>
							<td class="small-cell">
								@if(!$o->deleted_at)
									<form action="{{ action('AdminController@postDeleteOrganisation', $o->id) }}" method="post">
										<input type="hidden" name="_token" value="{{ csrf_token() }}">
										<button type="submit" class="btn btn-danger btn-xs">Delete</button>
									</form>
								@else
									<form action="{{ action('AdminController@postUndeleteOrganisation', $o->id) }}" method="post">
										<input type="hidden" name="_token" value="{{ csrf_token() }}">
										<button type="submit" class="btn btn-warning btn-xs">Undelete</button>
									</form>
								@endif
							</td>
						</tr>
					@endforeach
				</tbody>
			</table>
            {!! $organisations->appends(Input::except('page'))->render() !!}
		</div>
	</div>
</div>
@endsection
