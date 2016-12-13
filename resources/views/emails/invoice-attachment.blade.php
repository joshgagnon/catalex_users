<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>CataLex Invoice</title>
	<style type="text/css">
		body {
			width: 210mm;
			height: 276mm;
			font-family: sans-serif !important;
			font-size: 16px;
			position: relative;
			margin-bottom: 55px;
		}
		.row:after {
			display: table;
			content: '';
			clear: both;
		}
		.half, .third {
			float: left;
		}
		.half { width: 50%; }
		.third { width: 33.33%; }
		.right { text-align: right; }
		body > .row {
			margin: 1em 0;
		}
		h1 {
			font-size: 1.5em;
		}
		.b {
			font-weight: bold;
		}
		.footer {
			position: absolute;
			bottom: 0;
			width: 100%;
			height: 55px;
			font-size: 15px;
		}
		.footer label {
			display: inline-block;
			width: 100px;
			margin: 0.2em 0 0 0;
		}
	</style>
</head>
<body>
	<div><img src="../../public/images/logo-colourx2.png"></div>
	<h1>Invoice/Receipt for CataLex Good Companies</h1>
	<div class="row">
		<div class="half">
			<div>{{ $orgName or '' }}</div>
			<div>{{ $name }}</div>
		</div>
		<div class="half">
			<div class="row">
				<div class="half">
					<div class="b">Date:</div>
					<div class="b">Invoice #:</div>
					<div class="b">Account #:</div>
					<div class="b">GST #:</div>
				</div>
				<div class="half">
					<div>{{ $date }}</div>
					<div>{{ $invoiceNumber }}</div>
					<div>{{ $accountNumber }}</div>
					<div>114-642-495</div>
				</div>
			</div>
		</div>
	</div>
	@if($type === 'subscription')
        <div class="row b">
            <div class="half">Description</div>
            <div class="half">
                <div class="row">
                    <div class="third right">Paid Until</div>
                    <div class="third right">Price</div>
                </div>
            </div>
        </div>
        @foreach($listItems as $listItem)
		<div class="row">
			<div class="half">{{ $listItem['description'] }}</div>
			<div class="half">
				<div class="row">
                    <div class="third right">{{ $listItem['paidUntil'] }}</div>
					<div class="third right">${{ $listItem['amount'] }}</div>
				</div>
			</div>
		</div>
        @endforeach
	@endif
	<div class="row">
		<div class="half right b">Total Due</div>
		<div class="half">
			<div class="row">
				<div class="third">&nbsp;</div><div class="third">&nbsp;</div><div class="third right">${{  $totalAmount }}</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="half right b">GST Component</div>
		<div class="half">
			<div class="row">
				<div class="third">&nbsp;</div><div class="third">&nbsp;</div><div class="third right">${{ $gst }}</div>
			</div>
		</div>
	</div>
	<div class="b">Paid by way of credit card deduction on {{ $date }}</div>
	<div class="footer">
		<div><label>Supplier:</label>CataLex Limited (NZCN 5311842)</div>
		<div><label>Website:</label><a href="https://www.catalex.nz">www.catalex.nz</a></div>
		<div><label>Address:</label>C/- Kanu Jeram Chartered Accountant Limited, 112 Kitchener Road, Milford, Auckland 0620</div>
		<div><label>Email:</label><a href="mailto:mail@catalex.nz">mail@catalex.nz</a></div>
	</div>
</body>
</html>
