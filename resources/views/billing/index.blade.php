@extends('app')

@section('title')
    CataLex - Billing
@endsection

@section('content')
    <div class="container">
        @include('components.messages')

        <h2>
            Billing Overview |

            @if ($organisation)
                {{ $organisation->name }}
            @else
                {{ $user->fullName() }}
            @endif
        </h2>

        <h3>Past Invoices</h3>

        <div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
            @foreach ($chargeLogs as $chargeLog)
                <div class="panel panel-default">
                    <div class="panel-heading" role="tab" id="{{ 'heading' . $chargeLog->id }}">
                        <h5 class="panel-title">
                            <a role="button" data-toggle="collapse" data-parent="#accordion" href="{{ '#collapse' . $chargeLog->id }}" aria-expanded="false" aria-controls="{{ 'collapse' . $chargeLog->id }}">
                                <div class="row">
                                    <div class="col-md-3">
                                        {{ $chargeLog->timestamp->format('j M Y') }}
                                    </div>

                                    <div class="col-md-6 text-center">
                                        ${{ $chargeLog->total_amount }}
                                    </div>

                                    <div class="col-md-3">
                                        @if ($chargeLog->pending)
                                            <span class="text-warning pull-right">
                                                <i class="fa fa-ellipsis-h" aria-hidden="true"></i> Pending
                                            </span>
                                        @elseif ($chargeLog->success)
                                            <span class="text-success pull-right">
                                                <i class="fa fa-check" aria-hidden="true"></i> Successful
                                            </span>
                                        @else
                                            <span class="text-danger pull-right">
                                                <i class="fa fa-times" aria-hidden="true"></i> Failed
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </a>
                        </h5>
                    </div>
                    <div id="{{ 'collapse' . $chargeLog->id }}" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="{{ 'heading' . $chargeLog->id }}">
                        <div class="panel-body">
                            <dl>
                                <dt>Amount</dt>
                                <dd>${{ $chargeLog->total_amount }}</dd>

                                <dt>Including GST</dt>
                                <dd>${{ $chargeLog->gst }}</dd>

                                <dt>Date</dt>
                                <dd>{{ $chargeLog->timestamp->format('j M Y') }}</dd>
                            </dl>

                            <a href="{{ route('invoices.view', $chargeLog->id) }}" class="btn btn-info pull-left" target="_blank">View Invoice</a>


                            @if ($user->hasRole('global_admin'))
                                <form action="{{ route('invoices.resend', $chargeLog->id) }}" method="post" class="pull-left">
                                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                    <button type="submit" class="btn btn-danger" style="margin-left: 4px;">Resend Invoice</button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach


            <div class="panel panel-default">
                <div class="panel-heading" role="tab" id="headingOne">
                    <h4 class="panel-title">
                        <a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                            Collapsible Group Item #1
                        </a>
                    </h4>
                </div>
                <div id="collapseOne" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne">
                    <div class="panel-body">
                        Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident. Ad vegan excepteur butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth nesciunt you probably haven't heard of them accusamus labore sustainable VHS.
                    </div>
                </div>
            </div>
            <div class="panel panel-default">
                <div class="panel-heading" role="tab" id="headingTwo">
                    <h4 class="panel-title">
                        <a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                            Collapsible Group Item #2
                        </a>
                    </h4>
                </div>
                <div id="collapseTwo" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingTwo">
                    <div class="panel-body">
                        Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident. Ad vegan excepteur butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth nesciunt you probably haven't heard of them accusamus labore sustainable VHS.
                    </div>
                </div>
            </div>
            <div class="panel panel-default">
                <div class="panel-heading" role="tab" id="headingThree">
                    <h4 class="panel-title">
                        <a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                            Collapsible Group Item #3
                        </a>
                    </h4>
                </div>
                <div id="collapseThree" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingThree">
                    <div class="panel-body">
                        Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident. Ad vegan excepteur butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth nesciunt you probably haven't heard of them accusamus labore sustainable VHS.
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
