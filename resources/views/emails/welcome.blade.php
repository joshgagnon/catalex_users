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
									<p>Thanks for signing up with CataLex &mdash; the legal catalyst. You can now <a href="{{ route('browser-login') }}">access Law Browser here</a>.</p>
									<p>Your username is: {{ $email }}</p>
									<p>Your free trial has started and will end on {{ $trialEnd }}. At the end of the free trial, we will send you an email asking if you want to continue with a paid subscription to Law Browser. It costs NZ${{ Config::get('constants.monthly_price') }} per user, per month.</p>
									<p>We will not deduct any fees from your credit card unless you choose to continue with a paid subscription.</p>
									<p>If you want to invite other people to use Law Browser, you can become an administrator for an organisation. <a href="{{ action('HomeController@index') }}">Click here</a> to get started.</p>
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
<table class="container buttons">
	<tr>
		<td>
			<table class="row">
				<tr>
					<td class="wrapper">
						<table class="four columns">
							<tr>
								<td class="one sub-columns last"></td>
								<td class="ten sub-columns">
									<table class="button btn-orange">
										<tr>
											<td>
												<a href="#{{-- TODO --}}">Tips &amp; Tricks</a>
											</td>
										</tr>
									</table>
								</td>
								<td class="one sub-columns last"></td>
								<td class="expander"></td>
							</tr>
						</table>
					</td>
					<td class="wrapper">
						<table class="four columns">
							<tr>
								<td class="one sub-columns last"></td>
								<td class="ten sub-columns">
									<table class="button btn-primary">
										<tr>
											<td>
												<a href="{{ action('Auth\AuthController@getLogin') }}">Login Now</a>
											</td>
										</tr>
									</table>
								</td>
								<td class="one sub-columns last"></td>
								<td class="expander"></td>
							</tr>
						</table>
					</td>
					<td class="wrapper last">
						<table class="four columns">
							<tr>
								<td class="one sub-columns last"></td>
								<td class="ten sub-columns">
									<table class="button btn-facebook">
										<tr>
											<td>
												<a href="#{{-- TODO --}}">Facebook</a>
											</td>
										</tr>
									</table>
								</td>
								<td class="one sub-columns last"></td>
								<td class="expander"></td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
@endsection
