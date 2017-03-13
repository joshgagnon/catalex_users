@extends('app')

@section('title')
CataLex - Edit Organisation
@endsection

@section('content')
<div class="container">
    <h2>Edit {{ $organisation->name }}</h2>
    <div class="row">
        <div class="col-xs-12">
            <div class="panel panel-default">
                <div class="panel-body">
                    @include('components.messages')
                    <form method="POST" role="form" class="form-horizontal">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <h4>Details</h4>
                        <div class="form-group">
                            <div class="col-xs-12 col-md-12">
                                <label class="control-label">Name</label>
                                <input type="text" class="form-control" name="name" value="{{ $organisation->name }}">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <div class="col-xs-12">
                                <button type="submit" class="btn btn-primary">Update</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
