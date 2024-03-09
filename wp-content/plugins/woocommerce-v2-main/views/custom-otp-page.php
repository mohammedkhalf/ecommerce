<html lang="en">
    <body>
        <!-- Required div to display cowpay checkout button inside, with any id to be passed in mount() method. ! -->
        <div id="cowpay-checkout"></div>
        <!-- Loading Cowpay.js SDK -->
        <script src="public/js/cowpay.js"></script>
    </body>
</html>      

<script>
    Cowpay.checkout("<?php echo $options['secret']; ?>","<?php  echo $options['frameCode']; ?>").mount("cowpay-checkout");
</script>