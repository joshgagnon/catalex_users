@extends('app')

@section('content')
    <div class="container">
        <h2>Organisation Invites</h2>

        @include('components.messages')

        @forelse ($invites as $invite)
            <div class="panel panel-default">
                <div class="panel-body">
                    Basic panel example
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
