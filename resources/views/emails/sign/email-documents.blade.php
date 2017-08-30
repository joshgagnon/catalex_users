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

                                        <p>Please find attached the following document that {{ $senderName }} sent you from <a href="https://sign.catalex.nz">CataLex Sign</a>.</p>

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
