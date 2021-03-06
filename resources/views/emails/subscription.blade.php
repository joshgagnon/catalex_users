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
                                    <p>Thanks for subscribing to Good Companies</p>
                                    <p>You can now add companies to your account by importing their details from the New Zealand companies register.  Click <a href="{{ route('good-companies-login') }}">here</a> to start.</p>
                                    <p>In 14 days, we will charge your credit/debit card based on the number of companies connected to your account on that day, and your chosen billing period.  Additional charges will be made if companies are added to your account within the billing period.</p>

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
