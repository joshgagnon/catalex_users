@extends('app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <div class="text-center">
                            <h3>Welcome</h3>

                            <p>You have been invited to sign a document in CataLex Sign. First, click the button below to get a login link in your email inbox.</p>
                        </div>

                        <form class="text-center" method="POST">
                            {{ csrf_field() }}
                            <button type="submit" class="btn btn-primary">Email Login Link</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
