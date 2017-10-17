@extends('app')

@section('title')
    CataLex - Edit Services
@endsection

@section('content')
    <div class="container">
        @include('components.messages')

        <h2 class="page-title">Invoice Recipients</h2>

        <div class="panel panel-default">
            <div class="panel-body">
                <h3>Recipients</h3>
                <ol>
                    @foreach($recipients as $recipient)
                        <li>
                            <form action="{{ route('invoice-recipients.delete', $recipient->id) }}" method="POST" id="{{ 'delete-recipient-form-' . $recipient->id }}" class="hidden">
                                <input type="hidden" name="_method" value="DELETE">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            </form>

                            <strong>{{ $recipient->name }}:</strong> {{ $recipient->email }}
                            <a href="{{ route('invoice-recipients.edit', $recipient->id) }}">Edit</a>
                            <a href="#" onclick="document.getElementById('{{ 'delete-recipient-form-' . $recipient->id }}').submit()">Delete</a>
                        </li>
                    @endforeach
                </ol>

                <hr />

                <div>
                    <a href="#" class="btn btn-primary">Add Invoice Recipient</a>
                </div>
            </div>
        </div>
    </div>
@endsection
