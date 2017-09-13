@extends('app')

@section('content')
    <div class="container" xmlns="http://www.w3.org/1999/html">
        <h2>Stats</h2>

        @include('components.messages')

        <h3>Good Companies</h3>
        <p>
            <strong>{{ $totalGCCompanies }}</strong>
            total companies
        </p>

        @foreach($gcCompanyCount as $countRecord)
            <p>
                {{ $countRecord->count }}
                {{ $countRecord->condition }}
            </p>
        @endforeach

        <hr />

        <h3>CataLex Sign</h3>
        <p>
            <strong>{{ $totalSignSubscriptions }}</strong>
            total subscriptions
        </p>

        @foreach($signSubscriptionCount as $countRecord)
            <p>
                <strong>{{ $countRecord->count }}</strong>
                {{ $countRecord->condition }}
            </p>
        @endforeach
    </div>
@endsection
