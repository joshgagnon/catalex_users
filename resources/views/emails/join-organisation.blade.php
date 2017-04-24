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
                                    <h2 class="center">Invitation to Join a CataLex Organisation </h2>
                                    <p>Hi {{ $name }},</p>
                                    <p>
                                        {{ $inviter }} has invited you to join their CataLex organisation '{{ $organisation }}'.
                                        <a href="{{ route('organisation-invites.index') }}">Click here</a> to join this organisation, otherwise you can ignore this message.
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
