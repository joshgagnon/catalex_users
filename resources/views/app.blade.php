<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>
	@section('title')
		CataLex
	@show
	</title>

	{{-- TODO: <link rel="shortcut icon" type="image/png" href="/favicon.png" /> --}}

	<link href="/css/app.css" rel="stylesheet">

	{{-- Fonts --}}
	<link href='//brick.a.ssl.fastly.net/Ubuntu:400,400i,500,700' rel='stylesheet' type='text/css'>

	<!--[if lt IE 9]>
		<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
		<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
	<![endif]-->
</head>
<body>
	<nav class="navbar navbar-default">
		<div class="container">
			<div class="navbar-header">
				<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#app-navbar-collapse"  aria-expanded="false">
					<span class="sr-only">Toggle Navigation</span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
				<a class="nav-brand" href="/"><span class="catalex-brand-1">Cata</span><span class="catalex-brand-2">Lex</span></a>
			</div>

			<div class="collapse navbar-collapse" id="app-navbar-collapse">
				<ul class="nav navbar-nav navbar-right">
					@if(Auth::guest())
						<li><a href="{{ action('Auth\AuthController@getLogin') }}">Login</a></li>
						<li><a href="{{ action('Auth\AuthController@getRegister') }}">Sign Up</a></li>
					@elseif(isset($user)) {{-- Move View::share(['user'] such that $user is always correctly available here --}}
						{{-- TODO: Use route helper --}}
                        <li><a href="/">Services</a></li>
						@if($user->hasRole('global_admin'))
							<li class="dropdown">
								<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">Admin <span class="caret"></span></a>
								<ul class="dropdown-menu" role="menu">
									<li><a href="{{ action('AdminController@getUsers') }}">Users</a></li>
									<li><a href="{{ action('AdminController@getOrganisations') }}">Organisations</a></li>
									<li><a href="{{ action('AdminController@getAccessLog') }}">Access Log</a></li>
								</ul>
							</li>
						@endif
						@if($user->can('view_own_organisation'))
							<li><a href="{{ action('OrganisationController@getIndex') }}">Organisation</a></li>
						@endif
						<li class="dropdown">
							<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">{{ $user->fullName() }} <span class="caret"></span></a>
							<ul class="dropdown-menu" role="menu">
								<li><a href="{{ action('UserController@getProfile') }}">My Profile</a></li>
								<li><a href="{{ route('user-services.index') }}">Edit My Services</a></li>
								<li><a href="{{ action('Auth\AuthController@getLogout') }}">Logout</a></li>
							</ul>
						</li>
					@endif
				</ul>
			</div>
		</div>
	</nav>

	@yield('content')
    <footer>
        <div class="container">
                <p class="copyright">© Copyright {{ date('Y') }} - CataLex® Limited. All rights reserved.  “CataLex” is a registered trademark of CataLex Limited</p>
                    <a href="//catalex.nz">Home</a>
                    <a href="//users.catalex.nz/privacypolicy">Privacy Policy</a>
                    <a href="//users.catalex.nz/termsofuse">Terms of Use</a>
                    <p>C/- Kanu Jeram Chartered Accountant Limited, 112 Kitchener Road, Milford, Auckland, 0620, New Zealand</p>
            <p><a href="mailto:mail@catalex.nz">mail@catalex.nz</a></p>
        </div>
    </footer>

	<script src="//cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
	<script src="//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.2/js/bootstrap.min.js"></script>
</body>
</html>
