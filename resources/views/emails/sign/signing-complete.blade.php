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
                                        <h2 class="center">Documents Signed & Ready</h2>

                                        <p>Hi {{ $name }},</p>
                                        <p>The document set "{{ $setDescription }}" has now be signed by all recipients.</p>

                                        @include('emails.layouts.partials.button', ['text' => 'View Documents', 'url' => $link])

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
