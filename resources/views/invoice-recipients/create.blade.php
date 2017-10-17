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
                <form action="{{ route('invoice-recipients.store') }}" method="POST">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">

                    <div class="row">
                        <div class="col-xs-12 col-md-6">
                            <div class="form-group">
                                <label>Name</label>
                                <input type="text" name="name" class="form-control" placeholder="Name" />
                            </div>
                        </div>

                        <div class="col-xs-12">
                            <div class="form-group">
                                <label>Email address</label>
                                <input type="email" name="email" class="form-control" placeholder="Email" />
                            </div>
                        </div>
                    </div>

                    <hr />

                    <button type="submit" class="btn btn-primary">Add Recipient</button>
                </form>
            </div>
        </div>
    </div>
@endsection
