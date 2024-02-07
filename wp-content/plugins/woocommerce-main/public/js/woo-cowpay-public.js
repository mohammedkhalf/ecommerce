jQuery(window).ready(function(){
  "use strict";
  jQuery('body').on('#place_order', function(){
    if(cowpay_data.intentionSecret && cowpay_data.frameCode){
      console.log("hello")
      Cowpay.checkout(cowpay_data.intentionSecret,cowpay_data.frameCode).mount("cowpay-checkout");
    }
  });
  
});
