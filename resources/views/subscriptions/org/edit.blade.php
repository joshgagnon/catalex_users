@extends('app')

@section('title')
    CataLex - Edit Services
@endsection

@section('content')
    <div class="container">
        <h2 class="page-title">Subscriptions</h2>

        <div class="row">
            <div class="col-xs-12">
                <div class="panel panel-default">
                    <div class="panel-body">
                        @include('components.messages')

                        <form method="POST" role="form" class="form">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <input type="hidden" name="user_id" value="{{ $user->id }}">

                            <th class="form-group">
                                <table class="table subscriptions-table">
                                    <thead>
                                        <tr>
                                            <th><!-- Empty --></th>

                                            @foreach($services as $service)
                                                @if($service->name == 'Good Companies')
                                                    <th>
                                                        <i class="fa fa-briefcase"></i>
                                                        <div class="subscription-service-title">Good Companies</div>

                                                        <div class="subscription-service-unit">per company</div>
                                                        <div class="subscription-service-price">$1.50 monthly, $12 annually</div>
                                                    </th>
                                                @endif


                                                @if($service->name == 'CataLex Sign')
                                                    <th>
                                                        <i class="fa fa-pencil"></i>
                                                        <div class="subscription-service-title">CataLex Sign</div>


                                                        <div class="subscription-service-unit">per user</div>
                                                        <div class="subscription-service-price">$6 monthly, $60 annually</div>
                                                    </th>
                                                @endif
                                            @endforeach
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @foreach($members as $member)
                                            <?php $memberServices = $member->services->pluck('id')->toArray(); ?>
                                            <tr>
                                                <td>{{ $member->name }}</td>

                                                @foreach($services as $service)
                                                    <td>
                                                        <input type="checkbox" name="{{ 'subscriptions[' . $member->id . ']' . '[' . $service->id .']' }}" {{ in_array($service->id, $memberServices) ? 'checked' : '' }} />
                                                    </td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="form-group text-center">
                                <button type="submit" class="btn btn-primary">Update My Subscriptions</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
