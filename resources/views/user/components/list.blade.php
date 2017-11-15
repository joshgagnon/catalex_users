@if($user->can($viewPermission))

    <?php
        $allowDeleteUsers = isset($allowDeleteUsers) ? : false; // Make sure $allowDeleteUsers is set - default being false
        $showFilterControls = isset($showFilterControls) ? : false;
    ?>

    <h3>{{ $title }}</h3>
    <div class="row">
  <div class="col-md-6 col-md-offset-3">
  @if($showFilterControls)
    <form>
    <div class="input-group">
      <input type="text" class="form-control" placeholder="Filter records"  name="filter" value="{{ app('request')->input('filter') }}">
      <span class="input-group-btn">
        <button class="btn btn-default" type="submit" >Filter</button>
        <a class="btn btn-default" href="{{ Request::url() }}" >Clear</a>
      </span>
    </div>
     <div class="checkbox text-center">
        <label>

          {!! Form::checkbox('deleted', 'true', app('request')->input('deleted') === 'true' ) !!} Show Deleted
        </label>
      </div>

    </form>
    @endif
    </div>
    </div>
    <table class="table table-condensed user-list">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th class="small-cell">Details</th>
                <th class="small-cell">Active</th>
                @if($user->hasRole('global_admin'))
                    <th class="small-cell">Free</th>
                    <th class="small-cell">Has Billing</th>
                    <th class="small-cell">Shadow</th>
                @endif
                @if($user->can($editPermission))
                    @if ($allowDeleteUsers)
                        <th class="small-cell">Delete</th>
                    @else
                        <th class="small-cell">Remove</th>
                    @endif
                @endif
                @if($user->hasRole('global_admin'))
                    <th class="small-cell">Login As</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach($users as $u)
                <tr{!! $u->deleted_at ? ' class="deleted"' : '' !!}>
                    <td>
                        @if($user->hasRole('global_admin'))
                            <span
                                data-toggle="popover"
                                data-html="true"
                                data-trigger="hover"
                                data-content="<b>ID:</b> {{ $u->id }} <br /> <b>Created at:</b> <br/> {{ Carbon\Carbon::parse($u->created_at)->format('d F, Y - g:i a') }}"
                            >
                                {{ $u->fullName() }}
                            </span>
                        @else
                            {{ $u->fullName() }}
                        @endif
                    </td>
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
                    @if($user->hasRole('global_admin'))
                        <td class="small-cell">
                            @if($u->free)
                                <span class="glyphicon glyphicon-ok" aria-hidden="true"></span>
                            @endif
                        </td>
                        <td class="small-cell">
                            @if($u->hasBillingSetup())
                                <span class="glyphicon glyphicon-ok" aria-hidden="true"></span>
                            @endif
                        </td>
                        <td class="small-cell">
                            @if($u->is_shadow_user)
                                <span class="glyphicon glyphicon-ok" aria-hidden="true"></span>
                            @endif
                        </td>
                    @endif
                    @if($user->can($editPermission))
                        @if ($allowDeleteUsers)
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
                        @else
                            <td class="small-cell">
                                @if(!$u->hasRole('organisation_admin'))
                                    <form action="{{ route('organisation.users.remove', $u->id) }}" method="post">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <button type="submit" class="btn btn-danger btn-xs">Remove</button>
                                    </form>
                                @endif
                            </td>
                        @endif
                    @endif

                    @if($user->hasRole('global_admin'))
                        <td class="small-cell">
                            @if (!$u->hasRole('global_admin'))
                                <form action="{{ url('impersonation', $u->id) }}" method="post">
                                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                    <button type="submit" class="btn btn-info btn-xs">Login</button>
                                </form>
                            @endif
                        </td>
                    @endif


                </tr>
            @endforeach
        </tbody>
    </table>
    {!! $users->appends(Input::except('page'))->render() !!}
@endif
