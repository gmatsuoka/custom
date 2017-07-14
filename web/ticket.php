<?php
ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);

include "lib/test.php";
include "lib/mercadopago.php";

if(!isset($_REQUEST['site_id'])){
  $_REQUEST['site_id'] = "MLA";
}
$payer_email = MercadoPagoTest::getEmailBuyerTest($_REQUEST['site_id']);
$mercadopago = new MP(MercadoPagoTest::getAccessTokenSellerTest($_REQUEST['site_id']));
$customer = $mercadopago->get_or_create_customer($payer_email);

$payment_methods = array();
$payments = $mercadopago->get( '/v1/payment_methods/?access_token=' . MercadoPagoTest::getAccessTokenSellerTest($_REQUEST['site_id']) );
foreach ( $payments['response'] as $payment ) {
  if ( isset( $payment['payment_type_id'] ) ) {
    if ( $payment['payment_type_id'] != 'account_money' &&
      $payment['payment_type_id'] != 'credit_card' &&
      $payment['payment_type_id'] != 'debit_card' &&
      $payment['payment_type_id'] != 'prepaid_card' ) {
      array_push( $payment_methods, $payment );
    }
  }
}

?>

<html>

<head>
  <link rel="stylesheet" type="text/css" href="css/custom_checkout_mercadopago.css">
</head>

<body>

  <div id="mp-box-form">

    <!-- Copy from here -->

    <?php
    $images_path = 'images/';
    $form_labels = array(
      "form" => array(
        "payment_converted" => "Payment converted from",
        "to" => "to",
        "coupon_empty" => "Please, inform your coupon code",
        "apply" => "Apply",
        "remove" => "Remove",
        "discount_info1" => "You will save",
        "discount_info2" => "with discount from",
        "discount_info3" => "Total of your purchase:",
        "discount_info4" => "otal of your purchase with discount:",
        "discount_info5" => "*Uppon payment approval",
        "discount_info6" => "Terms and Conditions of Use",
        "coupon_of_discounts" => "Discount Coupon",
        "label_choose" => "Choose",
        "issuer_selection" => "Please, select the ticket issuer of your preference.",
        "payment_instructions" => "Click \"Place order\" button. The ticket will be generated and you will be redirected to print it.",
        "ticket_note" => "Important: The order will be confirmed only after the payment approval."
      ),
      "error" => array(

        //card number
        "205" => "Parameter cardNumber can not be null/empty",
        "E301" => "Invalid Card Number",
        //expiration date
        "208" => "Invalid Expiration Date",
        "209" => "Invalid Expiration Date",
        "325" => "Invalid Expiration Date",
        "326" => "Invalid Expiration Date",
        //card holder name
        "221" => "Parameter cardholderName can not be null/empty",
        "316" => "Invalid Card Holder Name",

        //security code
        "224" => "Parameter securityCode can not be null/empty",
        "E302" => "Invalid Security Code",
        "E203" => "Invalid Security Code",

        //doc type
        "212" => "Parameter docType can not be null/empty",
        "322" => "Invalid Document Type",
        //doc number
        "214" => "Parameter docNumber can not be null/empty",
        "324" => "Invalid Document Number",
        //doc sub type
        "213" => "The parameter cardholder.document.subtype can not be null or empty",
        "323" => "Invalid Document Sub Type",
        //issuer
        "220" => "Parameter cardIssuerId can not be null/empty",
      ),
      "coupon_error" => array(
        "EMPTY" => "Please, inform your coupon code"
      )
    );
    ?>

    <!-- Ticket logo and banner -->
    <div width="100%" style="margin:1px; background:white;">
      <img class="logo" src="<?php echo ($images_path . 'mplogo.png'); ?>" width="156" height="40" />
      <?php if ( count( $payment_methods ) > 1 ) : ?>
        <img class="logo" src="<?php echo ($images_path . 'boleto.png'); ?>"
        width="90" height="40" style="float:right;"/>
      <?php else : ?>
        <?php foreach ( $payment_methods as $payment ) : ?>
          <img class="logo" src="<?php echo $payment['secure_thumbnail']; ?>" width="90" height="40"
          style="float:right;"/>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <form action="post_ticket.php" method="post">
      <div class="mp-box-inputs mp-line" id="mercadopago-form-coupon-ticket">
        <label for="couponCodeLabel">
          <?php echo $form_labels['form']['coupon_of_discounts']; ?>
        </label>
        <div class="mp-box-inputs mp-col-65">
          <input type="text" id="couponCodeTicket" name="mercadopago_ticket[coupon_code]"
          autocomplete="off" maxlength="24" />
        </div>
        <div class="mp-box-inputs mp-col-10">
          <div id="mp-separete-date"></div>
        </div>
        <div class="mp-box-inputs mp-col-25">
          <input type="button" class="button" id="applyCouponTicket"
          value="<?php echo $form_labels['form']['apply']; ?>">
        </div>
        <div class="mp-box-inputs mp-col-100 mp-box-message">
          <span class="mp-discount" id="mpCouponApplyedTicket" ></span>
          <span class="mp-error" id="mpCouponErrorTicket" ></span>
        </div>
      </div>

      <div>
        <p>
          <?php
            if ( count( $payment_methods ) > 1 ) :
              echo $form_labels['form']['issuer_selection'];
            endif;
            echo ' ' . $form_labels['form']['payment_instructions'];
          ?> <br /> <?php
            echo $form_labels['form']['ticket_note'];
          ?>
        </p>
        <?php if ( count( $payment_methods ) > 1 ) : ?>
          <div class="mp-box-inputs mp-col-100">
            <?php $atFirst = true; ?>
            <?php foreach ( $payment_methods as $payment ) : ?>
              <div class="mp-box-inputs mp-line">
                <div id="paymentMethodId" class="mp-box-inputs mp-col-5">
                  <input type="radio" class="input-radio"
                  name="mercadopago_ticket[paymentMethodId]"
                  style="height:16px; width:16px;" value="<?php echo $payment['id']; ?>"
                  <?php if ( $atFirst ) : ?> checked="checked" <?php endif; ?> />
                </div>
                <div class="mp-box-inputs mp-col-45">
                  <label>
                    <img src="<?php echo $payment['secure_thumbnail']; ?>"
                    alt="<?php echo $payment['name']; ?>" />
                    &nbsp;&nbsp;<?php echo $payment['name']; ?>
                  </label>
                </div>
              </div>
              <?php $atFirst = false; ?>
            <?php endforeach; ?>
          </div>
        <?php else : ?>
          <div class="mp-box-inputs mp-col-100" style="display:none;">
            <select id="paymentMethodId" name="mercadopago_ticket[paymentMethodId]">
              <?php foreach ( $payment_methods as $payment ) : ?>
                <option value="<?php echo $payment['id']; ?>" style="padding: 8px;
                background: url('https://img.mlstatic.com/org-img/MP3/API/logos/bapropagos.gif')
                98% 50% no-repeat;"> <?php echo $payment['name']; ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        <?php endif; ?>

        <div class="mp-box-inputs mp-line">

          <div class="mp-box-inputs mp-col-50">
            <input type="submit" id="btnSubmit" value="Pay">
          </div>

          <!-- NOT DELETE LOADING-->
          <div class="mp-box-inputs mp-col-25">
            <div id="mp-box-loading">
            </div>
          </div>

        </div>

        <!-- utilities -->
        <div class="mp-box-inputs mp-col-100" id="mercadopago-utilities">
          <input type="text" id="site_id" value="<?php echo $_REQUEST['site_id']; ?>" name="mercadopago_ticket[site_id]"/>
          <input type="text" id="amountTicket" value="5249.99" name="mercadopago_ticket[amount]"/>
          <input type="text" id="campaign_idTicket" name="mercadopago_ticket[campaign_id]"/>
          <input type="text" id="campaignTicket" name="mercadopago_ticket[campaign]"/>
          <input type="text" id="discountTicket" name="mercadopago_ticket[discount]"/>
        </div>

      </div>
    </form>

    <script src="https://secure.mlstatic.com/sdk/javascript/v1/mercadopago.js"></script>
    <script src="js/MPv1Ticket.js?no_cache=<?php echo time(); ?>"></script>

    <script>
    var mercadopago_site_id = '<?php echo $_REQUEST['site_id']; ?>';
    var mercadopago_public_key = '<?php echo MercadoPagoTest::getPublicKeyTest($_REQUEST['site_id']); ?>';
    var mercadopago_payer_email = '<?php echo $payer_email; ?>';

    MPv1Ticket.text.choose = '<?php echo $form_labels["form"]["label_choose"]; ?>';
    MPv1Ticket.text.discount_info1 = '<?php echo $form_labels["form"]["discount_info1"]; ?>';
    MPv1Ticket.text.discount_info2 = '<?php echo $form_labels["form"]["discount_info2"]; ?>';
    MPv1Ticket.text.discount_info3 = '<?php echo $form_labels["form"]["discount_info3"]; ?>';
    MPv1Ticket.text.discount_info4 = '<?php echo $form_labels["form"]["discount_info4"]; ?>';
    MPv1Ticket.text.discount_info5 = '<?php echo $form_labels["form"]["discount_info5"]; ?>';
    MPv1Ticket.text.discount_info6 = '<?php echo $form_labels["form"]["discount_info6"]; ?>';
    MPv1Ticket.text.apply = '<?php echo $form_labels["form"]["apply"]; ?>';
    MPv1Ticket.text.remove = '<?php echo $form_labels["form"]["remove"]; ?>';
    MPv1Ticket.text.coupon_empty = '<?php echo $form_labels["form"]["coupon_empty"]; ?>';

    MPv1Ticket.Initialize(
      mercadopago_site_id,
      true,//mercadopago_coupon_mode == "yes",
      'discount.php',//mercadopago_discount_action_url,
      mercadopago_payer_email
    );
    </script>

    <?php include "html_test_ticket.php"; ?>

  </body>
