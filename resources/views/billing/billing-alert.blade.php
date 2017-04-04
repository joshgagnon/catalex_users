@if (!$subscriptionUpToDate)
    <div class="alert alert-danger">

        @if ($user->can('edit_own_organisation') || !$user->organisation)
            <h3 class="alert-heading">Your last bill failed.</h3>
            <p>Please click the button below to resolve this issue, before you can access your paid services.</p>

            <a href="{{ route('billing.edit') }}" class="btn btn-danger">Resolve Billing Issue</a>
        @else
            You are unable to access some services at the moment. Please contact one of your organisation's administrators to regain access.
        @endif
    </div>
@endif
