// MPv1
// Handlers Form Mercado Pago v1

( function() {

    var MPv1Ticket = {
      site_id: "",
      coupon_of_discounts: {
        discount_action_url: "",
        payer_email: "",
        default: true,
        status: false
      },
      inputs_to_create_discount: [
        "couponCodeTicket",
        "applyCouponTicket"
      ],
      selectors: {
        // coupom
        couponCode: "#couponCodeTicket",
        applyCoupon: "#applyCouponTicket",
        mpCouponApplyed: "#mpCouponApplyedTicket",
        mpCouponError: "#mpCouponErrorTicket",
        campaign_id: "#campaign_idTicket",
        campaign: "#campaignTicket",
        discount: "#discountTicket",
        // payment method and checkout
        paymentMethodId: "#paymentMethodId",
        amount: "#amountTicket",
        // form
        formCoupon: '#mercadopago-form-coupon-ticket'
      },
      text: {
        discount_info1: "You will save",
        discount_info2: "with discount from",
        discount_info3: "Total of your purchase:",
        discount_info4: "Total of your purchase with discount:",
        discount_info5: "*Uppon payment approval",
        discount_info6: "Terms and Conditions of Use",
        coupon_empty: "Please, inform your coupon code",
        apply: "Apply",
        remove: "Remove"
      },
      paths: {
        loading: "images/loading.gif",
        check: "images/check.png",
        error: "images/error.png"
      }
    }

    // === Coupon of Discounts

    MPv1Ticket.currencyIdToCurrency = function ( currency_id ) {
      if ( currency_id == "ARS" ) {
        return "$";
      } else if ( currency_id == "BRL" ) {
        return "R$";
      } else if ( currency_id == "COP" ) {
        return "$";
      } else if ( currency_id == "CLP" ) {
        return "$";
      } else if ( currency_id == "MXN" ) {
        return "$";
      } else if ( currency_id == "VEF" ) {
        return "Bs";
      } else if ( currency_id == "PEN" ) {
        return "S/";
      } else if ( currency_id == "UYU" ) {
        return "$U";
      } else {
        return "$";
      }
    }

    MPv1Ticket.checkCouponEligibility = function () {
      if ( document.querySelector( MPv1Ticket.selectors.couponCode ).value == "" ) {
        // Coupon code is empty.
          document.querySelector( MPv1Ticket.selectors.mpCouponApplyed ).style.display = "none";
        document.querySelector( MPv1Ticket.selectors.mpCouponError ).style.display = "block";
        document.querySelector( MPv1Ticket.selectors.mpCouponError ).innerHTML = MPv1Ticket.text.coupon_empty;
        MPv1Ticket.coupon_of_discounts.status = false;
        document.querySelector( MPv1Ticket.selectors.couponCode ).style.background = null;
        document.querySelector( MPv1Ticket.selectors.applyCoupon ).value = MPv1Ticket.text.apply;
        document.querySelector( MPv1Ticket.selectors.discount ).value = 0;
        // --- No cards handler ---
      } else if ( MPv1Ticket.coupon_of_discounts.status ) {
        // We already have a coupon set, so we remove it.
          document.querySelector( MPv1Ticket.selectors.mpCouponApplyed ).style.display = "none";
        document.querySelector( MPv1Ticket.selectors.mpCouponError ).style.display = "none";
        MPv1Ticket.coupon_of_discounts.status = false;
        document.querySelector( MPv1Ticket.selectors.applyCoupon ).style.background = null;
        document.querySelector( MPv1Ticket.selectors.applyCoupon ).value = MPv1Ticket.text.apply;
        document.querySelector( MPv1Ticket.selectors.couponCode ).value = "";
        document.querySelector( MPv1Ticket.selectors.couponCode ).style.background = null;
        document.querySelector( MPv1Ticket.selectors.discount ).value = 0;
        // --- No cards handler ---
      } else {
        // Set loading.
        document.querySelector( MPv1Ticket.selectors.mpCouponApplyed ).style.display = "none";
        document.querySelector( MPv1Ticket.selectors.mpCouponError ).style.display = "none";
        document.querySelector( MPv1Ticket.selectors.couponCode ).style.background = "url(" + MPv1Ticket.paths.loading + ") 98% 50% no-repeat #fff";
        document.querySelector( MPv1Ticket.selectors.applyCoupon ).disabled = true;

        // Check if there are params in the url.
        var url = MPv1Ticket.coupon_of_discounts.discount_action_url;
        var sp = "?";
        if ( url.indexOf( "?" ) >= 0 ) {
          sp = "&";
        }
        url += sp + "site_id=" + MPv1Ticket.site_id;
        url += "&coupon_id=" + document.querySelector( MPv1Ticket.selectors.couponCode ).value;
        url += "&amount=" + document.querySelector( MPv1Ticket.selectors.amount ).value;
        url += "&payer=" + MPv1Ticket.coupon_of_discounts.payer_email;
        //url += "&payer=" + document.getElementById( "billing_email" ).value;

        MPv1Ticket.AJAX({
          url: url,
          method : "GET",
          timeout : 5000,
          error: function() {
            // Request failed.
            document.querySelector( MPv1Ticket.selectors.mpCouponApplyed ).style.display = "none";
            document.querySelector( MPv1Ticket.selectors.mpCouponError ).style.display = "none";
            MPv1Ticket.coupon_of_discounts.status = false;
            document.querySelector( MPv1Ticket.selectors.applyCoupon ).style.background = null;
            document.querySelector( MPv1Ticket.selectors.applyCoupon ).value = MPv1Ticket.text.apply;
            document.querySelector( MPv1Ticket.selectors.couponCode ).value = "";
            document.querySelector( MPv1Ticket.selectors.couponCode ).style.background = null;
            document.querySelector( MPv1Ticket.selectors.discount ).value = 0;
            // --- No cards handler ---
          },
          success : function ( status, response ) {
            if ( response.status == 200 ) {
              document.querySelector( MPv1Ticket.selectors.mpCouponApplyed ).style.display =
                "block";
              document.querySelector( MPv1Ticket.selectors.discount ).value =
                response.response.coupon_amount;
              document.querySelector( MPv1Ticket.selectors.mpCouponApplyed ).innerHTML =
                //"<div style='border-style: solid; border-width:thin; " +
                //"border-color: #009EE3; padding: 8px 8px 8px 8px; margin-top: 4px;'>" +
                MPv1Ticket.text.discount_info1 + " <strong>" +
                MPv1Ticket.currencyIdToCurrency( response.response.currency_id ) + " " +
                Math.round( response.response.coupon_amount * 100 ) / 100 +
                "</strong> " + MPv1Ticket.text.discount_info2 + " " +
                response.response.name + ".<br>" + MPv1Ticket.text.discount_info3 + " <strong>" +
                MPv1Ticket.currencyIdToCurrency( response.response.currency_id ) + " " +
                Math.round( MPv1Ticket.getAmountWithoutDiscount() * 100 ) / 100 +
                "</strong><br>" + MPv1Ticket.text.discount_info4 + " <strong>" +
                MPv1Ticket.currencyIdToCurrency( response.response.currency_id ) + " " +
                Math.round( MPv1Ticket.getAmount() * 100 ) / 100 + "*</strong><br>" +
                "<i>" + MPv1Ticket.text.discount_info5 + "</i><br>" +
                "<a href='https://api.mercadolibre.com/campaigns/" +
                response.response.id +
                "/terms_and_conditions?format_type=html' target='_blank'>" +
                MPv1Ticket.text.discount_info6 + "</a>";
              document.querySelector( MPv1Ticket.selectors.mpCouponError ).style.display =
                "none";
              MPv1Ticket.coupon_of_discounts.status = true;
              document.querySelector( MPv1Ticket.selectors.couponCode ).style.background =
                null;
              document.querySelector( MPv1Ticket.selectors.couponCode ).style.background =
                "url(" + MPv1Ticket.paths.check + ") 98% 50% no-repeat #fff";
              document.querySelector( MPv1Ticket.selectors.applyCoupon ).value =
                MPv1Ticket.text.remove;
              // --- No cards handler ---
              document.querySelector( MPv1Ticket.selectors.campaign_id ).value =
                response.response.id;
              document.querySelector( MPv1Ticket.selectors.campaign ).value =
                response.response.name;
            } else if ( response.status == 400 || response.status == 404 ) {
              document.querySelector( MPv1Ticket.selectors.mpCouponApplyed ).style.display = "none";
              document.querySelector( MPv1Ticket.selectors.mpCouponError ).style.display = "block";
              document.querySelector( MPv1Ticket.selectors.mpCouponError ).innerHTML = response.response.message;
              MPv1Ticket.coupon_of_discounts.status = false;
              document.querySelector(MPv1Ticket.selectors.couponCode).style.background = null;
              document.querySelector( MPv1Ticket.selectors.couponCode ).style.background = "url(" + MPv1Ticket.paths.error + ") 98% 50% no-repeat #fff";
              document.querySelector( MPv1Ticket.selectors.applyCoupon ).value = MPv1Ticket.text.apply;
              document.querySelector( MPv1Ticket.selectors.discount ).value = 0;
              // --- No cards handler ---
            }
            document.querySelector( MPv1Ticket.selectors.applyCoupon ).disabled = false;
          }
        });
      }
    }

    // === Initialization function

    MPv1Ticket.addListenerEvent = function( el, eventName, handler ) {
      if ( el.addEventListener ) {
        el.addEventListener( eventName, handler );
      } else {
        el.attachEvent( "on" + eventName, function() {
          handler.call( el );
        } );
      }
    };

    /*
    *
    * Utilities
    *
    */

    MPv1Ticket.referer = (function () {
      var referer = window.location.protocol + "//" +
        window.location.hostname + ( window.location.port ? ":" + window.location.port: "" );
      return referer;
    })();

    MPv1Ticket.AJAX = function( options ) {
      var useXDomain = !!window.XDomainRequest;
      var req = useXDomain ? new XDomainRequest() : new XMLHttpRequest()
      var data;
      options.url += ( options.url.indexOf( "?" ) >= 0 ? "&" : "?" ) + "referer=" + escape( MPv1Ticket.referer );
      options.requestedMethod = options.method;
      if ( useXDomain && options.method == "PUT" ) {
        options.method = "POST";
        options.url += "&_method=PUT";
      }
      req.open( options.method, options.url, true );
      req.timeout = options.timeout || 1000;
      if ( window.XDomainRequest ) {
        req.onload = function() {
          data = JSON.parse( req.responseText );
          if ( typeof options.success === "function" ) {
            options.success( options.requestedMethod === "POST" ? 201 : 200, data );
          }
        };
        req.onerror = req.ontimeout = function() {
          if ( typeof options.error === "function" ) {
            options.error( 400, {
              user_agent:window.navigator.userAgent, error : "bad_request", cause:[]
            });
          }
        };
        req.onprogress = function() {};
      } else {
        req.setRequestHeader( "Accept", "application/json" );
        if ( options.contentType ) {
          req.setRequestHeader( "Content-Type", options.contentType );
        } else {
          req.setRequestHeader( "Content-Type", "application/json" );
        }
        req.onreadystatechange = function() {
          if ( this.readyState === 4 ) {
            if ( this.status >= 200 && this.status < 400 ) {
              // Success!
              data = JSON.parse( this.responseText );
              if ( typeof options.success === "function" ) {
                options.success( this.status, data );
              }
            } else if ( this.status >= 400 ) {
              data = JSON.parse( this.responseText );
              if ( typeof options.error === "function" ) {
                options.error( this.status, data );
              }
            } else if ( typeof options.error === "function" ) {
              options.error( 503, {} );
            }
          }
        };
      }
      if ( options.method === "GET" || options.data == null || options.data == undefined ) {
        req.send();
      } else {
        req.send( JSON.stringify( options.data ) );
      }
    }

    MPv1Ticket.Initialize = function( site_id, coupon_mode, discount_action_url, payer_email ) {

      // Sets.
      MPv1Ticket.site_id = site_id;
      MPv1Ticket.coupon_of_discounts.default = coupon_mode;
      MPv1Ticket.coupon_of_discounts.discount_action_url = discount_action_url;
      MPv1Ticket.coupon_of_discounts.payer_email = payer_email;

      // Flow coupon of discounts.
      if ( MPv1Ticket.coupon_of_discounts.default ) {
        MPv1Ticket.addListenerEvent(
          document.querySelector( MPv1Ticket.selectors.applyCoupon ),
          "click",
          MPv1Ticket.checkCouponEligibility
        );
      } else {
        document.querySelector( MPv1Ticket.selectors.formCoupon ).style.display = "none";
      }

      return;

    }

    this.MPv1Ticket = MPv1Ticket;

  } ).call();
