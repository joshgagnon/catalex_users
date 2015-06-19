@extends('app')

@section('content')
<div class="container">
	<h2>Access Log</h2>
	<div class="row">
		<form method="get" class="form-inline">
			<div class="col-xs-12 col-sm-4">
				<div class="form-group">
					<input type="text" class="form-control" id="filter_email" name="filter_email" placeholder="Filter By Email" value="{{ $filterEmail or '' }}">
				</div>
			</div>
			<div class="col-xs-6 col-sm-2">
				<div class="checkbox"><label><input type="checkbox" name="user_logins" id="user_logins"{{ $includeUserLogins ? ' checked' : '' }}> User Logins</label></div>
			</div>
			<div class="col-xs-6 col-sm-2">
				<div class="checkbox"><label><input type="checkbox" name="browser_logins" id="browser_logins"{{ $includeBrowserLogins ? ' checked' : '' }}> Browser Logins</label></div>
			</div>
			<div class="col-xs-6 col-sm-2">
				<div class="checkbox"><label><input type="checkbox" name="logouts" id="logouts"{{ $includeLogouts ? ' checked' : '' }}> Logouts</label></div>
			</div>
			<div class="col-xs-6 col-sm-2">
				<button type="submit" class="btn btn-primary btn-sm">Update</button>
			</div>
		</form>
	</div>
	<div class="row">
		<div class="col-xs-12">
			@include('components.messages')
			<table class="table table-condensed user-list">
				<thead>
					<tr>
						<th>Name</th>
						<th class="mid-cell">Route</th>
						<th class="mid-cell">Time</th>
					</tr>
				</thead>
				<tbody>
					@foreach($logs as $l)
						<tr>
							<td>
								@if($l->user)
									<a href="{{ action('UserController@getEdit', $l->user->id) }}">{{ $l->user->fullName() }}</a>
								@else
									Not Logged In
								@endif
							</td>
							<td class="mid-cell">{{ $l->route }}</td>
							<td class="mid-cell">{{ $l->timestamp->copy()->toDateTimeString() }}</td>
						</tr>
					@endforeach
				</tbody>
			</table>
			{!! $logs->appends(Input::except('page'))->render() !!}
		</div>
	</div>
</div>
@endsection
