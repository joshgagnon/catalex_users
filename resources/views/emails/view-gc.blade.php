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
                                    <h2 class="center">Invitation to Access {{ $company_name }}. </h2>
                                    <p>Hi {{ $name }},</p>
                                    <p>
                                        {{ $inviter }} has invited you to access records for {{ $company_name }}.
                                        <a href="{{ url('/good-companies-login') }}">Click here</a> to view.
                                    </p>
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
