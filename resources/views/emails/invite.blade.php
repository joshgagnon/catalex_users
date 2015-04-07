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
                                    <center>
                                        <h2 class="center">Get Started With CataLex</h2>
                                        <p>Hi {{ $name }},</p>
                                        <p class="center">Welcome to CataLex Law Browser!</p>
                                        <p class="center">At CataLex, we aim to re-invent law or, more accurately, how the public interacts with it. We believe that by combining technology and innovative thinking, legal services can be more accessible, cost-effective, understandable, and, most importantly, useful to business.</p>
                                        <p><a href="{{ $loginURL }}">Please login here </a> with the temporary password: {{ $tempPassword }}</p>
                                        <p>You should verify your details and choose a new password, then you'll be ready to use the Law Browser app.</p>
                                    </center>
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
