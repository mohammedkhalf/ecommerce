jQuery(window).ready(function() {
  "use strict";
  jQuery('body').on('updated_checkout', function () {
    if (cowpay_data.tansaction_id) {
      Cowpay.checkout("SECRET","frameCode").mount("cowpay-checkout");
    }
  });

});