<html lang="en">
    <body>
        <!-- Required div to display cowpay checkout button inside, with any id to be passed in mount() method. ! -->
        <div id="cowpay-checkout"></div>
        <!-- Loading Cowpay.js SDK -->
        <script src="public/js/cowpay.js"></script>
    </body>
</html>      

<script>
    Cowpay.checkout("83b3b99c-0655-4d4a-a6f8-01c2cce6aff1","584fc843-b6b3-466c-b05b-cfd01fb0af28").mount("cowpay-checkout");
</script>