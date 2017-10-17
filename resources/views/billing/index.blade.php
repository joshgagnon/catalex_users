@extends('app')

@section('title')
    CataLex - Billing
@endsection

@section('content')
    <div class="container">
        @include('components.messages')

        @include('billing.billing-alert')

        <h2>
            Billing Overview |

            @if (!empty($organisation))
                {{ $organisation->name }}
            @else
                {{ $subject->fullName() }}
            @endif
        </h2>

        <hr />

        @if (Request::path() === 'billing')
            <h3>Options</h3>

            <a href="{{ route('billing.edit') }}" class="btn btn-default btn-sm">Card Details</a>
            <a href="{{ route('user-services.index') }}" class="btn btn-default btn-sm">Subscription</a>

            @if ($organisation)
                <a href="{{ route('invoice-recipients.index') }}" class="btn btn-default btn-sm">Invoice Recipients</a>
            @endif

            <hr />
        @endif

        @if ($discountPercent)
            <h3>Discount</h3>
            <p>All of your bills will include a discount of <strong>{{ $discountPercent }}%</strong>.</p>

            <hr />
        @endif

        <h3>Billing Items</h3>

        <div class="scrollable-table-container">
            <table class="table table-condensed">
                <thead>
                <tr>
                    <th>Service</th>
                    <th>Name</th>
                    <th>Owner</th>
                    <th>Created Date</th>
                </tr>
                </thead>

                <tbody>
                @foreach ($billingItems as $billingItem)
                    <?php
                    $itemData = json_decode($billingItem->json_data, true);
                    $itemType = $billingItem->item_type;
                    $description = \App\BillingItem::description($itemType, $itemData);
                    ?>

                    <tr>
                        <td>{{ $billingItem->service->name }}</td>
                        <td>{{ $description }}</td>
                        <td>
                            @if ($billingItem->user)
                                {{ $billingItem->user->fullName() }}
                            @else
                                {{ $billingItem->organisation->name }}
                            @endif
                        </td>
                        <td>{{ $billingItem->created_at->format('j M Y')  }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <hr />

        <h3>Past Invoices</h3>

        <div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
            @forelse ($chargeLogs as $index => $chargeLog)
                <div class="panel panel-default">
                    <div class="panel-heading" role="tab" id="{{ 'heading' . $chargeLog->id }}">
                        <h5 class="panel-title">
                            <a role="button" data-toggle="collapse" data-parent="#accordion"
                               href="{{ '#collapse' . $chargeLog->id }}" aria-expanded="false"
                               aria-controls="{{ 'collapse' . $chargeLog->id }}">
                                <div class="row">
                                    <div class="col-sm-3">
                                        {{ $chargeLog->timestamp->format('j M Y') }}
                                    </div>

                                    <div class="col-sm-6 text-center">
                                        ${{ $chargeLog->total_amount }}
                                    </div>

                                    <div class="col-sm-3">
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
                    <div id="{{ 'collapse' . $chargeLog->id }}"
                         class="panel-collapse collapse {{ $index == 0 ? 'in' : '' }}" role="tabpanel"
                         aria-labelledby="{{ 'heading' . $chargeLog->id }}">
                        <div class="panel-body">
                            <dl>
                                <dt>Amount</dt>
                                <dd>${{ $chargeLog->total_amount }}</dd>

                                <dt>Including GST</dt>
                                <dd>${{ $chargeLog->gst }}</dd>

                                @if ($chargeLog->discount_percent)
                                    <dt>Including Discount</dt>
                                    <dd>{{ $chargeLog->discount_percent }}%</dd>
                                @endif

                                <dt>Date</dt>
                                <dd>{{ $chargeLog->timestamp->format('j M Y') }}</dd>
                            </dl>

                            @if ($chargeLog->success && !$chargeLog->pending)
                                <div>
                                    <a href="{{ route('invoices.view', $chargeLog->id) }}" class="btn btn-default"
                                       target="_blank">View Invoice</a>
                                    <a href="{{ route('invoices.download', $chargeLog->id) }}" class="btn btn-default">Download
                                        Invoice</a>

                                    @if ($user->hasRole('global_admin'))
                                        <form action="{{ route('invoices.resend', $chargeLog->id) }}" method="post"
                                              style="display: inline-block;">
                                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                            <button type="submit" class="btn btn-danger">Resend Invoice</button>
                                        </form>
                                    @endif
                                </div>
                            @endif
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
