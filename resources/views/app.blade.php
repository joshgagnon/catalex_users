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

    <link rel="shortcut icon" type="image/png" href="/favicon.png" />

    <link href="{{ elixir('css/app.css') }}" rel="stylesheet">

    {{-- Fonts --}}
    <link href='//brick.a.ssl.fastly.net/Ubuntu:300,400,400i,500,700' rel='stylesheet' type='text/css'>

    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','GTM-5NXTBW3');</script>

    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<body>
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-5NXTBW3"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    @if ($isImpersonating)
        <div class="impersonation-banner">
            <div class="container">
                <span class="pull-left">
                    You are currently impersonating <strong>{{ $user->fullName() }}</strong>.
                </span>

                <form action="{{ url('impersonation') }}" method="POST" id="return-to-admin-form">
                    <input type="hidden" name="_method" value="DELETE">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">

                    <button type="submit" class="btn btn-primary btn-sm pull-right">Return to Admin</button>
                </form>
            </div>
        </div>
    @endif

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
                        <li><a href="{{ route('index')}}">CataLex Home</a></li>

                        @if($user->hasRole('global_admin'))
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">Admin <span class="caret"></span></a>
                                <ul class="dropdown-menu" role="menu">
                                    <li><a href="{{ route('admin.users') }}">Users</a></li>
                                    <li><a href="{{ action('AdminController@getOrganisations') }}">Organisations</a></li>
                                    <li><a href="{{ route('admin.stats') }}">Stats</a></li>
                                    <li><a href="{{ action('AdminController@getAccessLog') }}">Access Log</a></li>
                                </ul>
                            </li>
                        @endif

                        <li><a href="{{ route('organisation.index') }}">Organisation</a></li>
                        <li class="dropdown">
                            <a href="{{ route('index')}}" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">{{ $user->fullName() }} <span class="caret"></span></a>
                            <ul class="dropdown-menu" role="menu">
                                <li><a href="{{ action('UserController@getProfile') }}">My Profile</a></li>

                                @if ($showBilling)
                                    <li><a href="{{ url('billing') }}">Billing</a></li>
                                @endif

                                @if ($isImpersonating)
                                    <li><a href="#" onclick="document.getElementById('return-to-admin-form').submit()">Return to Admin</a></li>
                                @else
                                    <li><a href="{{ action('Auth\AuthController@getLogout') }}">Logout</a></li>
                                @endif
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
                <p class="copyright">© Copyright {{ date('Y') }} - CataLex Limited. All rights reserved.  “CataLex” is a registered trademark of CataLex Limited</p>
                    <a href="/">CataLex Home</a>
                    <a href="//catalex.nz">catalex.nz</a>
                    <a href="//users.catalex.nz/privacypolicy">Privacy Policy</a>
                    <a href="//users.catalex.nz/termsofuse">Terms of Use</a>
            <p><a href="mailto:mail@catalex.nz">mail@catalex.nz</a></p>
        </div>
    </footer>

    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.2/js/bootstrap.min.js"></script>
    <script src="{{ elixir('js/app.js') }}"></script>

    <script type="text/javascript">
        $(document).ready(function(){
            $('[data-toggle="popover"]').popover({ trigger: 'hover' });
        });
    </script>
</body>
</html>
