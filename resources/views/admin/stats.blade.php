@extends('app')

@section('content')
    <div class="container" xmlns="http://www.w3.org/1999/html">
        <h2>Stats</h2>

        @include('components.messages')

        <h3>
            <strong>Good Companies:</strong> {{ $totalGCCompanies }} companies total
        </h3>

        @foreach($gcCompanyCount as $countRecord)
            <p>
                {{ $countRecord->count }}
                {{ $countRecord->condition }}
            </p>
        @endforeach

        <hr />

        <h3>
            <strong>CataLex Sign:</strong> {{ $totalSignSubscriptions }} subscriptions total
        </h3>

        @foreach($signSubscriptionCount as $countRecord)
            <p>
                <strong>{{ $countRecord->count }}</strong>
                {{ $countRecord->condition }}
            </p>
        @endforeach
    </div>
@endsection
