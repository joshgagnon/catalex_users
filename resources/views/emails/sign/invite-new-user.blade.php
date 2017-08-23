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
                                        <h2 class="center">Invitation to sign a document in CataLex Sign.</h2>

                                        <p>Hi {{ $inviteeName }},</p>
                                        <p>{{ $inviterName }} has invited you to sign a document in CataLex Sign. Click below to set your CataLex account and sign the document.</p>

                                        @include('emails.layouts.partials.button', ['text' => 'Sign Document', 'url' => route('first-login.index', $token)])

                                        <p>Kind regards</p>
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
