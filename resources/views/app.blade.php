<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>
	@section('title')
		CataLex Law Browser
	@show
	</title>

	{{-- TODO: <link rel="shortcut icon" type="image/png" href="/favicon.png" /> --}}

	<link href="/css/app.css" rel="stylesheet">

	{{-- Fonts --}}
	<link href='//fonts.googleapis.com/css?family=Roboto:400,300' rel='stylesheet' type='text/css'>
	<link href='//brick.a.ssl.fastly.net/Ubuntu:300,400,400i,700' rel='stylesheet' type='text/css'>

	<!--[if lt IE 9]>
		<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
		<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
	<![endif]-->
</head>
<body>
	<nav class="navbar">
		<div class="container">
			<div class="navbar-header">
				<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
					<span class="sr-only">Toggle Navigation</span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
				<a class="navbar-brand" href="/"><img alt="Catalex" src="/images/logo-colourx2.png"></a>
			</div>

			<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
				@if(Auth::check())
					<ul class="nav navbar-nav">
						{{-- TODO: Use route helper --}}
						<li><a href="/">Home</a></li>
						<li><a href="/organisation">Organisation</a></li>
					</ul>
				@endif

				<ul class="nav navbar-nav navbar-right">
					@if(Auth::guest())
						<li><a href="/auth/login">Login</a></li>
						<li><a href="/auth/register">Sign Up</a></li>
					@else
						<li class="dropdown">
							<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">{{ $user->fullName() }} <span class="caret"></span></a>
							<ul class="dropdown-menu" role="menu">
								<li><a href="/auth/logout">Logout</a></li>
							</ul>
						</li>
					@endif
				</ul>
			</div>
		</div>
	</nav>

	@yield('content')

	<footer class="footer">
		<div class="container text-center">
			<p class="copyright">© Copyright {{ date('Y') }} - CataLex Limited. All rights reserved.</p>
			<p class="links"><a href="/customeragreement">Customer Agreement</a><a href="/privacypolicy">Privacy Policy</a><a href="/termsofuse">Law Browser Terms of Use</a></p>
			<p>P: C/- Kanu Jeram Chartered Accountant Limited, 112 Kitchener Road, Milford, Auckland, 0620, New Zealand</p>
			<p>E: <a href="mailto:mail@catalex.nz">mail@catalex.nz</a> &nbsp; M: +64 274 538 552 &nbsp; Fax: +64 9 929 3332</p>
		</div>
	</footer>

	<script src="//cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
	<script src="//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.2/js/bootstrap.min.js"></script>
</body>
</html>
