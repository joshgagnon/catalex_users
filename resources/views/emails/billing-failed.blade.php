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
                                        <p>Hi {{ $name }},</p>
                                        <p>Unfortunately your last bill for CataLex failed. Please click below to update your billing.</p>

                                        <a href="{{ route('billing.edit') }}" class="btn btn-primary">Update Billing</a>

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
