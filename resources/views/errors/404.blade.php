@extends('app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-xs-12">
            <h2>The requested page could not be found</h2>
            <p><a href="#" onclick="history.go(-1); return false;">Back</a></p>
            <p><a href="/">Home</a></p>
        </div>
    </div>
</div>
@endsection
