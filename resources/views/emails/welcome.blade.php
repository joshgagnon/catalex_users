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
									<p>Hi {{ $name }},</p>
									<p>Thanks for signing up with CataLex &mdash; the legal catalyst. You can now save and load sessions with <a href="{{ route('browser-login') }}">Law Browser</a>. You can also try out our new company administration service called <a href="https://users.catalex.nz/my-services?Good%2BCompanies=1">Good Companies</a>.  It costs just $12 annually or $1.50 monthly, per company.</p>
                                    <p>Your username is {{ $email }}</p>
									<p>If you would like to add users to your account, click <a href="{{ action('OrganisationController@getIndex') }}">here</a> to start or manage an organisation.</p>
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
