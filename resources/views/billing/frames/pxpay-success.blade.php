@extends('billing.frames.wrapper')

@section('content')
<body class="inside-panel">
	<div class="container-fluid">
		<div class="row">
			<div class="col-xs-12">
                <h3 class="text-center">Card Successfully Authorised!</h3>
			</div>
		</div>
	</div>
</body>
<script type="text/javascript">
    parent.submitCCForm();
</script>
@endsection
