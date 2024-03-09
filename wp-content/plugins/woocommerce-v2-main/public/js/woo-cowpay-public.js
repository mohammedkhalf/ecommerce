jQuery(window).ready(function() {
  "use strict";
  jQuery('body').on('updated_checkout', function () {
      Cowpay.checkout(cowpay_data.intentionSecret,cowpay_data.frameCode).mount("cowpay-checkout");
  });

});