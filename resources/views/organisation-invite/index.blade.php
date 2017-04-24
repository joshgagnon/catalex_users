@extends('app')

@section('content')
    <div class="container">
        <h2>Organisation Invites</h2>

        @include('components.messages')

        @forelse ($invites as $invite)
            <div class="panel panel-default">
                <div class="panel-body inline">
                    <form action="{{ route('organisation-invites.accept', $invite->id) }}" method="POST">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">

                        <button type="submit" class="btn btn-success pull-right">Join</button>
                    </form>

                    <form action="{{ route('organisation-invites.delete', $invite->id) }}" method="POST">
                        <input type="hidden" name="_method" value="DELETE">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">

                        <button type="submit" class="btn btn-default pull-right" style="margin-right: 8px;">Dismiss</button>
                    </form>

                    <p>Invite to: <strong>{{ $invite->organisation->name }}</strong></p>
                </div>
            </div>
        @empty
            <div class="panel panel-default">
                <div class="panel-body">
                    No pending organisation invites.
                </div>
            </div>
        @endforelse
    </div>
@endsection
