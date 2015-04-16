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
									<h2>Password Reset</h2>
									<p>Hi {{ $user->fullName() }},</p>
									<p><a href="{{ url('password/reset/' . $token) }}">Click here</a> to reset your CataLex password. This link will remain valid for 24 hours. If you did not request a password reset, you can safely ignore this email.</p>
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
