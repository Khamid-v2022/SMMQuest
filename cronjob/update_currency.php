<?php 
	require_once "include/db_configure.php";
    require_once "include/functions.php";
    

	$url = "https://api.currencyapi.com/v3/latest?apikey=" . $_ENV['CURRENCY_APIKEY'] . "&base_currency=USD&currencies=EUR,INR,TRY,RUB,BRL,NGN,KRW,THB,SAR,CNY,VND,KWD,EGP,PKR,PHP,IRR,ARS,MYR,IDR,AZN,TWD,UAH,PLN,HKD,COP,JPY,KES,KZT,ILS,GBP,IQD";

	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

	$resp = curl_exec($curl);
	curl_close($curl);
	// 

	$resp = json_decode($resp);

	$last_updated_at = strtotime($resp->meta->last_updated_at);
	$last_updated_at = date("Y-m-d H:i:s", $last_updated_at);

	$currency = $resp->data;

	$sql = "UPDATE `base_currency` SET `EUR`={$currency->EUR->value}, `INR`={$currency->INR->value}, `TRY`={$currency->TRY->value}, `RUB`={$currency->RUB->value}, `BRL`={$currency->BRL->value}, `NGN`={$currency->NGN->value}, `KRW`={$currency->KRW->value}, `THB`={$currency->THB->value}, `SAR`={$currency->SAR->value}, `CNY`={$currency->CNY->value}, `VND`={$currency->VND->value}, `KWD`={$currency->KWD->value}, `EGP`={$currency->EGP->value}, `PKR`={$currency->PKR->value}, `PHP`={$currency->PHP->value}, `IRR`={$currency->IRR->value}, `ARS`={$currency->ARS->value}, `MYR`={$currency->MYR->value}, `IDR`={$currency->IDR->value}, `AZN`={$currency->AZN->value}, `TWD`={$currency->TWD->value}, `UAH`={$currency->UAH->value}, `PLN`={$currency->PLN->value}, `HKD`={$currency->HKD->value}, `COP`={$currency->COP->value}, `JPY`={$currency->JPY->value}, `KES`={$currency->KES->value}, `KZT`={$currency->KZT->value}, `ILS`={$currency->ILS->value}, `GBP`={$currency->GBP->value}, `IQD`={$currency->IQD->value}, `updated_at`='{$last_updated_at}' WHERE `id` = 1";
	$result = $conn->query($sql);
?>