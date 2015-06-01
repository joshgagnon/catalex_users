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
									<h2 class="center">Welcome to CataLex</h2>
									<p>Hi {{ $user->fullName() }},</p>
									<p>{{ $inviter }} has invited you to join the account {{ $user->organisation->name }} holds with CataLex. <a href="{{ url('password/reset/' . $token) }}">Click here</a> to confirm your user account and gain access to <a href="{{ Config::get('constants.law_browser_features') }}">Law Browser</a></p>
									<p>Your username is: {{ $user->email }}</p>
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
