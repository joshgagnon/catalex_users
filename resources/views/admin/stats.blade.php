@extends('app')

@section('content')
    <div class="container">
        <h2>Stats</h2>

        @include('components.messages')

        <h4>Total companies: {{ $totalCompanies }}</h4>

        <hr />

        <dl>
            @foreach($companyCount as $countRecord)
                <dt>{{ $countRecord->count }}</dt>
                <dd>{{ $countRecord->condition }}</dd>
                <br />
            @endforeach
        </dl>
    </div>
@endsection
