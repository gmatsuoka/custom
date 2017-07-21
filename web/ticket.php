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
$customer = MercadoPagoTest::getInfoBuyerTest($_REQUEST['site_id']);

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
  <link rel="stylesheet" type="text/css" href="css/custom_checkout_mercadopago.css?no_cache=<?php echo time(); ?>">
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
        "payment_instructions" => "Click \"Pay\" button. The ticket will be generated and you will be redirected to print it.",
        "ticket_note" => "Important: The order will be confirmed only after the payment approval.",
        "febraban_rules" => "Informações solicitadas em conformidade com as normas das circulares Nro. 3.461/09, 3.598/12 e 3.656/13 do Banco Central do Brasil.",
        "select" => "SELECIONE...",
        "name" => "NOME",
        "surname" => "SOBRENOME",
        "docNumber" => "CPF",
        "address" => "ENDEREÇO",
        "number" => "NÚMERO",
        "city" => "CIDADE",
        "state" => "ESTADO",
        "zipcode" => "CEP"
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

        //febraban
        "FEB001" => "Obrigatório o preenchimento do Nome",
        "FEB002" => "Obrigatório o preenchimento do Sobrenome",
        "FEB003" => "Obrigatório o preenchimento do CPF",
        "FEB004" => "Obrigatório o preenchimento do Endereço",
        "FEB005" => "Obrigatório o preenchimento do número residencial",
        "FEB006" => "Obrigatório informar a cidade",
        "FEB007" => "Obrigatório informar o estado",
        "FEB008" => "Obrigatório informar o CEP"
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

    <form action="post_ticket.php" method="post" class="panel-body" id="mercadopago-ticket-form-general" name="mercadopago-ticket-form-general">

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

      <div id="mercadopago-form-ticket" class="mp-box-inputs mp-line">

        <div id="form-ticket">
          <div class="form-row">
            <div class="form-col-4">
              <label  for="firstname"><?php echo $form_labels['form']['name']; ?><em class="obrigatorio"> *</em></label>
              <input type="text" value="<?php echo $customer['firstname']; ?>" data-checkout="firstname" placeholder="<?php echo $form_labels['form']['name']; ?>" id="firstname" class="form-control-mine">
            </div>
            <div class="form-col-4">
              <label  for="lastname"><?php echo $form_labels['form']['surname']; ?><em class="obrigatorio"> *</em></label>
              <input type="text" value="<?php echo $customer['lastname']; ?>" data-checkout="lastname" placeholder="<?php echo $form_labels['form']['surname']; ?>" id="lastname" class="form-control-mine">
            </div>
            <div class="form-col-4">
              <label for="docNumber"><?php echo $form_labels['form']['docNumber']; ?><em class="obrigatorio"> *</em></label>
              <input type="text" placeholder="<?php echo $form_labels['form']['docNumber']; ?>" class="form-control-mine" maxlength="11" id="docNumber"
                onkeypress="return event.charCode >= 48 && event.charCode <= 57" data-checkout="docNumber" value="<?php echo $customer['docNumber']; ?>" >
            </div>
          </div>
          <span class="erro_febraban" data-main="#firstname" id="error_firstname"><?php echo $form_labels['error']['FEB001']; ?></span>
          <span class="erro_febraban" data-main="#lastname" id="error_lastname"><?php echo $form_labels['error']['FEB002']; ?></span>
          <span class="erro_febraban" data-main="#docNumber" id="error_docNumber"><?php echo $form_labels['error']['FEB003']; ?></span>
          <div class="form-row">
            <div class="form-col-9">
              <label for="address"><?php echo $form_labels['form']['address']; ?><em class="obrigatorio"> *</em></label>
              <input type="text" value="<?php echo $customer['address']; ?>" data-checkout="address" placeholder="<?php echo $form_labels['form']['surname']; ?>" id="address" class="form-control-mine">
            </div>
            <div class="form-col-3">
              <label for="number"><?php echo $form_labels['form']['number']; ?><em class="obrigatorio"> *</em></label>
              <input type="text" value="<?php echo $customer['addressnumber']; ?>" data-checkout="number" placeholder="<?php echo $form_labels['form']['number']; ?>" id="number"
                onkeypress="return event.charCode >= 48 && event.charCode <= 57"  class="form-control-mine">
            </div>
          </div>
          <span class="erro_febraban" data-main="#address" id="error_address"><?php echo $form_labels['error']['FEB004']; ?></span>
          <span class="erro_febraban" data-main="#number" id="error_number"><?php echo $form_labels['error']['FEB005']; ?></span>
          <div class="form-row">
            <div class="form-col-4">
              <label for="city"><?php echo $form_labels['form']['city']; ?><em class="obrigatorio"> *</em></label>
              <input type="text" value="<?php echo $customer['city']; ?>" data-checkout="city" placeholder="<?php echo $form_labels['form']['city']; ?>" id="city" class="form-control-mine">
            </div>
            <div class="form-col-4">
              <label for="state"><?php echo $form_labels['form']['state']; ?><em class="obrigatorio"> *</em></label>
              <select name="state" id="state" data-checkout="state" class="form-control-mine">
                <option value=""><?php echo $form_labels['form']['select']; ?></option>
                <option value="AC" <?php if ($customer['state'] == 'AC') {echo 'selected="selected"';} ?>>Acre</option>
                <option value="AL" <?php if ($customer['state'] == 'AL') {echo 'selected="selected"';} ?>>Alagoas</option>
                <option value="AP" <?php if ($customer['state'] == 'AP') {echo 'selected="selected"';} ?>>Amapá</option>
                <option value="AM" <?php if ($customer['state'] == 'AM') {echo 'selected="selected"';} ?>>Amazonas</option>
                <option value="BA" <?php if ($customer['state'] == 'BA') {echo 'selected="selected"';} ?>>Bahia</option>
                <option value="CE" <?php if ($customer['state'] == 'CE') {echo 'selected="selected"';} ?>>Ceará</option>
                <option value="DF" <?php if ($customer['state'] == 'DF') {echo 'selected="selected"';} ?>>Distrito Federal</option>
                <option value="ES" <?php if ($customer['state'] == 'ES') {echo 'selected="selected"';} ?>>Espírito Santo</option>
                <option value="GO" <?php if ($customer['state'] == 'GO') {echo 'selected="selected"';} ?>>Goiás</option>
                <option value="MA" <?php if ($customer['state'] == 'MA') {echo 'selected="selected"';} ?>>Maranhão</option>
                <option value="MT" <?php if ($customer['state'] == 'MT') {echo 'selected="selected"';} ?>>Mato Grosso</option>
                <option value="MS" <?php if ($customer['state'] == 'MS') {echo 'selected="selected"';} ?>>Mato Grosso do Sul</option>
                <option value="MG" <?php if ($customer['state'] == 'MG') {echo 'selected="selected"';} ?>>Minas Gerais</option>
                <option value="PA" <?php if ($customer['state'] == 'PA') {echo 'selected="selected"';} ?>>Pará</option>
                <option value="PB" <?php if ($customer['state'] == 'PB') {echo 'selected="selected"';} ?>>Paraíba</option>
                <option value="PR" <?php if ($customer['state'] == 'PR') {echo 'selected="selected"';} ?>>Paraná</option>
                <option value="PE" <?php if ($customer['state'] == 'PE') {echo 'selected="selected"';} ?>>Pernambuco</option>
                <option value="PI" <?php if ($customer['state'] == 'PI') {echo 'selected="selected"';} ?>>Piauí</option>
                <option value="RJ" <?php if ($customer['state'] == 'RJ') {echo 'selected="selected"';} ?>>Rio de Janeiro</option>
                <option value="RN" <?php if ($customer['state'] == 'RN') {echo 'selected="selected"';} ?>>Rio Grande do Norte</option>
                <option value="RS" <?php if ($customer['state'] == 'RS') {echo 'selected="selected"';} ?>>Rio Grande do Sul</option>
                <option value="RO" <?php if ($customer['state'] == 'RO') {echo 'selected="selected"';} ?>>Rondônia</option>
                <option value="RA" <?php if ($customer['state'] == 'RA') {echo 'selected="selected"';} ?>>Roraima</option>
                <option value="SC" <?php if ($customer['state'] == 'SC') {echo 'selected="selected"';} ?>>Santa Catarina</option>
                <option value="SP" <?php if ($customer['state'] == 'SP') {echo 'selected="selected"';} ?>>São Paulo</option>
                <option value="SE" <?php if ($customer['state'] == 'SE') {echo 'selected="selected"';} ?>>Sergipe</option>
                <option value="TO" <?php if ($customer['state'] == 'TO') {echo 'selected="selected"';} ?>>Tocantins</option>
              </select>
            </div>
            <div class="form-col-4">
              <label for="zipcode"><?php echo $form_labels['form']['zipcode']; ?><em class="obrigatorio"> *</em></label>
              <input type="text" value="<?php echo $customer['zipcode']; ?>" data-checkout="zipcode" placeholder="<?php echo $form_labels['form']['zipcode']; ?>" id="zipcode"
                onkeypress="return event.charCode >= 48 && event.charCode <= 57" class="form-control-mine">
            </div>
          </div>
          <span class="erro_febraban" data-main="#city" id="error_city"><?php echo $form_labels['error']['FEB006']; ?></span>
          <span class="erro_febraban" data-main="#state" id="error_state"><?php echo $form_labels['error']['FEB007']; ?></span>
          <span class="erro_febraban" data-main="#zipcode" id="error_zipcode"><?php echo $form_labels['error']['FEB008']; ?></span>
          <div class="form-col-12">
            <label>
              <span class="mensagem-febraban"><em class="obrigatorio">* </em><?php echo $form_labels['form']['febraban_rules']; ?></span>
            </label>
          </div>
        </div>

        <div>
          <p>
            <?php
              if ( count( $payment_methods ) > 1 ) :
                echo $form_labels['form']['issuer_selection'];
              endif;
              echo ' ' . $form_labels['form']['payment_instructions'];
            ?>&nbsp;<?php
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
                  <div class="mp-box-inputs mp-col-80">
                    <label>
                      &nbsp;
                      <img src="<?php echo $payment['secure_thumbnail']; ?>"
                      alt="<?php echo $payment['name']; ?>" />
                      &nbsp;
                      <?php echo $payment['name']; ?>
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
        </div>

        <div class="mp-box-inputs mp-line">

          <div class="mp-box-inputs mp-col-50">
            <input type="submit" id="btnSubmit" value="Pay" name="btnSubmit">
          </div>

          <!-- NOT DELETE LOADING-->
          <div class="mp-box-inputs mp-col-25">
            <div id="mp-box-loading">
            </div>
          </div>

        </div>

        <!-- utilities -->
        <div class="mp-box-inputs mp-col-100" id="mercadopago-utilities">
          <input type="hidden" id="site_id" value="<?php echo $_REQUEST['site_id']; ?>" name="mercadopago_ticket[site_id]"/>
          <input type="hidden" id="amountTicket" value="5249.99" name="mercadopago_ticket[amount]"/>
          <input type="hidden" id="campaign_idTicket" name="mercadopago_ticket[campaign_id]"/>
          <input type="hidden" id="campaignTicket" name="mercadopago_ticket[campaign]"/>
          <input type="hidden" id="discountTicket" name="mercadopago_ticket[discount]"/>
          <!-- febraban fields -->
          <input type="hidden" id="febrabanFirstname" name="mercadopago_ticket[firstname]"/>
          <input type="hidden" id="febrabanLastname" name="mercadopago_ticket[lastname]"/>
          <input type="hidden" id="febrabanDocNumber" name="mercadopago_ticket[docNumber]"/>
          <input type="hidden" id="febrabanAddress" name="mercadopago_ticket[address]"/>
          <input type="hidden" id="febrabanNumber" name="mercadopago_ticket[number]"/>
          <input type="hidden" id="febrabanCity" name="mercadopago_ticket[city]"/>
          <input type="hidden" id="febrabanState" name="mercadopago_ticket[state]"/>
          <input type="hidden" id="febrabanZipcode" name="mercadopago_ticket[zipcode]"/>
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

      MPv1Ticket.selectors.form = "#mercadopago-ticket-form-general"

      MPv1Ticket.Initialize(
        mercadopago_site_id,
        true,//mercadopago_coupon_mode == "yes",
        'discount.php',//mercadopago_discount_action_url,
        mercadopago_payer_email
      );
    </script>

    <?php include "html_test_ticket.php"; ?>

  </body>
