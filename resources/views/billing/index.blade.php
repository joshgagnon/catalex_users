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
            @forelse ($chargeLogs as $index => $chargeLog)
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
                    <div id="{{ 'collapse' . $chargeLog->id }}" class="panel-collapse collapse {{ $index == 0 ? 'in' : '' }}" role="tabpanel" aria-labelledby="{{ 'heading' . $chargeLog->id }}">
                        <div class="panel-body">
                            <dl>
                                <dt>Amount</dt>
                                <dd>${{ $chargeLog->total_amount }}</dd>

                                <dt>Including GST</dt>
                                <dd>${{ $chargeLog->gst }}</dd>

                                <dt>Date</dt>
                                <dd>{{ $chargeLog->timestamp->format('j M Y') }}</dd>
                            </dl>

                            <div>
                                <a href="{{ route('invoices.view', $chargeLog->id) }}" class="btn btn-info" target="_blank">View Invoice</a>
                                <a href="{{ route('invoices.download', $chargeLog->id) }}" class="btn btn-info">Download Invoice</a>

                                @if ($user->hasRole('global_admin'))
                                    <form action="{{ route('invoices.resend', $chargeLog->id) }}" method="post" style="display: inline-block;">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <button type="submit" class="btn btn-danger">Resend Invoice</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="well text-center">
                    No invoices yet.
                </div>
            @endforelse
        </div>
    </div>
@endsection
