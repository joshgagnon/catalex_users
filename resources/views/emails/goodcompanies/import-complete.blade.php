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
                                    <h2 class="center">Companies Imported</h2>
                                    <p>Hi {{ $name }}</p>
                                    <p>Good Companies has successfully bulk imported <strong>{{ $successCount }}</strong> of <strong>{{ $totalCount }}</strong> companies.</p>
                                     @include('emails.layouts.partials.button', array('text' => 'View your Companies', 'url' => $link))
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
