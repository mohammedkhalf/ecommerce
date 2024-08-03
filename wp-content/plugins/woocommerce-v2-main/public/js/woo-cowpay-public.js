jQuery(window).ready(function() {
  "use strict";
  jQuery('body').on('updated_checkout', function () {
      Cowpay.checkout("5593fafe-11f3-46d1-bb48-56b1242ee267","584fc843-b6b3-466c-b05b-cfd01fb0af28").mount("cowpay-checkout");
  });

});