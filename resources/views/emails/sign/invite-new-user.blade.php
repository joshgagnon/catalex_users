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
                                        <h2 class="center">Invitation to sign a document with CataLex Sign</h2>

                                        <p>Hi {{ $inviteeName }},</p>
                                        <p>{{ $inviterName }} has invited you to sign a document with CataLex Sign. Click below to set up your CataLex account and sign the document.</p>

                                        @if (!empty($message))
                                            <p>Message from {{ $inviterName }}: {{ $message }}</p>
                                        @endif

                                        @include('emails.layouts.partials.button', ['text' => 'Sign Document', 'url' => route('first-login.index', [$token, 'next' => $link])])

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
