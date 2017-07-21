<?php
ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);

include "lib/mercadopago.php";
include "lib/test.php";

/*
	{
		"coupon_code": "",
		"paymentMethodId": "bolbradesco",
		"site_id": "MLB",
		"amount": "5249.99",
		"campaign_id": "",
		"campaign": "",
		"discount": "",
		"firstname": "APRO",
		"lastname": "APRO",
		"docNumber": "19119119100",
		"address": "TESTE",
		"number": "99",
		"city": "Osasco",
		"state": "SP",
		"zipcode": "01508020"
	}
*/
$params_mercadopago = $_REQUEST['mercadopago_ticket'];

$mercadopago = new MP(MercadoPagoTest::getAccessTokenSellerTest($params_mercadopago['site_id']));

if($params_mercadopago['paymentMethodId'] == ""){
  $params_mercadopago['paymentMethodId'] = $params_mercadopago['paymentMethodSelector'];
}


$payment = array();
$payment['transaction_amount'] = (float) $params_mercadopago['amount'];
$payment['description'] = "Loja teste 12345";
$payment['payment_method_id'] = $params_mercadopago['paymentMethodId'];
$payment['external_reference'] = "12345678";
$payment['statement_descriptor'] = "TESTE";


// custom code for notification url
$domain = $_SERVER['HTTP_HOST'];
if(strpos($domain, "localhost") === false){
  $route = str_replace("post.php", "", $_SERVER['REQUEST_URI']);
  $payment['notification_url'] = "http://" . $domain . $route . "notification.php?site_id=" . $params_mercadopago['site_id'];
}

if(isset($params_mercadopago['issuer']) && $params_mercadopago['issuer'] != "" && $params_mercadopago['issuer'] > -1){
  $payment['issuer_id'] = $params_mercadopago['issuer'];
}

//payer email
$payer_email = MercadoPagoTest::getEmailBuyerTest($params_mercadopago['site_id']);
$payment['payer']['email'] = $payer_email;

// Additional Info
// Items Info
$payment['additional_info']['items'] = array();
$item = array();
$item['id'] = "1234";
$item['title'] = "TV 32";
$item['picture_url'] = "";
$item['description'] = "TV 32 LCD";
$item['category_id'] = "others";
$item['quantity'] = (int) 1;
$item['unit_price'] = (float) 123.20;
$payment['additional_info']['items'][] = $item;

// Always Present Payer Info
$payment['additional_info']['payer']['first_name'] = $params_mercadopago['firstname'];
$payment['additional_info']['payer']['last_name'] = $params_mercadopago['lastname'];
$payment['additional_info']['payer']['address']['street_name'] = $params_mercadopago['address'];
$payment['additional_info']['payer']['address']['street_number'] = (int) $params_mercadopago['number'];
$payment['additional_info']['payer']['address']['zip_code'] = $params_mercadopago['zipcode'];
// Not Always Present Info
$payment['additional_info']['payer']['registration_date'] = "2015-06-02T12:58:41.425-04:00";
$payment['additional_info']['payer']['phone']['area_code'] = "11";
$payment['additional_info']['payer']['phone']['number'] = "1234 1234";
// MLB Payer Info
if ($params_mercadopago['site_id']) {
	$payment['payer']['identification']['type'] = "CPF";
	$payment['payer']['identification']['number'] = $params_mercadopago['docNumber'];
}

// Shipments Info
$payment['additional_info']['shipments']['receiver_address']['zip_code'] = $params_mercadopago['zipcode'];
$payment['additional_info']['shipments']['receiver_address']['street_name'] =
	$params_mercadopago['address'] . " - " .
	$params_mercadopago['number'] . " - " .
	$params_mercadopago['state'];
$payment['additional_info']['shipments']['receiver_address']['street_number'] = (int) $params_mercadopago['number'];
// $payment['additional_info']['shipments']['receiver_address']['floor'] = (int) "";
// $payment['additional_info']['shipments']['receiver_address']['apartment'] = "";


// Flow: for Customer & Cards
$customer = $mercadopago->get_or_create_customer($payer_email);

$payment['metadata']['customer_id'] = $customer['id'];

$payment = $mercadopago->create_payment($payment);

?>

<a href="ticket.php?site_id=<?php echo $params_mercadopago['site_id']; ?>"><?php echo $params_mercadopago['site_id']; ?></a>

<br/>
<br/>

<pre>
  <?php echo json_encode($_REQUEST, JSON_PRETTY_PRINT); ?>
</pre>
<pre>
  <?php echo json_encode($payment, JSON_PRETTY_PRINT); ?>
</pre>
