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
									<p>Hi {{ $user->fullName() }},</p>
									<p>Your trial period with CataLex has come to an end. To continue accessing Law Browser, please <a href="{{ action('BillingController@getStart') }}">click here</a> to confirm your billing information.</p>
									<p>Best regards</p>
									<p>The CataLex team</p>
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
