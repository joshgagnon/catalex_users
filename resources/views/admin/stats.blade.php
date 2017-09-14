@extends('app')

<?php
    $map = [
        'user_monthly' => 'users (paying monthly)',
        'user_annually' => 'users (paying annually)',
        'org_monthly' => 'users in organisations (paying monthly)',
        'org_annually' => 'users in organisations (paying annually)',
    ]
?>

@section('content')
    <div class="container" xmlns="http://www.w3.org/1999/html">
        <h2>Stats</h2>

        @include('components.messages')

        <h3>
            <strong>Good Companies:</strong> {{ $totalGCCompanies }} companies total
        </h3>

        <h4>Paying Monthly</h4>

        <div style="margin-left: 25px;">
            <p><strong>{{ $gcCompaniesCounts['user_monthly'] ?? 0 }}</strong> companies for users</p>
            <p><strong>{{ $gcCompaniesCounts['org_monthly'] ?? 0 }}</strong> companies for users in organisations</p>
        </div>

        <h4>Paying Annually</h4>

        <div style="margin-left: 25px;">
            <p><strong>{{ $gcCompaniesCounts['user_annually'] ?? 0 }}</strong> companies for users</p>
            <p><strong>{{ $gcCompaniesCounts['org_annually'] ?? 0 }}</strong> companies for users in organisations</p>
        </div>

        <hr />

        <h3>
            <strong>CataLex Sign:</strong> {{ $totalSignSubscriptions }} subscriptions total
        </h3>

        <h4>Paying Monthly</h4>

        <div style="margin-left: 25px;">
            <p><strong>{{ $signSubscriptionsCounts['user_monthly'] ?? 0 }}</strong> users</p>
            <p><strong>{{ $signSubscriptionsCounts['org_monthly'] ?? 0 }}</strong> users in organisations</p>
        </div>

        <h4>Paying Annually</h4>

        <div style="margin-left: 25px;">
            <p><strong>{{ $signSubscriptionsCounts['user_annually'] ?? 0 }}</strong> users</p>
            <p><strong>{{ $signSubscriptionsCounts['org_annually'] ?? 0 }}</strong> users in organisations</p>
        </div>
    </div>
@endsection
