@extends('app')

@section('title')
CataLex Law Browser - Create Organisation
@endsection

@section('content')
<div class="container">
    <h2>Create Organisation</h2>
    <div class="row">
        <div class="col-xs-12">
            @include('components.messages')
            <p><strong>Note:</strong> Creating an organisation directly from the admin panel will mark them as a free (non-billable) organisation until card details are provided.</p>
            <form class="form-inline" role="form" method="POST" action="{{ action('AdminController@postCreateOrganisation') }}">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <div class="form-group">
                    <label for="organisation_name">Organisation Name</label>
                    <input type="text" class="form-control" id="organisation_name" name="organisation_name" value="{{ old('organisation_name') }}">
                </div>
                <div class="form-group">
                    <label>&nbsp;</label>
                    <button type="submit" class="btn btn-default">Create</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
