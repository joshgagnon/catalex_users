<?xml version="1.0"?>
<Txn>
	<PostUsername>{{ $postUsername }}</PostUsername>
	<PostPassword>{{ $postPassword }}</PostPassword>
	<Amount>{{ $amount }}</Amount>
	<InputCurrency>NZD</InputCurrency>
	<TxnType>Purchase</TxnType>
	<DpsBillingId>{{ $dpsBillingId }}</DpsBillingId>
	<MerchantReference>CataLex Ltd - ID {{ $id }}</MerchantReference>
</Txn>
