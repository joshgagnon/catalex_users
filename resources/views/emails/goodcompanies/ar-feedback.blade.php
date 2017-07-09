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
                                    <h2 class="center">Annual Return Feedback</h2>
                                    <p>Hi {{ $name }}</p>
                                    <p>{{ $feedbacker }} has provided feedback for the annual return of <strong>{{ $companyName }}</strong></p>
                                    <p>Click <a href={{ $link }}>here</a> to review.</p>
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
