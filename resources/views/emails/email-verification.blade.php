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
                                        <h2 class="center">CataLex Email Verification</h2>

                                        <p>Hi {{ $name }},</p>
                                        <p>Please click the button below to verify to CataLex that this is your email address.</p>

                                        @include('emails.layouts.partials.button', ['text' => 'Verify Email', 'url' => $link])

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
