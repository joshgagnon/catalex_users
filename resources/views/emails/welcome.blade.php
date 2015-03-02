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
										<h2 class="center">Get Started With Catalex</h2>
										<p>Hi {{ $name }},</p>
										<p class="center">Welcome to Catalex Law Browser!</p>
										<p class="center">At CataLex, we aim to re-invent law or, more accurately, how the public interacts with it. We believe that by combining technology and innovative thinking, legal services can be more accessible, cost-effective, understandable, and, most importantly, useful to business.</p>
										<p class="center">Choose from the links below to get started.</p>
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
												<a href="#">Tips &amp; Tricks</a>
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
												<a href="#">Login Now</a>
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
												<a href="#">Facebook</a>
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
