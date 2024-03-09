<html lang="en">
    <body>
        <!-- Required div to display cowpay checkout button inside, with any id to be passed in mount() method. ! -->
        <div id="cowpay-checkout"></div>
        <!-- Loading Cowpay.js SDK -->
        <script src="public/js/cowpay.js"></script>
    </body>
</html>      

<script>

    Cowpay.checkout("<?php echo $_SESSION['creditCard']['secret']; ?>","<?php  echo $_SESSION['creditCard']['frameCode']; ?>").mount("cowpay-checkout");
</script>