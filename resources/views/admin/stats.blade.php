@extends('app')

@section('content')
    <div class="container">
        <h2>Stats</h2>

        @include('components.messages')

        <dl>
            @foreach($companyCount as $countRecord)
                <dt>{{ $countRecord->count }}</dt>
                <dd>{{ $countRecord->condition }}</dd>
                <br />
            @endforeach
        </dl>
    </div>
@endsection
