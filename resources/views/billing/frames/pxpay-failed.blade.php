@extends('billing.frames.wrapper')

@section('content')
<body class="inside-panel">
	<div class="container-fluid">
		<div class="row">
			<div class="col-xs-12">
				<h3 class="text-center">Card Authorization Failed</h3>
				<p>This page will be reloaded in 5 seconds. Please check your details and try again.</p>
			</div>
		</div>
	</div>
	<script type="text/javascript">
		window.setTimeout(function() {
			window.top.location.reload(true);
		}, 5000);
	</script>
</body>
@endsection
