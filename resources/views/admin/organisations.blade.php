@extends('app')

@section('content')
<div class="container">
    <h2>Organisation Admin</h2>
    <div class="row">
        <div class="col-xs-12 col-sm-6">
            <a href="#TODO"><span class="glyphicon glyphicon-plus"></span>Add organisation</a>
        </div>
        <div class="col-xs-12 col-sm-6">
            @if($showDeleted)
                <a href="{{ URL::current() }}">Hide deleted organisations</a>
            @else
                <a href="{{ URL::current() }}?deleted=1">Show deleted organisations</a>
            @endif
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12">
            @include('components.messages')
            {{-- TODO: List organisations --}}
        </div>
    </div>
</div>
@endsection
