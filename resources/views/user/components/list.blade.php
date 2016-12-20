@if($user->can($viewPermission))
	<h3>{{ $title }}</h3>
	<table class="table table-condensed user-list">
		<thead>
			<tr>
                <th>Name</th>
				<th>Email</th>
				<th class="small-cell">Details</th>
				<th class="small-cell">Active</th>
				@if($user->can($editPermission))
					<th class="small-cell">Delete</th>
				@endif
                @if($user->can($editPermission))
                    <th class="small-cell">Login As</th>
                @endif
			</tr>
		</thead>
		<tbody>
			@foreach($users as $u)
				<tr{!! $u->deleted_at ? ' class="deleted"' : '' !!}>
                    <td>{{ $u->fullName() }}</td>
					<td>{{ $u->email }}</td>
					<td class="small-cell">
						@if($user->can($editPermission))
							<a href="{{ action('UserController@getEdit', $u->id) }}">Edit</a>
						@else
							<a href="{{ action('UserController@getView', $u->id) }}">View</a>
						@endif
					</td>
					<td class="small-cell">
						@if($u->active)
							<span class="glyphicon glyphicon-ok" aria-hidden="true"></span>
						@else
							<span class="glyphicon glyphicon-remove" aria-hidden="true"></span>
						@endif
					</td>
					@if($user->can($editPermission))
						<td class="small-cell">
							@if(!$u->deleted_at)
								<form action="{{ action('UserController@postDelete', $u->id) }}" method="post">
									<input type="hidden" name="_token" value="{{ csrf_token() }}">
									<button type="submit" class="btn btn-danger btn-xs">Delete</button>
								</form>
							@else
								<form action="{{ action('UserController@postUndelete', $u->id) }}" method="post">
									<input type="hidden" name="_token" value="{{ csrf_token() }}">
									<button type="submit" class="btn btn-warning btn-xs">Undelete</button>
								</form>
							@endif
						</td>
					@endif

                        <td class="small-cell">
                    @if($user->can($editPermission) && !$u->hasRole('global_admin'))
                            <a href="{{ action('AdminController@getBecomeUser', $u->id) }}" class="btn btn-info btn-xs">Login</a>
                    @endif
                        </td>


				</tr>
			@endforeach
		</tbody>
	</table>
	{!! $users->appends(Input::except('page'))->render() !!}
@endif
