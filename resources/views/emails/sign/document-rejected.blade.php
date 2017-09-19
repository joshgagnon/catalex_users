@extends('emails.layouts.non-member')

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
                                        <p>Hi {{ $recipientName }}</p>

                                        <p>{{ $rejectorName }} has declined to sign one or more documents. Click below to view.</p>

                                        @if (!empty($message))
                                            <p>Message from {{ $rejectorName }}: {{ $message }}</p>
                                        @endif

                                        @include('emails.layouts.partials.button', ['text' => 'View Documents', 'url' => $link])

                                        <p>Best regards</p>
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
