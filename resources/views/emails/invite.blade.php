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
									<h2>Get Started With CataLex</h2>
									<p>Hi {{ $user->fullName() }},</p>
									<p>Welcome to CataLex Law Browser!</p>
									<p>At CataLex, we aim to re-invent law or, more accurately, how the public interacts with it. We believe that by combining technology and innovative thinking, legal services can be more accessible, cost-effective, understandable, and, most importantly, useful to business.</p>
									<p><a href="{{ url('password/reset/' . $token) }}">Click here</a> to create your account with CataLex and access Law Browser</p>
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
