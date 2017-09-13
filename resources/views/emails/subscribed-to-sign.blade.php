@extends('emails.ink-template')

@section('content')
    <table class="container main">
        <tr>
            <td>
                <table class="row">
                    <tr>
                        <td class="wrapper last">
                            <table class="twelve columns">
                                <tr>
                                    <td>
                                        <p>Hi {{ $name }},</p>

                                        <p>
                                            Thanks for subscribing to CataLex Sign.

                                            @if ($billingDate)
                                                You will be billed on {{ $billingDate }}.
                                            @endif
                                        </p>

                                        @if (!$emailVerified)
                                            <p>Click below to verify your email, so you can accept sign requests.</p>

                                            @include('emails.layouts.partials.button', ['text' => 'Verify Email Address', 'url' => $verifyEmailLink])
                                        @endif


                                        <p>The CataLex team</p>
                                        <p><a href="mailto:mail@catalex.nz">mail@catalex.nz</a></p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
@endsection
