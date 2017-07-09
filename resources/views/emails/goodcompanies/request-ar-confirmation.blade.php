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
                                    <h2 class="center">Request to Confirm Annual Return</h2>
                                    <p>Hi {{ $name }}</p>
                                    <p>{{ $inviter }} would like you to confirm the details of <strong>{{ $companyName }}</strong> so its annual return can be filed with the Companies Office.</p>
                                    <p>Please review the company’s details within Good Companies <a href={{ $link }}>here</a>.   If the details are correct, click confirm.  If not, please note the required corrections in the form and submit the changes to {{ $inviter }} for updating.</p>
                                    @if (isset($requestBy) && !empty($requestBy))
                                        <p>{{ $name }} as reqested that response you by: <strong>{{ $requestBy }}</strong></p>
                                    @endif
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
